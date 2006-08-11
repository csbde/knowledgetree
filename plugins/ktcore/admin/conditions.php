<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTConditionDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
    var $sHelpPage = 'ktcore/admin/dynamic conditions.html';
    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Conditions Management'));
        return true;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/conditions');
        $oTemplate->setData(array(
            'conditions' => KTSavedSearch::getConditions(),
        ));
        return $oTemplate->render();
    }

    function do_delete() {
        $this->oPage->setBreadcrumbDetails(_kt('Confirm deletion'));
        $this->oPage->setTitle(_kt('Confirm deletion'));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/condition_delete_confirmation');

        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);

        $oTemplate->setData(array(
            'condition_id' => $oSearch->getId(),
        ));
        return $oTemplate->render();
    }

    function do_delete_confirmed() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);
        KTPermissionDynamicCondition::deleteByCondition($oSearch);
        $res = $oSearch->delete();
        $this->oValidator->notError($res, array(
            'redirect_to' => 'main',
            'message' => _kt('Search not deleted'),
        ));
        $this->successRedirectToMain(_kt('Dynamic condition deleted'));
    }

    function do_new() {
        $this->oPage->setBreadcrumbDetails(_kt('Create a new condition'));
        $this->oPage->setTitle(_kt('Create a new condition'));
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
	$aCriteria =& $oCriteriaRegistry->getCriteria();
        
        $aTemplateData = array(
            "title" => _kt("Create a new condition"),
            "sNameTitle" => _kt("Name of condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _kt("Save"),
            "context" => &$this,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_view() {

    }
    
    function do_edit() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain('No such dynamic condition');
        }
        
        $aSearch = $oSearch->getSearch();
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search_edit");
        
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
	$aCriteria =& $oCriteriaRegistry->getCriteria();
        
        // we need to help out here, since it gets unpleasant inside the template.
        foreach ($aSearch['subgroup'] as $isg => $as) {
            $aSubgroup =& $aSearch['subgroup'][$isg];

            if(count($aSubgroup['values'])) {
                foreach ($aSubgroup['values'] as $iv => $t) {
                    $datavars =& $aSubgroup['values'][$iv];
		    $oCriterion = $oCriteriaRegistry->getCriterion($datavars['type']);
                    $datavars['typename'] = $oCriterion->sDisplay;
                    $datavars['widgetval'] = $oCriterion->searchWidget(null, $datavars['data']);
                }
            }
        }
        
        
        $aTemplateData = array(
            "title" => _kt("Edit an existing condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _kt("Update Dynamic Condition"),
            'aSearch' => $aSearch,
            'context' => $this,
            'iSearchId' => $oSearch->getId(),
            'old_name' => $oSearch->getName(),
            'sNameTitle' => _kt('Edit Dynamic Condition'),
        );
        return $oTemplate->render($aTemplateData);        
    }
    

    // XXX: Rename to do_save
    function do_updateSearch() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain('No such dynamic condition');
        }
        
        
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
        }

        //$sName = "Neil's saved search";
        if (!empty($sName)) {
            $oSearch->setName($sName);
        }
        
        $oSearch->setSearch($datavars);
        $res = $oSearch->update();
        
        $this->oValidator->notError($res, array(
            'redirect_to' => 'main',
            'message' => _kt('Search not saved'),
        ));
        $this->successRedirectToMain(_kt('Dynamic condition saved'));
    }    

    // XXX: Rename to do_save
    function do_performSearch() {
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
        }

        $sName = $this->oValidator->validateEntityName(
            'KTSavedSearch', 
            KTUtil::arrayGet($_REQUEST, 'name'), 
            array('extra_condition' => 'is_condition', 'redirect_to' => array('new'))
        );
        
        $sNamespace = KTUtil::nameToLocalNamespace('Saved searches', $sName);

        $oSearch = KTSavedSearch::createFromArray(array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'iscondition' => true,
            'iscomplete' => true,
            'userid' => null,
            'search' => $datavars,
        ));

        $this->oValidator->notError($oSearch, array(
            'redirect_to' => 'main',
            'message' => _kt('Search not saved'),
        ));
        $this->successRedirectToMain(_kt('Dynamic condition saved'));
    }
}

?>
