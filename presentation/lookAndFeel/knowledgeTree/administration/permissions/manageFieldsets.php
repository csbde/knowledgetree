<?php
require_once("../../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentFieldSet.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class ManageFieldsetsDispatcher extends KTAdminDispatcher {
    function do_main() {
        $oTemplating = new KTTemplating;
        $aFieldSets =& DocumentFieldSet::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_fieldsets");
        $aTemplateData = array(
             "fieldsets" => $aFieldSets,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    /** assumption: this particular path is NOT followed by plugins.  This sets */
    function do_newFieldset() {
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $namespace = KTUtil::arrayGet($_REQUEST, 'namespace');
        if (empty($name) || empty($namespace)) {
            return $this->errorRedirectToMain("Both a human name and a namespace are required and not given");
        }
        $oFieldSet = DocumentFieldSet::createFromArray(array(
            'name' => $name,
            'namespace' => $namespace,
            'mandatory' => 0,   /* user-created fieldsets can be deleted by users. */
        ));
        global $default;
        $default->log->debug('Trying to create a new fieldset.' . $oFieldSet->bMandatory);
        $oFieldSet = $oFieldSet->create();
        if (PEAR::isError($oFieldSet)) {
            return $this->errorRedirectToMain("Error creating fieldset");
        }
        /** FIXME: why is this errorRedirectToMain */
        return $this->errorRedirectToMain("Fieldset created");
    }
    
    
    function do_deleteFieldset() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        if (empty($id)) {
            return $this->errorRedirectToMain("No id specified.");
        }
        $oFieldSet= DocumentFieldSet::get($id);
        if (PEAR::isError($oFieldSet)) {
            return $this->errorRedirectToMain("Error finding fieldset");
        }
        if ($oFieldSet->getMandatory() === true) {
            return $this->errorRedirectToMain("Can't delete a mandatory permission");
        }
        $res = $oFieldSet->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain("Error deleting fieldset");
        }
        return $this->errorRedirectToMain("FieldSet deleted");
    }
    
    function do_editFieldset() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $oTemplating = new KTTemplating;
        $oFieldSet =& DocumentFieldSet::get($id);
        if (PEAR::isError($oFieldSet)) {
            return $this->errorRedirectToMain("No such fieldset.");
        }
        $childFields =& DocumentField::getList('parent_fieldset = ' . $id);
        $freeFields =& DocumentField::getList('parent_fieldset IS NULL');
        $oTemplate = $oTemplating->loadTemplate("ktcore/edit_fieldset");
        $aTemplateData = array(
             'setId' => $id,
             'children' => $childFields,
             'freefields' => $freeFields,
             'fieldSet' => $oFieldSet,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_addToFieldset() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $fieldsToAdd = KTUtil::arrayGet($_REQUEST, 'fieldsToAdd');
        if (empty($id)) {
            return $this->errorRedirectToMain("No id specified.");
        }
        if (empty($fieldsToAdd)) {
            return $this->errorRedirectToMain("No fields specified.");
        }
        $oFieldSet= DocumentFieldSet::get($id);
        if (PEAR::isError($oFieldSet)) {
            return $this->errorRedirectToMain("Error finding fieldset");
        }
        // DEBUG LOGGING
        //global $default;
        // we now have a working fieldset, and need to go through
        // each added item, and set this to be its parent_fieldset.
        if (is_array($fieldsToAdd))  // multiple passed in
        {
            foreach ($fieldsToAdd as $fieldToAdd)
            {
                $addField =& DocumentField::get($fieldToAdd);
                $addField->setParentFieldset($id);
                $addField->update();
                //$default->log->debug('MASSADD TO FIELDSET: ' . $addField->getParentFieldset());    
            }
        }
        else
        {
            $addField =& DocumentField::get($fieldsToAdd);
            $addField->setParentFieldset($id);
            $addField->update();
            //$default->log->debug('ADD TO FIELDSET: ' . $addField->getParentFieldset());
        }

        return $this->errorRedirectToMain("Fields added.");
    }

    function do_removeFromFieldset() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        $fieldsToRemove = KTUtil::arrayGet($_REQUEST, 'fieldsToRemove');
        global $default;
        $default->log->debug('PREREMOVE: ' . $fieldsToRemove);    
        if (empty($id)) {
            return $this->errorRedirectToMain("No id specified.");
        }
        if (empty($fieldsToRemove)) {
            return $this->errorRedirectToMain("No fields specified.");
        }
        $oFieldSet= DocumentFieldSet::get($id);
        if (PEAR::isError($oFieldSet)) {
            return $this->errorRedirectToMain("Error finding fieldset");
        }
        // DEBUG LOGGING
        global $default;
        // we now have a working fieldset, and need to go through
        // each added item, and set this to be its parent_fieldset.
        if (is_array($fieldsToRemove))  // multiple passed in
        {
            foreach ($fieldsToRemove as $fieldToRemove)
            {
                $addField =& DocumentField::get($fieldToRemove);
                $addField->setParentFieldset(null);
                $addField->update();
                $default->log->debug('MASSREMOVE FROM FIELDSET: ' . $addField->getParentFieldset());    
            }
        }
        else
        {
            $addField =& DocumentField::get($fieldsToRemove);
            $addField->setParentFieldset(null);
            $addField->update();
            $default->log->debug('REMOVE FROM FIELDSET: ' . $addField->getParentFieldset());    
        }

        return $this->errorRedirectToMain("Fields removed.");
    }

    function do_addConditions() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, 'fieldset_id');
    }

}

$oDispatcher = new ManageFieldsetsDispatcher();
$oDispatcher->dispatch();

?>
