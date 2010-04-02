<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- TMM MCP File
*-------------------------------------------------------------------
*	Script info:
* Version:		1.0.0 - "TMM"
* Copyright:	(C) 2010 | David, House, Comkid
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
class mcp_ban
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpEx;

		$user->add_lang('mods/tmm.php');
		include($phpbb_root_path . 'includes/functions_tmm.' . $phpEx);
		include($phpbb_root_path . 'includes/tmm_constants.'. $phpEx);
		$tmm = new tmm;

		$this->tpl_name = 'mcp_tmm';
		$multimod = $db->sql_escape(request_var('multimod', (int) 0));
		$forum_id = request_var('f', 0);
		$topic_id = request_var('t', 0);
		
		if(!$multimod || !is_numeric($multimod))
		{
			trigger_error('INVALID_MULTI_MOD');
		}
		else
		{
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
			if (confirm_box(true))
			{
				$apply = $tmm->apply_multi_mod($multimod, $topic_id, $forum_id);
				if(!$apply)
				{
					$message = $user->lang['TMM_FAIL'];
				}
				else
				{
					$message = $user->lang['TMM_PASS'];
				}
				$back_link = append_sid($phpbb_root_path . 'viewtopic.' . $phpEx, "f={$forum_id}&amp;t={$topic_id}");
				trigger_error($message . $back_link)
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(array(
					'submit'    => true,
					'multimod' => $multimod,
					)
				);
				$user->lang['APPLY_TMM_CONFIRM'] = sprintf('
				//display mode
				confirm_box(false, 'APPLY_TMM', $s_hidden_fields);
			}
		}

		// Define language vars
		$this->page_title = 'MCP_TMM'];
	}
}