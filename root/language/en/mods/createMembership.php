<?php
/**
 *
 * @author DougA (Doug Antill) doug@action-replay.co.uk
 * @version $Id$
 * @copyright (c) 2011 Doug Antill
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */


/**
 * @ignore
 */
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();


if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Membership Management';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'memberman_version';


// The language file which will be included when installing
$language_file = 'mods/application';


/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	'0.0.4'	=> array(
		'config_add' => array(
			array('ms_enable_membership', '1', 0),
        )
    ),
	'0.0.3'	=> array(
		// Now to add some permission settings
      	'permission_add' => array(
			array('a_approve_application', true),
			array('a_mark_paid', true),
		),
		'permission_set' => array(
			// Global Role permissions
			array('ROLE_ADMIN_FULL', 'a_approve_application'),
			array('ROLE_ADMIN_FULL', 'a_mark_paid'),
	   )
    ),
	'0.0.2' => array(
        'table_add' =>array(
            array(IPN_LOG_TABLE, array(
                'COLUMNS'       => array(
					'ipn_id' => array('UINT:10', NULL, 'auto_increment'),
					'txn_id' => array('VCHAR:20', ''),
                    'creation_timestamp'=> array('TIMESTAMP', 0),
                    'ipn_data'          => array('TEXT_UNI', ''),
                ),
                'PRIMARY_KEY'    => array('ipn_id')
            )
        )
     ),
   ),
	'0.0.1' => array(
        'table_add' =>array(
            array($table_prefix . 'membership', array(
                'COLUMNS'               => array(
                    'membership_no'     => array('UINT', NULL, 'auto_increment'),
                    'group_id'          => array('INT:11','0'),
                    'user_id'           => array('INT:11','0'),
        			'renewal_date'      => array('INT:11', '0'),
        			'datepaid'          => array('INT:11', '0'),
        			'uncleared'         => array('BOOL', '0'),
                    'portal'            => array('VCHAR:20', ''),
                    'subscriber_id'     => array('VCHAR:20', ''),
                    'txn_id'            => array('VCHAR:20', ''),
                    'reminderdate'      => array('INT:11', '0'),
                    'remindercount'     => array('TINT:2', '0'),
                    'remindertype'      => array('TINT:4','0'),
                ),
                'PRIMARY_KEY'    => array('membership_no'),
            ))),
		'config_add' => array(
			array('ms_application_forum', '2', 0),
			array('ms_approval_required', '1', 0),
			array('ms_group_join_amount', '5', 0),
			array('ms_membership_date_format', 'D M d, Y', 0),
			array('ms_subscription_extra_days', '30', 0),
			array('ms_subscription_group', '5', 0),
			array('ms_due_soon_period', '2', 0),
			array('ms_due_soon_period_basis', 'm', 0),
			array('ms_due_period', '2', 0),
			array('ms_due_period_basis', 'm', 0),
			array('ms_overdue_period', '2', 0),
			array('ms_overdue_period_basis', 'm', 0),
			array('ms_last_chance_period', '2', 0),
			array('ms_last_chance_period_basis', 'm', 0),
			array('ms_grace_period', '2', 0),
			array('ms_grace_period_basis', 'm', 0),
			array('ms_billing_cycle1', '1', 0),
			array('ms_billing_cycle1_basis', 'y', 0),
			array('ms_billing_cycle1_amount', '25', 0),
			array('ms_billing_cycle2', '1', 0),
			array('ms_billing_cycle2_basis', 'm', 0),
			array('ms_billing_cycle2_amount', '4', 0),
			array('ms_billing_cycle3', '3', 0),
			array('ms_billing_cycle3_basis', 'm', 0),
			array('ms_billing_cycle3_amount', '10', 0),
			array('ms_billing_cycle4', '0', 0),
			array('ms_billing_cycle4_basis', 'y', 0),
			array('ms_billing_cycle4_amount', '0', 0),
			array('ms_billing_cycle5', '0', 0),
			array('ms_billing_cycle5_basis', 'y', 0),
			array('ms_billing_cycle5_amount', '0', 0),
			array('pp_subscription_allowed_cheque', '0', 0),
			array('pp_payment_method_cheque', '0', 0),
			array('pp_subscription_allowed_eft', '0', 0),
			array('pp_payment_method_eft', '0', 0),
			array('pp_subscription_allowed_paypal', '0', 1),
			array('pp_payment_method_paypal', '0', 0),
			array('pp_eft_bankname', 'Enter the name of the bank holding your account', 0),
			array('pp_cheque_address', 'Type the address you want the cheque sent to', 0),
			array('pp_eft_account', '012345678', 0),
			array('pp_eft_bankaddress', 'type in the address of your bank', 0),
			array('pp_eft_bankcode', '00-00-00', 0),
			array('pp_paypal_co_name', 'The name to appear on Paypal statement', 0),
			array('pp_paypal_currency_code', 'GBP', 0),
			array('pp_paypal_p_account', 'Your paypal email account or account no', 0),
			array('pp_paypal_use_sandbox', '1', 0),
            array('pp_paypal_API_userid', 'Your Paypal Express Checkout API userid', 0),
            array('pp_paypal_API_password', 'Your Paypal Express Checkout API Password', 0),
            array('pp_paypal_API_signature', 'Your Paypal Express Checkout API signature', 0),
            array('pp_paypal_sandbox_API_userid', 'Your Paypal Express Checkout API userid', 0),
            array('pp_paypal_sandbox_API_password', 'Your Paypal Express Checkout API Password', 0),
            array('pp_paypal_sandbox_API_signature', 'Your Paypal Express Checkout API signature', 0),
			array('membership_gc', '86400', 0),
			array('membership_last_gc', '0', true)
		),

		'module_add' => array(
		    array('acp', 'ACP_CAT_USERGROUP', 'ACP_MEMBERSHIP'),
		    array('acp', 'ACP_MEMBERSHIP', array(
		            'module_basename'       => 'membership',
		            'modes'                 => array('settings', 'list'),
		        ),
		    ),
		    array('acp', 'ACP_CAT_DOT_MODS', 'ACP_PAYMENTS'),
		    array('acp', 'ACP_PAYMENTS', array(
		            'module_basename'       => 'payments',
		        ),
		    ),
        ),
		'custom'	=> 'other_elements'
   )
);
// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

function other_elements($action, $version)
{
	global $db, $table_prefix, $umil;

    $profile_fields = array(
    	array('ms_realname','2','realname','40','0','40','','','.*','0','1','1','1','1','6','1','0','Your real name','First name and surname'),
    	array('ms_address','3','address','5|80','0','1000','','','.*','0','1','0','1','1','7','1','0','Postal address', 'If you are a paid-up member of the club this is the address your magazine will be posted to'),
    	array('ms_postcode','2','postcode','10','0','20','','','.*','0','1','1','1','1','8','1','0','Post Code', 'Please enter your postcode'),
    	array('ms_phone','2','phone','20','0','20','','','.*','0','1','0','1','1','9','1','0','Phone number', 'This information will not be made available unless you give permission'),
    	array('ms_mobile','2','mobile','20','0','20','','','.*','0','1','1','1','1','10','1','0','Mobile Phone', 'Please enter your mobile phone number. The number is kept private and would only be used if we needed to get in touch with you urgently. For instance if a rally was cancelled at short notice.'),
    	array('ms_vehicle', '2', 'vehicle', '20', '0', '40', '', '', '.*', '0', '1', '0', '0', '1', '3', '1', '1','Make and Model', 'Vehicle make and model'),
    	array('ms_vehicle_reg','2','vehicle_reg','10','0','10','','','[\w]+','0','1','1','1','1','5','1','0','Registration', '	(Paid up club members only) Please enter your rv registration number. This information is kept private.'),
    	array('ms_details','2','details','10','0','20','','','.*','0','1','0','1','1','4','1','0','Vehicle Details', 'Please enter the details about your vehicle'),
    	array('ms_publish','4','publish','2','0','0','0','0','','0','1','1','1','1','11','1','0','Publish Info?', 'Tick the box if your contact details can be published'),
    );


	switch ($action)
	{
		case 'install':
            $sql = 'ALTER TABLE ' . SESSIONS_TABLE . ' ADD shopping_basket TEXT NULL DEFAULT NULL';
        	$result = $db->sql_query($sql);

    		$sql = "SELECT MAX('field_order') AS max_field_order FROM " . PROFILE_FIELDS_TABLE;
    		$result=$db->sql_query($sql);
    	 	$field_order = (int) $db->sql_fetchfield('max_field_order');
    		foreach ($profile_fields as $profile)
    		{
    			$field_order++;
    			$insert_sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' (field_ident, field_type, field_name, field_length, field_minlen, field_maxlen, field_novalue, field_default_value, field_validation, field_required, field_show_on_reg, field_hide, field_no_view, field_active, field_order, field_show_profile, field_show_on_vt) VALUES ("' . $profile[0]  . '", "' . $profile[1]  . '", "' . $profile[2]  . '", "' . $profile[3]  . '", "' . $profile[4]  . '", "' . $profile[5]  . '", "' . $profile[6]  . '", "' . $profile[7]  . '", "' . $profile[8]  . '", "' . $profile[9]  . '", "' . $profile[10]  . '", "' . $profile[11]  . '", "' . $profile[12]  . '", "' . $profile[13]  . '", "' . $field_order  . '", "' . $profile[14]  . '", "' . $profile[15]  . '")';
    			$db->sql_query($insert_sql);
    			$sql = 'SELECT LAST_INSERT_ID() as a';
    			$result = $db->sql_query($sql);
    			$field_id = $db->sql_fetchfield('a');
    
    			$insert_sql = 'INSERT INTO ' . PROFILE_LANG_TABLE . ' VALUES ("' . $field_id . '", 1, "' . $profile[17]  . '", "' . $profile[18]  . '", "")';
    			
    			$db->sql_query($insert_sql);
    
    
    			$sql = 'ALTER TABLE ' . PROFILE_FIELDS_DATA_TABLE . ' ADD pf_' . $profile[0];
    
    			switch ($profile[1])
    			{
    				case 1:
    					$sql .= ' bigint(20)';
    					break;
    				case 2:
    					$sql .= ' varchar(255)';
    					break;
    				case 3:
    					$sql .= ' text';
    					break;
    				case 4:
    					$sql .= ' tinyint(2)';
    					break;
    			}
    			$db->sql_query($sql);
            }
		break;

		case 'uninstall':
            $sql = 'ALTER TABLE ' . SESSIONS_TABLE . ' DROP shopping_basket';
			$db->sql_query($sql);

			foreach ($profile_fields as $profile)
			{
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_DATA_TABLE . ' DROP COLUMN pf_' . $profile[0];
				$db->sql_query($sql);
				
				$sql = 'DELETE pl.* from ' . PROFILE_LANG_TABLE . ' AS pl right join ' . PROFILE_FIELDS_TABLE . ' AS pf ON pl.field_id=pf.field_id where pf.field_ident="' . $profile[0] . '"' ; 
				$db->sql_query($sql);

				$sql = 'DELETE pf.* from ' . PROFILE_FIELDS_TABLE . ' AS pf WHERE pf.field_ident="' . $profile[0] . '"' ; 
				$db->sql_query($sql);
			}
		break;
	}
	
	
	/**
	* Return a string
	* 	The string will be shown as the action performed (command).  It will show any SQL errors as a failure, otherwise success
	*/
	// return 'EXAMPLE_CUSTOM_FUNCTION';

	/**
	* Return an array
	* 	With the keys command and result to specify the command and the result
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	return array(
		'command'	=> 'Custom Profile Fields',
		'result'	=> 'SUCCESS',
	);
}

?>