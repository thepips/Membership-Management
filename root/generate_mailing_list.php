<?php
/**
* @ignore
*/
function xlsBOF() {
    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
    return;
}

function xlsEOF() {
    echo pack("ss", 0x0A, 0x00);
    return;
}

function xlsWriteNumber($Row, $Col, $Value) {
    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
    echo pack("d", $Value);
    return;
}

function xlsWriteLabel($Row, $Col, $Value ) {
    $L = strlen($Value);
    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    echo $Value;
return;
} 

# Original PHP code by Chirp Internet: www.chirp.com.au 
# Please acknowledge use of this code by including this header.
function cleanData(&$str)
{
	$str = preg_replace("/\t/", "\\t", $str);
	$str = preg_replace("/\r?\n/", "\\n", $str);
}

/**
 * @author Action Replay
 * @copyright 2010
 */

	define('IN_PHPBB', true); // we tell the page that it is going to be using phpBB, this is important.
	$phpbb_root_path = './'; // See phpbb_root_path documentation
	$phpEx = substr(strrchr(__FILE__, '.'), 1); // Set the File extension for page-wide usage.
	include($phpbb_root_path . 'common.' . $phpEx); // include the common.php file, this is important, especially for database connects.
	
// Start session management -- This will begin the session for the user browsing this page.

    // Start session management
    $user->session_begin();
    $auth->acl($user->data);
    $user->setup(); 
    if ($user->data['user_id'] == ANONYMOUS)
    {
        login_box('', $user->lang['LOGIN']);
    } 
	$filename = "mailing_list_" . date('Ymd') . ".xls";
	
	// Send Header

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");;
	header("Content-Disposition: attachment;filename=\"$filename\""); 
	header("Content-Transfer-Encoding: binary ");
	
	xlsBOF();
	xlsWriteLabel(0,0,"User Id");
	xlsWriteLabel(0,1,"Name");
	xlsWriteLabel(0,2,"Address1");
	xlsWriteLabel(0,3,"Address2");
	xlsWriteLabel(0,4,"Address3");
	xlsWriteLabel(0,5,"Address4");
	xlsWriteLabel(0,6,"Postcode");
	$xlsRow = 1;
	
	$sql = 'SELECT ' . USERS_TABLE . '.user_id AS userid, pf_realname, pf_address, pf_postcode, pf_renewaldate FROM ' . USERS_TABLE . ' LEFT JOIN ' . PROFILE_FIELDS_DATA_TABLE . ' ON ' . USERS_TABLE . '.user_id = ' . PROFILE_FIELDS_DATA_TABLE . '.user_id LEFT JOIN '. USER_GROUP_TABLE  . ' ON ' . USERS_TABLE . '.user_id = ' . USER_GROUP_TABLE . '.user_id WHERE ' . USERS_TABLE . '.user_type<>2 AND ' . USER_GROUP_TABLE . '.group_id=8 AND pf_renewaldate > 0' ;
	
	$result = $db->sql_query($sql);
	$address_lines = array();
	
//	$xlsRow = 4;
	while ($row = $db->sql_fetchrow($result))
	{
		$address_lines = split(chr(10), $row['pf_address']);
		xlsWriteNumber($xlsRow,0,$row['userid']);
		xlsWriteLabel($xlsRow,1,$row['pf_realname']);
		xlsWriteLabel($xlsRow,2,trim($address_lines[0]));
		xlsWriteLabel($xlsRow,3,trim($address_lines[1]));
		xlsWriteLabel($xlsRow,4,trim($address_lines[2]));
		xlsWriteLabel($xlsRow,5,trim($address_lines[3]));
		xlsWriteLabel($xlsRow,6,$row['pf_postcode']);
		$xlsRow++;
	}
	xlsEOF();
	exit();
?>