<?php

require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

class KTSearchPortlet extends KTPortlet {

    function KTSearchPortlet() {
        parent::KTPortlet(_("Search"));
    }
    function render() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/search_portlet");
        
        $aSearches = KTSavedSearch::getList();
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
        $current_action = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', 'folder');
        $modes = array(
            'folder' => array('name' => _('Folder')),
            
            'document_type' => array('name' => _('Document Type'), 'target' => 'selectType'),
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


