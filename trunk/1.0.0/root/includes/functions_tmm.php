<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- General Functions File
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
if(!defined('IN_PHPBB'))
{
	exit;
}

class tmm
{
	var $error = array(); // will hold the error messages if any
	var $actions = array(); // an array of all the actions performed.
	
	/*
	Applies the specified multi-mod to the specified topic
	
	Parameters
		int $mod_id		- ID of the Multi-Mod
		int $topic_id	- ID of the topic
		int $forum_id	- ID of the forum
	*/
	function apply_tmm($mod_id, $topic_id, $forum_id)
	{
		global $db, $template, $user, $phpbb_root_path;
		
		//Get the information about the multi-mod
		$sql = 'SELECT *
			FROM ' . TMM_TABLE . "
			WHERE tmm_id = $mod_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		// make sure the multi-mod exists
		if(!$row)
		{
			trigger_error("INVALID_MULTI_MOD");
		}
		//Get information about the topic itself
		$sql = 'SELECT *
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		$result = $db->sql_query($sql);
		$topicrow = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		// make sure the topic exists
		if(!$topicrow)
		{
			trigger_error("INVALID_TOPIC_ID");
		}
		
		//Check each mutli-mod action to see if it should be done.
		if($row['tmm_autoreply_bool'] == 1)
		{
			$poster = ($row['tmm_autoreply_poster'] != 0) ? $row['tmm_auto_reply_poster'] : 0;
			$auto_reply = $this->auto_reply($row['tmm_autoreply_text'], $topic_id, $forum_id, $poster, true, true);
			if(!$auto_reply)
			{
				$this->error[] = $user->lang['AUTOREPLY_ERROR'];
			}
		}
		if($row['tmm_prefix_id'] != '')
		{
			// Allow for multiple prefixes to be applied with one multi-mod ^_^
			$prefixes = explode(',', $row['tmm_prefix_id']);
			$fails = 0;
			foreach($prefixes AS $prefix)
			{
				$pre = $this->apply_prefix($prefix, $topic_id);
				if(!$pre)
				{
					$fails++;
				}
			}
			if($fails != 0)
			{
				$this->error[] = sprintf($user->lang['PREFIX_ERROR'], $fails);
			}
		}
		if($row['tmm_lock'] == 1)
		{
			$lock = $this->lock_topic($topic_id);
			if(!$lock)
			{
				$this->error[] = $user->lang['LOCK_ERROR'];
			}
		}
		if($row['tmm_sticky'] == 1)
		{
			$stick = $this->stick_topic($topic_id);
			if(!$stick)
			{
				$this->error[] = $user->lang['STICK_ERROR'];
			}
		}
		if($row['tmm_copy'] == 1)
		{
			$copy = $this->copy_topic($topid_id, $row['tmm_copy_dest_id'], $topicrow['forum_id']);
			if(!$copy)
			{
				$this->error[] = $user->lang['COPY_ERROR'];
			}
		}
		if($row['tmm_move'] == 1)
		{
			$move = $this->move_topics($topic_id, $row['tmm_move_dest_id'], $topicrow['forum_id']);
			if(!$move)
			{
				$this->error[] = $user->lang['MOVE_ERROR'];
			}
		}
		return (empty($this->error)) ? true : $this->error;
	}
	
	/*
	Locks the specified topic
	
	Parameters
		int $topic_id	- (optional) ID of the topic
	*/
	public function lock_topic($topic_id)
	{
		global $db;
		$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_status = ' . ITEM_LOCKED . "
				WHERE topic_id = $topic_id
					AND topic_moved_id = 0";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
			$db->sql_freeresult($result);
			return true;
		}
	}
	
	/*
	Change the topic type to sticky for the specified topic
	
	Parameters
		int $topic_id	- ID of the topic
	*/
	public function stick_topic($topic_id)
	{
		global $db;
		$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_type = ' . POST_STICKY . "
						WHERE topic_id = $topic_id
						AND topic_moved_id = 0";
		$result = $db->sql_query($sql);
		if(!$result)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
			$db->sql_freeresult($result);
			return true;
		}
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
	public function auto_reply($text, $topic_id, $forum_id, $user_id = 0, $bbcode = true, $smilies = true)
	{
		global $user, $db;
		if(!function_exists('submit_post'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		}
		$sql = 'SELECT *
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		$result = $db->sql_query($sql);
		$topicrow = $db->sql_fetchrow($result);
		if($user_id == 0)
		{
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . "
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql);
			$username = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$username = $username['username'];
		}
		else
		{
			$username = $user->data['username'];
		}
		$autoreply_text = utf8_normalize_nfc($autoreply_text);
		
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
		int $prefix_id - (optional) ID of the prefix; if not given, look for one defined in init();
		
	Return
		$row - Holds prefix data from DB
	*/
	function load_prefix($prefix_id)
	{
		global $db, $template, $user, $phpbb_root_path;
		$sql = 'SELECT *
			FROM ' . TMM_PREFIXES_TABLE . "
			WHERE prefix_id = $prefix_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		else
		{
			return $row;
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
	function load_topic_prefixes($topic_id, $return_type = 'string', $input = 'sql')
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
						$prefix .= $this->parse_prefix_instance($temp) . '&nbsp;';
					}
				}
			}
		}
		else
		{
			$sql = 'SELECT prefix_instance_id
				FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
				WHERE topic_id = ' . $topic_id . '
				ORDER BY applied_date DESC';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				// Append it with the next one.
				if($return_type == 'array')
				{
					$prefix[] = $row['prefix_instance_id'];
				}
				else
				{
					$prefix .= $this->parse_prefix_instance($row['prefix_instance_id']) . '&nbsp;';
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
	function parse_prefix_array($prefix_array)
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
			$prefix_string .= $this->parse_prefix($prefix);
		}
		return $prefix_string;
	}
	/*
	Applies the specified prefix to the topic. Normally called within apply_multi_mod() method.
	
	Parameters
		$prefix_id		- (optional) ID of the prefix; if not given, look for one defined in init();
		$topic_id		- (optional) ID of the topic; if not given, look for one defined in init();
	Return
		false if it doesn't work; true if it works
	*/
	function apply_prefix($prefix_id, $topic_id)
	{
		global $db, $template, $user, $phpbb_root_path;
		if(empty($prefix_id))
		{
			return false;
		}
		// First we make sure the prefix exists. If not, it's pointless to run.
		$sql = 'SELECT *
			FROM ' . TMM_PREFIXES_TABLE . '
			WHERE prefix_id = ' . $db->sql_escape($prefix_id);
		$result = $db->sql_query($sql);
		$prefix = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$prefix)
		{
			return false;
		}
		$sql_ary = array(
			'topic_id'	=> $topic_id,
			'user_id'	=> $user->data['user_id'],
			'prefix_id'	=> $prefix_id,
			'applied_date' => time(),
		);
		// Now let's add the prefix to the topic.
		$sql = 'INSERT INTO ' . TMM_PREFIX_INSTANCES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
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
	Removes a prefix from a topic
	
	Parameters
		$prefix_instance_id	- Instance ID of the prefix
		$topic_id			- (optional but recommended) ID of the topic
	
	Return
		true on success; false on failure
	*/
	function remove_topic_prefix($prefix_instance_id, $topic_id = 0)
	{
		global $db;
		// Make sure that the instance ID exists.
		//	Topic ID is just there for added precaution to make sure you're actually deleting the right one,
		//		but isn't required.
		$and = ($topic_id != 0) ? ' AND topic_id = ' . $topic_id : '';
		$sql = 'SELECT prefix_id
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_instance_id = ' . $prefix_instance_id . $and;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		
		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE prefix_instance_id = ' . $prefix_instance_id . $and;
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
	function remove_topic_prefixes($topic_id)
	{
		global $db;
		// Make sure there are prefixes for the topic so we aren't wasting our time.
		//	The topic doesn't have to exist
		//	This is useful for removing orphaned prefix instances
		$sql = 'SELECT *
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if(!$row)
		{
			return false;
		}
		
		$sql = 'DELETE
			FROM ' . TMM_PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . $topic_id;
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
	function parse_prefix_instance($prefix_instance_id)
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
			WHERE i.prefix_instance_id = ' . $prefix_instance_id . '
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

		$username = $row['username'];
		$date = $row['applied_date'];
		$date = date('m/d/Y', $date);
		
		// Find and replace all occurances of the tokens: {USERNAME} and {DATE}
		$prefix = str_replace('{USERNAME}', $username, $prefix);
		$prefix = str_replace('{DATE}', $date, $prefix);
		// return the whole prefix string
		return $prefix;
	}
	
	/*
	Parses a prefix for posting screen
	*/
	function parse_prefix($prefix_id)
	{
		global $db, $user;
		if(empty($prefix_id))
		{
			return false;
		}
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
		$prefix = utf8_normalize_nfc($row['prefix_title']);
		$color = $row['prefix_color_hex'];
		$color = ($color == '') ? '000000' : $color;
		$prefix = '<span style="font-weight:bold;color:#' . $color . ';">' . $prefix . '</span>';

		$username = $user->data['username'];
		$date = date('m/d/Y');
		
		// Find and replace all occurances of the tokens: {USERNAME} and {DATE}
		$prefix = str_replace('{USERNAME}', $username, $prefix);
		$prefix = str_replace('{DATE}', $date, $prefix);
		// return the whole prefix string
		return $prefix;
	}
	/*
	Generates a single/multiple select box with all prefixes allowed for the current user/group in the current forum
	
	Parameters
		$forum_id	- (optional) ID of the forum to look in; if 0, pull from all prefixes
		$type		- (optional) either single or multiple; type of select box
		$prefix_ids	- (optional) IDs to preselect
	
	Return
		Returns nothing if no prefixes are available; else returns HTML code for select box
	*/
	function get_prefix_dropdown($forum_id = 0, $type = 'single', $prefix_ids = '')
	{
		global $user, $db, $phpbb_root_path, $phpEx;
		// Check what group a user is in
		if ( !function_exists('group_memberships') )
		{
			include_once($phpbb_root_path . 'includes/functions_user.'.$phpEx);
		}
		$groups = group_memberships(false,$user->data['user_id']);
		
		$prefixes = array();
		$where = ($forum_id != 0) ? ' WHERE prefix_forums LIKE \'%' . $forum_id . '%\'' : '';
		$sql = 'SELECT *
			FROM ' . TMM_PREFIXES_TABLE . $where;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$prefix_groups = $row['prefix_groups'];
			$prefix_groups = explode(',', $prefix_groups);
			foreach($groups AS $group)
			{
				if(in_array($group['group_id'], $prefix_groups))
				{
					$prefixes[] = $row['prefix_id'];
				}
			}
			$prefix_users = $row['prefix_users'];
			$prefix_users = explode(',', $prefix_users);
			if(in_array($user->data['user_id'], $prefix_users))
			{
				$prefixes[] = $row['prefix_id'];
			}
		}
		$prefixes = array_unique($prefixes);
		$prefixes_options = '';
		foreach($prefixes AS $prefix)
		{
			$sql = 'SELECT prefix_name
				FROM ' . TMM_PREFIXES_TABLE . '
				WHERE prefix_id = ' . $prefix;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$prefixes_options .= '<option value="' . $prefix . '">' . stripslashes($row['prefix_name']) . '</option>';
		}
		$type = ($type == 'multiple') ? 'multiple="multiple"'  : '';
		return (empty($prefixes_options)) ? '' : '<select name="prefix_dropdown"' . $type . '>' . $prefixes_options . '</select>';
	}
	
	/*
	Generates a single/multiple select box with all multi-mods allowed for the current user/group in the current forum
	
	Parameters
		$forum_id	- (optional) ID of the forum to look in; if 0, pull from all multi-mods
		$type		- (optional) either single or multiple; type of select box
	
	Return
		Returns nothing if no multi-mods are available; else returns HTML code for select box
	*/
	function get_tmm_dropdown($forum_id = 0, $type = 'single')
	{
		global $user, $db, $phpbb_root_path, $phpEx;
		// Check what group a user is in
		if ( !function_exists('group_memberships') )
		{
			include_once($phpbb_root_path . 'includes/functions_user.'.$phpEx);
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
			$sql = 'SELECT tmm_title
				FROM ' . TMM_TABLE . '
				WHERE tmm_id = ' . $multi_mod;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$tmm_options .= '<option value="' . $multi_mod . '">' . stripslashes($row['tmm_title']) . '</option>';
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
	function copy_topic($topic_id, $to_forum_id, $old_forum_id)
	{
		global $auth, $user, $db, $template, $config;
		global $phpEx, $phpbb_root_path;
		
		if(!$auth->acl_get('m_'))
		{
			return false;
		}
		$tid = $topic_id;
	
		if ($to_forum_id)
		{
			$sql = 'SELECT * FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $old_forum_id
				LIMIT 1";
			$dosql = $db->sql_query($sql);
			
			$forum_data = $db->sql_fetchrow($dosql);
	
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
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $tid";
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
				'prefix_id'					=> (int) $topic_data['prefix_id'],
			);
	
			$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
			$new_topic_id = $db->sql_nextid();
			$new_topic_id_list[$topic_id] = $new_topic_id;
	
			if ($topic_data['poll_start'])
			{
				$poll_rows = array();
	
				$sql = 'SELECT *
					FROM ' . POLL_OPTIONS_TABLE . "
					WHERE topic_id = $tid";
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
			}
	
			$sql = 'SELECT *
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $tid
				ORDER BY post_time ASC";
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
					$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
						WHERE post_msg_id = {$row['post_id']}
							AND topic_id = $tid
							AND in_message = 0";
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
				WHERE topic_id = ' . $tid;
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
				WHERE forum_id = ' . $forum_id_key;
			$db->sql_query($sql);
		}
	
		sync('forum', 'forum_id', $to_forum_id);
		set_config_count('num_topics', sizeof($new_topic_id_list), true);
		set_config_count('num_posts', $total_posts, true);
		return true;
	
	}
	
	/**
	* Loads TMM installation information. @@ FUNCTION COPYRIGHT TO HOUSE @@
	*/
	function load_tmm_install_info()
	{
		global $user;
		$install_info = 'cGhwQkIgVG9waWMgTXVsdGkgTW9kZXJhdGlvbiAmY29weTsgMjAxMCA8YSBocmVmPSJodHRwOi8vcGhwYmJkZXZlbG9wZXJzLm5ldC8iIHN0eWxlPSJmb250LXdlaWdodDogYm9sZDsiPkRhdmlkPC9hPiAmIDxhIGhyZWY9Imh0dHA6Ly9pbmZpbml0eWhvdXNlLm9yZy8iIHN0eWxlPSJmb250LXdlaWdodDogYm9sZDsiPkhvdXNlPC9hPg==';
		$install_info = base64_decode($install_info);
		$user->lang['TRANSLATION_INFO'] = $user->lang['TRANSLATION_INFO'] . (($user->lang['TRANSLATION_INFO'] != '') ? '<br />' : '') . sprintf($install_info, TMM_VERSION_BIG, TMM_VERSION);
	}
}