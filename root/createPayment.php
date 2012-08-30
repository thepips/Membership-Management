<?php
/**
 *
 * @author DougA (Doug Antill) doug@action-replay.co.uk
 * @version $Id$
 * @copyright (c) 2011 Doug Antill
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */


/**
 * @ignore
 */
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();


if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Payment Processing';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'pay_version';

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	'0.0.16'	=> array(
		'config_add' => array(
			array('pp_paypal_err_email', 'email address to send errors to', ''),
		),
	),
	'0.0.9'	=> array(
		'config_add' => array(
			array('pp_payment_locale', '&pound;%01.2f', 0),
		),
	),
	'0.0.7'	=> array(		
		'table_column_add' => array(			
			array(IPN_LOG_TABLE, 'txn_type', array('VCHAR:20', '')),		
		),	
		'config_add' => array(
			array('pp_paypal_secure', '0', 0),
			array('pp_paypal_image', '0', 0),
			array('pp_eft_image', '0', 0),
			array('pp_cheque_image', '0', 0),
		),
	),	
	'0.0.6'	=> array(
		'config_add' => array(
			array('pp_email_notification', '', 0),
		),
	),
	'0.0.3'	=> array(
		// Now to add some permission settings
	 	'permission_add' => array(
			array('a_mark_paid', true),
		),
		'permission_set' => array(
			// Global Role permissions
			array('ROLE_ADMIN_FULL', 'a_mark_paid'),
		)
	),
	'0.0.2' => array(
		'table_add' =>array(
			array(IPN_LOG_TABLE, array(
				'COLUMNS'		=> array(
					'ipn_id' => array('UINT:10', NULL, 'auto_increment'),
					'txn_id' => array('VCHAR:20', ''),
					'creation_timestamp'=> array('TIMESTAMP', 0),
					'ipn_data'		 => array('TEXT_UNI', ''),
				),
				'PRIMARY_KEY'	=> array('ipn_id')
			)
		)
	),
	),
	'0.0.1' => array(
		'config_add' => array(
			array('pp_subscription_allowed_cheque', '0', 0),
			array('pp_payment_method_cheque', '0', 0),
			array('pp_subscription_allowed_eft', '0', 0),
			array('pp_payment_method_eft', '0', 0),
			array('pp_subscription_allowed_paypal', '0', 1),
			array('pp_payment_method_paypal', '0', 0),
			array('pp_eft_bankname', 'Enter the name of the bank holding your account', 0),
			array('pp_cheque_address', 'Type the address you want the cheque sent to', 0),
			array('pp_eft_account', '012345678', 0),
			array('pp_eft_bankaddress', 'type in the address of your bank', 0),
			array('pp_eft_bankcode', '00-00-00', 0),
			array('pp_paypal_co_name', 'The name to appear on Paypal statement', 0),
			array('pp_paypal_currency_code', 'GBP', 0),
			array('pp_paypal_p_account', 'Your paypal email account or account no', 0),
			array('pp_paypal_use_sandbox', '1', 0),
			array('pp_paypal_API_userid', 'Your Paypal Express Checkout API userid', 0),
			array('pp_paypal_API_password', 'Your Paypal Express Checkout API Password', 0),
			array('pp_paypal_API_signature', 'Your Paypal Express Checkout API signature', 0),
			array('pp_paypal_sandbox_API_userid', 'Your Paypal Express Checkout API userid', 0),
			array('pp_paypal_sandbox_API_password', 'Your Paypal Express Checkout API Password', 0),
			array('pp_paypal_sandbox_API_signature', 'Your Paypal Express Checkout API signature', 0),
			array('membership_gc', '86400', 0),
			array('membership_last_gc', '0', true)
		),

		'module_add' => array(
			array('acp', 'ACP_CAT_DOT_MODS', 'ACP_PAYMENTS'),
			array('acp', 'ACP_PAYMENTS', array(
					'module_basename'		=> 'payments',
				),
			),
		),
		'custom'	=> 'other_elements'
	)
);
// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

function other_elements($action, $version)
{
	global $db, $umil;

	switch ($action)
	{
		case 'install':
			$sql = 'ALTER TABLE ' . SESSIONS_TABLE . ' ADD shopping_basket TEXT NULL DEFAULT NULL';
			$result = $db->sql_query($sql);

		break;

		case 'uninstall':
			$sql = 'ALTER TABLE ' . SESSIONS_TABLE . ' DROP shopping_basket';
			$db->sql_query($sql);

			foreach ($profile_fields as $profile)
			{
				$sql = 'ALTER TABLE ' . PROFILE_FIELDS_DATA_TABLE . ' DROP COLUMN pf_' . $profile[0];
				$db->sql_query($sql);
				
				$sql = 'DELETE pl.* from ' . PROFILE_LANG_TABLE . ' AS pl right join ' . PROFILE_FIELDS_TABLE . ' AS pf ON pl.field_id=pf.field_id where pf.field_ident="' . $profile[0] . '"' ; 
				$db->sql_query($sql);

				$sql = 'DELETE pf.* from ' . PROFILE_FIELDS_TABLE . ' AS pf WHERE pf.field_ident="' . $profile[0] . '"' ; 
				$db->sql_query($sql);
			}
		break;
	}
	
	
	/**
	* Return a string
	* 	The string will be shown as the action performed (command).  It will show any SQL errors as a failure, otherwise success
	*/
	// return 'EXAMPLE_CUSTOM_FUNCTION';

	/**
	* Return an array
	* 	With the keys command and result to specify the command and the result
	*	Returning a result (other than SUCCESS) assumes a failure
	*/
	return array(
		'command'	=> 'Custom Profile Fields',
		'result'	=> 'SUCCESS',
	);
}

?>