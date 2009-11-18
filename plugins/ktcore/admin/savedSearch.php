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

// boilerplate includes
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTSavedSearchDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
    var $sHelpPage = 'ktcore/admin/saved searches.html';

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Saved Searches'),
        );
        return true;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/savedsearches');
        $oTemplate->setData(array(
            'saved_searches' => KTSavedSearch::getSearches(),
            'context' => $this,
        ));
        return $oTemplate->render();
    }

    function do_new() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
	$aCriteria =& $oCriteriaRegistry->getCriteria();
        
        $aTemplateData = array(
            "title" => _kt("Create a new saved search"),
            "aCriteria" => $aCriteria,
            "searchButton" => _kt("Save"),
            'context' => $this,
            "sNameTitle" => _kt('New Saved Search'),
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_delete() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain(_kt('No Such search'));
        }
        
        $res = $oSearch->delete();
        if (PEAR::isError($res) || ($res == false)) {
            return $this->errorRedirectToMain(_kt('Failed to delete search'));
        }
        
        $this->successRedirectToMain(_kt('Search Deleted'));
    }

    function do_view() {

    }
    
    function do_edit() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain('No Such search');
        }
        
        $aSearch = $oSearch->getSearch();
        /*
	print '<pre>';
	print_r($aSearch);
	exit(0);
        */
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search_edit");
        
        $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();
	$aCriteria =& $oCriteriaRegistry->getCriteria();
        
        // we need to help out here, since it gets unpleasant inside the template.
        
        foreach ($aSearch['subgroup'] as $isg => $as) {
            $aSubgroup =& $aSearch['subgroup'][$isg];
            if (is_array($aSubgroup['values'])) {
                foreach ($aSubgroup['values'] as $iv => $t) {
                    $datavars =& $aSubgroup['values'][$iv];
		    $oCriterion = $oCriteriaRegistry->getCriterion($datavars['type']);
                    $datavars['typename'] = $oCriterion->sDisplay;
                    $datavars['widgetval'] = $oCriterion->searchWidget(null, $datavars['data']);
                }
            }
        }
        
        //$s = '<pre>';
        //$s .= print_r($aSearch, true);
        //$s .= '</pre>';
        //print $s;        

        $aTemplateData = array(
            "title" => _kt("Edit an existing condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _kt("Update Saved Search"),
            'aSearch' => $aSearch,
            'context' => $this,
            'iSearchId' => $oSearch->getId(),
            'old_name' => $oSearch->getName(),
            'sNameTitle' => _kt('Edit Search'),
        );
        return $oTemplate->render($aTemplateData);        
        
        //return $s;
    }

    // XXX: Rename to do_save
    function do_updateSearch() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain('No such search');
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
        $this->successRedirectToMain(_kt('Search saved'));
    }

    // XXX: Rename to do_save
    function do_performSearch() {
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        
        $sName = $this->oValidator->validateEntityName(
            'KTSavedSearch', 
            KTUtil::arrayGet($_REQUEST, 'name'), 
            array('extra_condition' => 'not is_condition', 'redirect_to' => array('new'))
        );
            
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
        }

        $sNamespace = KTUtil::nameToLocalNamespace('Saved searches', $sName);

        $oSearch = KTSavedSearch::createFromArray(array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'iscondition' => false,
            'iscomplete' => true,
            'userid' => null,
            'search' => $datavars,
        ));

        $this->oValidator->notError($oSearch, array(
            'redirect_to' => 'main',
            'message' => _kt('Search not saved'),
        ));
        $this->successRedirectToMain(_kt('Search saved'));
    }

    // helper for the template
    function _getUserName($iUserId) {
	$oUser = User::get($iUserId);
	if(PEAR::isError($oUser)) {
	    return _kt('Error retrieving username');
	}
	return $oUser->getUserName();
    }

}

//$oDispatcher = new KTSavedSearchDispatcher();
//$oDispatcher->dispatch();

?>
