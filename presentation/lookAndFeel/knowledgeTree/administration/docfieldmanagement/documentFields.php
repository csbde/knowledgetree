<?php

require_once('../../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

class KTDocumentFieldDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
        array('action' => 'docfield', 'name' => 'Document Field Management'),
    );

    // {{{ do_main
    function do_main () {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/listFieldsets');
        $oTemplate->setData(array(
            'fieldsets' => KTFieldset::getList(),
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_edit
    function do_edit() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/editFieldset');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $this->aBreadcrumbs[] = array(
            'action' => 'docfield',
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => 'Fieldset ' . $oFieldset->getName()
        );
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ edit_object
    function do_editobject() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oFieldset->setName($_REQUEST['name']);
        $oFieldset->setNamespace($_REQUEST['namespace']);
        $res = $oFieldset->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not save fieldset changes', 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', 'Changes saved', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_new
    function do_new() {
        if (KTUtil::arrayGet($_REQUEST, 'generic')) {
            $generic = true;
        } else {
            $generic = false;
        }
        $res = KTFieldset::createFromArray(array(
            'name' => $_REQUEST['name'],
            'namespace' => $_REQUEST['namespace'],
            'mandatory' => false,
            'isconditional' => false,
            'isgeneric' => $generic,
        ));
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectToMain('Could not create fieldset');
            exit(0);
        }
        $this->successRedirectTo('edit', 'Fieldset created', 'fFieldsetId=' . $res->getId());
        exit(0);
    }
    // }}}

    // {{{ do_newfield
    function do_newfield() {
        $is_lookup = false;
        $is_tree = false;
        if ($_REQUEST['type'] === "lookup") {
            $is_lookup = true;
        }
        if ($_REQUEST['type'] === "tree") {
            $is_lookup = true;
            $is_tree = true;
        }
        $oFieldset = KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::createFromArray(array(
            'name' => $_REQUEST['name'],
            'datatype' => 'STRING',
            'haslookup' => $is_lookup,
            'haslookuptree' => $is_tree,
            'parentfieldset' => $oFieldset->getId(),
        ));
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not create field', 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        if ($is_lookup) {
            $this->successRedirectTo('editField', 'Field created', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
        } else {
            $this->successRedirectTo('edit', 'Field created', 'fFieldsetId=' . $oFieldset->getId());
        }
        exit(0);
    }
    // }}}

    // {{{ do_editField
    function do_editField() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/editField');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $this->aBreadcrumbs[] = array(
            'action' => 'docfield',
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => 'Fieldset ' . $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'name' => 'Field ' . $oField->getName()
        );
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
            'oField' => $oField,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_editFieldObject
    function do_editFieldObject() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/editField');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);

        $oField->setName($_REQUEST['name']);
        $res = $oField->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('editField', 'Could not save field changes', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
            exit(0);
        }
        $this->successRedirectTo('editField', 'Changes saved', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ do_addLookups
    function do_addLookups() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        if (empty($_REQUEST['value'])) {
            $this->errorRedirectTo('editField', 'Empty lookup not added', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        $oMetaData =& MetaData::createFromArray(array(
            'name' => $_REQUEST['value'],
            'docfieldid' => $oField->getId(),
        ));
        $this->successRedirectTo('editField', 'Lookup added', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ do_removeLookups
    function do_removeLookups() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $aMetadata = KTUtil::arrayGet($_REQUEST, 'metadata');
        if (empty($aMetadata)) {
            $this->errorRedirectTo('editField', 'No lookups selected', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        foreach ($_REQUEST['metadata'] as $iMetaDataId) {
            $oMetaData =& MetaData::get($iMetaDataId);
            $oMetaData->delete();
        }
        $this->successRedirectTo('editField', 'Lookups removed', 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ do_becomeconditional
    function do_becomeconditional() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oFieldset->setIsConditional(true);
        $oFieldset->setIsComplete(false);
        $res = $oFieldset->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not become conditional', 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', 'Became conditional', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_removeconditional
    function do_removeconditional() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oFieldset->setIsConditional(false);
        $oFieldset->setIsComplete(true);
        $res = $oFieldset->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not stop being conditional', 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', 'Became no longer conditional', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_removeFields
    function do_removeFields() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        foreach ($_REQUEST['fields'] as $iFieldId) {
            $oField =& DocumentField::get($iFieldId);
            $oField->delete();
        }
        $this->successRedirectTo('edit', 'Fields removed', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_manageConditional
    function do_manageConditional () {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/conditional/manageConditional');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $iMasterFieldId = $oFieldset->getMasterFieldId();
        if (!empty($iMasterFieldId)) {
            $oMasterField =& DocumentField::get($iMasterFieldId);
            if (PEAR::isError($oMasterField)) {
                $oMasterField = null;
            }
        } else {
            $oMasterField = null;
        }
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);
        $aFields = $oFieldset->getFields();

        $aFreeFieldIds = array();
        foreach ($aFields as $oField) {
            $aFreeFieldIds[] = $oField->getId();
        }
        if ($oMasterField) {
            $aParentFieldIds = array($oMasterField->getId());
            foreach ($aFieldOrders as $aRow) {
                $aParentFieldIds[] = $aRow['child_field_id'];
            }
            $aParentFields = array();
            foreach (array_unique($aParentFieldIds) as $iId) {
                $aParentFields[] =& DocumentField::get($iId);
            }
            $aFreeFields = array();
            foreach ($aFreeFieldIds as $iId) {
                if (in_array($iId, $aParentFieldIds)) {
                    continue;
                }
                $aFreeFields[] =& DocumentField::get($iId);
            }
        }
        $res = KTMetadataUtil::checkConditionalFieldsetCompleteness($oFieldset);
        if (PEAR::isError($res)) {
            $sIncomplete = $res->getMessage();
        } else {
            $sIncomplete = null;
        }
        $this->aBreadcrumbs[] = array(
            'action' => 'docfield',
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => 'Fieldset ' . $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'action' => 'docfield',
            'query' => 'action=manageConditional&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => 'Manage conditional field',
        );
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
            'free_fields' => $aFreeFields,
            'parent_fields' => $aParentFields,
            'aFieldOrders' => $aFieldOrders,
            'oMasterField' => $oMasterField,
            'sIncomplete' => $sIncomplete,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{
    function do_orderFields() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $aFreeFieldIds = $_REQUEST['fFreeFieldIds'];
        if (empty($aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', 'No children fields selected', 'fFieldsetId=' . $oFieldset->getId());
        }
        $iParentFieldId = $_REQUEST['fParentFieldId'];
        if (in_array($aParentFieldId, $aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', 'Field cannot be its own parent field', 'fFieldsetId=' . $oFieldset->getId());
        }
        foreach ($aFreeFieldIds as $iChildFieldId) {
            $res = KTMetadataUtil::addFieldOrder($iParentFieldId, $iChildFieldId, $oFieldset);
            $this->oValidator->notError($res, array(
                'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
                'message' => 'Error adding Fields',
            ));
        }
        $this->successRedirectTo('manageConditional', 'Fields ordered', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{
    function do_setMasterField() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& $this->oValidator->validateField($_REQUEST['fFieldId']);

        $res = KTMetadataUtil::removeFieldOrdering($oFieldset);
        $oFieldset->setMasterFieldId($oField->getId());
        $res = $oFieldset->update();

        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => 'Error setting master field',
        ));
        $this->successRedirectTo('manageConditional', 'Master field set', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_checkComplete
    /**
     * Checks whether the fieldset is complete, and if it is, sets it to
     * be complete in the database.  Otherwise, set it to not be
     * complete in the database (just in case), and set the error
     * messages as to why it isn't.
     */
    function do_checkComplete() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $res = KTMetadataUtil::checkConditionalFieldsetCompleteness($oFieldset);
        if ($res === true) {
            $oFieldset->setIsComplete(true);
            $oFieldset->update();
            $this->successRedirectTo('manageConditional', 'Set to complete', 'fFieldsetId=' . $oFieldset->getId());
        }
        $oFieldset->setIsComplete(false);
        $oFieldset->update();
        // Success, as we want to save the incompleteness to the
        // database...
        $this->successRedirectTo('manageConditional', 'Could not to complete', 'fFieldsetId=' . $oFieldset->getId());
    }
    // }}}
}

$d =& new KTDocumentFieldDispatcher;
$d->dispatch();

?>
