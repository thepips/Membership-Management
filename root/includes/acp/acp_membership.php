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

		$user->add_lang('acp/board');
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

				$display_vars = array(
					'title'	=> 'SUBSCRIPTION_SETTINGS',
					'vars'	=> array(
						'legend1'                     => 'SUBSCRIPTION_SETTINGS_TITLE',
						'ms_enable_membership'		=> array('lang' => 'ENABLE_MEMBERSHIP', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_subscription_group'		=> array('lang' => 'SUBSCRIPTION_GROUP', 'validate' => 'int', 'type' => 'select:10:0', 'method' => 'subscription_group', 'explain' => true),
						'ms_subscription_extra_days'=> array('lang' => 'SUBSCRIPTION_EXTRA_DAYS', 		'validate' => 'int',	'type' => 'text:4:4',	'explain' => true),
						'ms_process_on_payment'		=> array('lang' => 'PROCESS_ON_PAYMENT', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_approval_required'		=> array('lang' => 'APPROVAL_REQUIRED', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'ms_due_soon_period'		=> array('lang' => 'DUE_SOON_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_due_period'				=> array('lang' => 'DUE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_overdue_period'			=> array('lang' => 'OVERDUE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_last_chance_period'		=> array('lang' => 'LAST_CHANCE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_grace_period'			=> array('lang' => 'GRACE_PERIOD', 'validate' => 'int',	'type' => 'custom',	'method' => 'time_interval', 'explain' => true),
						'ms_application_forum'		=> array('lang' => 'APPLICATION_FORUM', 'validate' => 'int',	'type' => 'select:10:0', 'method' => 'application_forum', 'explain' => true),
						'ms_allow_associate'        => array('lang' => 'ALLOW_ASSOCIATES', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'legend2'					=> 'SUBSCRIPTION_CHARGES_SETTINGS',
                        'ms_period_start'           => array('lang' => 'PERIOD_START', 'validate' => 'num', 'type' => 'text:10:20',	'explain' => true),
						'ms_group_join_amount'		=> array('lang' => 'SUBSCRIPTION_JOINING_FEE', 'validate' => 'num', 'type' => 'text:10:20',	'explain' => true),
						'ms_billing_cycle1'			=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => true),
						'ms_billing_cycle2'			=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle3'			=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle4'			=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_billing_cycle5'			=> array('lang' => 'BILLING_CYCLE', 'validate' => 'int',	'type' => 'custom',	'method' => 'billing_cycle', 'explain' => false),
						'ms_due_soon_period_basis'    => false,
						'ms_due_period_basis'         => false,
						'ms_overdue_period_basis'     => false,
						'ms_last_chance_period_basis' => false,
						'ms_grace_period_basis'       => false,
						'ms_billing_cycle1_amount'    => false,
						'ms_billing_cycle1_basis'     => false,
						'ms_billing_cycle2_amount'    => false,
						'ms_billing_cycle2_basis'     => false,
						'ms_billing_cycle3_amount'    => false,
						'ms_billing_cycle3_basis'     => false,
						'ms_billing_cycle4_amount'    => false,
						'ms_billing_cycle4_basis'     => false,
						'ms_billing_cycle5_amount'    => false,
						'ms_billing_cycle5_basis'     => false,
						'legend3'					=> 'MEMBERSHIP_DATE_FORMAT_TITLE',
						'ms_membership_date_format'	=> array('lang' => 'MEMBERSHIP_DATE_FORMAT', 'validate' => 'text', 'type' => 'text:10:20',	'explain' => true),
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
			break;

			case 'list':
				$sql = 'SELECT  group_name FROM ' . GROUPS_TABLE . ' WHERE group_id = "' . $config['ms_subscription_group'] . '"';
				$result = $db->sql_query($sql);
				$group_name = $db->sql_fetchfield('group_name');
				$db->sql_freeresult($result);

				$action = request_var('action', '');
				$mark	= (isset($_REQUEST['mark'])) ? request_var('mark', array(0)) : array();
				$start	= request_var('start', 0);
		
				// Sort keys
				$limit_records_to	= request_var('st', 0);
				$sort_key	= request_var('sk', 'n');
				$sort_dir	= request_var('sd', 'a');
		
				$form_key = 'acp_membership';
				add_form_key($form_key);
		
				// We build the sort key and per page settings here, because they may be needed later
		
				// Number of entries to display
				$per_page = request_var('users_per_page', (int) $config['topics_per_page']);
		
				// Sorting
		
				$limit_records = array($user->lang['ALL_MEMBERS'], $user->lang['OVERDUE'], $user->lang['DUE_THIS_MONTH'], $user->lang['DUE_NEXT_MONTH'], $user->lang['NO_RENEWAL_DATE'], $user->lang['NOT_MEMBERS']);
		
				// Calculate range

                
                $where = array(
					// All Members
					(" AND u.group_id='{$config['ms_subscription_group']}'"),
					// Overdue i.e. renewal_date is before today
					(" AND u.group_id='{$config['ms_subscription_group']}' AND m.remindertype='3'"),
					// Due this period i.e. due soon
					(" AND u.group_id='{$config['ms_subscription_group']}' AND m.remindertype='2'"),
					//Due next period i.e. due on date
					(" AND m.group_id='{$config['ms_subscription_group']}' AND m.remindertype='1'"),
					//Missing Renewal Date
					(" AND m.group_id='{$config['ms_subscription_group']}' AND (m.renewal_date IS NULL )"),
					//Not members
					(' AND m.user_id IS NULL'),
				);
                

				$sort_by_text = array('r' => $user->lang['SORT_RENEWAL'], 'j' => $user->lang['SORT_REG_DATE'], 'n' => $user->lang['SORT_REALNAME'], 'd' => $user->lang['SORT_LAST_REMINDER'], 'u' => $user->lang['SORT_USERNAME'], 'p' => $user->lang['SORT_POSTS'], 'e' => $user->lang['SORT_REMINDER'], 'l' => $user->lang['SORT_LAST_VISIT']);
		
				$sort_by_sql = array('r' => 'renewal_date', 'j' => 'user_regdate', 'n' => 'pf_realname', 'd' => 'user_reminded_time', 'u' => 'username_clean', 'p' => 'user_posts', 'e' => 'user_reminded', 'l' => 'user_lastvisit');
		
				$s_limit_records = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_records, $sort_by_text, $limit_records_to, $sort_key, $sort_dir, $s_limit_records, $s_sort_key, $s_sort_dir, $u_sort_param);
		
				if ($submit && sizeof($mark))
				{
		//			if ($action !== 'remove' && !check_form_key($form_key))
		//			{
		//				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		//			}
					switch ($action)
					{
						case 'renew':
							// Get those 'being renewed'...
							if (confirm_box(true))
							{
                                $sql_array = array(
                                	'SELECT'    => 'u.user_id, username, renewal_date',
                                	'FROM'      => array(
                                		USERS_TABLE=> 'u',
                                	),
                                	'LEFT_JOIN' => array(
                                		array(
                                			'FROM'  => array(MEMBERSHIP_TABLE => 'm'),
                                			'ON'    => ('m.user_id = u.user_id AND m.group_id=' . $config['ms_subscription_group']),
                                		),
                                	),
                                	'WHERE'     	=> 'u.' . $db->sql_in_set('user_id', $mark),
                                );
                                $sql=$db->sql_build_query('SELECT', $sql_array);

								$result = $db->sql_query($sql);
			
								$user_affected = array();
								while ($row = $db->sql_fetchrow($result))
								{
									$user_affected[] = $row;
								}
								$db->sql_freeresult($result);

								foreach ($user_affected as $user_marked)
								{
			// Calculate renewal date
                                    $renewal_date = calc_date($config['ms_billing_cycle'], $config['ms_billing_cycle_basis'], $user_marked['renewal_date']);
                                    set_renewal_date($config['ms_subscription_group'], $user_marked['user_id'], $renewal_date);

									$sql = 'SELECT count(user_id) AS user_count FROM ' . MEMBERSHIP_TABLE . ' WHERE group_id = ' . $config['ms_subscription_group'] . ' and user_id = ' . $user_marked['user_id'];
									$result = $db->sql_query($sql);
									$user_count = (int) $db->sql_fetchfield('user_count');
									$db->sql_freeresult($result);
									if ($user_count)
									{
										add_log('admin', 'LOG_USER_GROUP_RENEWED', $user_marked['username'], $group_name);
									}
									ELSE										
									{
										group_user_add($config['ms_subscription_group'],$user_marked['user_id'], false, false,0,$config['ms_approval_required']);
										add_log('admin', 'LOG_USER_GROUP_JOINED', $user_marked['username'],$group_name);
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
								);
								confirm_box(false, sprintf($user->lang['RENEWAL_CONFIRMATION'], $config['ms_billing_cycle'], $config['ms_billing_cycle_basis']), build_hidden_fields($s_hidden_fields));
		
							}
							// For activate we really need to redirect, else a refresh can result in users being deactivated again
		
							$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
							$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';
		
							redirect($u_action);
							break;
							
						case 'remove':
		
							$sql = 'SELECT user_id, username
								FROM ' . USERS_TABLE . '
								WHERE ' . $db->sql_in_set('user_id', $mark);
							$result = $db->sql_query($sql);
							
							$usernames = $user_ids = array();
							
							while ($row = $db->sql_fetchrow($result))
							{
								$usernames[] = $row['username'];
								$user_ids[] = (int) $row['user_id'];
							}
							$db->sql_freeresult($result);
							
							if (confirm_box(true))
							{
								$sql = 'DELETE FROM ' . USER_GROUP_TABLE . ' WHERE ' . USER_GROUP_TABLE . '.group_id = ' . $config['ms_subscription_group'] . ' AND ' . USER_GROUP_TABLE . '.user_id IN (' . implode(', ', $user_ids) . ')';
								$result = $db->sql_query($sql);
								$sql = 'UPDATE ' . MEMBERSHIP_TABLE . ' SET reminderdate = 0 WHERE user_id IN (' . implode(', ', $user_ids) . ')';
								$result = $db->sql_query($sql);
								add_log('admin', 'LOG_GROUP_REMOVE', implode(', ',$usernames),$group_name);
							}
							else
							{
								$s_hidden_fields = array(
									'mode'			=> $mode,
									'action'		=> $action,
									'mark'			=> $mark,
									'submit'		=> 1,
									'start'			=> $start,
								);
								confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
							}
		
						break;
		
						case 'remind':
							if (empty($config['email_enable']))
							{
								trigger_error($user->lang['EMAIL_DISABLED'] . adm_back_link($this->u_action), E_USER_WARNING);
							}
                            $sql_array = array(
                            	'SELECT'    => 'u.user_id, username, user_email, user_lang, user_jabber, user_notify_type, user_regdate, user_actkey, realname, remindercount, renewal_date',
                            	'FROM'      => array(
                            		USERS_TABLE=> 'u',
                            	),
                            	'LEFT_JOIN' => array(
                            		array(
                            			'FROM'  => array(MEMBERSHIP_TABLE => 'm'),
                            			'ON'    => ('m.user_id = u.user_id AND m.group_id=' . $config['ms_subscription_group']),
                            		),
                            		array(
                            			'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
                            			'ON'    => ('pfd.user_id = u.user_id'),
                            		),
                            	),
                            	'WHERE'     	=> 'u.' . $db->sql_in_set('user_id', $mark),
                            );
                            $sql=$db->sql_build_query('SELECT', $sql_array);

							$result = $db->sql_query($sql);
		
							if ($row = $db->sql_fetchrow($result))
							{
								// Send the messages
								include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		
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
										'U_ACTIVATE'	=> generate_board_url() . "/ucp.$phpEx?mode=activate&u=" . $row['user_id'] . '&k=' . $row['user_actkey'])
									);
		
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
									' SET remindercount = remindercount + 1, reminderdate = ' . time() . ' WHERE user_id=' . $user_id;
									$result = $db->sql_query($sql);
									$affected_rows =$db->sql_affectedrows();
									if ($affected_rows == 0)
									{
										$db->sql_query('INSERT ' . MEMBERSHIP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
										'group_id'        => $group_id,
                                        'user_id'			=> $user_id,
										'remindercount'	=> 1,
										'reminderdate'	=> time())));
									}
								}
								$result = $db->sql_query($sql);
								
		
								add_log('admin', 'LOG_MEMBERSHIP_DUE_REMIND', implode(', ', $usernames));
								unset($usernames);
							}
							$db->sql_freeresult($result);
		
							// For remind we really need to redirect, else a refresh can result in more than one reminder
							$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
							$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';
		
							redirect($u_action);
		
						break;
					}
				}
		
				// Define where and sort sql for use in displaying logs
				
				$sql_where = $where[$limit_records_to];
				
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		
				$members = array();
				$members_count = 0;
		
				$start = view_members($members, $members_count, $per_page, $start, $sql_where, $sql_sort);
		
				foreach ($members as $row)
				{
					$template->assign_block_vars('members', array(
						'RENEWAL_DATE'	=> (!$row['renewal_date']) ? ' - ' : $user->format_date($row['renewal_date'], $config['ms_membership_date_format']),
						'REMINDED_DATE'		=> $user->format_date($row['reminderdate']),
						'JOINED'			=> $user->format_date($row['user_regdate']),
						'LAST_VISIT'		=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),
		
						'PF_REALNAME'		=> $row['pf_ms_realname'],
						'REMINDED'			=> $row['remindercount'],
						'REMINDED_EXPLAIN'	=> $user->lang('USER_LAST_REMINDED', (int) $row['remindercount'], $user->format_date($row['reminderdate'])),

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
		
				$option_ary = array('renew' => 'RENEW', 'remove' => 'REMOVE');
				if ($config['email_enable'])
				{
					$option_ary += array('remind' => 'REMIND');
				}
				$template->assign_vars(array(
					'S_MEMBERSHIP_OPTIONS'	=> build_select($option_ary),
		
					'S_LIMIT_RECORDS'		=> $s_limit_records,
					'S_SORT_KEY'			=> $s_sort_key,
					'S_SORT_DIR'			=> $s_sort_dir,
					'S_ON_PAGE'				=> on_page($members_count, $per_page, $start),
					'PAGINATION'			=> generate_pagination($this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page", $members_count, $per_page, $start, true),
					'USERS_PER_PAGE'		=> $per_page,
					'U_GENERATION'			=> append_sid("{$phpbb_root_path}generate_mailing_list.$phpEx"),
					'U_ACTION'				=> $this->u_action . '&amp;start=' . $start,
				));
		
				$this->tpl_name = 'acp_membership';
				$this->page_title = 'ACP_MEMBERSHIP';
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
        $period_basis = $key.'_basis';
        $period_charge = $key.'_amount';

        $return_string = '<input id="' . $period_charge . '" type="text" size="10" maxlength="20" name="config['. $period_charge . ']" value="' . $this->new_config[$period_charge] . '" />&nbsp;';
        $return_string .= $this->time_interval($value, $key);
        return ($return_string);
	}
       
	/**
	* Select subscription group action
	*/
	function subscription_group($value)
	{
	   global $phpbb_root_path, $phpEx;
        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		return $forum_list = group_select_options($value, false, false);
	}

	/**
	* Select Application action
	*/
	function application_forum($value)
	{
	   global $phpbb_root_path, $phpEx;
        include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		return $forum_list = make_forum_select($value, false, true, false, false, false, false);
	}
}
?>