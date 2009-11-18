<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');

class KTWebDAVDashletPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.ktwebdavdashlet.plugin";
    var $autoRegister = true;

    function KTWebDAVDashletPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('WebDAV Dashlet Plugin');
        return $res;
    }

    function setup() {
        $this->registerDashlet('KTWebDAVDashlet', 'ktstandard.ktwebdavdashlet.dashlet', __FILE__);

        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
    }
}

class KTWebDAVDashlet extends KTBaseDashlet {
    var $sClass = "ktInfo";

    function KTWebDAVDashlet( ) {
        $this->sTitle = _kt('WebDAV Connection Information');
    }

    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktstandard/ktwebdavdashlet/dashlet');

        $sURL = KTUtil::kt_url();

        // Check if this is a commercial installation - before displaying the KT Tools webdav link
        // Shortcut: Check if the the wintools plugin exists and set to true.
        // Long way: Check that a license is installed - this is only text so having a license is not a requirement.
        $isComm = false;
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $isComm = true;
        }
        $webdavUrl = $sURL.'/ktwebdav/ktwebdav.php';

        $aTemplateData = array(
            'url' => $sURL,
            'webdav_url' => $webdavUrl,
            'hasLicense' => $isComm
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTWebDAVDashletPlugin', 'ktstandard.ktwebdavdashlet.plugin', __FILE__);
?>
