<?php
/** 
*
* @package acp
* @version $Id: acp_shmoogle.php,v 0.0.1 2008/01/17
* @copyright (c) 2007 Will Inglis
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class acp_payments_info
{
	function module()
	{
	return array(
		'filename'	=> 'acp_payments',
		'title'		=> 'ACP_PAYMENT_SETTINGS',
		'version'	=> '1.0.0',
		'modes'		=> array(
			'config'		=> array('title' => 'ACP_PAYMENTS', 'auth' => 'acl_a_', 'cat' => array('ACP_PAYMENTS')),
			'cheque'		=> array('title' => 'ACP_CHEQUE', 'auth' => 'acl_a_', 'cat' => array('ACP_PAYMENTS')),
			'eft'			=> array('title' => 'ACP_EFT', 'auth' => 'acl_a_', 'cat' => array('ACP_PAYMENTS')),
			'paypal'		=> array('title' => 'ACP_PAYPAL', 'auth' => 'acl_a_', 'cat' => array('ACP_PAYMENTS')),
			),
		);
		
	}

	function install()
	{
	}

	function uninstall()
	{
	}

}
?>