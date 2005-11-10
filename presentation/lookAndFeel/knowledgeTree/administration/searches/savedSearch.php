<?php

// boilerplate includes
require_once("../../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

// specific includes

$sectionName = "General";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

class KTSavedSearchDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/savedsearches');
        $oTemplate->setData(array(
            'saved_searches' => KTSavedSearch::getList(),
        ));
        return $oTemplate->render();
    }

    function do_new() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "title" => "Create a new condition",
            "aCriteria" => $aCriteria,
            "searchButton" => "Save",
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_view() {

    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }
    
    // XXX: Rename to do_save
    function do_performSearch() {
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain('You need to have at least 1 condition.');
        }

        $sName = "Neil's saved search";
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
            'message' => 'Search not saved',
        ));
        $this->successRedirectToMain('Search saved');
    }
}

$oDispatcher = new KTSavedSearchDispatcher();
$oDispatcher->dispatch();

?>
