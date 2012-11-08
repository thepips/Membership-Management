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

$in_registration			= request_var('r', 0);
if (!$in_registration)
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		login_box('', $user->lang['LOGIN_APPLICATION_FORM']);
	}
}
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);
include($phpbb_root_path . 'includes/currency_format.' . $phpEx);

global $db, $user, $auth, $template;
global $config, $phpbb_root_path, $phpEx;

$mode					= request_var('mode', '');
$type					= request_var('type', '');
$userid					= request_var('i', $user->data['user_id']);


$payment_enabled		= !empty($config['pp_enable_payment']);

$payment_method			= request_var('method', 'payment');
$action					= request_var('action','');
$submit					= (isset($_POST['submit'])) ? true : false;
$backout				= (isset($_POST['backout'])) ? true : false;
$ref					= request_var('ref', 0);
$billing				= request_var('billing', '');

// 1st check to see if this user is already set up as an associate

$sql_array = array(
	'SELECT'	=> 'm.membership_no',
	'FROM' 		=> array(MEMBERSHIP_TABLE => 'm'),
	'WHERE'		=> "m.associate_id = {$userid}"
);

$sql			= $db->sql_build_query('SELECT', $sql_array);
$result			= $db->sql_query($sql);
$membership_no	= $db->sql_fetchfield('membership_no');

// finding a row with the associate id = userid means it's an associate, not a full member

if (empty($membership_no))
{
	$is_associate	= false;
	$sql 			= 'SELECT membership_no, associate_id FROM ' . MEMBERSHIP_TABLE . " WHERE user_id={$userid}";
	$result 		= $db->sql_query($sql);
	$row 			= $db->sql_fetchrow($result);

	$db->sql_freeresult($result);

	$is_member 		= !empty($row);
	if ($is_member)
	{
		$membership_no	= $row['membership_no'];
		$associate_id	= $row['associate_id'];
	}
}
else
{
	$is_associate	= true;
	$is_member		= true;
}

$payment_class = $payment_method . '_class';
if (!class_exists($payment_class))
{
	include($phpbb_root_path . 'includes/' . $payment_class . '.' . $phpEx);
}
$p = new $payment_class; 
if ($mode == 'renew')
{
	if(isset($_POST['edit_associate']))
	{
		$mode='associate';
	}
	elseif (isset($_POST['cancel_sub']))
	{
		$mode='cancel';
	}
}
elseif($mode == '')
{
	if (!$is_member)
	{
		$mode = 'apply';
	}
}
$error		= '';
if ($action == 'success')
{
	$submit	= true;
}
elseif ($action == 'cancel')
{
	$backout = 'true';
}
if ($backout)
{
	if (confirm_box(true))
	{
      $p->cancel_subscription_item(0);      
      redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}
	else
	{
		if ($action != 'confirm')
		{
			confirm_box(false, 'QUIT_SUBSCRIPTION', build_hidden_fields(array(
				'i'			=> $userid,
				'mode'		=> $mode,
				'action'	=> 'confirm',
				'backout'	=> 'yes',
			)));
		}
		else
		{
			$mode	= 'renew';
		}
	}
}

page_header($user->lang['MEMBERSHIP_DETAILS_PAGETITLE']);

$sql_ary = array();

if ($mode=="{L_ADD_TO_CART")
{
	$mode='billing';
}

$display_user_details=true;

switch ($mode)
{
	case 'mark_paid':
	{
		if (confirm_box(true))
		{
			process_payment($userid, false);
		}
		else
		{
			if ($action != 'confirm_paid')
			{
				confirm_box(false, 'PAYMENT_RECEIVED', build_hidden_fields(array(
					'i'			=> $userid,
					'mode'		=> $mode,
					'billing'	=> $billing,
					'action'	=> 'confirm_paid',
				)));
			}
		}
	}
	break;

	case 'mark_approved':
	{
		if (confirm_box(true))
		{
			if ($action != 'confirm_approved')
			{
				process_payment($userid, false);
			}
			mark_approved($userid);
		}
		else
		{
			$sql 			= 'SELECT datepaid FROM ' . MEMBERSHIP_TABLE . " WHERE user_id = {$userid}";
			$result 		= $db->sql_query($sql);
			$date_paid		= $db->sql_fetchfield('datepaid');
			confirm_box(false, ($date_paid==0 ? 'PAYMENT_NOT_RECEIVED' : 'MEMBERSHIP_APPROVED'), build_hidden_fields(array(
				'i'			=> $userid,
				'mode'		=> $mode,
				'action'	=> 'confirm_approved',
			)));
		}
	}
	break;

	case 'mark_rejected':
	{
		if (confirm_box(true))
		{
			remove_member($userid);
			log_message('LOG_USER_GROUP_REJECTED', $userid);
		}
		else
		{
			if ($action != 'confirm_rejected')
			{
				confirm_box(false, 'MEMBERSHIP_REJECTED', build_hidden_fields(array(
					'i'			=> $userid,
					'mode'		=> $mode,
					'action'	=> 'confirm_rejected',
				)));
			}
		}
	}
	break;

	case 'billing':
	{

		$billing				= request_var('rb_sub_choice',1);
		$subscribing			= request_var('rb_subscription',FALSE);
		$p->params['i']			= $userid;
		$p->params['billing']	= $billing;
		$p->params['subscribing']= $subscribing;
		$p->params['return']	= 'application';
		$p->params['r']			= request_var('r',0);

		if (!$subscribing)
		{
			if (!$is_member && !empty($config['ms_group_join_amount']))
			{
				$p->add_cart_item(null , $user->lang['INITIAL_FEE'],$config['ms_group_join_amount']);
			}
			$line_desc = sprintf($user->lang['APPLICATION_PURCHASE'],$config['ms_billing_cycle'.$billing], $user->lang($config['ms_billing_cycle'.$billing.'_basis']));
			$amount= $config['ms_billing_cycle'.$billing.'_amount'];
			if ($amount==0)
			{
				$line_desc .= ' ' . $user->lang['DONATION'];
			}
			$p->add_cart_item(null , $line_desc, $amount);
		}
		else
		{
			if (!$is_member)
			{
				$startdate=time();
			}
			else
			{
				$sql = 'SELECT renewal_date FROM ' . MEMBERSHIP_TABLE . " WHERE user_id={$userid}";
				$result = $db->sql_query($sql);
				$startdate=max($db->sql_fetchfield('renewal_date'), time());
			}

			$p->add_subscription_item(
				$config['ms_billing_cycle'.$billing.'_group'] . '-' . $userid, 
				0, 
				sprintf($user->lang['APPLICATION_PURCHASE'] . ' ' . $user->lang['SUBSCRIPTION'], $config['ms_billing_cycle'.$billing], $user->lang[$period_text[$config['ms_billing_cycle'.$billing.'_basis']]]), 
				$amount= $config['ms_billing_cycle'.$billing.'_amount'], 
				$startdate, 
				!($is_member), 
				$config['ms_billing_cycle'.$billing], 
				$config['ms_billing_cycle'.$billing.'_basis'], 
				$config['ms_subscription_extra_days'], 
				$config['ms_group_join_amount']
			);
		}
		if ($payment_enabled) redirect(append_sid("{$phpbb_root_path}shopping.$phpEx","ref={$ref}"));
	}
	
	case 'paid':
	{
		$uncleared	= request_var('status', $config['ms_process_on_payment']);
		$billing	= request_var('billing', $billing);

		process_payment($userid, $uncleared, $billing);
		if (!$is_member)
		{
			if (!empty($config['ms_application_forum']))
			{
				// NEW APPLICATION SO WE CAN NOW POST TO FORUM
				$sql_array = array(
				'SELECT'    => 'u.username_clean, pfd.*, m.associate_id, m.group_id, m.user_id, g.group_name',
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
						'ON'    => 'g.group_id = m.group_id'
							),
						array(
							'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
							'ON'	=> 'pfd.user_id = m.user_id'
							),
						),
					'WHERE'			=>  'm.user_id = '. $userid,
					);
				$sql=$db->sql_build_query('SELECT', $sql_array);
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);

				$apply_subject  = sprintf($user->lang['APPLICATION_SUBJECT'], $row['username_clean']);
				$apply_post	= sprintf($user->lang['APPLICATION_MESSAGE'], $row['username_clean'],$row['group_name']);

				$cpfs = list_cpf();

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
					'forum_id'			=> $config['ms_application_forum'],
					'topic_id'			=> 0,
					'icon_id'			=> false,
					'enable_bbcode'		=> true,
					'enable_smilies'	=> true,
					'enable_urls'		=> true,
					'enable_sig'		=> true,
					'message'			=> $apply_post,
					'message_md5'		=> md5($apply_post),
					'bbcode_bitfield'	=> $bitfield,
					'bbcode_uid'		=> $uid,
					'post_edit_locked'	=> 0,
					'topic_title'		=> $apply_subject,
					'notify_set'		=> false,
					'notify'			=> false,
					'post_time'			=> 0,
					'forum_name'		=> '',
					'enable_indexing'	=> true,
					'post_approved'		=> true,
					);
					// Sending the post to the forum set in configuration above
				submit_post('post', $apply_subject, '', POST_NORMAL, $poll, $data);
			}
		}
	
		// Thank you message goes here
		page_header($user->lang['PAYMENT_PAGE_TITLE']);
		$template->assign_vars(array(
			'S_CONFIRM_ACTION'	=> append_sid("{$phpbb_root_path}index.$phpEx"),
			'MESSAGE_TITLE'	=> $user->lang['PAYMENT_MADE'],
			'MESSAGE_TEXT'	=> $user->lang['PAYMENT_MADE_TEXT'],
			));
		$template->set_filenames(array(
			'body' => 'payment_error.html',
			));
		$display_user_details=false;
	}
	break;

	case 'renew':
	{
		present_billing_cycle();

		$template->assign_var('S_ACTION', append_sid("{$phpbb_root_path}application.$phpEx","i={$userid}&mode=billing&ref={$membership_no}&r={$in_registration}"));
		$template->assign_var('GIVE_OPTION', (!$in_registration && subscription_enabled()));
		$template->set_filenames(array(
			'body' => 'subscription.html',
		));
		$display_user_details=false;
	}
	break;
	
	case 'associate':
	{
		if (confirm_box(true))
		{
			$associate_id=request_var('a',0);
			process_associate($userid, $associate_id);
		}
		else
		{
			if ($action != 'confirm')
			{
				$associate_name = request_var('associate','');
				$error=validate_associate($associate_name,$associate_id);
				if ($error == '' && $associate_id>-1)
				{
					confirm_box(false, 'CHANGE_ASSOCIATE', build_hidden_fields(array(
						'mode'		=> $mode,
						'action'	=> 'confirm',
						'i'			=> $userid,
						'a'			=> $associate_id,
					)));
					break;
				}
			}
		}
	}
	break;
	
	case 'cancel':
	{
		if ($mode=='cancel')
		{
			if (confirm_box(true))
			{
				$sql_array = array(
					'SELECT'			=> 'portal, subscriber_id',
					'FROM'				=> array(
						MEMBERSHIP_TABLE=> 'm',
						),
					'WHERE'				=>  'm.user_id = '. $userid,
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
				if ($action != 'confirm')
				{
					confirm_box(false, 'CANCEL_SUBSCRIPTION', build_hidden_fields(array(
						'i'			=> $userid,
						'mode'		=> 'cancel',
						'action'	=> 'confirm',
					)));
					break;
				}
			}
		}
	}
	break;

	case 'apply':
	{
		if (!function_exists('generate_profile_fields'))
		{
			include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
		}
		$cp = new custom_profile();

		$cp_data = $cp_error = array();
		$bill = true;
		if ($submit)
		{
			// validate custom profile fields
			$cp->submit_cp_field('application', $user->get_iso_lang_id(), $cp_data, $cp_error);
			if (sizeof($cp_error))
			{
				$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $cp_error);
				$template->assign_vars(array(
					'ERROR'		=> (implode('<br />', $cp_error)),			
				));
				$bill = false;		
			}
			else
			{
				$cp->update_profile_field_data($user->data['user_id'], $cp_data);
			}
		}
		else
		{
		    $user->get_profile_fields($user->data['user_id']);		
			$num_cpfs = $cp->generate_profile_fields('application', $user->get_iso_lang_id());
			if ($num_cpfs > 0)
		{
			$bill = false;
		}
		}
		if ($bill)
		{
			present_billing_cycle(); // Select subscription period and charge
			$template->assign_var('GIVE_OPTION', (!$in_registration && subscription_enabled()));
			$template->assign_var('S_ACTION',		append_sid("{$phpbb_root_path}application.$phpEx","mode=billing&i={$userid}&r={$in_registration}"));
			$template->set_filenames(array(
				'body'			=> 'subscription.html',
			));
		}
		else
		{
			$template->assign_vars(array(
				'S_ACTION'		=> append_sid("{$phpbb_root_path}application.$phpEx","mode=apply&i={$userid}"),
			));		
			$template->set_filenames(array(
				'body'			=> 'appform_body.html',
			));
		}
		$display_user_details=false;
	}
	break;
}
	
if ($display_user_details)
{
	// Existing member so show their membership details

	$userid				= $user->data['user_id'];
	
	$membership_info	= display_subscription_message($userid, $in_registration);
	$template->set_filenames(array(
		'body' => 'membership_details.html',
		));
	if ($error != '')
	{
		$membership_info['ERROR_MESSAGE'] = $error . '<br />';
	}
	$template->assign_vars($membership_info);

	if ($in_registration || $user->data['user_type'] != 2)
	{
		//	Build form

		if (empty($is_pending))
		{

			// check if authorised to approve applications

			$approve_applicants = ($auth->acl_get('a_approve_application'));
			$approve_payment = ($auth->acl_get('a_mark_paid'));

			if ($approve_applicants || $approve_payment)
			{
				$sql_array = array(
					'SELECT'	=> 'pfd.*, ug.user_id, ug.group_id, ug.user_pending, m.renewal_date, m.datepaid, m.uncleared, m.membership_no, u.username_clean, g.group_name',
					'FROM'	=> array(
						MEMBERSHIP_TABLE => 'm',
					),
					'LEFT_JOIN' => array(
						array(
							'FROM'  => array(USER_GROUP_TABLE => 'ug'),
							'ON'	=> 'ug.group_id = m.group_id AND ug.user_id = m.user_id'
						),
						array(
							'FROM'  => array(USERS_TABLE => 'u'),
							'ON'	=> 'u.user_id = m.user_id'
						),
						array(
							'FROM'  => array(PROFILE_FIELDS_DATA_TABLE => 'pfd'),
							'ON'	=> 'pfd.user_id = m.user_id'
						),
						array(
							'FROM'  => array(GROUPS_TABLE => 'g'),
							'ON'	=> 'g.group_id = m.group_id'
						),
					),
					'WHERE'		=>  '(ug.user_pending = '. true . ' OR m.uncleared = '. true . ') AND ug.user_id IS NOT NULL',
					'ORDER_BY'		=> 'ug.group_id ASC',
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
					$no_applicants	= false;
					$url_approved	= append_sid("{$phpbb_root_path}application.$phpEx", "mode=mark_approved&i={$row['user_id']}");
					$url_rejected	= append_sid("{$phpbb_root_path}application.$phpEx", "mode=mark_rejected&i={$row['user_id']}");
					$url_paid		= append_sid("{$phpbb_root_path}application.$phpEx", "mode=mark_paid&i={$row['user_id']}");
					
					if ($row['group_id'] != $last_group)
					{
						$last_group	= $row['group_id'];
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
					'APPROVE_PAYMENT'	=> $approve_payment,
					'LIST_APPLICANTS'	=> $approve_applicants && !$no_applicants,
				));
				$db->sql_freeresult($result);
			}
		}
	}
}
page_footer();
?>
