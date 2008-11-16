<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php'); 
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');

define('KT_VERSION_URL', 'http://www.knowledgetree.com/kt_versions');

class AdminVersionDashlet extends KTBaseDashlet {
    var $oUser;
    var $sClass = 'ktError';

    function AdminVersionDashlet() {
        $this->sTitle = _kt('New Version Available');
    }

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
        if (function_exists('curl_init') || (!OS_WINDOWS)) {
            $this->registerDashlet('AdminVersionDashlet', 'ktstandard.adminversion.dashlet', 'KTAdminVersionPlugin.php');
            $this->registerPage('versions', 'AdminVersionPage');
        }
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
            $now = time();
            $diff = $now - $iLastCheck;
            if ($diff > (60 * 5)) {
                return;
            }
        }
        $now = time();
        $diff = $now - $iLastCheck;
        if ($diff > (60 * 60 * 6)) {
            return;
        }
        return $sLastValue;
    }

    function do_main() {
        session_write_close();
        $sCache = $this->_checkCache();
        if (!is_null($sCache)) {
            return $sCache;
        }

        $sUrl = KT_VERSION_URL;
        $aVersions = KTUtil::getKTVersions();
        foreach ($aVersions as $k => $v) {
            $sUrl .=  '?' . sprintf("%s=%s", $k, $v);
        }
        $sIdentifier = KTUtil::getSystemIdentifier();
        $sUrl .= '&' . sprintf("system_identifier=%s", $sIdentifier);

        if (!function_exists('curl_init')) {
            if (OS_WINDOWS) {
                return "";
            }
            $stuff = @file_get_contents($sUrl);
            if ($stuff === false) {
                $stuff = "";
            }
        } else {
            $ch = @curl_init($sUrl);
            if (!$ch) {
                return "";
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $stuff = curl_exec($ch);
            curl_close($ch);
            if (!$stuff) {
                $stuff = "";
            }
        }
        KTUtil::setSystemSetting('ktadminversion_lastcheck', time());
        KTUtil::setSystemSetting('ktadminversion_lastvalue', (string)$stuff);
        return $stuff;
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('AdminVersionPlugin', 'ktstandard.adminversion.plugin', __FILE__);
?>
