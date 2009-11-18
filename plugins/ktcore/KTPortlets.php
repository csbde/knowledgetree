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

require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

class KTSearchPortlet extends KTPortlet {

    function KTSearchPortlet() {
        parent::KTPortlet(_kt("Search"));
    }
    function render() {
        require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/search_portlet");

        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (!$iFolderId && !$iDocumentId) {
            return null;
        }

	$iUserId = $_SESSION['userID'];
	$aSearches = KTSavedSearch::getUserSearches($iUserId);

        // empty on error.
        if (PEAR::isError($aSearches)) {
            $aSearches = array();
        }

	$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $aTemplateData = array(
            "context" => $this,
            "saved_searches" => $aSearches,
	    "folder_id" => $iFolderId,
	    "document_id" => $iDocumentId,
        );

        return $oTemplate->render($aTemplateData);
    }
}



class KTBrowseModePortlet extends KTPortlet {

    function KTBrowseModePortlet($sTitle = null) {
        // match empty, false.
        if ($sTitle == null) {
            $sTitle = _kt('Browse by...');
        }
        parent::KTPortlet($sTitle);
    }

    function render() {
        // this is unfortunate, but such is life.
        $current_action = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', null);
        $modes = array(
            'folder' => array('name' => _kt('Folder'), 'target' => "main"),
            'document_type' => array('name' => _kt('Document Type'), 'target' => 'selectType'),
            'lookup_value' => array('name' => _kt('Lookup Value'), 'target' => 'selectField'),
        );

        // Browse by tag
        $oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.tagcloud.plugin');
		if(!PEAR::isError($oPlugin) && !empty($oPlugin)){
    		$tagUrl = $oPlugin->getPagePath('TagCloudRedirection');
    		$modes['tag'] = array('name' => '<a href="'.$tagUrl.'">'._kt('Tag').'</a>');
		}

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/browsemodes_portlet");
        $aTemplateData = array(
            "context" => $this,
            "current_action" => $current_action,
            "modes" => $modes,
        );

        return $oTemplate->render($aTemplateData);
    }
}


class KTAdminModePortlet extends KTPortlet {

    function KTAdminModePortlet() {
        parent::KTPortlet(_kt("Administrator mode"));
    }
    function render() {
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (!$iFolderId && !$iDocumentId) {
            return null;
        }
        if ($iDocumentId) {
            $oDocument = Document::get($iDocumentId);
            if (PEAR::isError($oDocument) || ($oDocument === false)) {
                return null;
            }
            $iFolderId = $oDocument->getFolderId();
        }
        require_once(KT_LIB_DIR . '/security/Permission.inc');
        $oUser =& User::get($_SESSION['userID']);
        if (!Permission::userIsSystemAdministrator($oUser) && !Permission::isUnitAdministratorForFolder($oUser, $iFolderId)) {
            return null;
        }
        require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/admin_mode_portlet");

        $toggleMode = 'action=disableAdminMode';
        if (KTUtil::arrayGet($_SESSION, 'adminmode', false) == false) {
            $toggleMode = 'action=enableAdminMode';
        }
        $QS = sprintf('fDocumentId=%s&fFolderId=%s&%s',$iDocumentId, $iFolderId, $toggleMode);

        $toggleUrl = KTUtil::addQueryString(KTBrowseUtil::getBrowseBaseUrl(), $QS);

        $aTemplateData = array(
            "context" => $this,
            'toggleurl' => $toggleUrl,
            'enabled' => KTUtil::arrayGet($_SESSION, 'adminmode', false),
        );
        return $oTemplate->render($aTemplateData);
    }
}



class KTAdminSectionNavigation extends KTPortlet {
    var $bActive = true;
    function KTAdminSectionNavigation() {
        parent::KTPortlet(_kt("Administration"));
    }

    function render() {
        require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");

        $oRegistry =& KTAdminNavigationRegistry::getSingleton();
        $categories = $oRegistry->getCategories();

        // we need to investigate sub_url solutions.

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/admin_categories");
        $aTemplateData = array(
              "context" => $this,
              "categories" => $categories,
        );
        return $oTemplate->render($aTemplateData);
    }
}
