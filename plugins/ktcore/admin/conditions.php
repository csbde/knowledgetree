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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");
require_once(KT_LIB_DIR .'/permissions/permissiondynamiccondition.inc.php');

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

        // Get associated permission objects before deleting the condition
        $sWhere = 'condition_id = ?';
        $aParams = array($id);
        $aPermissionObjects = KTPermissionDynamicCondition::getPermissionObjectIdList($sWhere, $aParams);

        $oSearch = KTSavedSearch::get($id);
        KTPermissionDynamicCondition::deleteByCondition($oSearch);
        $res = $oSearch->delete();
        $this->oValidator->notError($res, array(
            'redirect_to' => 'main',
            'message' => _kt('Search not deleted'),
        ));

        // Update permission objects if they exist
        if(!PEAR::isError($aPermissionObjects) && !empty($aPermissionObjects)){
            // update permission objects
            foreach($aPermissionObjects as $iPermObjectId){
                $oPO = KTPermissionObject::get($iPermObjectId['permission_object_id']);
                KTPermissionUtil::updatePermissionLookupForPO($oPO);
            }
        }

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
                    if($oCriterion == null || $oCriterion == "" || PEAR::isError($oCriterion)) {
                        $this->errorRedirectToMain('Criterion error');
                    }
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

        // Update permission object if exists
        $sWhere = 'condition_id = ?';
        $aParams = array($id);
        $aPermissionObjects = KTPermissionDynamicCondition::getPermissionObjectIdList($sWhere, $aParams);

        if(!PEAR::isError($aPermissionObjects) && !empty($aPermissionObjects)){
            // update permission objects
            foreach($aPermissionObjects as $iPermObjectId){
                $oPO = KTPermissionObject::get($iPermObjectId['permission_object_id']);
                KTPermissionUtil::updatePermissionLookupForPO($oPO);
            }
        }

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
