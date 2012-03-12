<?php
/**
* @package cron_membership.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

    if (!function_exists('calc_date'))
    {
        include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);
    }

	$expiry_date = calc_date(-$config['ms_grace_period'], $config['ms_grace_period_basis'], time());

	$sql_array = array(
		'SELECT'    => 'u.username_clean, g.group_name, m.associate m.group_id, m.user_id',
		'FROM'      => array(
			MEMBERSHIP_TABLE=> 'm',
			),
		'LEFT_JOIN' => array(
			array(
				'FROM'  => array(USERS_TABLE => 'u'),
				'ON'    => 'u.user_id = m.user_id'
				),
			array(
				'FROM'  => array(GROUPS_TABLE => 'g'),
				'ON'    => 'g.group_id = m.group_id'
				),
			),
		'WHERE'     	=>  " renewal_date < {$expiry_date}",
		);
    $sql=$db->sql_build_query('SELECT', $sql_array);
    $result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
        $delete_members[]=$row;
    }
    foreach($delete_members as $data)
    {
        remove_member($data['group_id'],$data['user_id'],$data['associate']);
        log_message('LOG_USER_GROUP_EXPIRED', $data['username_clean'],$data['group_name']);
    }

	// Process email notifications

	// Setup dates & messages

	$due_date[1] = calc_date(-$config['ms_due_soon_period'], $config['ms_due_soon_period_basis']);	// looking for anything with a renewal date prior to today + 2m
	$due_date[2] = calc_date(-$config['ms_due_period'], $config['ms_due_period_basis']);				// looking for anything with a renewal date prior to today + 1m
	$due_date[3] = calc_date(-$config['ms_overdue_period'], $config['ms_overdue_period_basis']);		// looking for anything with a renewal date prior to today + 1d
	$due_date[4] = calc_date(-$config['ms_last_chance_period'], $config['ms_last_chance_period_basis']);// looking for anything with a renewal date prior to today - 1m
	$sql_array = array(
		'SELECT'    => 'm.*, u.user_id, u.username_clean, u.user_email, u.user_lang, u.user_jabber, u.user_notify_type, u.user_regdate, u.user_actkey, pfd.pf_realname, g.group_name',
		'FROM'      => array(
			MEMBERSHIP_TABLE=> 'm',
			),
		'LEFT_JOIN' => array(
			array(
				'FROM'  => array(USERS_TABLE => 'u'),
				'ON'    => 'u.user_id = m.user_id'
				),
			array(
				'FROM'  => array(GROUPS_TABLE => 'g'),
				'ON'    => 'g.group_id = m.group_id'
				),
			array(
				'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
				'ON'    => ('pfd.user_id = u.user_id'),
			),
		),
	);

	foreach($due_date as $key => $action_date)
	{
		$sql_array['WHERE']=  " m.renewal_date <= {$action_date} AND reminder_type < {$key}";
		$sql=$db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			// Send the messages
			if (!class_exists('messenger'))
			{
				include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
			}

			$messenger = new messenger();
			$usernames = $user_ids = array();

			do
			{
				$messenger->template('user_remind_inactive', $row['user_lang']);

				$messenger->to($row['user_email'], $row['username']);
				$messenger->im($row['user_jabber'], $row['username']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($row['username']),
					'REGISTER_DATE'	=> $user->format_date($row['user_regdate'], false, true),
				));

				$messenger->send($row['user_notify_type']);

				$usernames[] = $row['username'];
				$user_ids[] = (int) $row['user_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			$messenger->save_queue();

			// Add the remind state to the database

			foreach ($user_ids as $user_id)
			{
				$sql = 'UPDATE ' . MEMBERSHIP_TABLE . 
				' SET remindercount = remindercount + 1, reminderdate = ' . time() . ' remindertype = ' . $key . ' WHERE user_id=' . $user_id . ' AND m.group_id=' . $config['ms_subscription_group'];
				$result = $db->sql_query($sql);
				$affected_rows =$db->sql_affectedrows();
				if ($affected_rows == 0)
				{
					$db->sql_query('INSERT ' . MEMBERSHIP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'user_id'			=> $user_id,
					'group_id'			=> $config['ms_subscription_group'],
					'remindercount'		=> 1,
					'reminderdate'		=> time(),
					'remindertype'		=> $key,
					)));
				}
			}
			$result = $db->sql_query($sql);
			

			add_log('admin', 'LOG_MEMBERSHIP_DUE_REMIND', implode(', ', $usernames));
			unset($usernames);
		}
	}
	$db->sql_freeresult($result);
?>