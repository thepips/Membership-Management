<?php
/**
*
* info_acp_qi [English]
*
* @package language
* @version $Id: info_acp_qi.php 37 2008-03-13 18:19:39Z evil3 $
* @copyright (c) 2008 evil3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

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

$lang = array_merge($lang, array(
	'ACP_MEMBERSHIP_USERS'			   => 'Members',
	'ACP_MEMBERSHIP_SETTINGS'          => 'Membership Settings',
	'ACP_MEMBERSHIP'                   => 'Membership Management',
    'ACP_USERS_MEMBERSHIP'          => 'Premium Membership',
    'ALL_MEMBERS'					=> 'Members',
    'ALLOW_ASSOCIATES'              => 'Allow Associate Members',
    'ALLOW_ASSOCIATES_EXPLAIN'      => 'An additional user account can be associated with a paid-up member. They do not pay a subcription but have all of the rights of a full member',
	'APPLICATION_FORUM'				=> 'Forum to post application details into',
	'APPLICATION_FORUM_EXPLAIN'		=> 'Enter the code number of the forum you want the application details to be posted to.',
	'APPROVAL_REQUIRED' 		=> 'Approval by User Admin required',
	'APPROVAL_REQUIRED_EXPLAIN' => 'Enable this setting if you require potential members still to be approved by a User Administrator. It will appear in ACP group members list as a pending member.',
    'ASSOCIATE_RANK'                => 'Associates Rank',
    'BACK'                          => 'Back',
    'BILLING_CYCLE'					=> 'Billing Cycle',
	'BILLING_CYCLE_EXPLAIN'			=> 'Enter the charge per period for membership. You can set this field to 0 for user determined donations. The number of days, weeks, months or years in a billing period',
	'DISPLAY_MEMBERS'				=> 'Display accounts that are ',
	'DUE_NEXT_MONTH'				=> 'Membership Due next month',
    'DUE_PERIOD'                   => 'Membership is now due alert',
    'DUE_PERIOD_EXPLAIN'           => 'How long before membership expires should the user receive a notification that their membership is due for renewal. Enter the number of day(s), week(s), month(s) or year(s)',
    'DUE_SOON_PERIOD'              => 'Membership due soon alert',
    'DUE_SOON_PERIOD_EXPLAIN'      => 'How long before membership expires should the user receive a notification that their membership will soon be due. Enter the number of day(s), week(s), month(s) or year(s)',
	'DUE_THIS_MONTH'				=> 'Membership Due this month',
    'ENABLE_MEMBERSHIP'             => 'Enable Membership',
    'ENABLE_MEMBERSHIP_EXPLAIN'     => 'Enable or disable Membership Mod',
	'ENABLE_NOTIFY_ERR_EMAIL' 		=> 'Enable to notify admin in case of error on notify_url',
	'ENABLE_NOTIFY_ERR_EMAIL_EXPLAIN' => 'In case there is error detected when payment gateway acesses the notify url, send alert email to the board admin. Donot turn it on if your email system is not functioning.',
	'GENERATE'						=> 'Generate Mailing List',
	'GRACE_PERIOD'					=> 'Grace Period length',
	'GRACE_PERIOD_EXPLAIN'			=> 'How long after subscription expires before the account is disabled',
    'LAST_CHANCE_PERIOD'            => 'Membership is now about to be cancelled alert',
    'LAST_CHANCE_PERIOD_EXPLAIN'    => 'How long before/after membership expires should the user receive a final notification that their membership is about to be revoked. Enter the number of day(s), week(s), month(s) or year(s). Enter a negative amount to send the notification AFTER expiry',
	'MEMBERSHIP_DATE_FORMAT'		=> 'Date format',
	'MEMBERSHIP_DATE_FORMAT_EXPLAIN'=> 'Enter the format for the date to be used throughout the membership system. Leave blank to use the forum default date formatting',
    'MEMBERSHIP_DATE_FORMAT_TITLE'	=> 'Date Format',
	'MEMBERSHIP_MANAGEMENT'			   => 'Membership Management',
	'MEMBERSHIP_MANAGEMENT_EXPLAIN'		=> 'This is a list of users who are paid members as distinct from members of the forum. You can change the date of membership and other clever stuff.activate, delete or remind (by sending an e-mail) these users if you wish.',
	'NO_CLUB_MEMBERS'				=> 'No Members',
	'NO_RENEWAL_DATE'				=> 'Missing Renewal Date',
    'NOT_MEMBERS'					=> 'Not members',
	'OVERDUE'						=> 'Overdue Club Members',
    'OVERDUE_PERIOD'               => 'Membership is now overdue alert',
    'OVERDUE_PERIOD_EXPLAIN'       => 'How long before membership expires should the user receive a notification that their membership has lapsed. Enter the number of day(s), week(s), month(s) or year(s)',
    'PROCESS_ON_PAYMENT'               => 'Payment required prior to processing',
    'PROCESS_ON_PAYMENT_EXPLAIN'       => 'Select Yes and user membership will be processed on receipt of payment. Select No if you want to process immediately',
    'PERIOD_START'                  => 'Set when period starts',
    'PERIOD_START_EXPLAIN'          => 'Enter 0 for today, -1 for start of month, 1 for start of next month or 2 to round to closest',
	'PF_REALNAME'					   => 'Real Name',
    'RANK'                          => 'Rank',
    'RANK_EXPLAIN'                  => 'Users will be given this rank when they become premium members',
    'JOIN'                          => 'Join',
	'REMOVE'						=> 'Remove',
	'RENEW'							=> 'Renew',
	'RENEWAL_CONFIRMATION'			=> 'Confirm membership renewal',
	'RENEWAL_DATE'                     => 'Renewal date',
	'SEARCH_USER_POSTS'            => 'Search Users posts',
	'SORT_REALNAME'					=> 'Real Name',
	'SORT_RENEWAL'					=> 'Renewal date',
	'SORT_REMINDER'					=> 'Reminder sent',
	'SORT_USERNAME'					=> 'Forum Name',
	'SUBSCRIPTION_CHARGES_SETTINGS'	=> 'Subscription Charges',
	'SUBSCRIPTION_EMAIL_ALERT_DAYS' => 'Define days that you want to send alert email',
	'SUBSCRIPTION_EMAIL_ALERT_DAYS_EXPLAIN' => 'Send email in the number of days before the expiration date. you can define multiple days, for example: 7,3 (you must use <b>,</b> or <b>;</b> to seperate the number)<br />In above example, alert email will be sent 7 days and 3 days before the expiration date.<br />If you donot want to send alert email, leave this field empty.',
	'SUBSCRIPTION_EXTRA_DAYS' 		=> 'Give extra days to subscriber',
	'SUBSCRIPTION_EXTRA_DAYS_EXPLAIN' => 'Allow a period of free membership.',
	'SUBSCRIPTION_FEE'				=> 'Subscription Membership Fee',
	'SUBSCRIPTION_FEE_EXPLAIN'		=> 'Charge per period for membership. Set this field to 0 for user determined donations',
	'SUBSCRIPTION_GROUP'			=> 'Subscribed membership Group',
	'SUBSCRIPTION_GROUP_EXPLAIN'	=> 'Define the group the subscribed member will be added into',
	'SUBSCRIPTION_JOINING_FEE'		=> 'Subscription Joining Fee',
	'SUBSCRIPTION_JOINING_FEE_EXPLAIN'	=> 'This is the amount charged to join',
	'SUBSCRIPTION_SETTINGS' 		=> 'Subscription General Settings',
	'SUBSCRIPTION_SETTINGS_TITLE'	=> 'Subscription Settings',
	'SUBSCRIPTION_SETTINGS_EXPLAIN'	=> 'Update the subscribtion related information',
	'WEEK'                          => 'Week',
    ));
?>