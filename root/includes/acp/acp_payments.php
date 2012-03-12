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
class acp_payments
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $cache;

		$user->add_lang('acp/board');
		$user->add_lang('posting');
		$user->add_lang('mods/info_acp_payments');
		
		//Get standard data
		$action		= request_var('action', 'all');		
		$submit		= (isset($_POST['submit']))  ? true : false;

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
			case 'config':
				$display_vars = array(
					'title'	=> 'ACP_CONFIG',
					'vars'	=> array(
						'legend1'						=> 'ACP_PAYMENT_SETTINGS',
						'pp_enable_payment'				=> array('lang' => 'PP_ENABLE_PAYMENT', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'pp_payment_locale'				=> array('lang' => 'PAYMENT_LOCALE', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						
				));
			break;
			case 'cheque':
				$display_vars = array(
					'title'	=> 'ACP_CONFIG',
					'vars'	=> array(
						'legend1'						=> 'CHEQUE_SETTINGS_TITLE',
						'pp_payment_method_cheque'		=> array('lang' => 'CHEQUE_ENABLED', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'pp_subscription_allowed_cheque'=> array('lang' => 'SUBSCRIPTION_ALLOWED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pp_cheque_address'				=> array('lang' => 'PP_CHEQUE_ADDRESS',		'validate' => 'string',	'type' => 'textarea:5:40', 'explain' => true),
						'pp_cheque_image'				=> array('lang' => 'PP_IMAGE',		'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
				));
			break;

			case 'paypal':
				$display_vars = array(
					'title'	=> 'ACP_CONFIG',
					'vars'	=> array(
						'legend1'						=> 'PAYPAL_GENERAL_SETTINGS_TITLE',
						'pp_payment_method_paypal'		=> array('lang' => 'PAYPAL_ENABLED', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'pp_subscription_allowed_paypal'=> array('lang' => 'SUBSCRIPTION_ALLOWED', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pp_paypal_p_account'			=> array('lang' => 'OUR_PAYPAL_ACCT', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_co_name'				=> array('lang' => 'PAYPAL_CO_NAME', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_currency_code'		=> array('lang' => 'PAYPAL_CURRENCY_CODE', 'validate' => 'string',	'type' => 'text:10:0', 'explain' => true),
						'pp_paypal_secure'				=> array('lang' => 'PAYPAL_SECURE', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pp_paypal_use_sandbox'			=> array('lang' => 'PAYPAL_SANDBOX', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pp_email_notification'	 		=> array('lang' => 'PAYPAL_ERR_EMAIL', 'validate' => 'bool',	'type' => 'custom',	'method' => 'email_notification', 'explain' => true),
						'pp_paypal_err_email'	   		=> false,
						'pp_paypal_image'			 	=> array('lang' => 'PP_IMAGE', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'legend2'						=> 'PAYPAL_SETTINGS_TITLE',
						'pp_paypal_API_username'		=> array('lang' => 'PAYPAL_API_USERNAME', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_API_password'		=> array('lang' => 'PAYPAL_API_PASSWORD', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_API_signature'		=> array('lang' => 'PAYPAL_API_SIGNATURE', 'validate' => 'string',	'type' => 'text:80:80', 'explain' => true),
						'legend3'						=> 'PAYPAL_SANDBOX_SETTINGS_TITLE',
						'pp_paypal_sandbox_API_username'=> array('lang' => 'PAYPAL_API_USERNAME', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_sandbox_API_password'=> array('lang' => 'PAYPAL_API_PASSWORD', 'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_paypal_sandbox_API_signature'=> array('lang' => 'PAYPAL_API_SIGNATURE', 'validate' => 'string',	'type' => 'text:80:80', 'explain' => true),
				));
			break;

			case 'eft':
				$display_vars = array(
					'title'	=> 'ACP_CONFIG',
					'vars'	=> array(
						'legend1'						=> 'EFT_SETTINGS_TITLE',
						'pp_payment_method_eft'			=> array('lang' => 'EFT_ENABLED', 'validate' => 'bool', 'type' => 'radio:yes_no',	'explain' => true),
						'pp_subscription_allowed_eft'	=> array('lang' => 'SUBSCRIPTION_ALLOWED',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pp_eft_bankname'				=> array('lang' => 'PP_EFT_BANKNAME',		'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_eft_bankaddress'			=> array('lang' => 'PP_EFT_BANKADDRESS',		'validate' => 'string',	'type' => 'textarea:5:40', 'explain' => true),
						'pp_eft_bankcode'				=> array('lang' => 'PP_EFT_BANKCODE',		'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_eft_account'				=> array('lang' => 'PP_EFT_ACCOUNT',		'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
						'pp_eft_image'					=> array('lang' => 'PP_IMAGE',		'validate' => 'string',	'type' => 'text:40:0', 'explain' => true),
				));
			break;
			
			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
				
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}
	
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
	/**
	* Select interval
	*/
	function email_notification($value, $key)
	{
		global $user;
		$message	= '<label><input id="' . $key . '" class="radio" type="radio"' . ($value ? ' checked="checked"' : '') . ' value="1" name="config[' . $key . ']"> ' . $user->lang['YES'];
		$message	.='</label>';
		$message	.='<label>';
		$message	.= '<input class="radio" type="radio"' . (!$value ? ' checked="checked"' : '') . 'value="0" name="config[' . $key . ']"> ' . $user->lang['NO'];
		$message	.='</label>';
		$message	.='<input type="text" value="'. $this->new_config['pp_paypal_err_email'] . '" name="config[pp_paypal_err_email]" maxlength="255" size="40" id="pp_paypal_err_email">';
	return $message;
//		return '<input id="' . $key . '" type="text" size="3" maxlength="4" name="config['. $key . ']" value="' . $value . '" />&nbsp;<select name="config['. $period_basis. ']">' . '</select>';
	}

	/**
	* Select interval
	*/
	function payment_image($value, $key)
	{
		global $user;
		$message	= '<label>';
		$message	.= '<input type="text" value="' . $this->new_config['pp_image'] . ' id="' . $key . '" name="config[pp_image]"> ';
		$message	.= '<img align="middle" src="./styles/prosilver/imageset/en/renew_membership.png">';
		$message	.='</label>';
	return $message;
//<input type="text" value="VCHAR:20" name="config[pp_eft_image]" maxlength="255" size="40" id="pp_eft_image">
	}

}
?>