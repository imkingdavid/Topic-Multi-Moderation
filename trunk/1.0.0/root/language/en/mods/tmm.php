<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- Language File
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
	
	'INVALID_MULTI_MOD'			=> 'No multi-mod specified.',
	'INVALID_TOPIC_ID'			=> 'No topic specified.',
	
	'ADD'						=> 'Add Prefix',
	'CLEAR'						=> 'Remove All',
	'REMOVE'					=> 'Remove Checked',
	
	'APPLY_TMM'					=> 'Apply Multi-Mod',
	'APPLY_TMM_CONFIRM'			=> 'The following actions will be performed:<br />%1$s',
	'TMM_PREFIX_ID'				=> 'Prefixes applied: %1$s',
	'TMM_LOCK'					=> 'Topic Locked',
	'TMM_STICKY'				=> 'Topic Stickied',
	'TMM_MOVE'					=> 'Topic Moved',
	'TMM_COPY'					=> 'Topic Copied',
	'TMM_AUTOREPLY_BOOL'		=> 'Reply posted to topic',
	
	//Errors
	'AUTOREPLY_ERROR'			=> 'Could not add reply',
	'LOCK_ERROR'				=> 'Could not lock topic',
	'STICK_ERROR'				=> 'Could not sticky topic',
	'COPY_ERROR'				=> 'Could not copy topic',
	'MOVE_ERROR'				=> 'Could not move topic',
	'PREFIX_ERROR'				=> 'Could not apply prefix(es)',
	
	'TMM_PASS'					=> 'The multi-mod has been applied successfully',
	'TMM_FAIL'					=> 'Some parts of the multi-mod could not be applied. They are listed below.',
	
	'FILE_BUG_REPORT'			=> 'Well, I\'m not sure how you are seeing this page, but please file a bug report, letting me know what you did to get here. Thanks!',

	'MCP_TMM'					=> 'Topic Multi-Moderation',
));
