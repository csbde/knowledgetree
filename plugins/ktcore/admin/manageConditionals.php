<?php
require_once("../../../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");



class ManageConditionalDispatcher extends KTAdminDispatcher {
    function ManageConditionalDispatcher() {
        parent::KTAdminDispatcher();
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Document Field Management'));


    }

    function do_main() {

        $aFieldsets = KTFieldset::getList("is_conditional = 1");
        $oTemplating = new KTTemplating;

        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/select_fieldset");
        $aTemplateData = array(
            "context" => &$this,
            "available_fieldsets" => $aFieldsets,
        );
        return $oTemplate->render($aTemplateData);
    }

    // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editFieldset() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editsimple");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         *  FIXME we fake it here with nested arrays...
         */
        $oFieldset =& KTFieldset::get($fieldset_id);
        $aFields =& $oFieldset->getFields();

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=edit&fFieldsetId=' . $oFieldset->getId(),
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=manageConditional&fFieldsetId=' . $oFieldset->getId(),
            'name' => _('Manage conditional fieldset'),
        );
        $this->aBreadcrumbs[] = array(
            'name' => _('Manage simple conditional'),
        );

        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "aFields" => $aFields,
            "iMasterFieldId" => $aFields[0]->getId(),
        );
        return $oTemplate->render($aTemplateData);
    }
    
        // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editComplexFieldset() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editcomplex");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         *  FIXME we fake it here with nested arrays...
         */
        $oFieldset =& KTFieldset::get($fieldset_id);
        $aFields =& $oFieldset->getFields();
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=edit&fFieldsetId=' . $oFieldset->getId(),
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=manageConditional&fFieldsetId=' . $oFieldset->getId(),
            'name' => _('Manage conditional fieldset'),
        );
        $this->aBreadcrumbs[] = array(
            'name' => _('Manage complex conditional'),
        );
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "aFields" => $aFields,
            "iMasterFieldId" => $aFields[0]->getId(),
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oDispatcher = new ManageConditionalDispatcher();
$oDispatcher->dispatch();

?>
