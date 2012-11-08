<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_membership
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $cache;

		$user->add_lang(array('acp/board', 'acp/users'));
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit']) || isset($_POST['allow_quick_reply_enable'])) ? true : false;

		$form_key = 'acp_board';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'settings':
			{
				$display_vars = array(
					'title'	=> 'SUBSCRIPTION_SETTINGS',
					'vars'	=> array(
						'legend1'						=> 'SUBSCRIPTION_SETTINGS_TITLE',
						'ms_enable_membership'			=> array('lang' => 'ENABLE_MEMBERSHIP', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_registration'				=> array('lang' => 'REGISTRATION', 'validate' => 'int', 'type' => 'custom', 'method' => 'registration', 'explain' => true),
						'ms_default_group'				=> array('lang' => 'DEFAULT_GROUP', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_rank'						=> array('lang'	=> 'RANK', 'validate' => 'int', 'type' => 'select', 'method' => 'rank', 'explain' => true),
						'ms_subscription_extra_days'	=> array('lang' => 'SUBSCRIPTION_EXTRA_DAYS',		'validate' => 'int',	'type' => 'text:4:4',	'explain' => true),
						'ms_process_on_payment'			=> array('lang' => 'PROCESS_ON_PAYMENT', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_approval_required'			=> array('lang' => 'APPROVAL_REQUIRED', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_due_soon_period'			=> array('lang' => 'DUE_SOON_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_due_period'					=> array('lang' => 'DUE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_overdue_period'				=> array('lang' => 'OVERDUE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_last_chance_period'			=> array('lang' => 'LAST_CHANCE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_grace_period'				=> array('lang' => 'GRACE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_application_forum'			=> array('lang' => 'APPLICATION_FORUM', 'validate' => 'int',	'type' => 'select', 'method' => 'application_forum', 'explain' => true),
						'ms_allow_associate'			=> array('lang' => 'ALLOW_ASSOCIATES', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_associate_rank'				=> array('lang' => 'ASSOCIATE_RANK', 'validate' => 'int', 'type' => 'select', 'method' => 'rank', 'explain' => false),
						'legend2'						=> 'SUBSCRIPTION_CHARGES_SETTINGS',
						'ms_period_start'				=> array('lang' => 'PERIOD_START', 'validate' => 'num', 'type' => 'text:10:20',	'explain' => true),
						'ms_group_join_amount'			=> array('lang' => 'SUBSCRIPTION_JOINING_FEE', 'validate' => 'num', 'type' => 'text:10:20',	'explain' => true),
						'ms_billing_cycle1'				=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => true),
						'ms_billing_cycle2'				=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle3'				=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle4'				=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle5'				=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_due_soon_period_basis'		=> false,
						'ms_due_period_basis'			=> false,
						'ms_overdue_period_basis'		=> false,
						'ms_last_chance_period_basis'	=> false,
						'ms_grace_period_basis'			=> false,
						'ms_billing_cycle1_amount'		=> false,
						'ms_billing_cycle1_basis'		=> false,
						'ms_billing_cycle1_group'		=> false,
						'ms_billing_cycle2_amount'		=> false,
						'ms_billing_cycle2_basis'		=> false,
						'ms_billing_cycle2_group'		=> false,
						'ms_billing_cycle3_amount'		=> false,
						'ms_billing_cycle3_basis'		=> false,
						'ms_billing_cycle3_group'		=> false,
						'ms_billing_cycle4_amount'		=> false,
						'ms_billing_cycle4_basis'		=> false,
						'ms_billing_cycle4_group'		=> false,
						'ms_billing_cycle5_amount'		=> false,
						'ms_billing_cycle5_basis'		=> false,
						'ms_billing_cycle5_group'		=> false,
						'legend3'						=> 'MEMBERSHIP_DATE_FORMAT_TITLE',
						'ms_membership_date_format'		=> array('lang' => 'MEMBERSHIP_DATE_FORMAT', 'validate' => 'text', 'type' => 'text:10:20',	'explain' => true),
				));
//				$user->add_lang($display_vars['lang']);
	
				$this->new_config = $config;
				$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
				$error = array();
		
				// We validate the complete config if whished
				validate_config_vars($display_vars['vars'], $cfg_array, $error);
		
				if ($submit && !check_form_key($form_key))
				{
					$error[] = $user->lang['FORM_INVALID'];
				}
				// Do not write values if there is an error
				if (sizeof($error))
				{
					$submit = false;
				}
		
				// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
				foreach ($display_vars['vars'] as $config_name => $null)
				{
					if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
					{
						continue;
					}
		
					$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];
		
					if ($submit)
					{
						set_config($config_name, $config_value);
					}
				}
		
				if ($submit)
				{
					add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));
		
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}
		
				$this->tpl_name = 'acp_board';
				$this->page_title = $display_vars['title'];
		
				$template->assign_vars(array(
					'L_TITLE'			=> $user->lang[$display_vars['title']],
					'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
		
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),
		
					'U_ACTION'			=> $this->u_action)
				);
		
				// Output relevant page
				foreach ($display_vars['vars'] as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}
		
					if (strpos($config_key, 'legend') !== false)
					{
						$template->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
						);
		
						continue;
					}
		
					$type = explode(':', $vars['type']);
		
					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
					}
					else if ($vars['explain'])
					{
						$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}
		
					$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);
		
					if (empty($content))
					{
						continue;
					}
	
					$template->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> $content,
						)
					);
		
					unset($display_vars['vars'][$config_key]);
				}
			}	
			break;

			case 'list':
			{
				$renewal_date		= request_var('renewal_date', strtotime('+ 3 month'));
				$data['rday_day']	= request_var('rday_day', date('j', $renewal_date));
				$data['rday_month'] = request_var('rday_month', date('n', $renewal_date));
				$data['rday_year']  = request_var('rday_year', date('Y', $renewal_date));
				$premium_group		= request_var('premium_group',1);
				$group_filter		= request_var('group_filter', 0);
				$action	= request_var('action', '');
				$mark	= (isset($_REQUEST['mark'])) ? request_var('mark', array(0)) : array();
				$start	= request_var('start', 0);
		
				// Sort keys
				$limit_records_to	= request_var('st', 0);
				$sort_key			= request_var('sk', 'r');
				$sort_dir			= request_var('sd', 'a');
		
				$form_key = 'acp_membership';
				add_form_key($form_key);
		
				// We build the sort key and per page settings here, because they may be needed later
		
				// Number of entries to display
				$per_page = request_var('users_per_page', (int) $config['topics_per_page']);
		
				// Sorting
		
				$limit_records = array($user->lang['ALL_MEMBERS'], $user->lang['OVERDUE'], $user->lang['DUE_THIS_MONTH'], $user->lang['DUE_NEXT_MONTH'], $user->lang['NO_RENEWAL_DATE'], $user->lang['NOT_MEMBERS'], $user->lang['ANOMOLIES']);
		
				// Calculate range

  
				$where = array(
					// All Members
					(' AND m.user_id IS NOT NULL'),
					// Overdue i.e. renewal_date is before today
					(" AND m.remindertype='3'"),
					// Due this period i.e. due soon
					(" AND m.remindertype='2'"),
					//Due next period i.e. due on date
					(" AND m.remindertype='1'"),
					//Missing Renewal Date
					(" AND (m.renewal_date IS NULL)"),
					//Not members
					(' AND m.user_id IS NULL'),
					//Group members missing subscription record
					(' AND (ug.user_id IS NULL XOR m.user_id IS NULL)'),
				);
  

				$sort_by_text = array('r' => $user->lang['SORT_RENEWAL'], 'j' => $user->lang['SORT_DATE_JOINED'], 'n' => $user->lang['SORT_REALNAME'], 'd' => $user->lang['SORT_LAST_REMINDER'], 'u' => $user->lang['SORT_USERNAME'], 'p' => $user->lang['SORT_POSTS'], 'e' => $user->lang['SORT_REMINDER'], 'l' => $user->lang['SORT_LAST_VISIT']);
		
				$sort_by_sql = array('r' => 'renewal_date', 'j' => 'date_joined', 'n' => 'pf_realname', 'd' => 'user_reminded_time', 'u' => 'username_clean', 'p' => 'user_posts', 'e' => 'user_reminded', 'l' => 'user_lastvisit');
		
				$s_limit_records = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_records, $sort_by_text, $limit_records_to, $sort_key, $sort_dir, $s_limit_records, $s_sort_key, $s_sort_dir, $u_sort_param);
		
				if ($submit && sizeof($mark))
				{
					$error = validate_data($data, array(
						'rday_day'		=> array('num', true, 1, 31),
						'rday_month'	=> array('num', true, 1, 12),
						'rday_year'		=> array('num', true, 1901, strtotime('+ 10 years')),
					));
					if (checkdate($data['rday_month'],$data['rday_day'],$data['rday_year']))
					{
						$renewal_date = mktime(0,0,0,$data['rday_month'],$data['rday_day'],$data['rday_year']);
					}
					else
					{
						$error[] = 'INVALID_DATE'; 
					}
					if (!sizeof($error))
					{
						// Get those 'marked'...
						if (confirm_box(true))
						{
							$sql_array = array(
								'SELECT' => 'u.user_id, u.username, u.user_email, u.user_lang, u.user_jabber, u.user_notify_type, m.date_joined, u.user_actkey, pfd.*, m.remindercount, m.renewal_date, m.user_id as in_membership, ug.user_id as in_group, g.group_name',
								'FROM' => array(
									USERS_TABLE=> 'u',
								),
								'LEFT_JOIN' => array(
									array(
										'FROM'  => array(MEMBERSHIP_TABLE => 'm'),
										'ON' => ('m.user_id = u.user_id'),
									),
									array(
										'FROM'  => array(USER_GROUP_TABLE => 'ug'),
										'ON' => ('ug.user_id = u.user_id AND ug.group_id=m.group_id' ),
									),
									array(
										'FROM'  => array(GROUPS_TABLE => 'g'),
										'ON' => ('g.group_id=m.group_id'),
									),
									array(
										'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
										'ON' => ('pfd.user_id = u.user_id'),
									),
								),
								'WHERE'	=> 'u.' . $db->sql_in_set('user_id', $mark),
							);
							$sql=$db->sql_build_query('SELECT', $sql_array);

							$result = $db->sql_query($sql);
		
							$user_marked = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$user_marked[] = $row;
							}
							$db->sql_freeresult($result);

							foreach ($user_marked as $user_selected)
							{
								switch ($action)
								{
									case 'join':
									case 'renew':

										$sql_ary = array(
											'remindercount'	=> '0', 
											'reminderdate'	=> '0',
											'remindertype'	=> '0',
											'renewal_date'	=> $renewal_date,
											'group_id'		=> $premium_group,
										);

										update_membership($user_selected['user_id'], $sql_ary);

										if ($action=='join' || empty($user_marked['in_group']))
										{
											group_user_add($premium_group,$user_selected['user_id'], false, false, $config['ms_default_group'], 0, $config['ms_approval_required']);
										}
										ELSE										
										{
											add_log('admin', 'LOG_USER_GROUP_RENEWED', $user_selected['username'], $group_name);
										}
				
										// For activate we really need to redirect, else a refresh can result in users being deactivated again
					
										$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
										$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';
					
										redirect($u_action);
									break;
									
									case 'remove':
					
										remove_member($user_selected['user_id']);
				
									break;
				
									case 'remind':
				
										// Send the messages
										if(!class_exists('messenger'))
										{
											include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
										}
				
										$messenger = new messenger();
										$usernames = array();
				
										$messenger->template('user_remind_inactive', $user_selected['user_lang']);
			
										$messenger->to($user_selected['user_email'], $user_selected['username']);
										$messenger->im($user_selected['user_jabber'], $user_selected['username']);
			
										$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
										$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
										$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
										$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
			
										$messenger->assign_vars(array(
											'USERNAME'		=> htmlspecialchars_decode($user_selected['username']),
											'REGISTER_DATE'	=> $user->format_date($user_selected['date_joined'], false, true),
											'U_ACTIVATE'	=> generate_board_url() . "/ucp.$phpEx?mode=activate&u=" . $user_selected['user_id'] . '&k=' . $user_selected['user_actkey'])
										);
			
										$messenger->send($user_selected['user_notify_type']);
			
										$usernames[] = $row['username'];
				
										$messenger->save_queue();
				
										// Add the remind state to the database
										$sql_ary = array(
											'reminderdate'	=> time(),
											'remindercount'	=> 'remindercount+1',
										);
										$sql = 'UPDATE ' . MEMBERSHIP_RECORD . '
											SET reminderdate = ' . time() . ', remindercount=remindercount+1 
											WHERE user_id IN ';
										$result = $db->sql_query($sql);

										add_log('admin', 'LOG_MEMBERSHIP_DUE_REMIND', implode(', ', $usernames));
										unset($usernames);
				
										// For remind we really need to redirect, else a refresh can result in more than one reminder
										$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
										$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';
					
										redirect($u_action);
					
									break;
									
									case 'change':
										change_premium_group($user_selected['user_id'], $premium_group);
									break;
								}
							}
						}
						else
						{
							$s_hidden_fields = array(
								'mode'			=> $mode,
								'action'		=> $action,
								'mark'			=> $mark,
								'submit'		=> 1,
								'start'			=> $start,
								'renewal_date'	=> $renewal_date,
								'premium_group'	=> $premium_group,
							);
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
						}
					}
					else
					{
						// Replace "error" strings with their real, localised form
						$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);
					}
				}

				$s_renewal_day_options = '<option value="0"' . ((!$data['rday_day']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $data['rday_day']) ? ' selected="selected"' : '';
					$s_renewal_day_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_renewal_month_options = '<option value="0"' . ((!$data['rday_month']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $data['rday_month']) ? ' selected="selected"' : '';
					$s_renewal_month_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				$s_renewal_year_options = '';
				$s_paid_year_options = '';
				$now = getdate();
				$s_renewal_year_options = '<option value="0"' . ((!$data['rday_year']) ? ' selected="selected"' : '') . '>--</option>';
				for ($i = $now['year']; $i <= $now['year']+5; $i++)
				{
					$selected = ($i == $data['rday_year']) ? ' selected="selected"' : '';
					$s_renewal_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}
				unset($now);

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
				
				$premium_groups = '<select name="premium_group">';
				$s_group_filter = '<select name="group_filter"><option value="0"' . ($group_filter == 0 ? ' selected="selected"' : '') .'>' . 'All Groups' . '</option>';

				while ($row = $db->sql_fetchrow($result))
				{
					$premium_groups .= '<option value="' . $row['group_id'] . '"' . (($row['group_id'] == $premium_group) ? ' selected="selected"' : '') . '>' . $row['group_name'] . '</option>';
					$s_group_filter .= '<option value="' . $row['group_id'] . '"' . (($row['group_id'] == $group_filter) ? ' selected="selected"' : '') . '>' . $row['group_name'] . '</option>';
				}
				$db->sql_freeresult($result);
				
				$premium_groups .= '</select>';
				$s_group_filter .= '</select>';
				
				// Define where and sort sql 
				
				$sql_where = $where[$limit_records_to];
				if ($limit_records_to != 5)
				{
					if ($group_filter>0)
					{
						$sql_where .= ' AND m.group_id = ' . $group_filter;
					}
					else
					{
						$sql_where .= ' AND m.group_id IN (' . implode(',', $groups) . ')';
					}
				}

				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
				
				$members = array();
				$members_count = 0;
		
				$start = view_members($members, $members_count, $per_page, $start, $sql_where, $sql_sort);

				$error_message='';
				$statuses = array('', 'Due Soon', 'Due', 'Overdue', 'Last Chance', 'To be removed');
				if ($limit_records_to==5)
				{
					$option_ary=array('join'=>'JOIN');
				}
					else
				{
					$option_ary = array('renew' => 'RENEW', 'remove' => 'REMOVE', 'change' => 'CHANGE');
					if ($config['email_enable'])
					{
						$option_ary += array('remind' => 'REMIND');
					}
					$dates[1] = calc_date($config['ms_due_soon_period'], $config['ms_due_soon_period_basis']);
					$dates[2] = calc_date($config['ms_due_period'], $config['ms_due_period_basis']);
					$dates[3] = calc_date($config['ms_overdue_period'], $config['ms_overdue_period_basis']);
					$dates[4] = calc_date(-$config['ms_last_chance_period'], $config['ms_last_chance_period_basis']);
					$dates[5] = calc_date(-$config['ms_grace_period'], $config['ms_grace_period_basis']);
				}
				$date_joined = $renewal_date = '';
				$status = 0;

				foreach ($members as $row)
				{
					if ($limit_records_to != 5)
					{
						if (!$row['renewal_date']) 
						{
							$renewal_date = '-';
						}
						else
						{
							$renewal_date = $user->format_date($row['renewal_date'], $config['ms_membership_date_format']);
							for ($i=5;$i>0; $i--)
							{
								if ($row['renewal_date']<$dates[$i])
								{
									break;
								}
							}
							$status = $statuses[$i];
						}
						if (!empty($row['reminderdate']))
						{
							$reminder_date = $user->format_date($row['reminderdate'], $config['ms_membership_date_format']);
						}
						else
						{
							$reminder_date = '-';
						}
						
						if (!empty($row['date_joined']))
						{
							$date_joined = $user->format_date($row['date_joined'], $config['ms_membership_date_format']);
						}
						else
						{
							$date_joined = '-';
						}
					}
					if ($limit_records_to == 6)
					{
						if (is_null($row['in_group']))
						{
							$error_message	= $user->lang['ERROR_GROUP'];
						}
						elseif (is_null($row['in_membership']))
						{
							$error_message	= $user->lang['ERROR_MEMBERSHIP'];
						}
					}
					$template->assign_block_vars('members', array(
						'COMMENT'			=> $error_message,
						'RENEWAL_DATE'		=> $renewal_date,
						'STATUS'			=> $status,
						'REMINDED_DATE'		=> $user->format_date($row['reminderdate'], $config['ms_membership_date_format']),
						'JOINED'			=> $date_joined,
						'LAST_VISIT'		=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),
						'GROUP_NAME'		=> $row['group_name'],
						'PF_REALNAME'		=> isset($row['pf_ms_realname']) ? $row['pf_ms_realname'] : '',
						'REMINDED'			=> $row['remindercount'],
						'REMINDED_EXPLAIN'	=> $user->lang('USER_LAST_REMINDED', $row['remindercount'], $user->format_date($row['reminderdate'])),
						'USER_ID'			=> $row['user_id'],
						'POSTS'				=> ($row['user_posts']) ? $row['user_posts'] : 0,
						'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview')),
						'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
						'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
		
						'U_USER_ADMIN'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
						'U_USER_EXTRACT'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
						'U_SEARCH_USER'		=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id={$row['user_id']}&amp;sr=posts") : '',
					));
				}

var_dump($group_filter);
				$template->assign_vars(array(
					'S_GROUP_FILTER'			=> $s_group_filter,
					'S_PREMIUM_GROUPS'			=> $premium_groups,
					'S_MEMBERSHIP_OPTIONS'		=> build_select($option_ary),
					'S_RENEWAL_DAY_OPTIONS'		=> $s_renewal_day_options,	
					'S_RENEWAL_MONTH_OPTIONS'	=> $s_renewal_month_options,	
					'S_RENEWAL_YEAR_OPTIONS'	=> $s_renewal_year_options,	
					'S_LIMIT_RECORDS'			=> $s_limit_records,
					'S_SORT_KEY'				=> $s_sort_key,
					'S_SORT_DIR'				=> $s_sort_dir,
					'S_ON_PAGE'					=> on_page($members_count, $per_page, $start),
					'PAGINATION'				=> generate_pagination($this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page", $members_count, $per_page, $start, true),
					'USERS_PER_PAGE'			=> $per_page,
					'U_GENERATION'				=> append_sid("{$phpbb_root_path}generate_mailing_list.$phpEx"),
					'U_ACTION'					=> $this->u_action . '&amp;start=' . $start,
				));
		
				$this->tpl_name		= 'acp_membership';
				$this->page_title	= 'ACP_MEMBERSHIP';
			}				
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

	}

	/**
	* Select interval
	*/
	function time_interval($value, $key)
	{
		global $user;

		$period_options = '';
		$period_types = array('d' => 'DAY', 'w' => 'WEEK', 'm' => 'MONTH', 'y' => 'YEAR');

		foreach ($period_types as $period_type => $lang)
		{
			$selected = ($this->new_config[$key.'_basis'] == $period_type) ? ' selected="selected"' : '';
			$period_options .= '<option value="' . $period_type . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}
		$period_basis = $key.'_basis';
		return '<input id="' . $key . '" type="text" size="3" maxlength="4" name="config['. $key . ']" value="' . $value . '" />&nbsp;<select name="config['. $period_basis. ']">' . $period_options . '</select>';
	}
  
	/**
	* Select subscription period and charge
	*/
 
	function billing_cycle($value, $key)
	{
		global $phpbb_root_path, $phpEx;

		$period_basis = $key.'_basis';
		$period_charge = $key.'_amount';
		$period_group = $key.'_group';
		
		$return_string = '<input id="' . $period_charge . '" type="text" size="10" maxlength="20" name="config['. $period_charge . ']" value="' . $this->new_config[$period_charge] . '" />&nbsp;';
		$return_string .= $this->time_interval($value, $key);
		
		$return_string .= '<select name="config['. $period_group . ']">' . group_select_options($this->new_config[$period_group], false, false) . '</select>';
		return ($return_string);
	}
  
	/**
	* Select Application action
	*/
	function application_forum($value)
	{
		global $phpbb_root_path, $phpEx;
		
		if (!function_exists('make_forum_select'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		return $forum_list = make_forum_select($value, false, true, false, false, false, false);
	}
	/**
	* Select Application action
	*/
	function rank($value)
	{
		global $db, $user;
		$sql = 'SELECT *
			FROM ' . RANKS_TABLE . '
			WHERE rank_special = 1
			ORDER BY rank_title';
		$result = $db->sql_query($sql);
	
		$s_rank_options = '<option value="0"' . ((!$value) ? ' selected="selected"' : '') . '>' . $user->lang['NO_SPECIAL_RANK'] . '</option>';
	
		while ($row = $db->sql_fetchrow($result))
		{
			$selected = ($value == $row['rank_id']) ? ' selected="selected"' : '';
			$s_rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
		}
		$db->sql_freeresult($result);
		return ($s_rank_options);
	}
	/**
	*	Select Premium membership on registration option
	*/
	function registration($value)
	{
		global $user;
		$s_reg_options	= '<label><input type="radio" class="radio" value="0" ' . (($value==0) ? ' checked="checked"' : '') . ' id="ms_registration" name="config[ms_registration]">' . $user->lang['NEVER'] . '</label>';
		$s_reg_options	.= '<label><input type="radio" class="radio" value="1" ' . (($value==1) ? ' checked="checked"' : '') . ' id="ms_registration" name="config[ms_registration]">' . $user->lang['OPTIONAL'] . '</label>';
		$s_reg_options	.= '<label><input type="radio" class="radio" value="2" ' . (($value==2) ? ' checked="checked"' : '') . ' id="ms_registration" name="config[ms_registration]">' . $user->lang['ALWAYS'] . '</label>';
		return ($s_reg_options);
	}
}
?>
