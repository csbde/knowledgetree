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
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
            'free_fields' => $oFieldset->getFields(),
            'parent_fields' => $oFieldset->getFields(),
            'aFieldOrders' => $aFieldOrders,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{
    function do_orderFields() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $aFreeFieldIds = $_REQUEST['fFreeFieldIds'];
        $iParentFieldId = $_REQUEST['fParentFieldId'];
        if (in_array($aParentFieldId, $aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', 'Field cannot be its own parent field', 'fFieldsetId=' . $oFieldset->getId());
        }
        foreach ($aFreeFieldIds as $iChildFieldId) {
            $res = KTMetadataUtil::addFieldOrder($iParentFieldId, $iChildFieldId, $oFieldset);
            $this->oValidator->notError($this, $res, array(
                'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
                'message' => 'Error adding Fields',
            ));
        }
        $this->successRedirectTo('manageConditional', 'Fields ordered', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}
}

$d =& new KTDocumentFieldDispatcher;
$d->dispatch();

?>
