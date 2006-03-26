<?php

/**
 * $Id$
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
        
        $aSearches = KTSavedSearch::getSearches();
        // empty on error.
        if (PEAR::isError($aSearches)) { 
            $aSearches = array(); 
        }
        
        $aTemplateData = array(
            "context" => $this,
            "saved_searches" => $aSearches,
        );

        return $oTemplate->render($aTemplateData);
    }
}



class KTBrowseModePortlet extends KTPortlet {

    function KTBrowseModePortlet($sTitle = null) {
        // match empty, false.
        if ($sTitle == null) {
            $sTitle = _kt('Browse Documents By');
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
