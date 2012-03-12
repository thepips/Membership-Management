<?php
/**
* @package eft_class.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'includes/payment_class.' . $phpEx);

class eft_class extends payment_class
{
	
	var $fields = array();		   // array holds the fields to submit to paypal


	function __construct()
	{
	   
		// initialization constructor.  Called when class is created.
		
		parent::__construct();
		global $config, $user;

		$this->last_error = '';
   }

	public function checkout()
	{
		parent::preserve_shopping_basket($this->fields);
		return true;
	}

	public function take_payment()
	{
		parent::retrieve_shopping_basket();
		if ($this->fields==null)
		{
			trigger_error("I'm sorry but your session has expired");
			return false;
		}
		$this->subscriber_id='EFT';
		parent::remove_shopping_basket();
		return true;
	}

}
?>