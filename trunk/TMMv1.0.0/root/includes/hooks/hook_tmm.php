<?php
/**
*
* @author David King (imkingdavid@gmail.com) http://www.phpbbdevelopers.net
*
* @package phpBB3
* @copyright (c) 2010 David King
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
// Hooks allow you to include stuff without actually editing the main files as much
function tmm_open()
{
	global $phpbb_root_path, $phpEx, $user, $table_prefix;
	$user->add_lang('mods/tmm');
	if (!defined('TMM_VERSION'))
	{
		include($phpbb_root_path . 'includes/mods/tmm_constants.' . $phpEx);
	}
	if (!class_exists('tmm_cache'))
	{
		include($phpbb_root_path . 'includes/mods/functions_tmm_cache.' . $phpEx);
	}
	if (!class_exists('tmm'))
	{
		include($phpbb_root_path . 'includes/mods/functions_tmm.' . $phpEx);
	}
	if (!class_exists('tmm_admin'))
	{
		include($phpbb_root_path . 'includes/mods/functions_tmm_admin.' . $phpEx);
	}
	if (!function_exists('move_topics'))
	{
		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}
	tmm::start();
}
//don't break the UMIL install
if(!defined('UMIL_AUTO') && !defined('IN_INSTALL'))
{
	$phpbb_hook->register('phpbb_user_session_handler', 'tmm_open');
}