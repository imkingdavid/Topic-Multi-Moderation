<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- Cache Functions File
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

// Prefix caching -- Stolen/Adapted from Erik Frerejean's Subject Prefixes MOD
if (!class_exists('acm'))
{
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
}
class tmm_cache extends acm
{
	private static $prefixes_cached = array(); // an array of all of the prefixes
	private static $multi_mods_cached = array(); // an array of all of the multi-mods.
	
	public function get_multi_mods()
	{
		global $db;
		if(!empty(self::$multi_mods_cached))
		{
			return self::$multi_mods_cached;
		}
		
		if ((self::$multi_mods_cached = $this->get('_tmm')) === false)
		{
			$sql = 'SELECT *
				FROM ' . TMM_TABLE;
			$result	= $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				self::$multi_mods_cached[$row['tmm_id']] = array(
					'id'				=> $row['tmm_id'],
					'title'				=> $row['tmm_title'],
					'description'		=> $row['tmm_desc'],
					'lock'				=> $row['tmm_lock'],
					'sticky'			=> $row['tmm_sticky'],
					'move'				=> $row['tmm_move'],
					'move_dest'			=> $row['tmm_move_dest_id'],
					'copy'				=> $row['tmm_copy'],
					'copy_dest'			=> $row['tmm_copy_dest_id'],
					'users'				=> $row['tmm_users'],
					'forums'			=> $row['tmm_forums'],
					'groups'			=> $row['tmm_groups'],
					'prefix'			=> $row['tmm_prefix_id'],
					'autoreply_bool'	=> $row['tmm_autoreply_bool'],
					'autoreply_text'	=> $row['tmm_autoreply_text'],
					'autoreply_poster'	=> $row['tmm_autoreply_poster'],
				);
			}
			$db->sql_freeresult($result);

			$this->put('_tmm', self::$multi_mods_cached);
		}
		
		return self::$multi_mods_cached;
	}
	
	public function get_prefixes()
	{
		global $db;
		if(!empty(self::$prefixes_cached))
		{
			return self::$prefixes_cached;
		}
		
		if ((self::$prefixes_cached = $this->get('_tmm_prefixes')) === false)
		{
			$sql = 'SELECT *
				FROM ' . TMM_PREFIXES_TABLE;
			$result	= $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				self::$prefixes_cached[$row['prefix_id']] = array(
					'id'		=> $row['prefix_id'],
					'name'		=> $row['prefix_name'],
					'title'		=> $row['prefix_title'],
					'colour'	=> $row['prefix_color_hex'],
					'groups'	=> $row['prefix_groups'],
					'users'		=> $row['prefix_users'],
					'forums'	=> $row['prefix_forums'],
				);
			}
			$db->sql_freeresult($result);

			$this->put('_tmm_prefixes', self::$prefixes_cached);
		}
		
		return self::$prefixes_cached;
	}
	
	/**
	* Quick way to clear the whole subject_prefix cache
	*/
	public function clear_tmm_cache()
	{
		$this->destroy('_tmm_prefixes');
		$this->destroy('_tmm');
	}
}