<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- Admin Functions File
*-------------------------------------------------------------------
*	Script info:
* Version:		1.0.0 - "TMM"
* Copyright:	(C) 2010 | David
* License:		http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
* Package:		phpBB3
*
*===================================================================
*
*/

if(!defined('IN_PHPBB'))
{
	exit;
}
class tmm_admin extends tmm
{
	/**
	 * Obtains the latest MOD version information
	 *
	 * Code ported from phpBB's version check system.
     *
     * @return string | false Version info on success, false on failure.
	 */
	function obtain_latest_mod_version_info()
	{
		$errstr = '';
		$errno = 0;

		$info = get_remote_file('www.phpbbdevelopers.net', '/modver',
				'tmm.txt', $errstr, $errno);
	
		if ($info === false)
		{
			return false;
		}
	
		return $info;
	}
	/*
	*
	*	Check the latest version against the current version
	*
	*/
	function check_version()
	{
		global $user, $template;
		$info = $this->obtain_latest_mod_version_info();
		$info = explode("\n", $info);
		$modversion = trim($info[0]);
		if(!$modversion)
		{
			$vermsg = $user->lang['SERVER_DOWN'];
			$version = '';
			$up2date = 0;
		}
		else
		{
			$up2date = (version_compare(TMM_VERSION, $modversion, '>=') == 1) ? 1 : 0;
			$vermsg = ($up2date == 1) ? $user->lang['UP_2_DATE'] : $user->lang['NOT_UP_2_DATE'];
		}
		$template->assign_vars(array(
			'CURRENT_VERSION'	=> TMM_VERSION,
			'LATEST_VERSION'	=> $modversion,
			'VERMSG'			=> $vermsg,
			'S_UP_TO_DATE'		=> $up2date,
			'UPDATE_TO'			=> sprintf($user->lang['UPDATE_TO'], $modversion),
			'S_VERSION_CHECK'	=> true,
			'U_DLUPDATE'		=> trim($info[1]),
		));
	}
	
	//$mode = 'add' || 'update'
	function create_prefix($mode = 'add', $prefix_name, $prefix_title, $prefix_color_hex = '', $prefix_forums = '', $prefix_groups = '', $prefix_users = '', $prefix_id = 0, $u_action = '')
	{
		global $db, $user, $tmm;
		$error = '';
		if($mode == 'edit' && $prefix_id == 0)
		{
			return false;
		}
		$data = array(
			'prefix_title'		=> $prefix_title,
			'prefix_name'		=> $prefix_name,
			'prefix_color_hex'	=> $prefix_color_hex,
			'prefix_forums'		=> $prefix_forums,
			'prefix_groups'		=> $prefix_groups,
			'prefix_users'		=> $prefix_users,
		);
		foreach($data AS $key => $value)
		{
			$data[$key] = $db->sql_escape($value);
		}
		
		if($mode == 'add')
		{
			$sql = 'INSERT INTO ' . TMM_PREFIXES_TABLE . '
				   ' . $db->sql_build_array('INSERT', $data);
			$result = $db->sql_query($sql);
		}
		elseif($mode == 'edit')
		{
			$sql = 'UPDATE ' . TMM_PREFIXES_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $data) . '
					WHERE prefix_id = ' . $prefix_id;
			$result = $db->sql_query($sql);
		}
		else
		{
			$result = false;
		}
		if($mode == 'add')
		{
			$message = $user->lang[(($result) ? 'PREFIX_CREATED' : 'PREFIX_CREATE_ERROR')];
		}
		elseif($mode == 'edit')
		{
			$message = $user->lang[(($result) ? 'PREFIX_EDITED' : 'PREFIX_EDIT_ERROR')];
		}
		else
		{
			$message = $user->lang['NO_MODE'];
		}
		$message .= adm_back_link($u_action);
		trigger_error($message);
	}
	
	//$mode = ('new' || 'update')
	function submit_tmm($mode = 'new', $tmm_options = array(), $tmm_id = 0, $u_action = '')
	{
		global $db, $user;
		foreach($tmm_options AS $option => $value)
		{
			$data[$option] = $value;
		}
		if($mode == 'new')
		{
			$sql = 'INSERT INTO ' . TMM_TABLE . ' ' . $db->sql_build_array('INSERT', $data);
		}
		else
		{
			$sql = 'UPDATE ' . TMM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE tmm_id = ' . $tmm_id;
		}
		$result = $db->sql_query($sql);
		if($mode == 'new')
		{
			$message = $user->lang[(($result) ? 'TMM_CREATED' : 'TMM_CREATE_ERROR')];
		}
		elseif($mode == 'update')
		{
			$message = $user->lang[(($result) ? 'TMM_EDITED' : 'TMM_EDIT_ERROR')];
		}
		else
		{
			$message = $user->lang['NO_MODE'];
		}
		$message .= adm_back_link($u_action);
		trigger_error($message);
	}
	
	function delete_prefix($prefix_id)
	{
		global $db;
		$sql = 'SELECT *
			FROM ' . TMM_PREFIXES_TABLE . '
			WHERE prefix_id = ' . $prefix_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		
		$sql = 'DELETE
			FROM ' . TMM_PREFIXES_TABLE . '
			WHERE prefix_id = ' . $prefix_id;
		$result = $db->sql_query($sql);
		if(!$result)
		{
			return false;
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_id = ' . $prefix_id;
	}
	
	function delete_tmm($tmm_id)
	{
		global $db;
		$sql = 'SELECT *
			FROM ' . TMM_TABLE . '
			WHERE prefix_id = ' . $tmm_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		$sql = 'DELETE
			FROM ' . TMM_TABLE . '
			WHERE prefix_id = ' . $tmm_id;
		$result = $db->sql_query($sql);
		if(!$result)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	

	function get_group_select($group_id)
	{
		global $db, $config, $user;

		//Stolen from functions_admin.php
		$sql_and = (!$config['coppa_enable']) ? "group_name <> 'REGISTERED_COPPA'" : '';
		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . "
			WHERE $sql_and
			ORDER BY group_type DESC, group_name ASC";
		$result = $db->sql_query($sql);
		$s_group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			if($group_id != 0)
			{
				if(is_array($group_id))
				{
					$selected = (in_array($row['group_id'], $group_id)) ? ' selected="selected"' : '';
				}
				else
				{
					$selected = ($row['group_id'] == $group_id) ? ' selected="selected"' : '';
				}
			}
			else
			{
				$selected = '';
			}
			$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}
		$db->sql_freeresult($result);
		return $s_group_options;
	}
	
	function get_prefix_select($prefix_id)
	{
		global $db, $config, $user;

		//Stolen from functions_admin.php
		$sql = 'SELECT *
			FROM ' . TMM_PREFIXES_TABLE;
		$result = $db->sql_query($sql);
		$s_prefix_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			if($prefix_id != 0)
			{
				if(is_array($prefix_id))
				{
					$selected = (in_array($row['prefix_id'], $prefix_id)) ? ' selected="selected"' : '';
				}
				else
				{
					$selected = ($row['prefix_id'] == $prefix_id) ? ' selected="selected"' : '';
				}
			}
			else
			{
				$selected = '';
			}
			$s_prefix_options .= '<option value="' . $row['prefix_id'] . '"' . $selected . '><span style="color:#' . $row['prefix_color_hex'] . ';">' . $row['prefix_title'] . '</span></option>';
		}
		$db->sql_freeresult($result);
		return $s_prefix_options;
	}
}