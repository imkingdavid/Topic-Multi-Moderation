<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- TMM Execution File
*-------------------------------------------------------------------
*	Script info:
* Version:		1.0.0 - "Triton"
* Copyright:	(C) 2010 | David
* License:		http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
* Package:		phpBB3
*
*===================================================================
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/tmm');

$multimod = request_var('tmm_select', 0);
$forum_id = request_var('f', 0);
$topic_id = request_var('t', 0);

if (!$multimod || !is_numeric($multimod))
{
	trigger_error('INVALID_MULTI_MOD');
}
//Make sure it exists
$tmm_cache = tmm::$tmm_cache->get_multi_mods();
if(!array_key_exists($multimod, $tmm_cache))
{
	trigger_error('INVALID_MULTI_MOD');
}
//Now make sure the user has permission to use it
// Either by group or user-specific
//This was stolen from one of the tmm methods in functions_tmm.php and adapted for use here.
// If there's an easier way, I'll consider it later on but this will work for now.
//Make sure the method is available
if(!function_exists('group_memberships'))
{
	include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
}
$groups = group_memberships(false,$user->data['user_id']);

$multi_mods = array();
$where = ($forum_id != 0) ? ' WHERE tmm_forums LIKE \'%' . $forum_id . '%\'' : '';
$sql = 'SELECT *
	FROM ' . TMM_TABLE . $where;
$result = $db->sql_query($sql);
while($row = $db->sql_fetchrow($result))
{
	$tmm_groups = $row['tmm_groups'];
	$tmm_groups = explode(',', $tmm_groups);
	foreach($groups AS $group)
	{
		if(in_array($group['group_id'], $tmm_groups))
		{
			$multi_mods[] = $row['tmm_id'];
		}
	}
	$tmm_users = $row['tmm_users'];
	$tmm_users = explode(',', $tmm_users);
	if(in_array($user->data['user_id'], $tmm_users))
	{
		$multi_mods[] = $row['tmm_id'];
	}
}
$multi_mods = array_unique($multi_mods);
$tmm_options = '';
foreach($multi_mods AS $multi_mod)
{
	$tmm_id = tmm::$multi_mods_cache[$multi_mod]['id'];
	$tmm_options[] = $tmm_id;
}
if(!in_array($multimod, $tmm_options))
{
	trigger_error('INVALID_MULTI_MOD');
}
///-----------
$possible_actions = array('lock', 'sticky', 'copy', 'move', 'autoreply_bool', 'prefix');
$actions = array(); // will be populated in a minute
foreach ($possible_actions AS $possible_action)
{
	if ($possible_action != 'prefix')
	{
		if($tmm_cache[$multimod][$possible_action] == 1)
		{
			$actions[] = $possible_action;
		}
	}
	else
	{
		if (!empty($tmm_cache[$multimod]['tmm_prefix_id']))
		{
			$actions['prefix'] = 'prefix';
			$prefixes = explode(',', $tmm_cache[$multimod]['prefix']);
			$prefix_string = tmm::parse_prefix_array($prefixes);
		}
	}
}
//! If it didn't get set, just set it to an empty string now...
$prefix_string = (isset($prefix_string)) ? $prefix_string : '';
if (confirm_box(true))
{
	if (!$topic_id)
	{
		trigger_error('INVALID_TOPIC_ID');
	}
	$apply = tmm::apply_tmm($multimod, $topic_id, $forum_id);
	// Default to a success, but change it to failure message if needed.
	$message = $user->lang['TMM_PASS'];
	if (!$apply)
	{
		$message = $user->lang['TMM_FAIL'] . '<br />';
		foreach(tmm::$error AS $error)
		{
			$message .= $error . '<br />';
		}
	}
	$back_link = append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, "f={$forum_id}&amp;t={$topic_id}");
	$back_link = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $back_link . '">', '</a>');
	trigger_error($message . '<br />' . $back_link);
}
else
{
	$s_hidden_fields = build_hidden_fields(array(
		'submit'    => true,
		'tmm_select' => $multimod,
		)
	);
	// empty message thing
	$message = '';

	foreach($actions AS $action)
	{
		if(!is_numeric($action))
		{
			$action = strtoupper($action);
			$message .= ($action == 'TMM_PREFIX_ID') ? sprintf($user->lang['TMM_PREFIX_ID'], $prefix_string) : $user->lang['TMM_' . $action];
			$message .= '<br />';
		}
	}
	$user->lang['APPLY_TMM_CONFIRM'] = sprintf($user->lang['APPLY_TMM_CONFIRM'], $message);
	//display mode
	confirm_box(false, 'APPLY_TMM', $s_hidden_fields);
	// Shouldn't get here...
	$redirect = append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, array('f' => $forum_id, 't' => $topic_id));
	redirect($redirect);
}