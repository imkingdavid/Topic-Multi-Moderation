<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- Install File
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

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'TMM_TITLE';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'tmm_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/info_acp_tmm_install';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)

$options = array(
	'test_username'	=> array('lang' => 'TEST_USERNAME', 'type' => 'text:40:255', 'explain' => true, 'default' => $user->data['username'], 'select_user' => true),
	'test_boolean'	=> array('lang' => 'TEST_BOOLEAN', 'type' => 'radio:yes_no', 'default' => true),
);
*/

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/

$versions = array(
	/* Feature for later planned: canned responses, can be used inside and out of multi-mod; extension of "Auto-Response feature".
	'1.1.0' => array(
		'table_add' => array(
			array('phpbb_tmm_canned_responses', array(
					'COLUMNS'	=> array(
						'response_id'		=> array('UINT', NULL, 'auto_increment'),
						'response_text'		=> array('VCHAR_UNI', ''), // Descriptive name for use in drop downs
						'response_title'	=> array('VCHAR_UNI', ''), // The actual prefix text that is shown
						'response_forums'	=> array('VCHAR_UNI', ''), // Forums that the prefix can be used in
						'response_users'	=> array('VCHAR_UNI', ''), // Users that can use this prefix (overrides group settings)
						'response_groups'	=> array('VCHAR_UNI', ''), // Groups that can use this prefix
					),
					'PRIMARY_KEY'	=> 'prefix_id',
				),
			),
		),
	),*/
	// Had to do .01 or it wouldn't let me do an update. Next time I'll need to take RC's into account.
	'1.0.01' => array(
		// We don't need these permissions anymore; they were for the older version and got scrapped.
		'permission_remove' => array(
			array('f_tmm_use', false),
			array('f_prefix_use', false),
		),
	),
	'1.0.0' => array(
	 /* No SQL changes */
	),
	'0.2.1' => array(
	 /* No SQL changes */
	),
	'0.2.0' => array(
		'module_add' => array(
			array('acp', 'ACP_CAT_POSTING', 'ACP_TMM'),
			array('acp', 'ACP_TMM', array(
					'module_basename'		=> 'tmm',
					'modes'					=> array('index', 'prefixes'),
				),
			),
		),

		'permission_add' => array(
			array('a_tmm_auth', true),
			array('a_prefix_auth', true),
			array('f_tmm_use', false),
			array('f_prefix_use', false),
		),
		'permission_set' => array(
			array('ROLE_ADMIN_FULL', 'a_tmm_auth'),
		),
		'permission_set' => array(
			array('ROLE_ADMIN_FULL', 'a_prefix_auth'),
			array('ROLE_FORUM_STANDARD', 'f_tmm_use'),
			array('ROLE_FORUM_STANDARD', 'f_prefix_use'),			
		),
		'table_add' => array(
			array('phpbb_tmm', array(
					'COLUMNS'		=> array(
						'tmm_id'				=> array('UINT', NULL, 'auto_increment'), // ID of the multi-mod
						'tmm_title'				=> array('VCHAR_UNI', ''), // Descriptive title of the mulit-mod for use in drop downs and such
						'tmm_desc'				=> array('VCHAR_UNI', ''), // Description of the multi-mod... not used very many places outside of the ACP, tbh
						'tmm_lock'				=> array('TINT:1', 0), // Boolean; lock topic?
						'tmm_sticky'			=> array('TINT:1', 0), // Boolean; stick topic?
						'tmm_move'				=> array('TINT:1', 0), // Boolean; move topic?
						'tmm_move_dest_id'		=> array('UINT', 0), // ID of destination for move
						'tmm_copy'				=> array('TINT:1', 0), // Boolean; copy topic?
						'tmm_copy_dest_id'		=> array('UINT', 0), // ID of destination for copy
						'tmm_forums'			=> array('VCHAR_UNI', ''), // Forums it can be applied in
						'tmm_users'				=> array('VCHAR_UNI', ''), // Users that can apply it (override group setting)
						'tmm_groups'			=> array('VCHAR_UNI', ''), // Groups that can apply it
						'tmm_prefix_id'			=> array('VCHAR_UNI', ''), // Prefixes to apply; now can be more than one!
						'tmm_autoreply_bool'	=> array('TINT:1', 0), // Boolean; reply to topic?
						'tmm_autoreply_text'	=> array('TEXT_UNI', ''), // Text of the reply
						'tmm_autoreply_poster'	=> array('UINT', 0), // User to respond as
					),
					'PRIMARY_KEY'	=> 'tmm_id',
				),
			),
			array('phpbb_tmm_prefixes', array(
					'COLUMNS'	=> array(
						'prefix_id'			=> array('UINT', NULL, 'auto_increment'),
						'prefix_name'		=> array('VCHAR_UNI', ''), // Descriptive name for use in drop downs
						'prefix_title'		=> array('VCHAR_UNI', ''), // The actual prefix text that is shown
						'prefix_color_hex'	=> array('VCHAR_UNI', ''), // Hexadecimal code corresponding to the color
						'prefix_forums'		=> array('VCHAR_UNI', ''), // Forums that the prefix can be used in
						'prefix_users'		=> array('VCHAR_UNI', ''), // Users that can use this prefix (overrides group settings)
						'prefix_groups'		=> array('VCHAR_UNI', ''), // Groups that can use this prefix
					),
					'PRIMARY_KEY'	=> 'prefix_id',
				),
			),
			array('phpbb_tmm_prefixes_applied', array(
					'COLUMNS'	=> array(
						'prefix_instance_id'	=> array('UINT', NULL, 'auto_increment'), // ID of the instance; aka one place it has been applied.
																						// This allows multiple prefixes on one topic
						'topic_id'				=> array('UINT', 0), // ID of the topic to apply it to
						'prefix_id'				=> array('UINT', 0), // ID of the prefix to use
						'user_id'				=> array('UINT', 0), // ID of the user who applied it
						'applied_date'			=> array('TIMESTAMP', 0), // Date it was applied (for ordering)
					),
					'PRIMARY_KEY'	=> 'prefix_instance_id',
				),
			),
		),
	),
);
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);