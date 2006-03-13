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
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTSavedSearchDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _('Saved Searches'),
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
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "title" => _("Create a new condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _("Save"),
            'context' => $this,
            "sNameTitle" => _('New Saved Search'),
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_delete() {
        $id = KTUtil::arrayGet($_REQUEST, 'fSavedSearchId');
        $oSearch = KTSavedSearch::get($id);
        
        if (PEAR::isError($oSearch) || ($oSearch == false)) {
            $this->errorRedirectToMain(_('No Such search'));
        }
        
        $res = $oSearch->delete();
        if (PEAR::isError($res) || ($res == false)) {
            return $this->errorRedirectToMain(_('Failed to delete search'));
        }
        
        $this->successRedirectToMain(_('Search Deleted'));
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
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search_edit");
        
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
        
        //$s = '<pre>';
        //$s .= print_r($aSearch, true);
        //$s .= '</pre>';
        //print $s;        
        
        $aTemplateData = array(
            "title" => _("Edit an existing condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _("Update Saved Search"),
            'aSearch' => $aSearch,
            'context' => $this,
            'iSearchId' => $oSearch->getId(),
            'old_name' => $oSearch->getName(),
            'sNameTitle' => _('Edit Search'),
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
            $this->errorRedirectToMain('No Such search');
        }
        
        
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_('You need to have at least 1 condition.'));
        }

        //$sName = "Neil's saved search";
        if (!empty($sName)) {
            $oSearch->setName($sName);
        }
        
        $oSearch->setSearch($datavars);
        $res = $oSearch->update();
        
        $this->oValidator->notError($res, array(
            'redirect_to' => 'main',
            'message' => _('Search not saved'),
        ));
        $this->successRedirectToMain(_('Search saved'));
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
            $this->errorRedirectToMain(_('You need to have at least 1 condition.'));
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
            'message' => _('Search not saved'),
        ));
        $this->successRedirectToMain(_('Search saved'));
    }
}

//$oDispatcher = new KTSavedSearchDispatcher();
//$oDispatcher->dispatch();

?>
