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


class SearchDashlet extends KTBaseDashlet {
    function SearchDashlet() {
        $this->sTitle = _kt('Search Dashlet');
    }

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