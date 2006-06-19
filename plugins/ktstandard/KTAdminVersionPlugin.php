<?php
/**
 * $Id: KTWorkflowAssociation.php 5336 2006-04-25 12:42:45Z bshuttle $
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php'); 
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
 
define('KT_VERSION_URL', 'http://www.ktdms.com/kt_versions');

class AdminVersionDashlet extends KTBaseDashlet {
    var $oUser;
    
    function is_active($oUser) {
	$this->oUser = $oUser;
	return Permission::userIsSystemAdministrator($oUser);
    }
    
    function render() {
	global $default;
        $oPlugin =& $this->oPlugin;
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktstandard/adminversion/dashlet');

	$aVersions = KTUtil::getKTVersions();
	$sVersions = '{';
	
	foreach($aVersions as $k=>$v) {
	    $sVersions .= "'$k' : '$v',";
	}

	$sVersions = substr($sVersions, 0, -1) . '}';	    

        $sUrl = $oPlugin->getPagePath('versions');

	$aTemplateData = array('context' => $this, 
			       'kt_versions' => $sVersions,
			       'kt_versions_url' => $sUrl,
                               );

	return $oTemplate->render($aTemplateData);
    }
}
 
class AdminVersionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.adminversion.plugin";
    var $autoRegister = true;
    
    function AdminVersionPlugin($sFilename = null) {
	$res = parent::KTPlugin($sFilename);
	$this->sFriendlyName = _kt('Admin Version Plugin');
	return $res;
    }
    
    function setup() {
	$this->registerDashlet('AdminVersionDashlet', 'ktstandard.adminversion.dashlet', 'KTAdminVersionPlugin.php');
	$this->registerPage('versions', 'AdminVersionPage');
    }
}

class AdminVersionPage extends KTStandardDispatcher {
    function _checkCache() {
        global $default;
        $iLastCheck = KTUtil::getSystemSetting('ktadminversion_lastcheck');
        if (empty($iLastCheck)) {
            return;
        }
        $sLastValue = KTUtil::getSystemSetting('ktadminversion_lastvalue');
        if (empty($sLastValue)) {
            return;
        }
        $now = time();
        $diff = $now - $iLastCheck;
        if ($diff > (60 * 60 * 6)) {
            return;
        }
        return $sLastValue;
    }

    function do_main() {
        $sCache = $this->_checkCache();
        if (!empty($sCache)) {
            return $sCache;
        }

        $sUrl = KT_VERSION_URL;
	$aVersions = KTUtil::getKTVersions();
        foreach ($aVersions as $k => $v) {
            $sUrl = KTUtil::addQueryString($sUrl, sprintf("%s=%s", $k, $v));
        }
        $sIdentifier = KTUtil::getSystemIdentifier();
        $sUrl = KTUtil::addQueryString($sUrl, sprintf("system_identifier=%s", $sIdentifier));

        $stuff = @file_get_contents($sUrl);
        if ($stuff === false) {
            return "";
        }
        KTUtil::setSystemSetting('ktadminversion_lastcheck', time());
        KTUtil::setSystemSetting('ktadminversion_lastvalue', $stuff);
        return $stuff;
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('AdminVersionPlugin', 'ktstandard.adminversion.plugin', __FILE__);
?>
