<?php
/**
*
*===================================================================
*
*  phpBB Topic Multi Moderation and Prefixes -- ACP Module Info File
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
* @package module_install
*/
class acp_tmm_info
{
    public function module()
    {
        return array(
            'filename'    => 'acp_tmm',
            'title'        => 'ACP_TMM',
            'version'    => '1.0.0',
            'modes'        => array(
                'index'        => array('title' => 'ACP_TMM_MANAGE', 'auth' => 'acl_a_tmm_auth', 'cat' => array('')),
				'prefixes'        => array('title' => 'ACP_PREFIXES_MANAGE', 'auth' => 'acl_a_prefix_auth', 'cat' => array('')),
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