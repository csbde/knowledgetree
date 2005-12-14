<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTConditionDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/search/administration/conditions');
        $oTemplate->setData(array(
            'conditions' => KTSavedSearch::getConditions(),
        ));
        return $oTemplate->render();
    }

    function do_new() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/boolean_search");
        
        $aCriteria = Criteria::getAllCriteria();
        
        $aTemplateData = array(
            "title" => _("Create a new condition"),
            "sNameTitle" => _("Name of condition"),
            "aCriteria" => $aCriteria,
            "searchButton" => _("Save"),
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_view() {

    }

    // XXX: Rename to do_save
    function do_performSearch() {
        $datavars = KTUtil::arrayGet($_REQUEST, 'boolean_search');
        if (!is_array($datavars)) {
            $datavars = unserialize($datavars);
        }
        
        if (empty($datavars)) {
            $this->errorRedirectToMain(_('You need to have at least 1 condition.'));
        }

        $sName = $_REQUEST['name'];
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
            'message' => _('Search not saved'),
        ));
        $this->successRedirectToMain(_('Search saved'));
    }
}

?>
