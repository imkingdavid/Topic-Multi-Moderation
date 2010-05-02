<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- Language File
*-------------------------------------------------------------------
*	Script info:
* Version:		0.2.1 - "Triton"
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
	
	'ADD'						=> 'Add Prefix',
	'CLEAR'						=> 'Remove All',
	'REMOVE'					=> 'Remove Checked',
	
	// Eventual search integration
	'SEARCH_PREFIXES'			=> 'Search by prefix(es)',
	'SEARCH_PREFIXES_EXPLAIN'	=> 'Show all topics with at least one of the selected prefixes',
));
