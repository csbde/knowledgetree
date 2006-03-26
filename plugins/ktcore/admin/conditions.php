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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTConditionDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

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

    function do_new() {
        $this->oPage->setBreadcrumbDetails(_kt('Create a new condition'));
        $this->oPage->setTitle(_kt('Create a new condition'));
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
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
        
        $aCriteria = Criteria::getAllCriteria();
        
        // we need to help out here, since it gets unpleasant inside the template.
        foreach ($aSearch['subgroup'] as $isg => $as) {
            $aSubgroup =& $aSearch['subgroup'][$isg];

            if(count($aSubgroup['values'])) {
                foreach ($aSubgroup['values'] as $iv => $t) {
                    $datavars =& $aSubgroup['values'][$iv];
                    $datavars['typename'] = $aCriteria[$datavars['type']]->sDisplay;
                    $datavars['widgetval'] = $aCriteria[$datavars['type']]->searchWidget(null, $datavars['data']);
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
