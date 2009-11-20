<?php
/**
* Installer Paths.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
*
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
* @package Installer
* @version Version 0.1
*/
	$browser = $_SERVER['HTTP_USER_AGENT'];
	//MSIE 6.0
	if(preg_match("/MSIE 6\.\d/", $browser)) {
		define('AGENT', 'IE6');
	} else if(preg_match("/MSIE 7\.\d/", $browser)) {
		define('AGENT', 'IE7');
	} else if(preg_match("/MSIE 8\.\d/", $browser)) {
		define('AGENT', 'IE8');
	} else {
		define('AGENT', 'OTHER');
	}
	// Define installer environment
	define('AJAX', 0);
	if (substr(php_uname(), 0, 7) == "Windows") {
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
	$sys = '';
	foreach ($xdir as $k=>$v) {
		$sys .= $v.DS;
	}
	if(isset($_GET['type'])) {
		switch ($_GET['type']) {
			case 'migrate' :
				$wizard = $sys.'migrate';
			break;
			case 'upgrade' :
				$wizard = $sys.'upgrade';
			break;
			default:

			break;
		}
	}
	$xdir = explode(DS, $wizard);
	array_pop($xdir);
	array_pop($xdir);
	$sys = '';
	foreach ($xdir as $k=>$v) {
		$sys .= $v.DS;
	}
	// Define paths to wizard
    define('WIZARD_DIR', $wizard.DS);
    define('WIZARD_LIB', WIZARD_DIR."lib".DS);
    define('SERVICE_LIB', WIZARD_LIB."services".DS);
    define('CONF_DIR', WIZARD_DIR."config".DS);
    define('RES_DIR', WIZARD_DIR."resources".DS);
    define('JS_DIR', RES_DIR."js".DS);
    define('CSS_DIR', RES_DIR."css".DS);
    define('IMG_DIR', RES_DIR."graphics".DS);
    define('STEP_DIR', WIZARD_DIR."steps".DS);
    define('TEMP_DIR', WIZARD_DIR."templates".DS);
    define('SYS_DIR', WIZARD_LIB."system".DS);
    define('HELPER_DIR', WIZARD_LIB."helpers".DS);
    define('VALID_DIR', WIZARD_LIB."validation".DS);
    // Define paths to system webroot
	define('SYSTEM_DIR', $sys);
	define('SYS_VAR_DIR', SYSTEM_DIR."var".DS);
    define('SYS_BIN_DIR', SYSTEM_DIR."bin".DS);
    define('SYS_OUT_DIR', SYS_VAR_DIR);
    define('VAR_BIN_DIR', SYS_VAR_DIR."bin".DS);
    // Define paths to system
    array_pop($xdir);
	$asys = '';
	foreach ($xdir as $k=>$v) {
		$asys .= $v.DS;
	}
    define('SYSTEM_ROOT', $asys);
	$verType = SYSTEM_DIR."docs".DS."VERSION-TYPE.txt";
	$type = false;
    if(file_exists($verType)) {
		$type = file_get_contents($verType);
    }
    if($type) {
		define('INSTALL_TYPE', trim($type));
    } else {
		define('INSTALL_TYPE', 'community');
	}
?>
