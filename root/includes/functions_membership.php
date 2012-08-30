<?php

if (!defined('IN_PHPBB'))
{
	exit;
}
function __autoload($Class)
{
	$phpbb_root_path = './includes/'; // See phpbb_root_path documentation
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	$filename = $phpbb_root_path . $Class . '.' . $phpEx;
	if (file_exists($filename))
	{
		include($filename);  // include the class file
	}
}

function log_message ($msg, $userid)
{
	global $db;
	$sql_array = array(
		'SELECT'		=> 'u.username_clean, g.group_name',
		'FROM'			=> array(
			MEMBERSHIP_TABLE=> 'm',
		),
		'LEFT_JOIN' 	=> array(
			array(
				'FROM'  => array(USERS_TABLE => 'u'),
				'ON'	=> 'u.user_id = m.user_id'
			),
			array(
				'FROM'  => array(GROUPS_TABLE => 'g'),
				'ON'	=> 'g.group_id = m.group_id'
			),
		),
		'WHERE'			=>  'm.user_id = '. $userid,
	);
	$sql		= $db->sql_build_query('SELECT', $sql_array);
	$result		= $db->sql_query($sql);
	$bitresult	= $db->sql_fetchrow($result);
	$user_name	= $bitresult['username_clean'];
	$group_name	= $bitresult['group_name'];
	add_log('admin', $msg, $user_name, $group_name);
}

function mark_approved ($userid)
{
	global $db;

	$sql = 'UPDATE ' . USER_GROUP_TABLE . ' u INNER JOIN ' . MEMBERSHIP_TABLE . " m USING (user_id, group_id) SET user_pending='0' WHERE m.user_id ={$userid}";
	$db->sql_query($sql);	
	log_message('LOG_USER_GROUP_APPROVED', $userid);
}

function mark_paid ($userid, $renewal_date='')
{
	global $db, $config;
	$sql_ary = array(
		'uncleared'		=> 0,
		'datepaid'		=> time(),
		'remindercount'	=> 0,
		'reminderdate'	=> 0,
		'remindertype'	=> 0,
		'renewal_date'	=> $renew_until_date,
	);
	if ($renewal_date!='')
	{
		$sql_ary['renewal_date'] = $renewal_date;
	}
	update_membership($userid, $sql_ary);
	if ($config['ms_approval_required']==0)
	{
		$sql = 'SELECT user_pending from ' . USER_GROUP_TABLE . ' ug INNER JOIN ' . MEMBERSHIP_TABLE . " m ON user_id, group_id) WHERE m.user_id ={$userid}";
		$db->sql_query($sql);
		$pending = $db->sql_fetchfield('user_pending');
		if ($pending)
		{
			mark_approved($userid);
		}			
	}
	log_message('LOG_USER_GROUP_PAID', $userid);
}

function process_payment($userid, $uncleared, $billing='9')
{
	global $db, $config;
	
	$sql= 'SELECT m.*, ug.user_pending, ug.user_id as user_group_user_id 
		FROM ' . MEMBERSHIP_TABLE . ' AS m
		LEFT JOIN ' . USER_GROUP_TABLE . ' AS ug USING (group_id, user_id) ' .
		"WHERE m.user_id = {$userid}";
	$result		= $db->sql_query($sql);
	$row		= $db->sql_fetchrow($result);

	if ($uncleared) // Cleared payment
	{
		log_message('LOG_USER_GROUP_PAYMENT_SENT', $userid,$config['ms_billing_cycle'.$billing.'_group']);
	}
	else
	{
		log_message('LOG_USER_GROUP_PAID', $userid,$row['group_id']);
	}

	if (empty($row) || empty($row['user_group_user_id']))
	{
		// Can't be a member
		$is_member	= FALSE; 
		$pending	= $config['ms_approval_required'];
		$groupid	= $config['ms_billing_cycle'.$billing.'_group'];
		$renewal_date=calculate_start_date();
		group_user_add($groupid,$userid,null,null,$config['ms_default_group'],null,$pending);
		log_message('LOG_USER_GROUP_JOINED', $userid,$groupid);
	}
	else
	{
		$is_member	= TRUE;
		$billing	= $row['billing'];
		$pending	= $row['user_pending'];
		$groupid	= $row['group_id'];
		$renewal_date = $row['renewal_date'];
		log_message('LOG_USER_GROUP_RENEWED', $userid,$groupid);
		if ($pending && $config['ms_approval_required']==0)
		{
			mark_approved($userid, $row['group_id']);
		}			
	}

	$next_renewal_date = calc_date($config['ms_billing_cycle'.$billing], $config['ms_billing_cycle'.$billing.'_basis'], $renewal_date);
	$sql_ary = array(
		'remindercount'		=> '0', 
		'reminderdate'		=> '0',
		'remindertype'		=> '0',
		'uncleared'			=> $uncleared,
		'prev_renewal_date'	=> '0',
		'datepaid'			=> time(),
		'group_id'			=> $groupid,
		'billing'			=> $billing,
	);
	if ((!$uncleared) || (!$config['ms_process_on_payment'])) // Cleared payment
	{
		$sql_ary['renewal_date']		= $next_renewal_date;
		$sql_ary['prev_renewal_date']	= $renewal_date;
	}

	update_membership($userid, $sql_ary);

	if ($config['ms_rank'])
	{	
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_rank ={$config['ms_rank']} 
			WHERE user_id ={$userid}";
		$db->sql_query($sql);		
	}
//	if ($config['ms_process_on_payment']) // Don't process until payment has cleared
//	{
//		$sql = 'UPDATE ' . MEMBERSHIP_TABLE . " 
//			SET billing = '{$billing}', uncleared = 1, datepaid = " . time() . "	
//			WHERE user_id = {$userid}";
//		$result =$db->sql_query($sql);
//	}
//
}

function display_subscription_message($userid, $type='')
{
	global $db, $user, $phpbb_root_path, $phpEx, $config;
	
	if (empty($config['ms_enable_membership']))
	{
		return(null);
	}
	// 1st check to see if this user is already set up as an associate

	$sql_array = array(
		'SELECT'	=> 'm.membership_no',
		'FROM'  	=> array(MEMBERSHIP_TABLE => 'm'),
		'WHERE'		=> "m.associate_id = {$userid}"
	);

	$sql			= $db->sql_build_query('SELECT', $sql_array);
	$result			= $db->sql_query($sql);
	$row			= $db->sql_fetchrow($result);

	if (!empty($row['membership_no']))
	{
		return array(
			'IS_ASSOCIATE' => true,
			'IS_MEMBER'	=> true,
			'MEMBERSHIP_NO' => $row['membership_no']);
	}

	// Check if userid is already in premium group
	$sql= 'SELECT m.*, ug.user_pending, ug.user_id as user_group_user_id 
		FROM ' . MEMBERSHIP_TABLE . ' AS m
		LEFT JOIN ' . USER_GROUP_TABLE . ' AS ug USING (group_id, user_id) ' .
		"WHERE m.user_id = {$userid}";
			
	$result				= $db->sql_query($sql);
	$row				= $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (is_null($row))
	{
		return(null);
	}

	if (empty($row['user_group_user_id']))
	{
		$is_member	= $is_pending = FALSE;
	}
	else
	{
		$is_member	= TRUE;
		$is_pending	= $row['user_pending'];
	}
	
	$renewal_date	= $user->format_date($row['renewal_date'],$config['ms_membership_date_format']);

	$associate_name='';
	if (!empty($row['associate_id']))
	{
		$sql			= 'SELECT username_clean FROM ' . USERS_TABLE . " WHERE user_id = {$row['associate_id']}";
		$result			= $db->sql_query($sql);
		$associate_name	= $db->sql_fetchfield('username_clean');
		$db->sql_freeresult($result);
	}
	$result=(array(
		'MEMBERSHIP_PENDING'	=> $is_pending,
		'MEMBERSHIP_NO'			=> $row['membership_no'],
		'ALLOW_ASSOCIATES'  	=> $config['ms_allow_associate']==1,
		'IS_MEMBER'				=> $is_member,
		'UNCLEARED'				=> $row['uncleared'],
		'PAYMENT_MESSAGE'		=> sprintf($user->lang['PAYMENT_PENDING'], $user->format_date($row['datepaid'],$config['ms_membership_date_format'])),
		'RENEWAL_DATE'			=> $user->format_date($renewal_date,$config['ms_membership_date_format']),
		'RENEWAL_ACTION'		=> append_sid("{$phpbb_root_path}application.$phpEx","mode=renew&i={$userid}"),
		'MEMBERSHIP_NO'			=> $row['membership_no'],						
		'CANCEL_SUB_ACTION' 	=> append_sid("{$phpbb_root_path}application.$phpEx","mode=cancel&i={$userid}"),
		'ASSOCIATE'				=> $associate_name,
		'SUBSCRIBER'			=> !empty($row['subscriber_id']),
		'S_ACTION'				=> append_sid("{$phpbb_root_path}application.$phpEx","mode=renew&member={$is_member}&i={$userid}&ref={$row['membership_no']}"),
	));
	if (empty($type) || $row['remindertype']>0)
	{
			$result['RENEWAL_MESSAGE']= sprintf($user->lang['RENEWAL_PROMPT_'.$row['remindertype']], $renewal_date);
			$result['reminder_type']=$row['remindertype'];
	}
	return $result;
}
function calculate_start_date()
{
	global $config;
	$month	= date('n');
	$day	= date('j');

	switch ($config['ms_period_start'])
	{
		case '-1':  // Always start on 1st of month
			$day = 1;
		break;
		case '0';
		break;
		case '1':	// Start on 1st of next month unless it's the first
			if ($day>1)
			{
				$month = $month+1;
			}
			$day=1;
		break;
		case '2':	// start on 1st of this month or next month whichever is closer
			if (round($day)>date('t')/2)
			{
				$month = $month+1;
			}
			$day = 1;
		break;
	}
	return mktime(0,0,0,$month,$day);
}

function calc_date($billing_cycle=1, $billing_cycle_basis='y', $date=0)
// Default settings are to use the current date and a renewal period of 1 year
{
	if ($date==0)
	{
		$date = time();
	}
	$days = $months = $years = 0;
	switch ($billing_cycle_basis)
	{
		case 'd':
		{
			$days = $billing_cycle;
		}
		break;
		
		case 'w':
		{
			$days = $billing_cycle * 7;
		}
		break;

		case 'm':
		{
			$months = $billing_cycle;
		}
		break;
		
		case 'y':
		{
			$years = $billing_cycle;
		}
		break;
	}
	$return_date = mktime(0,0,0,date('m',$date)+$months, date('d',$date)+$days, date('Y',$date)+$years);
	return ($return_date);
}


function set_renewal_date($userid, $renew_until_date)
{
	$sql_ary = array(
		'remindercount'	=> 0,
		'reminderdate'	=> 0,
		'remindertype'	=> 0,
		'renewal_date'	=> $renew_until_date,
	);
	update_membership($userid, $sql_ary);
}

function update_membership($userid, $sql_ary)
{
	global $db;

	$sql	= 'SELECT COUNT(*) as count FROM ' . MEMBERSHIP_TABLE . " WHERE user_id = {$userid}";
	$result = $db->sql_query($sql);
	$row	= $db->sql_fetchrow($result);
	if ($row['count'] == 0)
	{
		$db->sql_query('INSERT ' . MEMBERSHIP_TABLE . ' ' . $db->sql_build_array('INSERT', array_merge(
			array(
				'user_id'			=> $userid,
				'prev_renewal_date'	=> 0,
				'date_joined'		=> time(),
			),
			$sql_ary
		)));
		$result = $db->sql_query($sql);
	}		
	else
	{
		$sql 	= 'UPDATE ' . MEMBERSHIP_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE user_id = {$userid}";
		$db->sql_query($sql);
	}		
}

function change_premium_group($userid, $new_group)
{
	global $db;
	$sql		= 'SELECT COUNT(*) as ug_count FROM ' . USER_GROUP_TABLE . " WHERE user_id = {$userid} AND group_id = {$new_group}";
	$result 	= $db->sql_query($sql);
	$ug_count	= $db->sql_fetchfield('count');

	if ($ug_count == 0)
	{
		// Isn't in this group so it's safe to modify the group record
		$sql = 'UPDATE ' . USER_GROUP_TABLE . ' ug JOIN ' . MEMBERSHIP_TABLE . " m USING (user_id, group_id) SET group_id={$new_group} WHERE user_id = {$userid} AND group_id = {$new_group}";
		$result = $db->sql_query($sql);
	}

	update_membership($userid, array('group_id'	=> $new_group));
}

/**
* Lists members
*/
function view_members(&$users, &$user_count, $limit = 0, $offset = 0, $sql_where = '', $sort_by = '')
{
	global $db, $user, $config;

	$sql_array = array(
		'SELECT'		=> 'count(u.user_id) AS user_count',
		'FROM'			=> array(
			USERS_TABLE	=> 'u',
		),
		'LEFT_JOIN' 	=> array(
			array(
				'FROM'  => array(MEMBERSHIP_TABLE => 'm'),
				'ON'	=> 'm.user_id = u.user_id'
			),
			array(
				'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
				'ON'	=> 'pfd.user_id = u.user_id'
			),
			array(
				'FROM'  => array(USER_GROUP_TABLE => 'ug'),
				'ON'	=> 'ug.user_id = m.user_id AND ug.group_id=m.group_id'
			),
			array(
				'FROM'  => array(GROUPS_TABLE => 'g'),
				'ON'	=> 'g.group_id=m.group_id'
			),
		),
		'WHERE'			=> 'user_type=' . USER_NORMAL . $sql_where,
		'ORDER_BY'		=> $sort_by,
	);
	$sql	= $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);

	$user_count = (int) $db->sql_fetchfield('user_count');
	$db->sql_freeresult($result);

	if ($offset >= $user_count)
	{
		$offset = ($offset - $limit < 0) ? 0 : $offset - $limit;
	}

	$sql_array['SELECT'] = 'u.user_id, user_colour, username, user_lastvisit, user_posts, m.reminderdate, m.remindercount, m.date_joined, pfd.*, m.renewal_date, ug.user_id as in_group, m.user_id as in_membership, g.group_name';

	$sql=$db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query_limit($sql, $limit, $offset);

	while ($row = $db->sql_fetchrow($result))
	{
		$users[] = $row;
	}
	$db->sql_freeresult($result);
	return $offset;
}
function list_cpf()
{
	global $db, $user;
	$output = array();
	$lang_id = $user->get_iso_lang_id();
	
	$sql = 'SELECT l.*, f.*
		FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
		WHERE f.field_active = 1
			AND field_ident LIKE 'ms_%'
			AND l.lang_id = {$lang_id}
			AND l.field_id = f.field_id
		ORDER BY f.field_order";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$output[]=$row;
	}
	return $output;
}
/**
* Select subscription period and charge
*/
function present_billing_cycle()
{
	global $db,$user, $config, $template;

	$period_types = array('d' => 'DAY', 'w' => 'WEEK', 'm' => 'MONTH', 'y' => 'YEAR');
	
	for ($i=1; $i<6; $i++)
	{
		$key			= 'ms_billing_cycle'.$i;
		if ($config[$key]!=0)
		{
			$period_basis	= $key.'_basis';
			$period_charge  = $key.'_amount';
			$period_group	= $key.'_group';
			if ($config[$period_charge]==0)
			{
				$money	=  $user->lang['DONATION'];
			}
			else
			{
				$money = currency_format($config[$period_charge]);
			}

			$sql = 'SELECT group_name FROM ' . GROUPS_TABLE . " WHERE group_id = $config[$period_group]";
			$result = $db->sql_query($sql);

			$group_name = $db->sql_fetchfield('group_name');

			$string=sprintf($user->lang['BILLING_CYCLE_CHARGE'],$money,$config[$key], $user->lang[$config[$period_basis]],$group_name);
			$template->assign_block_vars('subscriptions', array(
				'MESSAGE'			=>$string,
				'TYPE'			=> $i
				));
		}
	}
	return;
}

// Pass the user name you want validated. The associate id is populated with the userid
function validate_associate($associate_name, &$associate_id)
{
	global $db, $user, $config;

	$error='';

	if (empty($associate_name))
	{
		// effectively means associate is being deleted
		$associate_id = 0;
	}
	else
	{
		$sql = 'SELECT u.user_id FROM ' . USERS_TABLE . " AS u WHERE u.username_clean='{$associate_name}'";
		$result =$db->sql_query($sql);
		$user_id = $db->sql_fetchfield('user_id');
		if (empty($user_id))
		{
			$error = sprintf($user->lang['ASSOCIATE_WRONG'], $associate_name);
		}
		else
		{
			if ($user_id == $associate_id)
			{
				$associate_id = -1;
			}
			else
			{
				// 3. Check if entered user name is already in use as an associate
				$sql = 'SELECT count(user_id) AS count FROM ' . USER_GROUP_TABLE . " WHERE group_id={$config['ms_subscription_group']} AND user_id={$user_id}";
				$result =$db->sql_query($sql);
				$count =$db->sql_fetchfield('count');
				if (!empty($count))
				{
					$error = sprintf($user->lang['ASSOCIATE_IN_USE'], $associate_name);
				}
				else
				{
					$associate_id=$user_id;
				}
			}
		}
	}
	return($error);
}

function process_associate($userid, $associate_id)
{
	global $config, $db;

	$sql = 'SELECT m.associate_id, m.group_id FROM ' . MEMBERSHIP_TABLE . " AS m WHERE m.user_id= {$userid}";
	$result =$db->sql_query($sql);
	$current_associate = $db->sql_fetchrow($row);

	// 4. remove current associate from group
	if (!empty($current_associate['associate_id']))
	{
		group_user_del($current_associate['group_id'],$current_associate['associate_id']);
	}
	// 5. add new associate to group
	if ($associate_id>0)
	{
		$sql_array = array(
			'SELECT'    => 'ug.user_pending',
			'FROM'  	=> array(USER_GROUP_TABLE => 'ug'),
			'WHERE'		=>  'ug.user_id = '. $userid . ' AND ug.group_id = '. $current_associate['group_id'],
		);
		$sql=$db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$pending = $db->sql_fetchfield('user_pending');
		group_user_add($row['group_id'],$associate_id,null,null,$config['ms_default_group'],null,$pending);
	}
	// 6. update membership record
	$sql_ary = array(
		'associate_id'=>$associate_id,
		);
	update_membership($userid, $sql_ary);
}
function remove_member($userid, $associate=0, $groupid=0)
{
	global $config, $db;
	if (empty($groupid))
	{
		$sql = 'SELECT group_id FROM ' . MEMBERSHIP_TABLE . " WHERE user_id = {$userid}";
		$result = $db->sql_query($sql);
		$groupid = $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);
	}

	if ($associate>0)
	{
		remove_member($associate, 0 , $groupid);
	}
	$sql = 'UPDATE ' . USERS_TABLE . " SET user_rank = 0 WHERE user_id={$userid} AND user_rank = {$config['ms_rank']}";
	$result =$db->sql_query($sql);
	$db->sql_freeresult($result);
	
	$sql = 'DELETE FROM ' . MEMBERSHIP_TABLE . " WHERE user_id = {$userid}";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	group_user_del($groupid, $userid);
	return($groupid);
}

function subscription_enabled()
{
	global $config;
	$return = false;

	$results = substr_in_array(array_keys($config), 'pp_subscription_allowed_');
	foreach ($results as $result)
	{
		if ($config[$result])
		{
			$return=true;
			break;
		}
	}
	return $return;

}
function substr_in_array($haystack, $needle)
{
	$found = ARRAY();

	// cast to array
	$needle = (ARRAY) $needle;

	// map with preg_quote
	$needle = ARRAY_MAP('preg_quote', $needle);

	// loop over  array to get the search pattern
	FOREACH ($needle AS $pattern)
	{
		$found = PREG_GREP("/$pattern/", $haystack);
	}
	RETURN $found;
}
?>