<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright(C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 * Contributor(s): Guenter Roeck______________________________________
 *
 */

if (defined('DMS_DEFAULTS_INCLUDED')) {
	return;
}

define('DMS_DEFAULTS_INCLUDED', 1);
define('LATEST_WEBSERVICE_VERSION', 3);

// stuff in the new installer section database upgrade fails without this
global $default;

// ensure $default is a proper class
if (!($default instanceof stdClass)) {
    $default = new stdClass();
}

if (!session_id()) {
	session_start();
}

if (function_exists('apd_set_pprof_trace')) {
	apd_set_pprof_trace();
}

// Default settings differ, we need some of these, so force the matter.
// Can be overridden here if actually necessary.
error_reporting(E_ALL & ~ E_NOTICE);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('magic_quotes_runtime', '0');
ini_set('arg_separator.output', '&');

$microtime_simple = explode(' ', microtime());

$_KT_starttime = (float)$microtime_simple[1] + (float)$microtime_simple[0];
unset($microtime_simple);

// If not defined, set KT_DIR based on my usual location in the tree
if (!defined('KT_DIR')) {
	$rootLoc = realpath(dirname(__FILE__) . '/..');
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		$rootLoc = str_replace('\\', '/', $rootLoc);
	}
	define('KT_DIR', $rootLoc);
}

if (!defined('KT_PLUGIN_DIR'))
	define('KT_PLUGIN_DIR', KT_DIR . '/plugins');

if (!defined('KT_LIB_DIR')) {
	define('KT_LIB_DIR', KT_DIR . '/lib');
}

// If not defined, set KT_STACK_DIR based on my usual location in the tree
// TODO: This needs to use a config.ini entry if available
if (!defined('KT_STACK_DIR')) {
	$stackLoc = realpath(dirname(__FILE__) . '/../..');
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		$stackLoc = str_replace('\\', '/', $stackLoc);
	}
	define('KT_STACK_DIR', $stackLoc);
}

// TODO Remove this, we should not be supporting legacy PHP systems.
// PATH_SEPARATOR added in PHP 4.3.0
if (!defined('PATH_SEPARATOR')) {
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		define('PATH_SEPARATOR', ';');
	} else {
		define('PATH_SEPARATOR', ':');
	}
}

require_once(KT_LIB_DIR . '/validation/customerror.php');

function prependPath($path) {
	ini_set('include_path', $path . PATH_SEPARATOR . ini_get('include_path'));
}

prependPath(KT_DIR . '/thirdparty/ZendFramework/library');
prependPath(KT_DIR . '/thirdparty/pear');
prependPath(KT_DIR . '/thirdparty/Smarty');
prependPath(KT_DIR . '/thirdparty/simpletest');
prependPath(KT_DIR . '/thirdparty/xmlrpc-2.2/lib');
prependPath(KT_DIR . '/ktapi');
prependPath(KT_DIR . '/search2');

require_once(KT_DIR . '/thirdparty/pear/PEAR.php');
require_once(KT_LIB_DIR . '/util/legacy.inc');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . '/config/config.inc.php');

//====================================

require_once(KT_LIB_DIR . '/init/KTInitFactory.inc.php');
$KTInit = KTInitFactory::getSystemInitializer();
$KTInit->initConfig();
$KTInit->setupI18n();

define('KTLOG_CACHE', false);

if (isset($GLOBALS['kt_test'])) {
	$KTInit->initTesting();
}

$KTConfig = KTConfig::getSingleton();

if ($KTConfig->get('CustomErrorMessages/customerrormessages') == 'on') {
	$KTInit->catchFatalErrors();
}

$KTInit->setupServerVariables();

$loggingSupport = $KTInit->setupLogging();
$KTConfig->logErrors();

// Send all PHP errors to a file(and maybe a window)
set_error_handler(array('KTInit', 'handlePHPError'));

$KTInit->setupRandomSeed();

$GLOBALS['KTRootUrl'] = $KTConfig->get('KnowledgeTree/rootUrl');

require_once(KT_LIB_DIR . '/database/lookup.inc');
include('tableMappings.inc');

$default->systemVersion = trim(file_get_contents(KT_DIR . '/docs/VERSION.txt'));
$default->versionName = trim(file_get_contents(KT_DIR . '/docs/VERSION-NAME.txt'));
$default->versionTier = 'community';

$KTInit->cleanGlobals();
$KTInit->cleanMagicQuotes();

// TODO Find out why removal of the site map breaks the system.
require_once(KT_DIR . '/config/siteMap.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/session/control.inc');

// Plugin loading.  TODO Improve this loading so that plugins are available but only instantiated on demand.
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

if ($checkup !== true) {
	// Replace function later
	/* ** Get the page being loaded and load the plugins specific to the page ** */
	$scriptName = $GLOBALS['_SERVER']['SCRIPT_NAME'];
	$script = basename($scriptName);
	$pos = strpos($script, '.');
	$scriptExtension = substr($script, 0, $pos);

	$res = KTPluginUtil::loadPlugins($scriptExtension);
	if (PEAR::isError($res)) {
	    // If the plugins aren't loaded, there was a DB error, possibly a DB connection error.
	    $KTInit->showDBError($res);
	}

	// License specific check.
	if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
		$path = KTPluginUtil::getPluginPath('ktdms.wintools');
		require_once($path . 'baobabkeyutil.inc.php');
		$name = BaobabKeyUtil::getName();
		if ($name) {
			$default->versionName = sprintf('%s %s', $default->versionName, $name);
		}
	} else {
		$default->versionName = $default->versionName . ' ' . _kt('(Community Edition)');
	}
}

if (!extension_loaded('mbstring')) {
	require_once(KT_LIB_DIR . '/mbstring.inc.php');
}

require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
$GLOBALS['main'] = new KTPage();
// TODO move line above to dispatcher functions - since init no longer happens here???

// Ensure initialization is complete.
$KTInit->finalize();

?>
