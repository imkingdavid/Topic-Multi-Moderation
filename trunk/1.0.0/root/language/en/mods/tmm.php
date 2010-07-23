<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- Language File
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
	'MULTI_MODS'				=> 'Multi-mods',
	'PREFIX_SELECT'				=> 'Add a Prefix',
	'PREFIX_SELECT_EXPLAIN'		=> 'Select a prefix from the list to add it to this topic.',
	'APPLIED_PREFIXES'			=> 'Applied Prefixes',
	'APPLIED_PREFIXES_EXPLAIN'	=> 'These prefixes are already applied to this topic.',
	
	'ADD_PREFIX'				=> 'Add Prefix',
	'CLEAR'						=> 'Remove All',
	'REMOVE'					=> 'Remove Checked',
	
	'INVALID_MULTI_MOD'			=> 'No multi-mod specified.',
	'INVALID_TOPIC_ID'			=> 'No topic specified.',
	'APPLY_TMM'					=> 'Apply Multi-Mod',
	'APPLY_TMM_CONFIRM'			=> 'The following actions will be performed:<br />%1$s',
	'TMM_PREFIX_ID'				=> 'Prefixes applied: %1$s',
	'TMM_LOCK'					=> 'Topic Locked',
	'TMM_STICKY'				=> 'Topic Stickied',
	'TMM_MOVE'					=> 'Topic Moved',
	'TMM_COPY'					=> 'Topic Copied',
	'TMM_AUTOREPLY_BOOL'		=> 'Reply posted to topic',
	
	//Logs
	// Moderator
	'LOG_TMM_APPLIED'			=> '<strong>Applied multi-mod to topic</strong><br />» %s',
	'LOG_PREFIX_APPLIED'		=> '<strong>Applied prefix to topic</strong><br />» %s',
	'LOG_PREFIX_REMOVED'		=> '<strong>Removed prefix from topic</strong><br />» %s',
	'LOG_PREFIXES_CLEARED'		=> '<strong>Removed all prefixes from topic</strong>',
	// Admin
	//  Multi-mods
	'LOG_TMM_CREATED'			=> '<strong>Multi-mod created</strong><br />» %s',
	'LOG_TMM_MODIFIED'			=> '<strong>Multi-mod modified</strong><br />» %s',
	'LOG_TMM_DELETED'			=> '<strong>Multi-mod deleted</strong><br />» %s',
	//  Prefixes
	'LOG_PREFIX_CREATED'		=> '<strong>Prefix created</strong><br />» %s',
	'LOG_PREFIX_MODIFIED'		=> '<strong>Prefix modified</strong><br />» %s',
	'LOG_PREFIX_DELETED'		=> '<strong>Prefix deleted</strong><br />» %s',
	
	
	//Errors
	'AUTOREPLY_ERROR'			=> 'Could not add reply',
	'LOCK_ERROR'				=> 'Could not lock topic',
	'STICK_ERROR'				=> 'Could not sticky topic',
	'COPY_ERROR'				=> 'Could not copy topic',
	'MOVE_ERROR'				=> 'Could not move topic',
	'PREFIX_ERROR'				=> 'Could not apply prefix(es)',
	
	'TMM_PASS'					=> 'The multi-mod has been applied successfully',
	'TMM_FAIL'					=> 'Some parts of the multi-mod could not be applied. They are listed below.',
	
	// Eventual search integration
	'SEARCH_PREFIXES'			=> 'Search by prefix(es)',
	'SEARCH_PREFIXES_EXPLAIN'	=> 'Show all topics with at least one of the selected prefixes',
));