<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- General Functions File
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

class tmm
{
	public static $error = array(); // will hold the error messages if any
	public static $actions = array(); // an array of all the actions performed.
	public static $tmm_cache = null; // the cache object of the tmm_cache class
	public static $multi_mods_cache = ''; // the array of cached multi-mods
	public static $prefixes_cache = ''; // the array of cached prefixes
	public static $temp_cache = ''; // temporary cache of prefixes for use in posting.php
	
	/*
	Initialize the MOD.
	No paramters or return
	*/
	public static function start()
	{
		self::$tmm_cache = new tmm_cache();
		self::$multi_mods_cache = self::$tmm_cache->get_multi_mods();
		self::$prefixes_cache = self::$tmm_cache->get_prefixes();
		
		//if a topic ID is given in the URL, load the topic's prefixes.
		// This eliminates the need for edits in viewtopic and some other locations.
		$topic_id = request_var('t', 0);
		$forum_id = request_var('f', 0);
		if($topic_id)
		{
			global $user, $template, $phpbb_root_path, $phpEx;
			$viewtopic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t=$topic_id");
			$template->assign_vars(array(
				'TOPIC_PREFIX'	=> self::load_topic_prefixes($topic_id),
				'TMM_SELECT'	=> self::get_tmm_dropdown($topic_id),
				'S_TMM_ACTION' 	=> append_sid("{$phpbb_root_path}tmm.$phpEx", array('t' => $topic_id, 'f' => $forum_id)),
			));
		}
	}
	
	/*
	Applies the specified multi-mod to the specified topic
	
	Parameters
		int $mod_id		- ID of the Multi-Mod
		int $topic_id	- ID of the topic
		int $forum_id	- ID of the forum
	*/
	public static function apply_tmm($mod_id, $topic_id, $forum_id)
	{
		global $db, $template, $user, $phpbb_root_path, $phpEx;
		
		// make sure the multi-mod exists
		self::$multi_mods_cache = self::$tmm_cache->get_multi_mods();
		if(!array_key_exists($mod_id, self::$multi_mods_cache))
		{
			trigger_error("INVALID_MULTI_MOD");
		}
		
		//Check each mutli-mod action to see if it should be done.
		if(self::$multi_mods_cache[$mod_id]['autoreply_bool'] == 1)
		{
			$poster = (self::$multi_mods_cache[$mod_id]['autoreply_poster'] != 0) ? self::$multi_mods_cache[$mod_id]['autoreply_poster'] : 0;
			echo $poster;
			$auto_reply = self::auto_reply(self::$multi_mods_cache[$mod_id]['autoreply_text'], $topic_id, $forum_id, $poster, true, true);
			if(!$auto_reply)
			{
				self::$error[] = $user->lang['AUTOREPLY_ERROR'];
			}
		}
		if(self::$multi_mods_cache[$mod_id]['prefix'] != '')
		{
			// Allow for multiple prefixes to be applied with one multi-mod ^_^
			$prefixes = explode(',', self::$multi_mods_cache[$mod_id]['prefix']);
			$fails = 0;
			foreach($prefixes AS $prefix)
			{
				$pre = self::apply_prefix($prefix, $topic_id);
				if(!$pre)
				{
					$fails++;
				}
			}
			if($fails != 0)
			{
				self::$error[] = sprintf($user->lang['PREFIX_ERROR'], $fails);
			}
		}
		if(self::$multi_mods_cache[$mod_id]['lock'] == 1)
		{
			$lock = self::toggle_lock($topic_id);
			if(!$lock)
			{
				self::$error[] = $user->lang['LOCK_ERROR'];
			}
		}
		if(self::$multi_mods_cache[$mod_id]['sticky'] > -1)
		{
			$stick = self::alter_topic_type($topic_id, self::$multi_mods_cache[$mod_id]['sticky']);
			if(!$stick)
			{
				self::$error[] = $user->lang['STICK_ERROR'];
			}
		}
		if(self::$multi_mods_cache[$mod_id]['copy'] == 1)
		{
			$copy = self::copy_topic($topic_id, self::$multi_mods_cache[$mod_id]['copy_dest'], $forum_id);
			// new in RC7 apply prefixes to the copied topic as well
			
			// First get the prefixes applied to the old topic.
			$prefixes = self::load_topic_prefixes($topic_id, 'array', 'sql', 'prefixes');
			$fails = 0;
			foreach($prefixes AS $prefix)
			{
				$pre = self::apply_prefix($prefix, $copy);
				if(!$pre)
				{
					$fails++;
				}
			}
			if(!$copy || $fails < 0)
			{
				self::$error[] = self::$multi_mods_cache[$mod_id]['copy_dest'] . '<br />' . $user->lang['COPY_ERROR'];
			}
		}
		if(self::$multi_mods_cache[$mod_id]['move'] == 1)
		{
			$move = move_topics($topic_id, self::$multi_mods_cache[$mod_id]['move_dest'], $forum_id, true);
		}
		
		$return = (empty(self::$error)) ? true : false;
		if($return)
		{
			//New in RC7 -- Utilize the Moderator logs
			add_log('mod', $forum_id, $topic_id, 'LOG_TMM_APPLIED', self::$multi_mods_cache[$mod_id]['title']);
		}
		return $return;
	}
	
	/*
	Locks the specified topic
	
	Parameters
		int $topic_id	- (optional) ID of the topic
	*/
	public static function toggle_lock($topic_id)
	{
		global $db;
		// -- New in RC7; now toggles the Lock/Unlock status
		// First get the current locked status (either locked or unlocked)
		$sql = 'SELECT topic_status
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$old_status = $db->sql_fetchfield('topic_status');
		$db->sql_freeresult($result);
		
		// Now determine if we are locking or unlocking the topic
		$new_status = ($old_status == ITEM_UNLOCKED) ? ITEM_LOCKED : ITEM_UNLOCKED;

		// And finally update the topic with the new status
		$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_status = ' . $new_status . '
				WHERE topic_id = ' . (int) $topic_id . '
					AND topic_moved_id = 0';
		$result = $db->sql_query($sql);
		$return = ($result) ? true : false;
		$db->sql_freeresult($result);
		return $return;
	}
	
	/*
	Change the topic type
		
	Parameters
		int $topic_id	- ID of the topic
		int $type 		- The type to change to; -1 is leave as is
	*/
	public static function alter_topic_type($topic_id, $type = -1)
	{
		global $db;
		// -- New in RC 7
		// This function alters the topic type.
		
		// So first we get the current type.
		$sql = 'SELECT topic_type
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$old_type = $db->sql_fetchfield('topic_type');
		$db->sql_freeresult($result);
		
		// Now we decide what to do.
		// If the old type is -1, leave it as is. Otherwise, make it the new type
		$new_type = ($type === -1) ? $old_type : $type;
		
		// And now do it.
		$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_type = ' . $new_type . '
						WHERE topic_id = ' . (int) $topic_id . '
						AND topic_moved_id = 0';
		$result = $db->sql_query($sql);
		$return = ($result) ? true : false;
		$db->sql_freeresult($result);
		return $return;
	}
	
	/*
	Submits a new reply to the specified topic
	
	Parameters
		string $text	- Text body of reply
		int $topic_id	- (optional) ID of topic
		int $forum_id	- (optional) ID of forum
		int $user_id	- (optional) If given, reply as given user; otherwise/if 0, reply as current user
		bool $bbcode	- (optional) If 1/true, parse bbcode; otherwise, do not parse bbcode
		bool $smilies	- (optional) If 1/true, parse smilies; otherwise, do not parse smilies
	*/	
	public static function auto_reply($text, $topic_id, $forum_id, $user_id = 0, $bbcode = true, $smilies = true)
	{
		global $user, $db;
		if(!function_exists('submit_post'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		}
		$sql = 'SELECT *
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$topicrow = $db->sql_fetchrow($result);
		if($user_id == 0)
		{
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
			$result = $db->sql_query($sql);
			$username = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$username = $username['username'];
		}
		else
		{
			$username = $user->data['username'];
		}
		$autoreply_text = utf8_normalize_nfc($text);
		// variables to hold the parameters for submit_post
		$poll = $uid = $bitfield = $options = ''; 
		generate_text_for_storage($autoreply_text, $uid, $bitfield, $options, $bbcode, true, $smilies);		
		
		$data = array( 
			'forum_id'      => $forum_id,
			'topic_id'		=> $topic_id,
			'icon_id'       => false,
		
			'enable_bbcode'     => true,
			'enable_smilies'    => true,
			'enable_urls'       => true,
			'enable_sig'        => true,
		
			'message'       => $autoreply_text,
			'message_md5'   => md5($autoreply_text),
						
			'bbcode_bitfield'   => $bitfield,
			'bbcode_uid'        => $uid,
		
			'post_edit_locked'  => 0,
			'topic_title'		=> $topicrow['topic_title'],
			'notify_set'        => false,
			'notify'            => false,
			'post_time'         => 0,
			'forum_name'        => '',
			'enable_indexing'   => true,
		);
		
		if(!submit_post('reply', 'Re: ' . $topicrow['topic_title'], $username, POST_NORMAL, $poll, $data))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/*
	Loads the prefix data for the selected prefix ID
	
	Parameters
		int $prefix_id - ID of the prefix
		
	Return
		self::$prefixes_cache[$prefix_id] - Holds prefix data from cache
	*/
	public static function load_prefix($prefix_id)
	{
		global $db, $template, $user, $phpbb_root_path;
		// If the cache has not yet been loaded.
		if(!is_array(self::$prefixes_cache))
		{
			// load it
			self::$prefixes_cache = self::$tmm_cache->get_prefixes();
		}
		// see if the prefix is in the cache
		if(!array_key_exists($prefix_id, self::$prefixes_cache))
		{
			// if not, leave
			return false;
		}
		else
		{
			// otherwise, return the info about it
			return self::$prefixes_cache[$prefix_id];
		}
	}
	
	/*
	Loads and parses all prefixes that are assigned to a topic
	
	Parameters
		$topic_id		- ID of the topic
		$return_type	- Method of returning
							- default: 'string'
							- possible: 'string', 'array'
								- 'array' = array of IDs
								- 'string' = fully formatted string of prefixes
		$input			- Get input from manual array or from SQL
							- default: 'sql'
							- possible: 'sql', an array
								- 'sql' = grab it from the database
								- an array = use a prefilled array of ID's
		
	Return
		Fully formatted prefixes; empty if none
	*/
	public static function load_topic_prefixes($topic_id, $return_type = 'string', $input = 'sql', $method = 'instances')
	{
		global $db;
		
		//If there is nothing to add, at least return a blank string
		$prefix = ($return_type == 'array') ? array() : '';
		// Get each instance of each prefix applied to the topic
		//		ordered by the date it was applied, from the latest to the earliest
		if(is_array($input))
		{
			foreach($input AS $temp)
			{
				if(!empty($temp))
				{
					// Append it with the next one.
					if($return_type == 'array')
					{
						$prefix[] = $temp;
					}
					else
					{
						$prefix .= self::parse_prefix_instance($temp) . '&nbsp;';
					}
				}
			}
		}
		else
		{
			$sql = 'SELECT i.prefix_instance_id AS prefix_instance_id, p.prefix_id AS prefix_id
				FROM ' . TMM_PREFIX_INSTANCES_TABLE . ' i, ' . TMM_PREFIXES_TABLE . ' p
				WHERE topic_id = ' . (int) $topic_id . '
					AND i.prefix_id = p.prefix_id
				ORDER BY applied_date DESC';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				// Append it with the next one.
				if($return_type == 'array')
				{
					$prefix[] = ($method == 'prefixes') ? $row['prefix_id'] : $row['prefix_instance_id'];
				}
				else
				{
					$prefix .= ($method == 'prefixes') ? self::parse_prefix($row['prefix_id']) : self::parse_prefix_instance($row['prefix_instance_id']) . '&nbsp;';
				}
			}
		}
		//Finally return it
		return (empty($prefix)) ? '' : $prefix;
	}
	
	/*
	Generates a string of parsed prefixes using input of array
	
	Parameters
		$prefix_array	- Array of prefix ids
	
	Return
		$prefix_string	- String of parsed prefixes
	*/
	public static function parse_prefix_array($prefix_array)
	{
		if(!is_array($prefix_array))
		{
			return false;
		}
		if(empty($prefix_array))
		{
			return '';
		}
		$prefix_string = '';
		foreach($prefix_array AS $prefix)
		{
			$prefix_string .= (!empty($prefix)) ? self::parse_prefix($prefix) : '';
		}
		return $prefix_string;
	}
	/*
	Applies the specified prefix to the topic.
	
	Parameters
		$prefix_id		- (optional) ID of the prefix; if not given, look for one defined in init();
		$topic_id		- (optional) ID of the topic; if not given, look for one defined in init();
	Return
		false if it doesn't work; true if it works
	*/
	public static function apply_prefix($prefix_id, $topic_id)
	{
		global $db, $template, $user, $phpbb_root_path;
		if(empty($prefix_id))
		{
			return false;
		}
		// First we make sure the prefix exists. If not, it's pointless to run.
		if(!in_array($prefix_id, array_keys(self::$prefixes_cache)))
		{
			return false;
		}
		$sql_ary = array(
			'topic_id'	=> (int) $topic_id,
			'user_id'	=> (int) $user->data['user_id'],
			'prefix_id'	=> (int) $prefix_id,
			'applied_date' => time(),
		);
		// Now let's add the prefix to the topic.
		$sql = 'INSERT INTO ' . TMM_PREFIX_INSTANCES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$result = $db->sql_query($sql);
		//New in RC7 -- Utilize the Moderator logs
		add_log('mod', 0, $topic_id, 'LOG_PREFIX_APPLIED', self::parse_prefix_instance($db->sql_nextid()));
		if(!$result)
		{
			return false;
		}
		return true;
	}
	
	/*
	Removes a prefix from a topic
	
	Parameters
		$prefix_instance_id	- Instance ID of the prefix
		$topic_id			- (optional but recommended) ID of the topic
	
	Return
		true on success; false on failure
	*/
	public static function remove_topic_prefix($prefix_instance_id, $topic_id = 0)
	{
		global $db;
		// Make sure that the instance ID exists.
		//	Topic ID is just there for added precaution to make sure you're actually deleting the right one,
		//		but isn't required.
		$and = ($topic_id != 0) ? ' AND topic_id = ' . (int) $topic_id : '';
		$sql = 'SELECT prefix_id
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_instance_id = ' . (int) $prefix_instance_id . $and;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchfield('prefix_id');
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		//New in RC7 -- Utilize the Moderator logs
		add_log('mod', 0, $topic_id, 'LOG_PREFIX_REMOVED', self::parse_prefix_instance($prefix_instance_id));
		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_instance_id = ' . (int) $prefix_instance_id . $and;
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
	
	/*
	Removes ALL prefixes from a topic
		NOTE: Should be used in topic deletion method so that there are no orphaned prefix instances
	
	Parameters
		$topic_id = (required) ID of the topic
	*/
	public static function remove_topic_prefixes($topic_id)
	{
		global $db;
		// Make sure there are prefixes for the topic so we aren't wasting our time.
		//	The topic doesn't have to exist
		//	This is useful for removing orphaned prefix instances
		$sql = 'SELECT *
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		//New in RC7 -- Utilize the Moderator logs
		add_log('mod', 0, $topic_id, 'LOG_PREFIXES_CLEARED', '');
		
		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
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
	
	/*
	Parses a prefix instance.
		e.g. {DATE} would become the date the prefix is applied, {USERNAME} would become the username that applied it
	
	Parameters
		$prefix_instance_id = ID of the prefix instance
	
	Return
		Parsed prefix; return false if no prefix is found for that instance
	*/
	public static function parse_prefix_instance($prefix_instance_id)
	{
		global $db;
		if(empty($prefix_instance_id))
		{
			return false;
		}
		$sql = 'SELECT i.*,p.*,u.*
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . ' i,
				' . TMM_PREFIXES_TABLE . ' p,
				' . USERS_TABLE . ' u
			WHERE i.prefix_instance_id = ' . (int) $prefix_instance_id . '
				AND i.user_id = u.user_id
				AND i.prefix_id = p.prefix_id';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		$prefix = utf8_normalize_nfc($row['prefix_title']);
		$color = $row['prefix_color_hex'];
		$color = ($color == '') ? '000000' : $color;
		$prefix = '<span style="font-weight:bold;color:#' . $color . ';">' . $prefix . '</span>';
	
		// Find and replace all occurances of the tokens: {USERNAME} and {DATE}
		$prefix = str_replace('{USERNAME}', $row['username'], $prefix);
			//--To Do--
			// Allow admin to specify date format instead of hardcoding
		$prefix = str_replace('{DATE}', date('m/d/Y', $row['applied_date']), $prefix);
		// return the whole prefix string
		return $prefix;
	}
	
	/*
	Parses a prefix for posting screen
	*/
	public static function parse_prefix($prefix_id)
	{
		global $db, $user;
		if(empty($prefix_id))
		{
			return false;
		}
		$row = self::load_prefix($prefix_id);
		$prefix = utf8_normalize_nfc($row['title']);
		$color = $row['colour'];
		$color = ($color == '') ? '000000' : $color;
		$prefix = '<span style="font-weight:bold;color:#' . $color . ';">' . $prefix . '</span>';

		// Find and replace all occurances of the tokens: {USERNAME} and {DATE}
		$prefix = str_replace('{USERNAME}', $user->data['username'], $prefix);
		$prefix = str_replace('{DATE}', date('m/d/Y'), $prefix);
		// return the whole prefix string
		return $prefix;
	}
	/*
	Generates a single/multiple select box with all prefixes allowed for the current user/group in the current forum
	
	Parameters
		$forum_id		- (optional) ID of the forum to look in; if 0, pull from all prefixes
		$type			- (optional) either single or multiple; type of select box
		$prefix_ids		- (optional) prefixes to be preselected
		$excluded_ids	- (optional) array of prefixes to not be displayed
	Return
		Returns nothing if no prefixes are available; else returns HTML code for select box
	*/
	public static function get_prefix_dropdown($forum_id = 0, $type = 'single', $prefix_ids = '', $excluded_ids = '')
	{
		global $user, $db, $phpbb_root_path, $phpEx;
		//Make sure the method is available
		if(!function_exists('group_memberships'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}
		$groups = group_memberships(false,$user->data['user_id']);
		
		$prefixes = array();
		self::$prefixes_cache = (empty(self::$prefixes_cache)) ? array(0) : self::$prefixes_cache;
		foreach(self::$prefixes_cache AS $prefix_id)
		{
			$temp_forums = explode(',', $prefix_id['forums']);
			$temp_groups = explode(',', $prefix_id['groups']);
			if(in_array($forum_id, $temp_forums))
			{
				foreach($groups AS $group)
				{
					if(in_array($group['group_id'], $temp_groups))
					{
						if(!in_array($prefix_id, $prefixes))
						{
							$prefixes[] = $prefix_id;
						}
					}
				}
			}
			$prefix_users = $prefix_id['users'];
			$prefix_users = explode(',', $prefix_users);
			if(in_array($user->data['user_id'], $prefix_users))
			{
				$prefixes[] = $prefix_id;
			}
		}
		$prefixes_options = '';
		if(!is_array($excluded_ids))
		{
			$excluded_ids = explode(',', $excluded_ids);
		}
		foreach($prefixes AS $prefix)
		{
			if(!in_array($prefix['id'], $excluded_ids))
			{
				if(is_array($prefix_ids))
				{
					$disabled = (in_array($prefix, $prefix_ids)) ? 'disabled="disabled"' : '';
				}
				else
				{
					$disabled = ($prefix == $prefix_ids) ? 'selected="selected"' : '';
				}
				$prefixes_options .= '<option value="' . $prefix['id'] . '"' . $disabled . '>' . $prefix['name'] . '</option>';
			}
		}
		//--new in RC7 -- at least have one value if there are no prefixes
		if(empty($prefix_options))
		{
			$prefix_options = '<option value="0" disabled="disabled">' . $user->lang['NO_PREFIXES'] . '</option>';
		}
		$type = ($type == 'multiple') ? 'multiple="multiple"' : '';
		return (empty($prefixes_options)) ? '' : '<select name="prefix_dropdown"' . $type . '><option value="0" disabled="disabled" selected="selected">&nbsp;</option>' . $prefixes_options . '</select>';
	}
	
	/*
	Generates a single/multiple select box with all multi-mods allowed for the current user/group in the current forum
	
	Parameters
		$forum_id	- (optional) ID of the forum to look in; if 0, pull from all multi-mods
		$type		- (optional) either single or multiple; type of select box
	
	Return
		Returns nothing if no multi-mods are available; else returns HTML code for select box
	*/
	public static function get_tmm_dropdown($forum_id = 0, $type = 'single')
	{
		global $user, $db, $phpbb_root_path, $phpEx;
		//Make sure the method is available
		if(!function_exists('group_memberships'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}
		$groups = group_memberships(false,$user->data['user_id']);

		$multi_mods = array();
		$where = ($forum_id != 0) ? ' WHERE tmm_forums LIKE \'%' . (int) $forum_id . '%\'' : '';
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
			$tmm_title = self::$multi_mods_cache[$multi_mod]['title'];
			
			$tmm_options .= '<option value="' . $multi_mod . '">' . $tmm_title . '</option>';
		}
		$type = ($type == 'multiple') ? 'multiple="multiple"'  : '';
		return (empty($tmm_options)) ? '' : '<select name="tmm_select"' . $type . '>' . $tmm_options . '</select>';
	}
	
	/*
	Copies the topic to the new forum.
	
	Parameters
		$topic_id		- The ID of the topic being moved
		$to_forum_id	- ID of the forum the topic will be copied into
		$old_forum_id	- ID of the forum the topic will be copied from
		
	Return
		true - when it works; false - when it doesn't work
	*/
	public static function copy_topic($topic_id, $to_forum_id, $old_forum_id)
	{
		global $auth, $user, $db, $template, $config;
		global $phpEx, $phpbb_root_path;
		
		$tid = $topic_id;
	
		if ($to_forum_id)
		{
			$sql = 'SELECT * FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $old_forum_id . '
				LIMIT 1';
			$dosql = $db->sql_query($sql);
			
			$forum_data = $db->sql_fetchrow($dosql);
			$db->sql_freeresult($dosql);
			if ($forum_data['forum_type'] != FORUM_POST)
			{
				return false;
			}
			else if (!$auth->acl_get('f_post', $to_forum_id))
			{
				return false;
			}
		}
		$sql = 'SELECT *
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $tid;
		$dosql = $db->sql_query($sql);
	
		$topic_data = $db->sql_fetchrowset($dosql);
		if(!sizeof($topic_data))
		{
			return false;
		}
	
		$total_posts = 0;
		$new_topic_id_list = array();
	
		foreach ($topic_data as $topic_id => $topic_data)
		{
			$sql_ary = array(
				'forum_id'					=> (int) $to_forum_id,
				'icon_id'					=> (int) $topic_data['icon_id'],
				'topic_attachment'			=> (int) $topic_data['topic_attachment'],
				'topic_approved'			=> 1,
				'topic_reported'			=> 0,
				'topic_title'				=> (string) $topic_data['topic_title'],
				'topic_poster'				=> (int) $topic_data['topic_poster'],
				'topic_time'				=> (int) $topic_data['topic_time'],
				'topic_replies'				=> (int) $topic_data['topic_replies_real'],
				'topic_replies_real'		=> (int) $topic_data['topic_replies_real'],
				'topic_status'				=> (int) $topic_data['topic_status'],
				'topic_type'				=> (int) $topic_data['topic_type'],
				'topic_first_poster_name'	=> (string) $topic_data['topic_first_poster_name'],
				'topic_last_poster_id'		=> (int) $topic_data['topic_last_poster_id'],
				'topic_last_poster_name'	=> (string) $topic_data['topic_last_poster_name'],
				'topic_last_post_time'		=> (int) $topic_data['topic_last_post_time'],
				'topic_last_view_time'		=> (int) $topic_data['topic_last_view_time'],
				'topic_bumped'				=> (int) $topic_data['topic_bumped'],
				'topic_bumper'				=> (int) $topic_data['topic_bumper'],
				'poll_title'				=> (string) $topic_data['poll_title'],
				'poll_start'				=> (int) $topic_data['poll_start'],
				'poll_length'				=> (int) $topic_data['poll_length'],
			);
	
			$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			$new_topic_id = $db->sql_nextid();
			$new_topic_id_list[$topic_id] = $new_topic_id;
	
			if ($topic_data['poll_start'])
			{
				$poll_rows = array();
	
				$sql = 'SELECT *
					FROM ' . POLL_OPTIONS_TABLE . '
					WHERE topic_id = ' . (int) $tid;
				$result = $db->sql_query($sql);
	
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'poll_option_id'	=> (int) $row['poll_option_id'],
						'topic_id'			=> (int) $new_topic_id,
						'poll_option_text'	=> (string) $row['poll_option_text'],
						'poll_option_total'	=> 0
					);
	
					$db->sql_query('INSERT INTO ' . POLL_OPTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				}
				$db->sql_freeresult($result);
			}
	
			$sql = 'SELECT *
				FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . (int) $tid . '
				ORDER BY post_time ASC';
			$result = $db->sql_query($sql);
	
			$post_rows = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$post_rows[] = $row;
			}
			$db->sql_freeresult($result);
	
			if (!sizeof($post_rows))
			{
				continue;
			}
	
			$total_posts += sizeof($post_rows);
			foreach ($post_rows as $row)
			{
				$sql_ary = array(
					'topic_id'			=> (int) $new_topic_id,
					'forum_id'			=> (int) $to_forum_id,
					'poster_id'			=> (int) $row['poster_id'],
					'icon_id'			=> (int) $row['icon_id'],
					'poster_ip'			=> (string) $row['poster_ip'],
					'post_time'			=> (int) $row['post_time'],
					'post_approved'		=> 1,
					'post_reported'		=> 0,
					'enable_bbcode'		=> (int) $row['enable_bbcode'],
					'enable_smilies'	=> (int) $row['enable_smilies'],
					'enable_magic_url'	=> (int) $row['enable_magic_url'],
					'enable_sig'		=> (int) $row['enable_sig'],
					'post_username'		=> (string) $row['post_username'],
					'post_subject'		=> (string) $row['post_subject'],
					'post_text'			=> (string) $row['post_text'],
					'post_edit_reason'	=> (string) $row['post_edit_reason'],
					'post_edit_user'	=> (int) $row['post_edit_user'],
					'post_checksum'		=> (string) $row['post_checksum'],
					'post_attachment'	=> (int) $row['post_attachment'],
					'bbcode_bitfield'	=> $row['bbcode_bitfield'],
					'bbcode_uid'		=> (string) $row['bbcode_uid'],
					'post_edit_time'	=> (int) $row['post_edit_time'],
					'post_edit_count'	=> (int) $row['post_edit_count'],
					'post_edit_locked'	=> (int) $row['post_edit_locked'],
					'post_postcount'	=> 0,
				);
	
				$db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
				$new_post_id = $db->sql_nextid();
	
				// Copy Attachments
				if ($row['post_attachment'])
				{
					$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . '
						WHERE post_msg_id = ' . (int) $row['post_id'] . '
							AND topic_id = . ' . (int) $tid . '
							AND in_message = 0';
					$result = $db->sql_query($sql);
	
					$sql_ary = array();
					while ($attach_row = $db->sql_fetchrow($result))
					{
						$sql_ary[] = array(
							'post_msg_id'		=> (int) $new_post_id,
							'topic_id'			=> (int) $new_topic_id,
							'in_message'		=> 0,
							'is_orphan'			=> (int) $attach_row['is_orphan'],
							'poster_id'			=> (int) $attach_row['poster_id'],
							'physical_filename'	=> (string) basename($attach_row['physical_filename']),
							'real_filename'		=> (string) basename($attach_row['real_filename']),
							'download_count'	=> (int) $attach_row['download_count'],
							'attach_comment'	=> (string) $attach_row['attach_comment'],
							'extension'			=> (string) $attach_row['extension'],
							'mimetype'			=> (string) $attach_row['mimetype'],
							'filesize'			=> (int) $attach_row['filesize'],
							'filetime'			=> (int) $attach_row['filetime'],
							'thumbnail'			=> (int) $attach_row['thumbnail']
						);
					}
					$db->sql_freeresult($result);
	
					if (sizeof($sql_ary))
					{
						$db->sql_multi_insert(ATTACHMENTS_TABLE, $sql_ary);
					}
				}
			}
	
			$sql = 'SELECT user_id, notify_status
				FROM ' . TOPICS_WATCH_TABLE . '
				WHERE topic_id = ' . (int) $tid;
			$result = $db->sql_query($sql);
	
			$sql_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$sql_ary[] = array(
					'topic_id'		=> (int) $new_topic_id,
					'user_id'		=> (int) $row['user_id'],
					'notify_status'	=> (int) $row['notify_status'],
				);
			}
			$db->sql_freeresult($result);
	
			if (sizeof($sql_ary))
			{
				$db->sql_multi_insert(TOPICS_WATCH_TABLE, $sql_ary);
			}
		}
	
		// Sync new topics, parent forums and board stats
		sync('topic', 'topic_id', $new_topic_id_list);
	
		$sync_sql = array();
	
		$sync_sql[$to_forum_id][]	= 'forum_posts = forum_posts + ' . $total_posts;
		$sync_sql[$to_forum_id][]	= 'forum_topics = forum_topics + ' . sizeof($new_topic_id_list);
		$sync_sql[$to_forum_id][]	= 'forum_topics_real = forum_topics_real + ' . sizeof($new_topic_id_list);
	
		foreach ($sync_sql as $forum_id_key => $array)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . implode(', ', $array) . '
				WHERE forum_id = ' . (int) $forum_id_key;
			$db->sql_query($sql);
		}
	
		sync('forum', 'forum_id', $to_forum_id);
		set_config_count('num_topics', sizeof($new_topic_id_list), true);
		set_config_count('num_posts', $total_posts, true);
		return $new_topic_id;
	}
	
	/**
	* Loads prefix dropdown for posting screen.
	*/
	public static function get_prefixes_for_posting($topic_id = 0, $forum_id = 0, $literal_prefixes = '')
	{
		$literal_prefixes = (!empty($literal_prefixes)) ? $literal_prefixes : self::load_topic_prefixes($topic_id, 'array', 'sql', 'prefixes');
		$prefix_select = self::get_prefix_dropdown($forum_id, 'single', 0, $literal_prefixes);
		return $prefix_select;
	}
	/**
	* Performs an action in the posting page base don parameters
	*
	*	Parameters
	*	(string) $mode		= edit | post
	*	(int)	 $topic_id	= id of the topic that we are working with
	*	(int)	 $action	= 0 | 1 | 2 (add selected | remove selected | remove all)
	*	(int)	 $ids		= ids of prefixes to do $action with; only array if $action = 1
	*/
	public static function do_posting_action($mode = 'post', $topic_id = 0, $action = 0, $ids = 0, $temp_cache = '')
	{
		if($mode == 'edit')
		{
			if($action === 0)
			{
				tmm::apply_prefix($ids, $topic_id);
			}
			if($action === 1)
			{
				if(!is_array($ids))
				{
					$ids = explode(',', $ids);
				}
				foreach($ids AS $id)
				{
					tmm::remove_topic_prefix($id, $topic_id);
				}
			}
			if($action === 2)
			{
				tmm::remove_topic_prefixes($topic_id);
			}
			return '';
		}
		else
		{
			if($action === 0)
			{
				$temp_cache .= $ids . ',';
			}
			if($action === 1)
			{
				foreach($ids AS $id)
				{
					$temp_cache = preg_replace('/' . $id . ',/', '', $temp_cache, 1);
				}
			}
			if($action === 2)
			{
				$temp_cache = '';
			}
		}
		return $temp_cache;
	}
	
	/**
	* Gets the prefixes that are used in the forum and lets the user pick to only view topics with the selected prefix
	* NOTE: Does not work yet.
	*/
	public static function get_forum_prefixes($forum_id, $method = 'array')
	{
		global $phpbb_root_path, $phpEx, $template;
		$prefix_array = array();
		foreach(self::$prefixes_cache AS $prefix)
		{
			$prefix_forums = explode(',', $prefix['forums']);
			if(in_array($forum_id, $prefix_forums))
			{
				if($method != 'array')
				{
					$url = append_sid("{$phpbb_root_path}viewforum.{$phpEx}", "f={$forum_id}&amp;prefix={$prefix['id']}");
					$pre = self::parse_prefix($prefix['id']);
					$pre_link = '<a href="' . $url . '">' . $pre . '</a>';
					$template->assign_block_vars('prefixes', array(
						'PREFIX'	=> $pre_link,
					));
				}
				else
				{
					$prefix_array[] = $prefix['id'];
				}
			}
		}
	}
	
	public static function load_tmm_install_info()
	{
		global $user;
		$install_info = '%s &copy; 2010 <a href="http://phpbbdevelopers.net/" style="font-weight: bold;">phpBB Developers</a>';
		$user->lang['TRANSLATION_INFO'] = $user->lang['TRANSLATION_INFO'] . (($user->lang['TRANSLATION_INFO'] != '') ? '<br />' : '') . sprintf($install_info, TMM_VERSION_BIG);
	}
}