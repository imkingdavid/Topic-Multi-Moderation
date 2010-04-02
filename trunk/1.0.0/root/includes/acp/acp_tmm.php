<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- ACP Module File
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
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* acp_tmm
* Topic Multi-Moderation Administration
* @package acp
*/
class acp_tmm
{
   var $u_action;
   
   function main($id, $mode)
   {
      global $db, $user, $auth, $template;
      global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	  global $table_prefix;
	  include($phpbb_root_path . 'includes/tmm_constants.' . $phpEx);
	  include($phpbb_root_path . 'includes/functions_tmm.' . $phpEx);
	  include($phpbb_root_path . 'includes/functions_tmm_admin.' . $phpEx);
	  $tmm = new tmm_admin; // Has methods from tmm and tmm_admin because tmm_admin extends tmm
      //! Load our installation information...
	  $tmm->load_tmm_install_info();
	
	  switch($mode)
      {
	      case 'index':
			$tmm->check_version();
			
            $this->page_title = 'ACP_TMM';
            $this->tpl_name = 'acp_tmm';
			
			$action = request_var('action', '');
			$submit = (isset($_POST['submit'])) ? true : false;
			$create = (isset($_POST['create'])) ? true : false;
			$tmm_id = request_var('id', 0);
							 
			$form_name = 'acp_tmm';
			add_form_key('acp_tmm');
			if ($submit && !check_form_key('acp_tmm'))
			{
				$update = false;
				$errors[] = $user->lang['FORM_INVALID'];
			}
			
			if($action == 'add' || $action == 'edit')
			{
				if($submit)
				{
					$this->page_title = 'ACP_TMM_ADD_EDIT';
					$data = array(
						'tmm_title' => utf8_normalize_nfc(request_var('tmm_title', '', true)),
					);
					
					$prefix_select = $tmm->get_prefix_select(0);
					$parents_list = make_forum_select(0, false, false, false, false);
					$thisaction = ($action == 'add') ? $action : $action . '&amp;id=' . $tmm_id;
					$template->assign_vars(array(
						'S_EDIT'		=> true,
						'TMM_TITLE'		=> request_var('title', '', true),
						'FORUM_LIST'	=> $parents_list,
						'PREFIX_SELECT'	=> $prefix_select,
						'U_ACTION'		=> $this->u_action . '&amp;action=' . $thisaction,
					));
					$actions = array('LOCK', 'STICKY', 'MOVE', 'COPY', 'AUTOREPLY_BOOL');
					foreach($actions AS $action)
					{
						$template->assign_vars(array(
							"{$action}_YES"	=> '',
							"{$action}_NO"	=> 'checked="checked"',
						));
					}
				}
				if($action == 'edit' && !($submit || $create))
				{
					$sql = 'SELECT *
							FROM ' . TMM_TABLE . "
							WHERE tmm_id = $tmm_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					$actions = array('lock', 'sticky', 'move', 'copy', 'autoreply_bool');
					foreach($actions AS $action)
					{
						$uppercase = strtoupper($action);
						$template->assign_vars(array(
							"{$uppercase}_YES"	=> ($row['tmm_' . $action] == 1) ? 'checked="checked"' : '',
							"{$uppercase}_NO"	=> ($row['tmm_' . $action] == 1) ? '' : 'checked="checked"',
						));
					}
					
					$template->assign_vars(array(
						'TMM_TITLE'			=> $row['tmm_title'],
						'TMM_DESC'	 		=> $row['tmm_desc'],
						
						'MOVEWHERE'			=> $row['tmm_move_dest_id'],
						'COPYWHERE'			=> $row['tmm_copy_dest_id'],
						
						'AUTORESPONSE'		=> $row['tmm_autoreply_text'],
						'AUTOREPLY_POSTER'	=> $row['tmm_autoreply_poster'],
						
						'TMM_ID'			=> $row['tmm_id'],
					));
					$forum_ids = explode(",", $row['tmm_forums']);
					$forum_list = make_forum_select($forum_ids, false, false, false, false);
					$group_ids = explode(",", $row['tmm_groups']);
					//Make a list of the groups for the select box
					$group_options = $tmm->get_group_select($group_ids);
					$prefix_ids = explode(",", $row['tmm_prefix_id']);
					$prefix_options = $tmm->get_prefix_select($prefix_ids);

					$template->assign_vars(array(
						'U_ACTION'	=> $this->u_action . '&amp;action=edit',
						'GROUP_SELECT'	=> $group_options,
						'PREFIX_SELECT'	=> $prefix_options,
						'USER_ID'	=> $row['tmm_users'],
						'S_EDIT'	=> true,
						'FORUM_LIST'=> $forum_list,
					));
				}
				if($create)
				{
					$this->page_title = 'ACP_TMM_ADD_EDIT';
					
					$forum_id = request_var('forum_id', array('' => 0));
					$group_id = request_var('group_id', array('' => 0));
					$prefix_id = request_var('prefix_id', array('' => 0));
					$fid = implode(',', $forum_id);
					$gid = implode(',', $group_id);
					$pid = implode(',', $prefix_id);

					$data = array(
						'tmm_title'		=> utf8_normalize_nfc(request_var('title', '')),
						'desc'			=> utf8_normalize_nfc(request_var('desc', '')),
						'prefix_id'		=> $pid,
						'forum_id'		=> $fid,
						'group_id'		=> $gid,
						'user_ids'		=> request_var('user_ids', ''),
						'lock_option'	=> (int) request_var('lock_option', '0'),
						'sticky_option'	=> (int) request_var('sticky_option', '0'),
						'copy_option'	=> (int) request_var('copy_option', '0'),
						'copywhere'		=> (int) request_var('copywhere', '0'),
						'move_option'	=> (int) request_var('move_option', '0'),
						'movewhere'		=> (int) request_var('movewhere', '0'),
						'autoresponse'	=> request_var('autoresponse', ''),
						'autoreply'		=> (int) request_var('autoreply', '0'),
					);
					$sql_ary = array(
						'tmm_title'				=> $data['tmm_title'],
						'tmm_desc'				=> $data['desc'],
						'tmm_prefix_id'			=> $data['prefix_id'],
						'tmm_forums'			=> $data['forum_id'],
						'tmm_groups'			=> $data['group_id'],
						'tmm_users'				=> $data['user_ids'],
						'tmm_lock'				=> $data['lock_option'],
						'tmm_sticky'			=> $data['sticky_option'],
						'tmm_move'				=> $data['move_option'],
						'tmm_move_dest_id'		=> $data['movewhere'],
						'tmm_copy'				=> $data['copy_option'],
						'tmm_copy_dest_id'		=> $data['copywhere'],
						'tmm_autoreply_text'	=> $data['autoresponse'],
						'tmm_autoreply_bool'	=> $data['autoreply'],
					);					
					$type = ($action == 'add') ? 'new' : 'update';
					$u_action = $this->u_action;
					$tmm_id_post = request_var('tmm_id', 0);
					$tmm->submit_tmm($type, $sql_ary, $tmm_id_post, $u_action);
				}
			}
			elseif($action == 'delete')
			{
			}
			else
			{					
				$sql = 'SELECT *
					FROM ' . TMM_TABLE . '
					ORDER BY tmm_id ASC';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('tmm_row', array(
						'TMM_TITLE'			=> $row['tmm_title'],
						'TMM_DESC'			=> $row['tmm_desc'],
						'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;id=' . $row['tmm_id'],
						'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;id=' . $row['tmm_id'],
					));
					$prefixes = explode(',', $row['tmm_prefix_id']);
					foreach($prefixes AS $prefix)
					{
						if(!empty($prefix))
						{
							$sql = 'SELECT *
								FROM ' . TMM_PREFIXES_TABLE . '
								WHERE prefix_id = ' . $prefix;
							$res = $db->sql_query($sql);
							
							while($row2 = $db->sql_fetchrow($res))
							{
								$template->assign_block_vars('tmm_row.prefix', array(
									'PREFIX_TITLE'		=> $row2['prefix_title'],
									'COLOR'				=> $row2['prefix_color_hex'],
								));
							}
							$db->sql_freeresult($res);
						}
					}
				}
				$db->sql_freeresult($result);
			}
	
        break;

		case 'prefixes':
			$tmm->check_version();
		
			$this->page_title = 'ACP_PREFIXES_MANAGE';
			$this->tpl_name = 'acp_prefixes';
						
			$action = request_var('action', '');
			$submit = (isset($_POST['submit'])) ? true : false;
			$create = (isset($_POST['create'])) ? true : false;
			$prefix_id = request_var('id', (int) 0);
													 
			$form_name = 'acp_prefix';
			$form_key = add_form_key('acp_prefix');
			if ($submit && !check_form_key($form_key))
			{
				$update = false;
				$errors[] = $user->lang['FORM_INVALID'];
			}
			if($action == 'add' || $action == 'edit')
			{
				if($submit)
				{
					$this->page_title = 'ACP_TMM_ADD_EDIT';
					$data = array(
						'prefix_name' => utf8_normalize_nfc(request_var('prefix_name', '', true)),
					);
					$group_options = $tmm->get_group_select(0);
					$template->assign_vars(array(
						'PREFIX_NAME'	=> $data['prefix_name'],
						'GROUP_SELECT'	=> $group_options,
					));
				}
				if($action == 'edit' && !($submit || $create))
				{
					$sql = 'SELECT *
							FROM ' . TMM_PREFIXES_TABLE . '
							WHERE prefix_id = ' . $prefix_id . '
							LIMIT 1';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					$template->assign_vars(array(
						'PREFIX_NAME'	=> $row['prefix_name'],
						'PREFIX_TITLE' 	=> $row['prefix_title'],
						'PREFIX_COLOR'	=> $row['prefix_color_hex'],
						'PREFIX_USERS'	=> $row['prefix_users'],
					));
					$forum_ids = explode(",", $row['prefix_forums']);
					$forum_list = make_forum_select($forum_ids, false, false, false, false);
					$group_ids = explode(",", $row['prefix_groups']);
					//Make a list of the groups for the select box
					$group_options = $tmm->get_group_select($group_ids);

					$template->assign_vars(array(
						'U_ACTION'	=> $this->u_action . '&amp;action=edit',
						'GROUP_SELECT'	=> $group_options,
						'S_EDIT'	=> true,
						'FORUM_LIST'=> $forum_list,
					));
				}
				if($create)
				{
					$this->page_title = 'ACP_TMM_ADD_EDIT';
					$forum_id = request_var('forum_id', array('' => 0));
					$group_id = request_var('group_id', array('' => 0));
					$prefix_users = utf8_normalize_nfc(request_var('user_ids', ''));
					
					$prefix_forums = implode(',', $forum_id);
					$prefix_groups = implode(',', $group_id);
					$prefix_title = utf8_normalize_nfc(request_var('prefix_title', ''));
					$prefix_name = utf8_normalize_nfc(request_var('prefix_name', ''));
					$prefix_color_hex = utf8_normalize_nfc(request_var('prefix_color_hex', ''));
					$tmm->create_prefix($action, $prefix_name, $prefix_title, $prefix_color_hex, $prefix_forums, $prefix_groups, $prefix_users, $prefix_id, $this->u_action);
				}
				if($action == 'add')
				{
					$parents_list = make_forum_select(0, false, false, false, false);
					$template->assign_vars(array(
						'U_ACTION'	=> $this->u_action . '&amp;action=add',
						'S_EDIT'	=> true,
						'FORUM_LIST'=> $parents_list,
					));
				}
				elseif($action == 'edit')
				{
					$template->assign_vars(array(
						'PREFIX_ID' => $prefix_id,
						'U_ACTION' => $this->u_action . '&amp;action=edit&amp;id=' . $prefix_id,
					));
				}
			}
			elseif($action == 'delete')
			{
				$template->assign_vars(array(
					'U_ACTION' 	=> $this->u_action,
				));
				$tmm->delete_prefix($prefix_id);
			}
			else
			{
				$sql = 'SELECT *
				FROM ' . TMM_PREFIXES_TABLE . '
				ORDER BY prefix_id ASC';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('prefix_row', array(
						'PREFIX_NAME'		=> $row['prefix_name'],
						'PREFIX_TITLE'		=> $row['prefix_title'],
						'PREFIX_COLOR'		=> $row['prefix_color_hex'],
						'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;id=' . $row['prefix_id'],
						'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;id=' . $row['prefix_id'],
					));
				}
				$db->sql_freeresult($result);
			}
			$template->assign_var('U_SWATCH', append_sid("{$phpbb_admin_path}swatch.$phpEx", 'form=prefix&amp;name=prefix_color_hex'));
		break;

      }
   }
}