<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- Admin Language File
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
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_TMM'					=> 'Topic Multi-Moderation',
	'ACP_TMM_MANAGE'			=> 'Manage Multi-Mods',
	'ACP_TMM_EXPLAIN'			=> 'This page allows you to view and manage all multi-mods. To add a multi-mod, click Add.',
	'ACP_TMM_ADD_EDIT'			=> 'Add/Edit Multi-Mod',
	'ACP_TMM_ADD'				=> 'Add Multi-Mod',
	'ACP_TMM_TITLE'				=> 'Title of Multi-Mod',
	'ACP_TMM_TITLE_EXPLAIN'		=> 'The title of your multi-mod displayed in various locations to identify this multi-mod.',
	'ACP_TMM_DESC'				=> 'Description',
	'ACP_TMM_DESC_EXPLAIN'		=> 'A short description of what this multi-mod should do. This is only shown in the ACP.',
	'ACP_TMM_DELETED'			=> 'The Multi-Mod has been deleted successfully.',
	'NOROWS_TMM'				=> 'No multi-mods have been made.',
	'TMM_CREATED'				=> 'Your multi-mod has been created successfully.',
	'TMM_CREATE_ERROR'			=> 'There was an error creating your multi-mod. Please try again.',
	'TMM_EDITED'				=> 'Your multi-mod has been edited successfully.',
	'TMM_EDIT_ERROR'			=> 'There was an error editing your multi-mod. Please try again.',
	'TMM_DELETED'				=> 'Your multi-mod has been deleted successfully.',
	'TMM_DELETE_ERROR'			=> 'There was an error deleting your multi-mod. Please try again',
	'FORUMIDS'					=> 'Enabled forum id\'s',
	'BBCODEALLOW'				=> 'BBCode and Smileys are allowed',
	'TMM_FORUMS_EXPLAIN'		=> 'This multi-mod can be used in the selected forums. To select or deselect one or more forums, hold control and click on the forum name.',
	'TMM_GROUPS_EXPLAIN'		=> 'These groups have permission to use this multi-mod in the selected forums. To select or deselect one or more groups, hold control and click on the group name.',
	'TMM_USERS_EXPLAIN'			=> 'These users have permission to use this multi-mod in the selected forums, regardless of the groups chosen above. Enter the <strong>username</strong> of each user followed by a comma and no space. e.g. user,jack,jane',
	
//! Tasks
	'YES'						=> 'Yes',
	'ENABLE'					=> 'Enable',
	'ENABLED'					=> 'Enabled',
	'NO'						=> 'No',
	'DISABLE'					=> 'Disable',
	'DISABLED'					=> 'Disabled',
	// Changes in RC7
	//	-- Lock Topic has become Toggle Locked status
	'LOCK'						=> 'Toggle Lock/Unlock status?',
	'LOCK_EXPLAIN'				=> 'If Yes, the topic will be locked or unlocked, depending on its current status.',
	
	//	-- Stick Topic has become Alter Topic Type
	'STICKY'					=> 'Alter topic type?',
	'STICKY_EXPLAIN'			=> 'Leave as is, or make it normal, a sticky, an announcement, or a global announcement.',
	// -- And add the possible choices for topic types
	'TOPIC_LEAVE'				=> 'Leave As Is',
	'TOPIC_NORMAL'				=> 'Normal Topic',
	'TOPIC_STICKY'				=> 'Stick Topic',
	'TOPIC_ANNOUNCE'			=> 'Announce Topic',
	'TOPIC_GLOBAL'				=> 'Global Announce Topic',
	
	'NO_PREFIXES'				=> 'No prefixes',
	
	'MOVE'						=> 'Move this topic?',
	'MOVE_EXPLAIN'				=> 'If Yes, the topic will be moved to the destination forum set below.',
	'COPY'						=> 'Copy this topic?',
	'COPY_EXPLAIN'				=> 'If Yes, the topic will be copied to the destination forum set below.',
	'WHERE'						=> 'Destination forum',
	'AUTORESPONSE'				=> 'Auto-Reply Text',
	'AUTORESPONSE_EXPLAIN'		=> 'This is the body of the autoresponse. BBCode and smilies are allowed.',
	'AUTOREPLY'					=> 'Auto-Reply',
	'AUTOREPLY_EXPLAIN'			=> 'If enabled, supply poster ID and auto-reply text',
	'AUTOREPLY_POSTER'			=> 'Auto-Reply Poster',
	'AUTOREPLY_POSTER_EXPLAIN'	=> 'The auto-reply will be posted by the user with this user ID. If set to 0, the current user will be the post author.',
	
	
//! Prefixes
	'ACP_PREFIXES_TITLE'		=> 'Topic Prefixes',
	'ACP_PREFIX_TITLE'			=> 'Prefix',
	'ACP_PREFIX_TITLE_EXPLAIN'	=> 'This prefix will be applied to the topic.',
	'ACP_PREFIX_NAME'			=> 'Prefix Name',
	'ACP_PREFIX_NAME_EXPLAIN'	=> 'Name of the prefix used to identify it from other similar prefixes.<br /><strong>Not the actual prefix!</strong>',
	'ACP_PREFIX_ADD_EDIT'		=> 'Create/Modify Prefixes',
	'ACP_PREFIXES_ADD'			=> 'Add Prefix',
	'ACP_PREFIXES_MANAGE'		=> 'Manage Topic Prefixes',
	'PREFIX'					=> 'Prefix',
	'PREFIXES'					=> 'Prefixes',
	'ACP_PREFIXES_EXPLAIN'		=> 'This page allows you to view and manage all topic prefixes (which can be integrated with multi-mods). To add a prefix, click Add.',
	'COLOR'						=> 'Color (Hex)',
	'COLOR_EXPLAIN'				=> 'Click the link to the right of the box to choose from a palette of colors or type your own hexadecimal color code into the box. Leave blank for black.',
	'PREFIXES_SHORTNAME'		=> 'Short Name',
	'PREFIX_REMOVED'			=> 'The prefix has been deleted successfully.',
	'NOROWS_PREFIXES'			=> 'No prefixes have been made.',
	'PREFIX_CREATED'			=> 'Your prefix has been created successfully.',
	'PREFIX_CREATE_ERROR'		=> 'There was an error creating your prefix. Please try again.',
	'PREFIX_EDITED'				=> 'Your prefix has been edited successfully.',
	'PREFIEX_EDIT_ERROR'		=> 'There was an error editing your prefix. Please try again.',
	'PREFIX_DELETED'			=> 'Your prefix has been deleted successfully.',
	'PREFIX_DELETE_ERROR'		=> 'There was an error deleting your prefix. Please try again.',
	
	'TOKENS_EXPLAIN'			=> 'The following tokens can be used as placeholders for variable inforumation:<br />{USERNAME} becomes the username of the person who applied the prefix.<br />{DATE} becomes the date on which the prefix was applied.',
	'PREFIX_FORUMS_EXPLAIN'		=> 'This prefix can be used in the selected forums. To select or deselect one or more forums, hold control and click on the forum name.',
	'PREFIX_GROUPS_EXPLAIN'		=> 'These groups have permission to use this prefix in the selected forums. To select or deselect one or more groups, hold control and click on the group name.',
	'PREFIX_USERS_EXPLAIN'		=> 'These users have permission to use this prefix in the selected forums, regardless of the groups chosen above. Enter the <strong>username</strong> of each user followed by a comma and no space. e.g. user,jack,jane',
	
//! Version Check
	'LATEST_VERSION'			=> 'Latest Version:',
	'YOUR_VERSION'				=> 'Current Version:',
	'UPDATE_TO'					=> 'Update to %1$s',
	'SERVER_DOWN'				=> 'The update server appears to be down. Try again in a few minutes. If the problem persists for more than a day, please check the development topic at phpBB.com for the latest version information.',
	'UP_2_DATE'					=> 'The installed version of Topic Multi-Moderation is up to date.',
	'NOT_UP_2_DATE'				=> 'The installed version of Topic Multi-Moderation is <strong>not</strong> up to date.',
));
?>