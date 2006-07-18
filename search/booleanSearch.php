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

// boilerplate includes
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");
require_once(KT_LIB_DIR . "/search/searchutil.inc.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

class BooleanSearchDispatcher extends KTStandardDispatcher {
    var $sSection = "browse";

    function BooleanSearchDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        return parent::KTStandardDispatcher();
    }

   function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt("Advanced Search"));
        $this->oPage->setBreadcrumbDetails(_kt('defining search'));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "context" => &$this,
            "aCriteria" => $aCriteria,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_performSearch() {
        $title = null;
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');

        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        $boolean_search_id = KTUtil::arrayGet($_REQUEST, 'boolean_search_id');
        if ($boolean_search_id) {
            $datavars = $_SESSION['boolean_search'][$boolean_search_id];
        }
        $iSavedSearchId = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        if (!empty($iSavedSearchId)) {
            $oSearch = KTSavedSearch::get($iSavedSearchId);
            $datavars = $oSearch->getSearch();
            $title = $oSearch->getName();
        }

        if (is_null(KTUtil::arrayGet($datavars["subgroup"][0], "values"))) {
            $this->errorRedirectToMain(_kt("No search parameters given"));
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
        }
        
        $res = $this->handleCriteriaSet($datavars, KTUtil::arrayGet($_REQUEST, 'fStartIndex', 1), $title);
        
        return $res;
    }
    
    function do_saveSearch() {
        $this->startTransaction();
        
        $iSearchId = KTUtil::arrayGet($_REQUEST, 'fSearchId', false);
        $sName = KTUtil::arrayGet($_REQUEST, 'name', false);
            $sSearch = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        
        if($iSearchId === false && $sName === false) {
            $this->errorRedirectTo('performSearch', _kt('Please either enter a name, or select a search to save over'), sprintf('boolean_search_id=%s', $sSearch));
            exit(0);
        }
        
        $datavars = $_SESSION['boolean_search'][$sSearch];
            if (!is_array($datavars)) {
                $datavars = unserialize($datavars);
            }
           
            if (empty($datavars)) {
                $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
            }
        
        if($iSearchId) {
            $oSearch = KTSavedSearch::get($iSearchId);
            if(PEAR::isError($oSearch) || $oSearch == false) {
        	$this->errorRedirectToMain(_kt('No such search'));
        	exit(0);
            }
            $oSearch->setSearch($datavars);
            $oSearch = $oSearch->update();
        
        } else {
            $sName = $this->oValidator->validateEntityName('KTSavedSearch', 
        						   KTUtil::arrayGet($_REQUEST, 'name'), 
        						   array('extra_condition' => 'not is_condition', 'redirect_to' => array('new')));
                
            $sNamespace = KTUtil::nameToLocalNamespace('Saved searches', $sName);
        
            $oSearch = KTSavedSearch::createFromArray(array('name' => $sName,
        						    'namespace' => $sNamespace,
        						    'iscondition' => false,
        						    'iscomplete' => true,
        						    'userid' => $this->oUser->getId(),
        						    'search' => $datavars,));
        }
        
            $this->oValidator->notError($oSearch, array(
                'redirect_to' => 'main',
                'message' => _kt('Search not saved'),
            ));
        
        $this->commitTransaction();
        $this->successRedirectTo('performSearch', _kt('Search saved'), sprintf('boolean_search_id=%s', $sSearch));
    }


    function do_deleteSearch() {
        $this->startTransaction();
        
        $iSearchId = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId', false);
        $oSearch = KTSavedSearch::get($iSearchId);
        if(PEAR::isError($oSearch) || $oSearch == false) {
            $this->errorRedirectToMain(_kt('No such search'));
            exit(0);
        }
        
        $res = $oSearch->delete();
            $this->oValidator->notError($res, array(
                'redirect_to' => 'main',
                'message' => _kt('Error deleting search'),
            ));
        
        $this->commitTransaction();
        
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', false);
        $iDocumentId = KTUtil::arrayGet($_REQUEST, 'fFolderId', false);
        
        if($iFolderId) {
            controllerRedirect('browse', 'fFolderId=' . $iFolderId);
        } else {
            controllerRedirect('viewDocument', 'fDocumentId=' . $iDocumentId);
        }
    }
	
    function do_editSearch() {
        $sSearch = KTUtil::arrayGet($_REQUEST, 'boolean_search');
	    $aSearch = $_SESSION['boolean_search'][$sSearch];
        if (!is_array($aSearch)) {
            $aSearch = unserialize($aSearch);
        }
       
        if (empty($aSearch)) {
            $this->errorRedirectToMain(_kt('You need to have at least 1 condition.'));
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search_change");
        
        $aCriteria = Criteria::getAllCriteria();
        
        // we need to help out here, since it gets unpleasant inside the template.
        
        foreach ($aSearch['subgroup'] as $isg => $as) {
            $aSubgroup =& $aSearch['subgroup'][$isg];
            if (is_array($aSubgroup['values'])) {
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
            "searchButton" => _kt("Search"),
            'aSearch' => $aSearch,
            'context' => $this,
	    //            'iSearchId' => $oSearch->getId(),
	    //            'old_name' => $oSearch->getName(),
            'sNameTitle' => _kt('Edit Search'),
        );
        return $oTemplate->render($aTemplateData);        
    }


    function handleCriteriaSet($aCriteriaSet, $iStartIndex, $sTitle=null) {
        
        if ($sTitle == null) {
            $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Advanced Search'));
            $sTitle =  _kt('Search Results');
        } else {
           $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Saved Search'));
            $this->oPage->setTitle(_kt('Saved Search: ') . $sTitle);
        }
        $this->oPage->setBreadcrumbDetails($sTitle);
        

        $this->browseType = "Folder";
        $searchable_text = KTUtil::arrayGet($_REQUEST, "fSearchableText");
        $sSearch = md5(serialize($aCriteriaSet));
        $_SESSION['boolean_search'][$sSearch] = $aCriteriaSet;


        $collection = new AdvancedCollection;       
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = $oColumnRegistry->getColumnsForView('ktcore.views.search');
        $collection->addColumns($aColumns);	
        
        // set a view option
        $aTitleOptions = array(
            'documenturl' => $GLOBALS['KTRootUrl'] . '/view.php',
        );
        $collection->setColumnOptions('ktcore.columns.title', $aTitleOptions);
        
        $aOptions = $collection->getEnvironOptions(); // extract data from the environment
        
        $aOptions['result_url'] = KTUtil::addQueryStringSelf("action=performSearch&boolean_search_id=" . urlencode($sSearch));
        $aOptions['empty_message'] = _kt("No documents or folders match this query.");
                
        $collection->setOptions($aOptions);
        $collection->setQueryObject(new BooleanSearchQuery($aCriteriaSet));    


        //$a = new BooleanSearchQuery($aCriteriaSet); 
        //var_dump($a->getDocumentCount()); exit(0);

        // form fields for saving the search
        $save_fields = array();
        $save_fields[] = new KTStringWidget(_kt('New search'), _kt('The name to save this search as'), 'name', null, $this->oPage, true);

        $aUserSearches = KTSavedSearch::getUserSearches($this->oUser->getId(), true);
        if(count($aUserSearches)) {
            $aVocab = array('' => ' ---- ');
            foreach($aUserSearches as $oSearch) {
            $aVocab[$oSearch->getId()] = $oSearch->getName();
            }
            
            $aSelectOptions = array('vocab' => $aVocab);
            $save_fields[] = new KTLookupWidget(_kt('Existing search'), _kt('To save over one of your existing searches, select it here.'), 'fSearchId', null, $this->oPage, true, null, null, $aSelectOptions);
        }
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
            "context" => $this,
            "collection" => $collection,
            "custom_title" => $sTitle,
            "save_fields" => $save_fields,
            "boolean_search" => $sSearch,
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oDispatcher = new BooleanSearchDispatcher();
$oDispatcher->dispatch();

?>
