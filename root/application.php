<?php

/**

* @package application.php

* @copyright (c) DougA http://action-replay.co.uk 2011

* @license http://opensource.org/licenses/gpl-license.php GNU Public License

*

*/

define('IN_PHPBB', true);

$phpbb_root_path = './'; // See phpbb_root_path documentation
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management

$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/application');

// You need to login before being able to send out an application

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN_APPLICATION_FORM']);
}

include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);
include($phpbb_root_path . 'includes/currency_format.' . $phpEx);

global $db, $user, $auth, $template;
global $config, $phpbb_root_path, $phpEx;

$mode			= request_var('mode', '');
$type			= request_var('type', '');
$userid			= request_var('i', '');
$groupid		= request_var('g','');
$payment_method	= request_var('method', 'payment');
$action			= request_var('action','');
//$subscribe      = request_var('subscribing', '0');
$submit 		= (isset($_POST['submit'])) ? true : false;
$backout 		= (isset($_POST['backout'])) ? true : false;
$result         = request_var('result', '0');
$ref            = request_var('ref', 0);
$is_member		= request_var('member', false);

$error          = '';
if ($action=='success')
{
    $submit = true;
}
elseif ($action=='cancel')
{
    $backout = 'true';
}
if ($backout)
{
	if (confirm_box(true))
	{
	   $mode='apply';
    }
    else
	{
		if ($action!='confirm')
		{
			confirm_box(false, 'CANCEL_SUBSCRIPTION', build_hidden_fields(array(
				'i'			=> $userid,
				'g'			=> $groupid,
				'mode'		=> 'cancel',
				'action'	=> 'cancel',
			)));
			break;
		}
	}
}

$payment_class = $payment_method . '_class';
$p = new $payment_class; 

$sql_ary = array();
switch ($mode)
{
    case 'paid':

        $billing    = request_var('billing', '');
		if (!$is_member)
		{
            // Not a member so post application form
            
			if ($config['ms_application_forum'])
			{
				$sql_array = array(
					'SELECT'    => 'u.username_clean, pfd.*, g.group_name',
					'FROM'      => array(
						MEMBERSHIP_TABLE=> 'm',
						),
					'LEFT_JOIN' => array(
						array(
							'FROM'  => array(USERS_TABLE => 'u'),
							'ON'    => 'u.user_id = m.user_id'
							),
						array(
							'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
							'ON'    => 'pfd.user_id = m.user_id'
							),
						array(
							'FROM'  => array(GROUPS_TABLE => 'g'),
							'ON'    => 'g.group_id = m.group_id'
							),
						),
					'WHERE'     	=>  'm.user_id = '. $userid . ' AND m.group_id = '. $groupid,
					);
				 $sql=$db->sql_build_query('SELECT', $sql_array);
				 $result = $db->sql_query($sql);
				 $row = $db->sql_fetchrow($result);
				 
				$cpfs = list_cpf();

				$apply_subject  = sprintf($user->lang['APPLICATION_SUBJECT'], $row['username_clean']);
				$apply_post     = sprintf($user->lang['APPLICATION_MESSAGE'], $row['username_clean'],$row['group_name']);

				foreach ($cpfs as $cpf)
				{
				    $cpf['value'] = $row['pf_'.$cpf['field_ident']];
					$apply_post .= $cpf['field_name']. ': ';
					if ($cpf['field_type'] == 4)
					{
					  $apply_post .= ($cpf['value'] ? $user->lang['YES'] : $user->lang['NO']);  
					}
					else
					{ 
						$apply_post .= $cpf['value'];
					}
					$apply_post .= '<br />';
				}
				// variables to hold the parameters for submit_post					
				$poll = $uid = $bitfield = $options = '';
				generate_text_for_storage($apply_post, $uid, $bitfield, $options, true, true, true);
				$data = array(
					'forum_id'		    => $config['ms_application_forum'],
					'topic_id'		    => 0,
					'icon_id'		    => false,
					'enable_bbcode'	    => true,
					'enable_smilies'    => true,
					'enable_urls'	  => true,
					'enable_sig'	   => true,
					'message'		 => $apply_post,
					'message_md5'   	=> md5($apply_post),
					'bbcode_bitfield'	=> $bitfield,
					'bbcode_uid'		=> $uid,
					'post_edit_locked'	=> 0,
					'topic_title'		=> $apply_subject,
					'notify_set'		=> false,
					'notify'			=> false,
					'post_time' 		=> 0,
					'forum_name'		=> '',
					'enable_indexing'	=> true,
					);
					// Sending the post to the forum set in configuration above
				submit_post('post', $apply_subject, '', POST_NORMAL, $poll, $data);
			}
        }
        process_payment($groupid, $userid, true);
	
		// Thank you message goes here
		page_header($user->lang['PAYMENT_PAGE_TITLE']);
		$template->assign_vars(array(
			'S_CONFIRM_ACTION'	=> append_sid("{$phpbb_root_path}index.$phpEx"),
            'MESSAGE_TITLE'     => $user->lang['PAYMENT_MADE'],
            'MESSAGE_TEXT'      => $user->lang['PAYMENT_MADE_TEXT'],
			 ));
		$template->set_filenames(array(
			'body' => 'payment_error.html',
			));
    break;

	case 'associate':
        if ($action=='validate')
        {
            $associate_name = request_var('associate','');
            $error=validate_associate($associate_name,$associate_id);
            if ($error == '')
            {
        		confirm_box(false, 'CHANGE_ASSOCIATE', build_hidden_fields(array(
        			'mode'		=> $mode,
                    'action'    => 'confirm',
                    'i'         => $userid,
                    'g'         => $groupid,
                    'a'         => $associate_id,
        		)));
                break;
            }
            else
            {
                // the associate id is invalid
            }
        }
        else
        {
            if (confirm_box(true))
            {
                $associate_id=request_var('a',0);
                process_associate($userid, $associate_id);
            }
        }
    case 'cancel':
        if ($mode=='cancel')
        {
       		if (confirm_box(true))
       		{
                $sql_array = array(
                	'SELECT'    => 'portal, subscriber_id',
                	'FROM'      => array(
                		MEMBERSHIP_TABLE=> 'm',
                		),
                	'WHERE'     	=>  'm.user_id = '. $userid . ' AND m.group_id = '. $groupid,
                 );
                $sql=$db->sql_build_query('SELECT', $sql_array);
                $result =$db->sql_query($sql);
                $row = $db->sql_fetchrow($result);
                
                $payment_method = $row['portal'];
                $subscriber_id = $row['subscriber_id'];
                $payment_class =$payment_method . '_class';
                $p = new $payment_class;   
                $p->cancel_subscription($subscriber_id, $groupid, $userid);
            }
            else
    		{
    			if ($action!='confirm')
    			{
    				confirm_box(false, 'CANCEL_SUBSCRIPTION', build_hidden_fields(array(
    					'i'			=> $userid,
    					'g'			=> $groupid,
    					'mode'		=> 'cancel',
    					'action'	=> 'confirm',
    				)));
    				break;
    			}
    		}
        }
	case 'apply':
    default:
	{
        // Everything is set up in here 
		$approval_status = request_var('result','none');
		$is_member = true;
		switch ($approval_status)
		{
			case 'paid':
				if (confirm_box(true))
				{
                    process_payment($groupid, $userid, false);
				}
				else
				{
                    if ($action!='confirm')
                    {
    					confirm_box(false, 'PAYMENT_RECEIVED', build_hidden_fields(array(
    						'i'			=> $userid,
    						'g'			=> $groupid,
    						'mode'		=> $mode,
                            'result'    => $approval_status,
                            'action'	=> 'confirm',
    					)));
                    }
				}
            break;

			case 'approved':
				if (confirm_box(true))
				{
                    if ($action!='mark_paid')
                    {
    					process_payment($groupid, $userid, false);
                    }
					mark_approved($userid,$groupid);
				}
				else
				{
					$sql = 'SELECT datepaid FROM ' . MEMBERSHIP_TABLE . " WHERE group_id = {$groupid} AND user_id = {$userid}";
					$result = $db->sql_query($sql);
					$date_paid	= $db->sql_fetchfield('datepaid');
					confirm_box(false, ($date_paid==0) ? PAYMENT_NOT_RECEIVED : MEMBERSHIP_APPROVED, build_hidden_fields(array(
						'i'			=> $userid,
						'g'			=> $groupid,
						'mode'		=> $mode,
                        'result'    => $approval_status,
                        'action'    => 'mark_paid',
                    )));
				}
			break;
	
			case 'rejected':
				if (confirm_box(true))
				{
					remove_member($groupid, $userid);
					log_message('LOG_USER_GROUP_REJECTED', $userid,$groupid);
				}
				else
				{
					confirm_box(false, MEMBERSHIP_REJECTED, build_hidden_fields(array(
						'i'			=> $userid,
						'g'			=> $groupid,
						'mode'		=> $mode,
                        'result'    => $approval_status,
					)));
				}
			break;
                        
			case 'billing':
                $continue 		        = (isset($_POST['continue'])) ? true : false;
                $checkout 		        = (isset($_POST['checkout'])) ? true : false;
                $billing                = request_var('rb_sub_choice',0);
                $subscribing            = request_var('rb_subscription',FALSE);
                $p->params['i']         = $userid;
                $p->params['g']         = $groupid;
                $p->params['billing']   = $billing;
                $p->params['subscribing']= $subscribing;
                $p->params['return']    = 'application';

                if ($subscribing)
                {
                    $p->add_subscription_item();
                }
                else
                {
                    if (!$is_member)
                    {
        				$p->add_cart_item(null , $user->lang['INITIAL_FEE'],$config['ms_group_join_amount']);
                    }
                    $message = sprintf($user->lang['APPLICATION_PURCHASE'],$config['ms_billing_cycle'.$billing], period_text($config['ms_billing_cycle'.$billing.'_basis']));
                    $amount= $config['ms_billing_cycle'.$billing.'_amount'];
                    if ($amount==0)
                    {
                        $message .= ' ' . $user->lang['DONATION'];
                    }
        		    $p->add_cart_item(null , $message, $amount);
                }
                if ($continue)
                {
                    redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
                    
                }
                else
                {
                    redirect(append_sid("{$phpbb_root_path}shopping.$phpEx","ref={$ref}"));
                }
        }            

        if ($user->data['user_type'] !=2)
        {
    		$userid				= $user->data['user_id'];
    		$groupid			= $config['ms_subscription_group'];
            $membership_info    = display_subscription_message($userid, $groupid);
            if ($error != '')
            {
                $membership_info['ERROR_MESSAGE'] = $error . '<br />';
            }
            $is_member=$membership_info['IS_MEMBER'];
    		$template->assign_vars($membership_info);
    
    		if (empty($membership_info['MEMBERSHIP_NO']) && empty($membership_info['IS_ASSOCIATE']))
            {
                $sql = 'INSERT INTO ' . MEMBERSHIP_TABLE . ' (group_id, user_id) VALUES ('. $groupid . ','. $userid . ')';
                $result				= $db->sql_query($sql);
                $membership_info['MEMBERSHIP_NO'] = $db->sql_nextid();
                $membership_info['IS_ASSOCIATE'] = FALSE;
            }

    		page_header($user->lang['MEMBERSHIP_DETAILS_PAGETITLE']);
            if (!$is_member)
            {
        		if (!function_exists('generate_profile_fields'))
                {
                    include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
                }
                
        		$cp = new custom_profile();
        
        		$cp_data = $cp_error = array();
        		if ($submit)
        		{
        			// validate custom profile fields
        			$cp->submit_cp_field('application', $user->get_iso_lang_id(), $cp_data, $cp_error);
        			if (sizeof($cp_error))
        			{
        				$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $cp_error);
        			}
                    else
                    {
            			$cp->update_profile_field_data($user->data['user_id'], $cp_data);
                    }
                }
                if (sizeof($cp_error) || (!$submit))
                {
    			    $user->get_profile_fields($user->data['user_id']);
        			$cp->generate_profile_fields('application', $user->get_iso_lang_id());
                    $template->assign_vars(array(
        				'S_ACTION'	=> append_sid("{$phpbb_root_path}application.$phpEx","mode=apply&i={$userid}&g={$groupid}"),
                        'ERROR'		=> ((sizeof($cp_error)) ? implode('<br />', $cp_error) : ''),			
                        ));		
                    $template->set_filenames(array(
        	    		'body' => 'appform_body.html',
        	    		));
                }
                else
                {
                    // Set up membership options
                    present_billing_cycle();
                    $template->assign_vars(array(
        				'S_ACTION'	=> append_sid("{$phpbb_root_path}application.$phpEx","mode=apply&i={$userid}&g={$groupid}&result=billing&ref={$membership_info['MEMBERSHIP_NO']}"),
                        ));		
        			$template->set_filenames(array(
        			    'body' => 'shopping_item.html',
    		        ));
                }
            }                        
            else    // ALREADY MEMBER
            {
    			if ($approval_status=='index')
    			{
                    present_billing_cycle();
    
                    $template->assign_vars(array(
        				'S_ACTION'	=> append_sid("{$phpbb_root_path}application.$phpEx","mode=apply&i={$userid}&g={$groupid}&result=billing&ref={$membership_info['MEMBERSHIP_NO']}"),
                        ));		
    				$template->set_filenames(array(
    					'body' => 'shopping_item.html',
    				));
    			}
    			else
                {
    				//	Build form
        			// Existing member so show their membership details
    				$template->set_filenames(array(
        	    		'body' => 'membership_details.html',
        	    		));
    
                	$template->assign_vars($membership_info);
        
        			if (empty($is_pending))
        			{
        				
        				// check if authorised to approve applications
        				
        				$approve_applicants = ($auth->acl_get('a_approve_application'));
        				$approve_payment = ($auth->acl_get('a_mark_paid'));
        
        				if ($approve_applicants || $approve_payment)
        				{
        					$sql_array = array(
        			    		'SELECT'    => 'ug.user_id, ug.group_id, ug.user_pending, m.renewal_date, m.datepaid, m.uncleared, m.membership_no, u.username_clean, pfd.*, g.group_name',
        			    		'FROM'      => array(
        			        		USER_GROUP_TABLE => 'ug',
        			    		),
        			    		'LEFT_JOIN' => array(
        	      	  				array(
        	            				'FROM'  => array(USERS_TABLE => 'u'),
        	            				'ON'    => 'u.user_id = ug.user_id'
        	        				),
        	      	  				array(
        	            				'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
        	            				'ON'    => 'pfd.user_id = ug.user_id'
        	        				),
        	      	  				array(
        	            				'FROM'  => array(GROUPS_TABLE => 'g'),
        	            				'ON'    => 'g.group_id = ug.group_id'
        	        				),
        	      	  				array(
        	            				'FROM'  => array(MEMBERSHIP_TABLE => 'm'),
        	            				'ON'    => 'm.group_id = ug.group_id AND m.user_id = ug.user_id'
        	        				),
        	    				),
        			    		'WHERE'     	=>  '(ug.user_pending = '. true . ' OR m.uncleared = '. true . ')',
        			    		'ORDER_BY'		=> 'm.group_id ASC',
        					);
        					$sql=$db->sql_build_query('SELECT', $sql_array);
        					$result = $db->sql_query($sql);
        					$no_applicants = true;
        					$last_group = '';
        					
                            $cpfs = list_cpf();
                            
                            $display_bits = array();
    
        					while ($row = $db->sql_fetchrow($result))
        					{
                                foreach ($cpfs as $cpf)
                                {
                                    $display_bits[strtoupper($cpf['field_ident'])] = $row['pf_'.$cpf['field_ident']];
                                }
        						$no_applicants = false;
        						$url_approved = append_sid("{$phpbb_root_path}application.$phpEx", "mode=apply&result=approved&i={$row['user_id']}&g={$row['group_id']}");
        						$url_rejected = append_sid("{$phpbb_root_path}application.$phpEx", "mode=apply&result=rejected&i={$row['user_id']}&g={$row['group_id']}");
        						$url_paid = append_sid("{$phpbb_root_path}application.$phpEx", "mode=apply&result=paid&i={$row['user_id']}&g={$row['group_id']}");
        						
        						if ($row['group_id'] != $last_group)
        						{
        							$last_group=$row['group_id'];
        					        $template->assign_block_vars('groups', array(
        					            'GROUP_ID'	=> $row['group_id'],
        					            'GROUP_NAME'=> $row['group_name'],
        					        ));
        				        }
        		                $template->assign_block_vars('groups.applicants', array_merge($display_bits, array(
        							'USERNAME'		=> $row['username_clean'],
        							'ID'			=> $row['membership_no'],
        							'APPROVE'		=> $url_approved,
        							'REJECT'		=> $url_rejected,
        							'PAID'			=> $url_paid,
        							'NOT_PAID_YET'	=> ($row['uncleared'] || empty($row['datepaid'])),
        							'APPLYING'		=> ($row['user_pending'] && $approve_applicants),
            						'RENEWAL_DATE'	=> (!$row['renewal_date']) ? ' - ' : $user->format_date($row['renewal_date'], $config['ms_membership_date_format']),
        						)));
    
        					}
        					$template->assign_vars(array(
                                'APPROVE_PAYMENT'   => $approve_payment,
                                'LIST_APPLICANTS'	=> $approve_applicants,
        						'S_NO_APPLICANTS'	=> $no_applicants,
        					));
        					$db->sql_freeresult($result);
                        }
    				}
    			}
    		}
        }
    }
}

page_footer();
?>
