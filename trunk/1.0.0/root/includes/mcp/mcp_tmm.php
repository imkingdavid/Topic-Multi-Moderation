<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- TMM MCP File
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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package mcp
*/
class mcp_tmm
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpEx, $table_prefix;

		$user->add_lang('mods/tmm');
		include($phpbb_root_path . 'includes/functions_tmm.' . $phpEx);
		include($phpbb_root_path . 'includes/tmm_constants.'. $phpEx);
		$tmm = new tmm;

		$this->tpl_name = 'mcp_tmm';
		$multimod = request_var('tmm_select', (int) 0);
		$forum_id = request_var('f', (int) 0);
		$topic_id = request_var('t', (int) 0);
		
		if(!$multimod || !is_numeric($multimod))
		{
			trigger_error('INVALID_MULTI_MOD');
		}
		//Make sure it exists
		$sql = 'SELECT *
			FROM ' . TMM_TABLE . '
			WHERE tmm_id = ' . $multimod;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			trigger_error('INVALID_MULTI_MOD');
		}
		$possible_actions = array('tmm_lock', 'tmm_sticky', 'tmm_copy', 'tmm_move', 'tmm_autoreply_bool', 'tmm_prefix_id');
		$actions = array(); // will be populated in a minute
		foreach($possible_actions AS $possible_action)
		{
			if($possible_action != 'tmm_prefix_id')
			{
				if($row[$possible_action] == 1)
				{
					$actions[] = $possible_action;
				}
			}
			else
			{
				if(!empty($row['tmm_prefix_id']))
				{
					$actions['tmm_prefix_id'] = 'tmm_prefix_id';
					$prefixes = explode(',', $row['tmm_prefix_id']);
					$prefix_string = $tmm->parse_prefix_array($prefixes);
				}
			}
		}
		//! If it didn't get set, just set it to an empty string now...
		$prefix_string = (isset($prefix_string)) ? $prefix_string : '';
		if (confirm_box(true))
		{
			if(!$topic_id)
			{
				trigger_error('INVALID_TOPIC_ID');
			}
			$apply = $tmm->apply_tmm($multimod, $topic_id, $forum_id);
			if(!$apply)
			{
				$message = $user->lang['TMM_FAIL'] . '<br />';
				foreach($tmm->error AS $error)
				{
					$message .= $error . '<br />';
				}
			}
			else
			{
				$message = $user->lang['TMM_PASS'];
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
					$message .= ($action == 'TMM_PREFIX_ID') ? sprintf($user->lang['TMM_PREFIX_ID'], $prefix_string) : $user->lang[$action];
					$message .= '<br />';
				}
			}
			$user->lang['APPLY_TMM_CONFIRM'] = sprintf($user->lang['APPLY_TMM_CONFIRM'], $message);
			//display mode
			confirm_box(false, 'APPLY_TMM', $s_hidden_fields);
		}
		// Shouldn't get here...
		redirect($phpbb_root_path . 'viewtopic.' . $phpEx, "f={$forum_id}&amp;t={$topic_id}");
		// Define language vars
		$this->page_title = 'MCP_TMM';
	}
}