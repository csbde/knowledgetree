<?php

// boilerplate includes
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTSavedSearchDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->oPage->setTitle(_('Manage Saved Searches'));
        return true;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/savedsearches');
        $oTemplate->setData(array(
            'saved_searches' => KTSavedSearch::getList(),
            'context' => $this,
        ));
        return $oTemplate->render();
    }

    function do_new() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "title" => _("Create a new condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _("Save"),
            'context' => $this,
            "sNameTitle" => _('New Stored Search'),
        );
        return $oTemplate->render($aTemplateData);
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
        
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search_edit");
        
        $aCriteria = Criteria::getAllCriteria();
        
        // we need to help out here, since it gets unpleasant inside the template.
        foreach ($aSearch['subgroup'] as $isg => $as) {
            $aSubgroup =& $aSearch['subgroup'][$isg];
            foreach ($aSubgroup['values'] as $iv => $t) {
                $datavars =& $aSubgroup['values'][$iv];
                $datavars['typename'] = $aCriteria[$datavars['type']]->sDisplay;
                $datavars['widgetval'] = $aCriteria[$datavars['type']]->searchWidget(null, $datavars['data']);
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
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
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
