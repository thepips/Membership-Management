<?php
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))

{
	exit;

}

if (empty($lang) || !is_array($lang))

{
	$lang = array();

}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
    'ADD_TO_BASKET'                 => 'ADD TO SHOPPING BASKET',
	'APPLICATION_MESSAGE'			=> 'A new user has signed up by the application form to join %2$s, called 
[b] %1$s
[/b].<br /><br />',
	'APPLICATION_PAGETITLE'			=> 'Membership application form',
    'APPLICATION_PURCHASE'          => 'Premium Membership for %1$s %2$s',
	'APPLICATION_RECEIVED'			=> 'Thank you for your membership application. Your membership status is currently Pending. <BR />As soon as we have received your payment your membership status will be changed to Active.<BR />',
	'APPLICATION_SEND'				=> 'Your application has been sent to the membership secretary. You will now be passed to the payment portal. Two charges will be applied, a one-off £5 joining fee and the annual membership fee of £25, £30 in total. You can either pay by cheque or through Paypal.',
	'APPLICATION_SUBJECT'			=> 'Application from %s',
	'APPLICATION_WELCOME_MESSAGE'	=> 'We hope that you enjoy what you have found on this website and would like to become more involved in our club.<br /><br />Should you have any questions about the club, our activities or our members please feel free to contact any of the committee members or the admin team or staff on the website, we will all be happy to help.<br /><br />We look forward to greeting you all soon<br /><br />The committee<br /><br />The annual membership fee is £25 with a one-off joining fee of £5. Please fill the form in as fully as you can. ',
    'ASSOCIATE_IN_USE'              => 'Associate name %s is already linked to another member',
    'ASSOCIATE_MEMBER'              => 'You an associate member and do not need to take any action',
    'ASSOCIATE_NAME'                => 'Associate Name',
    'ASSOCIATE_NAME_EXPLAIN'        => 'Each premium member is allowed to have another account associate with it. This associated account is granted exactly the same permissions and membership is managed concurrently with the main account',
    'ASSOCIATE_WRONG'               => 'Associate name %s does not exist',
    'BACK'                          => 'Back',
    'BILLING_CYCLE_CHARGE'          => '%1$16s for %2$s %3$s',
	'CANCEL_APPLICATION'			=> 'Cancel Payment Process',
	'CANCEL_APPLICATION_CONFIRM'	=> 'Are you sure you want to cancel processing your payment?',
	'CANCEL_SUBSCRIPTION'			=> 'Cancel Subscription Process',
	'CANCEL_SUBSCRIPTION_CONFIRM'	=> 'Are you sure you want to cancel processing your subscription?',
    'CHANGE_ASSOCIATE_CONFIRM'      => 'Change the associate?',
    'CHECKOUT'                      => 'Checkout',
    'CONTINUE_SHOPPING'             => 'Continue Shopping',
    'CONTINUE'                      => 'Continue',
    'DATEPAID'                     => 'Date Paid',
    'DATEPAID_EXPLAIN'             => 'The date the subscription to the premium group was paid',
    'DONATION'                      => 'Donation',
    'INITIAL_FEE'                   => 'Joining Fee',
    'INVALID_DATE'                  => 'The date is invalid',
	'LOGIN_APPLICATION_FORM'		=> 'You need to login before you can send out an application.',
    'MARK_APPROVED'                     => 'Approved',
    'MARK_PAID'                     => 'Mark Paid',
    'MARK_REJECTED'                     => 'Rejected',
	'MEMBERSHIP_APPROVED_CONFIRM'	=> 'Approve membership?',
	'MEMBERSHIP_DETAILS_PAGETITLE'	=> 'Membership Number',
    'MEMBERSHIP_NO'                 => 'Membership Number',
    'MEMBERSHIP_NO_EXPLAIN'         => 'This is the unique premium membership number',
    'MEMBERSHIP_NO_IN_USE'          => 'This membership number is in use by %s',
	'MEMBERSHIP_PENDING'			=> 'Your application has not been approved yet',
	'MEMBERSHIP_REJECTED_CONFIRM'	=> 'membership application rejected?',
	'MS_ADDRESS'			=> 'Address',
	'MS_EMAIL'				=> 'E-mail address',
	'MS_FAX'				=> 'Fax Number',
	'MS_MAKE'				=> 'Make and Colour',
	'MS_MOBILE'			=> 'Mobile Number',
	'MS_PERMISSION'		=> 'Can we publish your details?',
	'MS_PHONE'				=> 'Phone Number',
	'MS_POSTCODE'			=> 'Post Code',
	'MS_REALNAME'			=> 'Name',
	'MS_REG'				=> 'Registration',
	'N0_APPLICANTS'					=> 'There are no applications outstanding',
    'NOT_MEMBER'                    => 'This user is not a premium group member',
    'ONE_OFF_PAYMENT'               => 'One Off Payment',
	'RENEW_MEMBERSHIP_CONFIRM'		=> 'Thank you for renewing your membership. Continue?',
	'RENEWAL_RECEIVED'			=> 'Thank you for renewing your membership.<BR />As soon as we have received your payment your membership status will be updated.<br />',
    'PAYMENT_MADE'                     => 'Payment Made',
    'PAYMENT_MADE_TEXT'             =>"Thank you for your payment. As soon as your chosen payment method has been received your membership status will be updated. <br />Welcome to the club",
	'PAYMENT_NOT_RECEIVED_CONFIRM'	=> 'Warning - user is not flagged as having paid. Approve anyway?',
	'PAYMENT_PAGETITLE'				=> 'Membership Payment form',
    'PAYMENT_PAGE_TITLE'            => 'Payment Completed',
    'PAYMENT_PENDING'               => '<BR /> You sent a payment on %1$s which has not yet cleared',
	'PAYMENT_RECEIVED_CONFIRM'		=> 'Payment has been received?',
	'PAYMENT_REFERENCE'				=> 'Please quote the following reference on your payment:- ',
	'PENDING_APPLICATIONS'			=> 'Membership requiring action',
	'PAY_BY_CHEQUE'					=> 'Please send your payment cheque to :-<BR />',
	'PAY_BY_EFT'					=> 'Please arrange to send your membership payment to our account:-',
	'PP_CHEQUE_ADDRESS'				=> 'Address',
	'PP_EFT_BANKNAME'				=> 'Bank Name',
	'PP_EFT_BANKCODE'				=> 'Bank Sort Code',
	'PP_EFT_ACCOUNT'				=> 'Bank Account Number',
	'PP_EFT_BANKADDRESS'			=> 'Bank Address',
	'PP_PAYMENT_METHOD_CHEQUE'		=> 'Cheques',
	'PP_PAYMENT_METHOD_CHEQUE_EXPLAIN'=> 'Send payment by cheque',
	'PP_PAYMENT_METHOD_EFT'			=> 'Electronic Funds Transfer',
	'PP_PAYMENT_METHOD_EFT_EXPLAIN'	=> 'Send payment by bank transfer',
	'PP_PAYMENT_METHOD_PAYPAL'		=> 'Paypal',
	'PP_PAYMENT_METHOD_PAYPAL_EXPLAIN'=> 'Pay by credit card through Paypal',
    'PROMPT_CHOICE'                 => 'Select one:',
    'RENEWAL'                       => 'Renewal Date',
    'RENEWAL_EXPLAIN'               => 'Allows you to reset the membership date',
    'RENEWAL_PROMPT_0'              => 'Your membership renewal is not due until %1$s',
    'RENEWAL_PROMPT_1'              => 'Your membership renewal is due soon. Please could you renew before %1$s',
    'RENEWAL_PROMPT_2'              => 'Your membership runs out on %1$s and renewal is overdue',    
    'RENEWAL_PROMPT_3'              => 'Your membership was due on %1$s and will be revoked if you do not make arrangements to pay',
    'RENEWAL_PROMPT_4'              => 'Your membership was due on %1$s and is about to be revoked.',
    'SUBSCRIBER'                    => 'You do not need to do anything as you have a subscription in effect',
    'SUBSCRIPTION'                  => 'Subscription',
	'USER_ID'						=> 'Membership Number/<br />User Id',
    'USER_MEMBERSHIP'               => 'Premium Group Membership',
));
?>