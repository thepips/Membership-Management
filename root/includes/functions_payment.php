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
function preserve_shopping_basket($data)
{
    global $user, $db;
    $x=serialize($data);

    // update session table
    $sql = 'UPDATE ' . SESSIONS_TABLE . '
      SET shopping_basket = "' . $db->sql_escape($x) . '"
      WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
    $db->sql_query($sql);
}

function retrieve_shopping_basket()
{
    global $user, $db;
    $sql = 'SELECT shopping_basket FROM ' . SESSIONS_TABLE . '
      WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
    $result = $db->sql_query($sql);
    $shopping_basket	= $db->sql_fetchfield('shopping_basket');
 
    return unserialize($shopping_basket);
}

function remove_shopping_basket()
{
    global $user, $db;
    $sql = 'UPDATE ' . SESSIONS_TABLE . '
      SET shopping_basket = ""
      WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
    $db->sql_query($sql);
}

?>