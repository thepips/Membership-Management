<?php
/**
* @package IPNListener.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
define('IN_PHPBB', true);
$phpbb_root_path = './'; // See phpbb_root_path documentation
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session
$user->session_begin();

include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_membership.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);			// required for group_user_del
include($phpbb_root_path . 'includes/paypal_class.' . $phpEx);  			// include the class file

global $db, $user, $auth, $template;
global $config, $phpbb_root_path, $phpEx;

// Paypal is calling page for Express Checkout validation...
$p = new paypal_class();
$result = $p->validate_ipn();
if ($result != 'fail') 
{
  
	// IPN has been received and is verified.  
	
	switch (strtoupper($p->fields['txn_type']))
	{
		// Check for new recurring payment profile

		case 'RECURRING_PAYMENT_PROFILE_CREATED':
			// Should already be set up as subscriber
			// but set subscriber field
			
			$vars_array = array(
				'portal'			=> 'paypal', 
				'subscriber_id'		=> $p->subscriber_id,
			);
			list($groupid, $userid) = explode('-',$p->fields['rp_invoice_id']);

			if ((!empty($p->fields['period1']) && empty($p->fields['amount1'])) || (!empty($p->fields['period2']) && empty($p->fields['amount2'])))
			{
				$cleared = true; // Free trial period so effectively cleared
				$vars_array['renewal_date'] = strtotime($p->fields['next_payment_date']);
			}
			else
			{
				$cleared = false; // no trial period specified or chargeable trial period
				$vars_array['renewal_date'] = time(); 
			}
			process_payment($groupid, $userid, $cleared);
			update_membership($groupid, $userid, $vars_array);

		break;

		case 'RECURRING_PAYMENT_PROFILE_CANCEL': 			// Turn off as subscriber
			list($groupid, $userid) = explode('-',$p->fields['rp_invoice_id']);
			if (cancel_recurring_payment($p->fields['recurring_payment_id'], $params['g'], $params['i']))
			{
				$sql = 'UPDATE ' . MEMBERSHIP_TABLE . " SET subscriber_id='', portal='' WHERE group_id='{$groupid}' AND user_id='{$userid}'";
				$db->sql_query($sql);	
			 	$p->write_results("Paypal Subscription cancelled for user-group {$params['i']} - {$params['g']}");
			}
			else
			{
				$p->write_results($p->last_error);
			}
		break;

		case 'RECURRING_PAYMENT_PROFILE_MODIFY':
			// Not used yet but will allow for optional costs/periods
		break;
	
		// Check for recurring payment
		
		case 'RECURRING_PAYMENT':
			if ($p->fields['payment_status'] == 'Completed')
			{
				// Set renewal date
				list($groupid, $userid) = explode('-',$p->fields['rp_invoice_id']);
				process_payment($groupid, $userid, false);
			 	$p->write_results("Paypal recurring payment received for user-group {$groupid} - {$userid}");
			}
			set_renewal_date($groupid, $userid, (strtotime($p->fields['next_payment_date'])));
			$db->sql_query($sql);		
		break;
		case 'EXPRESS_CHECKOUT':
			if ($p->fields['payment_status'] == 'Completed')
			{
				list($groupid, $userid) = explode('-',$p->fields['rp_invoice_id']);
				process_payment($groupid, $userid, false, (strtotime($p->fields['NEXTBILLINGDATE'])));//TODO:Check the nextbilling date is ok for express checkout
			}
		break;
		case 'RECURRING_PAYMENT_SKIPPED':
			// not sure
		break;
		case 'RECURRING_PAYMENT_FAILED': 
			// Paypal should send notification so I think we can ignore
		break;
		case 'RECURRING_PAYMENT_SUSPENDED_DUE_TO_MAX_FAILED_PAYMENT':
			// advise user
			// turn off subscription
			// Should we can cancel subscription?
		break;
	
		// Any other type of IPN can be treated as a normal order
		// Refunds come back with the same txn_type of the original payment so we skip order.php 
		// for refunds because refund.php will take care of updating the existing record data
		
		case 'CART':
		break;

		case 'VIRTUAL_TERMINAL':
		break;
		case 'WEB_ACCEPT':
		break;
		case 'SEND_MONEY':
		break;
		default:
			$p->write_results(array('Unsupported Transaction type',' IPNListener  ' . $p));
		break;
		
	}
}
?>
