<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- Admin Functions File
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
	public static function obtain_latest_mod_version_info()
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
	public static function check_version()
	{
		global $user, $template;
		$info = self::obtain_latest_mod_version_info();
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
	public static function submit_prefix($mode = 'add', $prefix_options = array(), $prefix_id = 0, $u_action = '')
	{
		global $db, $user, $tmm;
		$error = '';
		if($mode == 'edit' && $prefix_id == 0)
		{
			return false;
		}
		$data = array();
		foreach($prefix_options AS $key => $value)
		{
			$data[$key] = $value;
		}
		
		if($mode == 'add')
		{
			$sql = 'INSERT INTO ' . TMM_PREFIXES_TABLE . '
				   ' . $db->sql_build_array('INSERT', $data);
			$result = $db->sql_query($sql);
			$next_id = $db->sql_nextid();
			add_log('admin', 'LOG_PREFIX_CREATED', tmm::parse_prefix($next_id));
		}
		else if($mode == 'edit')
		{
			$sql = 'UPDATE ' . TMM_PREFIXES_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $data) . '
					WHERE prefix_id = ' . (int) $prefix_id;
			$result = $db->sql_query($sql);
			add_log('admin', 'LOG_PREFIX_MODIFIED', tmm::parse_prefix($prefix_id));
		}
		else
		{
			$result = false;
		}
		if($mode == 'add')
		{
			$message = $user->lang[(($result) ? 'PREFIX_CREATED' : 'PREFIX_CREATE_ERROR')];
		}
		else if($mode == 'edit')
		{
			$message = $user->lang[(($result) ? 'PREFIX_EDITED' : 'PREFIX_EDIT_ERROR')];
		}
		else
		{
			$message = $user->lang['NO_MODE'];
		}
		tmm::$tmm_cache->clear_tmm_cache();
		$message .= adm_back_link($u_action);
		trigger_error($message);
	}
	
	//$mode = ('new' || 'update')
	public static function submit_tmm($mode = 'new', $tmm_options = array(), $tmm_id = 0, $u_action = '')
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
			$sql = 'UPDATE ' . TMM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE tmm_id = ' . (int) $tmm_id;
		}
		$result = $db->sql_query($sql);
		if($mode == 'new')
		{
			$message = $user->lang[(($result) ? 'TMM_CREATED' : 'TMM_CREATE_ERROR')];
			add_log('admin', 'LOG_TMM_CREATED', $data['tmm_title']);
		}
		else if($mode == 'update')
		{
			$message = $user->lang[(($result) ? 'TMM_EDITED' : 'TMM_EDIT_ERROR')];
			add_log('admin', 'LOG_TMM_MODIFIED', $data['tmm_title']);
		}
		else
		{
			$message = $user->lang['NO_MODE'];
		}
		tmm::$tmm_cache->clear_tmm_cache();
		$message .= adm_back_link($u_action);
		trigger_error($message);
	}
	
	public static function delete_prefix($prefix_id)
	{
		global $db;
		$sql = 'SELECT prefix_id
			FROM ' . TMM_PREFIXES_TABLE . '
			WHERE prefix_id = ' . (int) $prefix_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		add_log('admin', 'LOG_PREFIX_DELETED', tmm::parse_prefix($prefix_id));
		$sql = 'DELETE
			FROM ' . TMM_PREFIXES_TABLE . '
			WHERE prefix_id = ' . (int) $prefix_id;
		$result = $db->sql_query($sql);
		if(!$result)
		{
			return false;
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_id = ' . (int) $prefix_id;
		$result = $db->sql_query($sql);
		if(!$result)
		{
			return false;
		}
		$db->sql_freeresult($result);
		tmm::$tmm_cache->clear_tmm_cache();
		return true;
	}
	
	public static function delete_tmm($tmm_id)
	{
		global $db;
		$sql = 'SELECT tmm_id,tmm_title
			FROM ' . TMM_TABLE . '
			WHERE tmm_id = ' . (int) $tmm_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		add_log('admin', 'LOG_TMM_DELETED', $row['tmm_title']);
		$sql = 'DELETE
			FROM ' . TMM_TABLE . '
			WHERE tmm_id = ' . (int) $tmm_id;
		$result = $db->sql_query($sql);
		if(!$result)
		{
			return false;
		}
		$db->sql_freeresult($result);
		tmm::$tmm_cache->clear_tmm_cache();
		return true;
	}
	

	public static function get_group_select($group_id)
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
	
	public static function get_prefix_select($prefix_id)
	{
		global $db, $config, $user;

		//Stolen from functions_admin.php
			//--To Do--
			// Use cache instead of grabbing from database
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
		//--new in RC7 -- at least have one value if there are no prefixes
		if(empty($s_prefix_options))
		{
			$s_prefix_options = '<option value="0" disabled="disabled">' . $user->lang['NO_PREFIXES'] . '</option>';
		}
		$db->sql_freeresult($result);
		return $s_prefix_options;
	}
	
	// new in RC7
	public static function get_userid_from_username($username)
	{
		global $db;
		$username = $db->sql_escape($username);
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username = '$username'";
		$result = $db->sql_query($sql);
		$user_id = $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);
		return $user_id;
	}
	// new in RC7
	public static function get_username_from_userid($user_id)
	{
		global $db;
		$sql = 'SELECT username
			FROM ' . USERS_TABLE . '
			WHERE user_id = \'' . (int) $user_id . '\'';
		$result = $db->sql_query($sql);
		$username = $db->sql_fetchfield('username');
		$db->sql_freeresult($result);
		return $username;
	}
	
	public static function toggle_username_id($in_type, $input)
	{
		global $db;
		//just in case...
		$output = $input;
		switch($in_type)
		{
			default:
			case 'username':
				$output = self::get_userid_from_username($input);
				if(!$output)
				{
					$output = $input;
				}
				
			break;
			
			case 'id':
				$input = (int) $input;
				if(is_int($input))
				{
					$output = self::get_username_from_userid($input);
				}
			break;
		}
		return $output;
	}
}