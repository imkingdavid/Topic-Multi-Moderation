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
	'ACP_TMM_DESC'				=> 'Description',
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
	
//! Tasks
	'YES'						=> 'Yes',
	'ENABLE'					=> 'Enable',
	'ENABLED'					=> 'Enabled',
	'NO'						=> 'No',
	'DISABLE'					=> 'Disable',
	'DISABLED'					=> 'Disabled',
	'LOCK'						=> 'Lock this topic?',
	'STICKY'					=> 'Make this topic sticky?',
	'MOVE'						=> 'Move this topic?',
	'COPY'						=> 'Copy this topic?',
	'WHERE'						=> 'ID of destination forum',
	'AUTORESPONSE'				=> 'Auto-Reply Text',
	'AUTOREPLY'					=> 'Auto-Reply',
	'AUTOREPLY_EXPLAIN'			=> 'If enabled, supply poster ID and auto-reply text',
	'AUTOREPLY_POSTER'			=> 'Auto-Reply Poster',
	'AUTOREPLY_POSTER_EXPLAIN'	=> 'The auto-reply will be posted by the user with this user ID. If set to 0, the current user will be the post author.',
	
	
//! Prefixes
	'ACP_PREFIXES_TITLE'		=> 'Topic Prefixes',
	'ACP_PREFIX_TITLE'			=> 'Prefix',
	'ACP_PREFIX_NAME'			=> 'Prefix Name',
	'ACP_PREFIX_ADD_EDIT'		=> 'Create/Modify Prefixes',
	'ACP_PREFIXES_ADD'			=> 'Add Prefix',
	'ACP_PREFIXES_MANAGE'		=> 'Manage Topic Prefixes',
	'PREFIX'					=> 'Prefix',
	'PREFIXES'					=> 'Prefixes',
	'ACP_PREFIXES_EXPLAIN'		=> 'This page allows you to view and manage all topic prefixes (which can be integrated with multi-mods). To add a prefix, click Add.',
	'COLOR'						=> 'Color (Hex)',
	'PREFIXES_SHORTNAME'		=> 'Short Name',
	'PREFIX_REMOVED'			=> 'The prefix has been deleted successfully.',
	'NOROWS_PREFIXES'			=> 'No prefixes have been made.',
	'PREFIX_CREATED'			=> 'Your prefix has been created successfully.',
	'PREFIX_CREATE_ERROR'		=> 'There was an error creating your prefix. Please try again.',
	'PREFIX_EDITED'				=> 'Your prefix has been edited successfully.',
	'PREFIEX_EDIT_ERROR'		=> 'There was an error editing your prefix. Please try again.',
	'PREFIX_DELETED'			=> 'Your prefix has been deleted successfully.',
	'PREFIX_DELETE_ERROR'		=> 'There was an error deleting your prefix. Please try again.',
	'USERS_EXPLAIN'				=> 'These users may use this prefix regardless of group settings above. Enter each user ID followed by a comma. (e.g. 1,5,62)',
	'TOKENS_EXPLAIN'			=> '<span style="text-decoration:underline;">Tokens:</span><br />{USERNAME} becomes the username of the person who applied the prefix.<br />{DATE} becomes the date on which the prefix was applied.',
	
//! Version Check
	'LATEST_VERSION'			=> 'Latest Version:',
	'YOUR_VERSION'				=> 'Current Version:',
	'UPDATE_TO'					=> 'Update to %1$s',
	'SERVER_DOWN'				=> 'The update server appears to be down. Try again in a few minutes. If the problem persists for more than a day, please check the development topic at phpBB.com for the latest version information.',
	'UP_2_DATE'					=> 'The installed version of Topic Multi-Moderation is up to date.',
	'NOT_UP_2_DATE'				=> 'The installed version of Topic Multi-Moderation is <strong>not</strong> up to date.',
));
?>