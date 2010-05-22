<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation -- TMM Constants file
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

//! MOD Version -- NOTE: Must be changed when a new version is released so that the version check works!
define('TMM_VERSION', '0.2.0');
define('TMM_VERSION_BIG', 'Topic Multi Moderation v0.2.0');

//! DB Tables
define('TMM_TABLE',						$table_prefix . 'tmm');
define('TMM_PREFIXES_TABLE',			$table_prefix . 'tmm_prefixes');
define('TMM_PREFIX_INSTANCES_TABLE',	$table_prefix . 'tmm_prefixes_applied');

//! Other
define('TMM_INSTALL_CHECKSUM', '87b1d3be9c22bea9f032a8c5942be459');
define('TMM_HASHFAIL', 'Q09QWVJJR0hUIElMTEVHQUxMWSBSRU1PVkVEISAtLSBNT0QgSVMgRElTQUJMRUQgVU5USUwgQ09QWVJJR0hUIElTIFJFUExBQ0VE');
