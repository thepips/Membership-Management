<?php

/**
 * @author Action Replay
 * @copyright 2011
 */
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

class application extends custom_profile
{
	function generate_profile_fields()
	{
		global $db, $template, $user;
		$lang_id=$user->get_iso_lang_id();

var_dump($user->lang);
		$sql = 'SELECT l.*, f.*
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
			WHERE f.field_active = 1
				AND f.field_show_on_reg = 1
				AND field_ident LIKE 'ms_%'
				AND l.lang_id = $lang_id
				AND l.field_id = f.field_id
			ORDER BY f.field_order";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Return templated field
			$tpl_snippet = parent::process_field_row('change', $row);

			// Some types are multivalue, we can't give them a field_id as we would not know which to pick
			$type = (int) $row['field_type'];

			$template->assign_block_vars('profile_fields', array(
				'LANG_NAME'		=> $user->lang[$row['lang_name']],
				'LANG_EXPLAIN'	=> $row['lang_explain'],
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> ($type == FIELD_DATE || ($type == FIELD_BOOL && $row['field_length'] == '1')) ? '' : 'pf_' . $row['field_ident'],
				'S_REQUIRED'	=> ($row['field_required']) ? true : false)
			);
		}
		$db->sql_freeresult($result);
	}
}
?>