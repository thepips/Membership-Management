<?php

if (!defined('IN_PHPBB'))
{
    exit;
}
function __autoload($Class)
{
    $phpbb_root_path = './includes/'; // See phpbb_root_path documentation
    $phpEx = substr(strrchr(__FILE__, '.'), 1);
    $filename = $phpbb_root_path . $Class . '.' . $phpEx;
    if (file_exists($filename))
    {
        include($filename);  // include the class file
    }
}
/**
* Select subscription period and charge
*/
function DEFINE_money_format () 
{
    function money_format($amount)
    {
        return (sprintf("&pound;%01.2f", $amount));
    }
}

if (!function_exists('money_format'))
{
    DEFINE_money_format() ;
}
?>