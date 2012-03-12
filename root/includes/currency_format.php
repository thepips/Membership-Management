<?php
/**
* @package currency_format.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

function currency_format($num, $length=0)
{
	global $config;
	$mask=$config['pp_payment_locale'];
	$money=sprintf(html_entity_decode($mask), $num);
	
	if($length>0)
	{
		$real_length=strlen(html_entity_decode($money));
		$length=$length+strlen($money)-$real_length;
		$money=str_replace('_', '&nbsp;', str_pad($money,$length,'_','PAD_STR_LEFT'));
	}
	return $money;
}
?>