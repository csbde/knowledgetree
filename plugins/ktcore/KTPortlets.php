<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
