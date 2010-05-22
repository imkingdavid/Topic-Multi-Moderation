<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- TMM MCP Info File
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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package module_install
*/
class mcp_tmm_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_tmm',
			'title'		=> 'MCP_TMM',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'index'		=> array('title' => 'MCP_TMM', 'auth' => 'acl_m_', 'cat' => array('MCP_TMM')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}