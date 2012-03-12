<?php
/**
*
* info_acp_qi 
[English]
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
	'ACP_CHEQUE'					=> 'Configure Cheque processing',
	'ACP_CONFIG'					=> 'Payment Portal Configuration',
	'ACP_CONFIG_EXPLAIN'			=> 'Enable or disable the methods of payments',
	'ACP_EFT'						=> 'Configure bank transfers',
	'ACP_PAYMENT_SETTINGS'			=> 'Accepted Payment Methods',
	'ACP_PAYMENTS'					=> 'Payment Configuration',
	'ACP_PAYPAL'					=> 'Configure Paypal IPN',
	'ACP_PAYPAL_SETTINGS'			=> 'PayPal Gateway Settings',
	'ACP_PP_CHEQUE'					=> 'Cheque Configuration',
	'ACP_PP_CONFIG'					=> 'Payment Portal Admin Control Panel',
	'ACP_PP_EFT'					=> 'bank transfer Configuration',
	'ACP_PP_PAYPAL'					=> 'Paypal IPN Configuration',
	'CC_ENABLED'					=> 'Allow payment by Credit & debit cards',
	'CHEQUE'						=> 'Cheques',
	'CHEQUE_ENABLED'				=> 'Allow payment by cheque',
	'CHEQUE_EXPLAIN'				=> 'Allow payment by cheque',
	'CHEQUE_SETTINGS'				=> 'Cheque Configuration',
	'CHEQUE_SETTINGS_TITLE'			=> 'Payment by Cheque Settings',
	'CHEQUE_SETTINGS_EXPLAIN'		=> 'Enter the information your customers will need to send payment by Cheque',
	'EFT'							=> 'Electronic Funds Transfer',
	'EFT_ENABLED'					=> 'Allow payment by bank transfer',
	'EFT_SETTINGS'					=> 'Bank Transfer Configuration',
	'EFT_EXPLAIN'					=> 'Allow payment by bank transfer',
	'EFT_SETTINGS_TITLE'			=> 'Payment by Electronic Funds Settings',
	'EFT_SETTINGS_EXPLAIN'			=> 'Enter the information your customers will need to make payment through online banking or direct into your bank account',
	'LOG_PP_METHOD_UPDATED'			=> 'Payment method updated',
	'LOG_PP_SETTINGS_UPDATED'		=> 'Payment portal methods updated',
	'OUR_PAYPAL_ACCT'				=> 'Your PayPal account to receive payment from members: ',
	'OUR_PAYPAL_ACCT_EXPLAIN'		=> 'Please enter your primary account of your paypal. Note that only Premier/Business Account is able to receive Recurring Payment and CreditCard.',
	'PAYPAL'						=> 'Paypal',
	'PAYPAL_API_PASSWORD'			=> 'Paypal API password',
	'PAYPAL_API_PASSWORD_EXPLAIN'	=> 'Obtain the API password from Paypal',
	'PAYPAL_API_SIGNATURE'			=> 'Paypal API signature',
	'PAYPAL_API_USERNAME'			=> 'Paypal API username',
	'PAYPAL_API_USERNAME_EXPLAIN'	=> 'Obtain the API username from Paypal',
	'PAYPAL_API_SIGNATURE_EXPLAIN'	=> 'Obtain the API signature from Paypal',
	'PAYPAL_CO_NAME'				=> 'Company Name',
	'PAYPAL_CO_NAME_EXPLAIN'		=> 'Enter the name you would like your customers to see on their paypal statement',
	'PAYPAL_CURRENCY_CODE'			=> 'The currency code your account supported: ',
	'PAYPAL_CURRENCY_CODE_EXPLAIN'	=> 'Here you should enter one of the currency code supported by PayPal only. for other Payment Gateway, it will be properly transferred accordingly. <br />All payment will be made in the Unit of this Currency Code. Make sure all your payment gateway support this currency type.',
	'PAYPAL_ENABLED'				=> 'Allow payment by Paypal',
	'PAYPAL_ERR_EMAIL'				=> 'Send email on Error',
	'PAYPAL_ERR_EMAIL_EXPLAIN'		=> 'Turn on to send an email on a transaction error',
	'PAYPAL_EXPLAIN'				=> 'Allow payment by Paypal with IPN',
	'PAYPAL_GENERAL_SETTINGS_EXPLAIN'=> 'Paypal configuration settings',
	'PAYPAL_GENERAL_SETTINGS_TITLE'	=> 'PayPal General Settings',
	'PAYMENT_LOCALE'				=> 'Host Locale Value',
	'PAYMENT_LOCALE_EXPLAIN'		=> 'The locale value is dependant upon your host. Different O/Ss use different values.',
	'PAYPAL_SANDBOX'				=> 'Use Paypal Sandbox',
	'PAYPAL_SANDBOX_EXPLAIN'		=> 'Use Paypal Sandbox for testing out configuration',
	'PAYPAL_SANDBOX_SETTINGS_EXPLAIN'=> 'Paypal Sandbox API configuration settings',
	'PAYPAL_SANDBOX_SETTINGS_TITLE'	=> 'PayPal Sandbox API configuration',
	'PAYPAL_SECURE'					=> 'Use secure page',
	'PAYPAL_SECURE_EXPLAIN'			=> 'Use https: for secure transactions',
	'PAYPAL_SETTINGS'				=> 'Payment Settings',
	'PAYPAL_SETTINGS_EXPLAIN'		=> 'Paypal API configuration settings',
	'PAYPAL_SETTINGS_TITLE'			=> 'PayPal API configuration',
	'PAYMENT_PORTAL_PAYMENT_NA'		=> 'This payment method has not been enabled. Please go to the configation screen and enable',
	'PP_CHEQUE_ADDRESS'				=> 'Address',
	'PP_CHEQUE_ADDRESS_EXPLAIN'		=> 'Enter the address your customers are send their cheques to',
	'PP_EFT_ACCOUNT'				=> 'Bank Account Number',
	'PP_EFT_ACCOUNT_EXPLAIN'		=> 'Normally a 9 digit number',
	'PP_EFT_BANKADDRESS'			=> 'Address',
	'PP_EFT_BANKADDRESS_EXPLAIN'	=> 'Enter your banks address',
	'PP_EFT_BANKCODE'				=> 'Sort Code',
	'PP_EFT_BANKCODE_EXPLAIN'		=> 'The sort code is normally in the format of 00-00-00',
	'PP_EFT_BANKNAME'				=> 'Bank Name',
	'PP_EFT_BANKNAME_EXPLAIN'		=> 'Enter the name of your bank',
	'PP_ENABLE_PAYMENT'				=> 'Enable payments module',
	'PP_ENABLE_PAYMENT_EXPLAIN'		=> 'Select Yes to enable the payments module',
	'PP_IMAGE'						=> 'Payment Button Image',
	'PP_IMAGE_EXPLAIN'				=> 'Enter the path and file name to the image you want to use for the payment button',
	'PP_METHOD_UPDATED'				=> 'Payment method updated',
	'PP_SETTINGS_UPDATED'			=> 'Payment portal methods updated',
	'REATTEMPT_PAYMENT'				=> 'Reattempt Payment',
	'REATTEMPT_PAYMENT_EXPLAIN'		=> 'Reattempt Payment if payment fsils for the subscription',
	'SUBSCRIPTION_ALLOWED'	  		=> 'Automated Payment Capable?',
	'SUBSCRIPTION_ALLOWED_EXPLAIN'	=> 'Does this payment method support automated notification of payment?',
));
?>