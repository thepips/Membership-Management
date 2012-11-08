<?php

/**
* @package acp_users_membership.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);

	$user->add_lang(array('mods/application'));	

	$delete_members=request_var('delete','');
	if ($delete_members)
	{
		if (confirm_box(true))
		{
			$sql = 'SELECT group_id FROM ' . MEMBERSHIP_TABLE . ' WHERE user_id =' . $user_id;
			$result = $db->sql_query($sql);
			$group_id = $db->sql_fetchfield('group_id');
			group_user_del($group_id, $user_id);
			$sql = 'DELETE FROM ' . MEMBERSHIP_TABLE . ' WHERE user_id =' . $user_id;
			$result = $db->sql_query($sql);
			trigger_error($user->lang['USER_DELETED'] . adm_back_link($this->u_action));
		}
		else
		{
			confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
				'u'				=> $user_id,
				'i'				=> $id,
				'mode'			=> $mode,
				'action'		=> $action,
				'update'		=> true,
				'delete'		=> 1,
				))
			);
		}
	}				
	$sql_array = array(
		'SELECT'	=> 'm.*, u.username_clean',
		'FROM'	=> array(
			MEMBERSHIP_TABLE=> 'm',
			),
		'LEFT_JOIN' => array(
			array(
				'FROM'  => array(USERS_TABLE => 'u'),
				'ON'	=> 'u.user_id = m.associate_id'
			),
		),
		'WHERE'		=>  'm.user_id = '. $user_id,
	);
	$sql=$db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	
	$db->sql_freeresult($result);

	$not_member = empty($row);

	if ($not_member)
	{
		$row['membership_no']	= '';
		$row['username_clean']	= '';
		$row['renewal_date']	= strtotime('+3 month');
		$row['datepaid']		= time();
		$row['group_id']		= 0;
	}
	$data = array(
		'membership_no'		=> request_var('membership_no', $row['membership_no']),
		'group_id'			=> request_var('premium_group', $row['group_id']),
		'associate_name'	=> request_var('associate_name', $row['username_clean'].''),
	);
	$data['rday_day']		= request_var('rday_day', date('j',$row['renewal_date']));
	$data['rday_month']		= request_var('rday_month', date('n',$row['renewal_date']));
	$data['rday_year']		= request_var('rday_year', date('Y',$row['renewal_date']));
	$data['pday_day']		= request_var('pday_day', date('j',$row['datepaid']));
	$data['pday_month']		= request_var('pday_month', date('n',$row['datepaid']));
	$data['pday_year']		= request_var('pday_year', date('Y',$row['datepaid']));

	if ($submit)
	{
		$error = validate_data($data, array(
			'associate_name'=> array('string', true, 3, 20),
			'rday_day'		=> array('num', true, 1, 31),
			'rday_month'	=> array('num', true, 1, 12),
			'rday_year'		=> array('num', true, 1901, gmdate('Y', time())+30),
			'pday_day'		=> array('num', true, 1, 31),
			'pday_month'	=> array('num', true, 1, 12),
			'pday_year'		=> array('num', true, 1901, gmdate('Y', time())+10),
		));

		if ($data['membership_no'] != $row['membership_no'])
		{
			if (!empty($data['membership_no']))
			{
				$sql_array = array(
					'SELECT'	=> 'u.username_clean',
					'FROM'	=> array(
						MEMBERSHIP_TABLE=> 'm',
						),
					'LEFT_JOIN' => array(
						array(
							'FROM'  => array(USERS_TABLE => 'u'),
							'ON'	=> 'u.user_id = m.user_id'
							),
						),
					'WHERE'		=>  "membership_no={$data['membership_no']}",
					);
				$sql=$db->sql_build_query('SELECT', $sql_array);
				$result =$db->sql_query($sql);
				$count =$db->sql_fetchfield('username_clean');
				if (!empty($count))
				{
					$error[] = sprintf($user->lang['MEMBERSHIP_NO_IN_USE'], $count);
				}
				else
				{
					$update_ary['membership_no']=$data['membership_no'];
				}
			}
		}
		
		if ($data['associate_name'] != $row['username_clean'])
		{
			$assoc_error = validate_associate($data['associate_name'], $associate_id);
			if (!empty($assoc_error))
			{
				$error[] = $assoc_error;
			}
		}
		
		if (checkdate($data['rday_month'],$data['rday_day'],$data['rday_year']))
		{
			$update_ary['renewal_date'] =	mktime(0,0,0,$data['rday_month'],$data['rday_day'],$data['rday_year']);
		}
		else
		{
			$error[] = 'INVALID_DATE';			
		}
		if (checkdate($data['pday_month'],$data['pday_day'],$data['pday_year']))
		{
			$update_ary['datepaid'] =	mktime(0,0,0,$data['pday_month'],$data['pday_day'],$data['pday_year']);
		}
		else
		{
			$error[] = 'INVALID_DATE';			
		}

		if (!check_form_key($form_name))
		{
			$error[] = 'FORM_INVALID';
		}

		if (!sizeof($error))
		{
			if ($data['group_id']!= $row['group_id'])
			{
				change_premium_group($user_id, $data['group_id']);
				$update_ary['group_id'] = $data['group_id'];
			}
			update_membership($user_id, $update_ary);

			if ($data['associate_name'] != $row['username_clean'])
			{
				process_associate($user_id, $associate_id);				
			}
			trigger_error($user->lang['USER_PROFILE_UPDATED'] . adm_back_link($this->u_action . '&amp;u=' . $user_id));
		}
		// Replace "error" strings with their real, localised form
		$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
	}

	$s_renewal_day_options = '<option value="0"' . ((!$data['rday_day']) ? ' selected="selected"' : '') . '>--</option>';
	$s_paid_day_options = '<option value="0"' . ((!$data['pday_day']) ? ' selected="selected"' : '') . '>--</option>';
	for ($i = 1; $i < 32; $i++)
	{
		$selected = ($i == $data['rday_day']) ? ' selected="selected"' : '';
		$s_renewal_day_options .= "<option value=\"$i\"$selected>$i</option>";

		$selected = ($i == $data['pday_day']) ? ' selected="selected"' : '';
		$s_paid_day_options .= "<option value=\"$i\"$selected>$i</option>";
	}

	$s_renewal_month_options = '<option value="0"' . ((!$data['rday_month']) ? ' selected="selected"' : '') . '>--</option>';
	$s_paid_month_options = '<option value="0"' . ((!$data['pday_month']) ? ' selected="selected"' : '') . '>--</option>';
	for ($i = 1; $i < 13; $i++)
	{
		$selected = ($i == $data['rday_month']) ? ' selected="selected"' : '';
		$s_renewal_month_options .= "<option value=\"$i\"$selected>$i</option>";

		$selected = ($i == $data['pday_month']) ? ' selected="selected"' : '';
		$s_paid_month_options .= "<option value=\"$i\"$selected>$i</option>";
	}
	$s_renewal_year_options = '';
	$s_paid_year_options = '';
	$now = getdate();
	$s_renewal_year_options = '<option value="0"' . ((!$data['rday_year']) ? ' selected="selected"' : '') . '>--</option>';
	$s_paid_year_options = '<option value="0"' . ((!$data['pday_year']) ? ' selected="selected"' : '') . '>--</option>';
	for ($i = $now['year'] - 10; $i <= $now['year']+20; $i++)
	{
		$selected = ($i == $data['rday_year']) ? ' selected="selected"' : '';
		$s_renewal_year_options .= "<option value=\"$i\"$selected>$i</option>";
		$selected = ($i == $data['pday_year']) ? ' selected="selected"' : '';
		$s_paid_year_options .= "<option value=\"$i\"$selected>$i</option>";
	}
	unset($now);

	// build list of enabled premium groups
	$groups = array();
	for ($i = 1; $i <= 6; $i++)
	{
		$subscription_option = 'ms_billing_cycle' . $i;
		if (!empty($config[$subscription_option]))
		{
			$groups[$config[$subscription_option . '_group']]=$config[$subscription_option . '_group'];
		}
	}
	// build list of enabled premium groups
	$sql = 'SELECT * FROM ' . GROUPS_TABLE . ' WHERE group_id IN (' . implode(",", array_keys($groups)) . ')';
	$result = $db->sql_query($sql);
	
	$s_premium_group_options = '';

	while ($row = $db->sql_fetchrow($result))
	{
		$s_premium_group_options .= '<option value="' . $row['group_id'] . '"' . (($row['group_id'] == $row['group_id']) ? ' selected="selected"' : '') . '>' . $row['group_name'] . '</option>';
	}
	$db->sql_freeresult($result);
	
	$template->assign_vars(array(
		'NOT_MEMBER'				=> $not_member,
		'MEMBERSHIP_NO'				=> $data['membership_no'],
		'ASSOCIATE_NAME'			=> $data['associate_name'],
		'S_RENEWAL_DAY_OPTIONS'		=> $s_renewal_day_options,
		'S_RENEWAL_MONTH_OPTIONS'	=> $s_renewal_month_options,
		'S_RENEWAL_YEAR_OPTIONS'	=> $s_renewal_year_options,
		'S_PAID_DAY_OPTIONS'		=> $s_paid_day_options,
		'S_PAID_MONTH_OPTIONS'		=> $s_paid_month_options,
		'S_PAID_YEAR_OPTIONS'		=> $s_paid_year_options,
		'S_PREMIUM_GROUP_OPTIONS'	=> $s_premium_group_options,

		'S_USERS_MEMBERSHIP'		=> true)
	);
?>
