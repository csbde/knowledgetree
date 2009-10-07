<?php
/**
* Upgrader Paths.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 3 as published by the
* Free Software Foundation.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
* California 94120-7775, or email info@knowledgetree.com.
*
* The interactive user interfaces in modified source and object code versions
* of this program must display Appropriate Legal Notices, as required under
* Section 5 of the GNU General Public License version 3.
*
* In accordance with Section 7(b) of the GNU General Public License version 3,
* these Appropriate Legal Notices must retain the display of the "Powered by
* KnowledgeTree" logo and retain the original copyright notice. If the display of the
* logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
* must display the words "Powered by KnowledgeTree" and retain the original
* copyright notice.
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Upgrader
* @version Version 0.1
*/
	// Define installer environment
	define('DEBUG', 0);
	define('AJAX', 0);
	// Define upgrader environment
	if (substr(php_uname(), 0, 7) == "Windows"){
    	define('WINDOWS_OS', true);
    	define('UNIX_OS', false);
    	define('OS', 'windows');
	} else {
    	define('WINDOWS_OS', false);
    	define('UNIX_OS', true);
    	define('OS', 'unix');
	}
	if(WINDOWS_OS) {
		define('DS', '\\');
	} else {
		define('DS', '/');
	}
	// Define environment root
	$wizard = realpath(dirname(__FILE__));
	$xdir = explode(DS, $wizard);
	array_pop($xdir);
	array_pop($xdir);
	$sys = '';
	foreach ($xdir as $k=>$v) {
		$sys .= $v.DS;
	}
	// Define paths to wizard
    define('UPGRADE_DIR', $wizard.DS);
    define('WIZARD_LIB', UPGRADE_DIR."lib".DS);
    define('SQL_DIR', UPGRADE_DIR."sql".DS);
    define('SQL_UPGRADE_DIR', SQL_DIR."upgrades".DS);
    define('CONF_DIR', UPGRADE_DIR."config".DS);
    define('RES_DIR', UPGRADE_DIR."resources".DS);
    define('STEP_DIR', UPGRADE_DIR."steps".DS);
    define('TEMP_DIR', UPGRADE_DIR."templates".DS);
    define('SHELL_DIR', UPGRADE_DIR."shells".DS);
    define('OUTPUT_DIR', UPGRADE_DIR."output".DS);
    // Define paths to system webroot
	define('SYSTEM_DIR', $sys);
	define('SYS_VAR_DIR', SYSTEM_DIR."var".DS);
    define('SYS_BIN_DIR', SYSTEM_DIR."bin".DS);
    define('SYS_LOG_DIR', SYS_VAR_DIR."log".DS);
    define('SYS_OUT_DIR', SYS_VAR_DIR);
    define('VAR_BIN_DIR', SYS_VAR_DIR."bin".DS);
    // Define paths to system
    array_pop($xdir);
	$asys = '';
	foreach ($xdir as $k=>$v) {
		$asys .= $v.DS;
	}
    define('SYSTEM_ROOT', $asys);
    // Upgrade Type
    preg_match('/Zend/', $sys, $matches); // TODO: Dirty
    if($matches) {
		$sysdir = explode(DS, $sys);
		array_pop($sysdir);
		array_pop($sysdir);
		array_pop($sysdir);
		array_pop($sysdir);
		$zendsys = '';
		foreach ($sysdir as $k=>$v) {
			$zendsys .= $v.DS;
		}
    	define('INSTALL_TYPE', 'Zend');
    	define('PHP_DIR', $zendsys."ZendServer".DS."bin".DS);
    } else {
    	$modules = get_loaded_extensions();
    	// TODO: Dirty
    	if(in_array('Zend Download Server', $modules) || in_array('Zend Monitor', $modules) || in_array('Zend Utils', $modules) || in_array('Zend Page Cache', $modules)) {
    		define('INSTALL_TYPE', 'Zend');
    		define('PHP_DIR', '');
    	} else {
    		define('INSTALL_TYPE', '');
    		define('PHP_DIR', '');
    	}
    }
    // Other
    date_default_timezone_set('Africa/Johannesburg');
    if(WINDOWS_OS) { // Mysql bin [Windows]
	    $serverPaths = explode(';',$_SERVER['PATH']);
	    foreach ($serverPaths as $apath) {
	    	preg_match('/mysql/i', $apath, $matches);
	    	if($matches) {
	    		define('MYSQL_BIN', $apath.DS);
	    		break;
	    	}
	    }
    } else {
    	define('MYSQL_BIN', ''); // Assume its linux and can be executed from command line
    }
    
?>
