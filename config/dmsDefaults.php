<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */ 

// include the environment settings
require_once("environment.php");
// table mapping entries
include("tableMappings.inc");
// site map definition
include("siteMap.inc");
// instantiate log
require_once("$default->fileSystemRoot/lib/Log.inc");
$default->log = new Log($default->fileSystemRoot . "/log", INFO);
$default->timerLog = new Log($default->fileSystemRoot . "/log", INFO, "timer");
// setup i18n if gettext is installed
if (in_array("gettext", get_loaded_extensions()) && function_exists('gettext') && function_exists('_')) {
	require_once("$default->fileSystemRoot/lib/i18n/languageFunctions.inc");	
	require_once("$default->fileSystemRoot/lib/i18n/accept-to-gettext.inc");	
	if ($default->useAcceptLanguageHeader) {
	    $aInstalledLocales = getInstalledLocales();
	    $sLocale=al2gt($aInstalledLocales, 'text/html');
	    $default->defaultLanguage = $sLocale;
	}		
	putenv('LANG=' . $default->defaultLanguage); 
	setlocale(LC_ALL, $default->defaultLanguage);
	// Set the text domain
	$sDomain = 'knowledgeTree';
	bindtextdomain($sDomain, $default->fileSystemRoot . "/i18n"); 
	textdomain($sDomain);
} else {
	$default->log->info("Gettext not installed, i18n disabled.");
	// define a dummy _ function so gettext is not -required-
	function _($sString) {
		return $sString;
	}
}
require_once("$default->fileSystemRoot/phpmailer/class.phpmailer.php");
require_once("$default->fileSystemRoot/lib/session/Session.inc");
require_once("$default->fileSystemRoot/lib/session/control.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
// browser settings
require_once("$default->fileSystemRoot/phpSniff/phpSniff.class.php");
require("browsers.inc");
// import request variables and setup language
require_once("$default->fileSystemRoot/lib/dms.inc");
?>
