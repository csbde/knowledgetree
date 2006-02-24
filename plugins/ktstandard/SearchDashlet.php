<?php


class SearchDashlet extends KTBaseDashlet {
    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktstandard/searchdashlet/dashlet');

        $aSearches = KTSavedSearch::getSearches();
        // empty on error.
        if (PEAR::isError($aSearches)) {
            $aSearches = array();
        }

        
        $aTemplateData = array(
            'savedsearches' => $aSearches,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>