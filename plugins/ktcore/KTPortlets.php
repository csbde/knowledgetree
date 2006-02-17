<?php

require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

class KTSearchPortlet extends KTPortlet {

    function KTSearchPortlet() {
        parent::KTPortlet(_("Search"));
    }
    function render() {
        require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

        $oTemplating = new KTTemplating;
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
            $sTitle = _('Browse Documents By');
        }
        parent::KTPortlet($sTitle);
    }

    function render() {    
        // this is unfortunate, but such is life.
        $current_action = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', null);
        $modes = array(
            'folder' => array('name' => _('Folder'), 'target' => "main"),            
            'document_type' => array('name' => _('Document Type'), 'target' => 'selectType'),
            'lookup_value' => array('name' => _('Lookup Value'), 'target' => 'selectField'),
        );        
        
        $oTemplating = new KTTemplating;        
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
        parent::KTPortlet(_("Administrator mode"));
    }
    function render() {
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId');
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
        if (!Permission::userIsSystemAdministrator() && !Permission::isUnitAdministratorForFolder($oUser, $iFolderId)) {
            return null;
        }
        require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

        $oTemplating = new KTTemplating;
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
