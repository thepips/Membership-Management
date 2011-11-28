<?php
/**
*
* @package acp
* @version $Id: acp_membership.php 8598 2008-06-04 15:37:06Z naderman $
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_membership_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_membership',
			'title'		=> 'ACP Membership',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'list'		=> array('title' => 'ACP_MEMBERSHIP_USERS', 'auth' => 'acl_a_user', 'cat' => array('ACP_CAT_USERS')),
				'settings'		=> array('title' => 'ACP_MEMBERSHIP_SETTINGS', 'auth' => 'acl_a_user', 'cat' => array('ACP_CAT_USERS')),
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