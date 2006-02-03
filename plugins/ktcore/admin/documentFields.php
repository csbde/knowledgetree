<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/documentmanagement/MDTree.inc');


// FIXME shouldn't this inherit from AdminDispatcher?
class KTDocumentFieldDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Document Field Management'));
        return true;
    }

    // {{{ do_main
    function do_main () {
        $this->oPage->setBreadcrumbDetails(_("view fieldsets"));
        
        // function KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) {
        // use widgets for the create form.
        $createFields = array();
        $createFields[] = new KTStringWidget('Name', _('A human-readable name, used in add and edit forms.'), 'name', null, $this->oPage, true);
        $createFields[] = new KTTextWidget('Description', _('A brief description of the information stored in this fieldset.'), 'description', null, $this->oPage, true);
        $createFields[] = new KTCheckboxWidget('Generic', _('A generic fieldset is one that is available for every document by default.  These fieldsets will be available for users to edit and add for every document in the document management system.'), 'generic', false, $this->oPage, false);
        $createFields[] = new KTCheckboxWidget('System',
            _('A system fieldset is one that is never displayed to a user, and is used only by the document management system.'), 'generic', false, $this->oPage, false);
        
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/listFieldsets');
        $oTemplate->setData(array(
            'fieldsets' => KTFieldset::getList(),
            'creation_fields' => $createFields,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ do_edit
    function do_edit() {
        $this->oPage->setBreadcrumbDetails("edit");    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/editFieldset');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        
        $editFieldset = array();
        $editFieldset[] = new KTStringWidget('Name', _('A human-readable name, used in add and edit forms.'), 'name',$oFieldset->getName(), $this->oPage, true);
        $editFieldset[] = new KTStringWidget('Namespace',_('Every fieldset needs to have a system name (used internally by the document management system).  For fieldsets which you create, this is automatically created by the system, but for fieldsets created by plugins, this controls how the fieldset works.'), 'namespace', $oFieldset->getNamespace(), $this->oPage, true);
        $editFieldset[] = new KTTextWidget('Description', _('A brief description of the information stored in this fieldset.'), 'description', $oFieldset->getDescription(), $this->oPage, true);                
        $createFields = array();
        $createFields[] = new KTStringWidget('Name', _('A human-readable name, used in add and edit forms.'), 'name',null, $this->oPage, true);
        $createFields[] = new KTTextWidget('Description', _('A brief description of the information stored in this field.'), 'description', null, $this->oPage, true);                
 
        
        // type is a little more complex.
        $vocab = array();
        if (!$oFieldset->getIsConditional()) {
           $vocab["normal"] = 'Normal';
        }
        $vocab['lookup'] = 'Lookup';
        $vocab['tree'] = 'Tree';
        $typeOptions = array("vocab" => $vocab);
        $createFields[] =& new KTLookupWidget('Type',_('Fields may be of type "Normal", "Lookup", or "Tree". Normal fields are simple text entry fields. Lookups are drop-down controls populated with values by your chosen values. Tree fields provide a rich means of selecting values from tree-like information structures.'), 
        'type', null, $this->oPage, true, null,  null, $typeOptions);
        
        
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => 'Fieldset ' . $oFieldset->getName()
        );
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
            'edit_fieldset_fields' => $editFieldset,
            'create_field_fields' => $createFields,
        ));
        return $oTemplate;
    }
    // }}}

    // {{{ edit_object
    function do_editobject() {
        
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oFieldset->setName($_REQUEST['name']);
        $oFieldset->setNamespace($_REQUEST['namespace']);
        $oFieldset->setDescription($_REQUEST['description']);        
        $res = $oFieldset->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', _('Could not save fieldset changes'), 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Changes saved'), 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_new
    function do_new() {
		$aErrorOptions = array(
			'redirect_to' => array('main'),
		);
	
        $bIsGeneric = false;
        $bIsSystem = false;

        if (KTUtil::arrayGet($_REQUEST, 'generic')) {
            $bIsGeneric = true;
        }

        if (KTUtil::arrayGet($_REQUEST, 'system')) {
            $bIsSystem = true;
            // Can't be a system fieldset and a generic fieldset...
            $bIsGeneric = false;
        }
		
		// basic validation
        $sName = $this->oValidator->validateEntityName("KTFieldset", "fieldset", KTUtil::arrayGet($_REQUEST, 'name'), $aErrorOptions);
			
		$sDescription = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'description'), 
			KTUtil::meldOptions($aErrorOptions, array('message' => "You must provide a description")));
				
        $sNamespace = KTUtil::arrayGet($_REQUEST, 'namespace');
		
        if (empty($sNamespace)) {
            $sNamespace = KTUtil::nameToLocalNamespace('fieldsets', $sName);
        }
		
        $res = KTFieldset::createFromArray(array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'description' => $sDescription,
            'mandatory' => false,
            'isconditional' => false,
            'isgeneric' => $bIsGeneric,
            'issystem' => $bIsSystem,
        ));
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectToMain('Could not create fieldset');
            exit(0);
        }
        $this->successRedirectTo('edit', _('Fieldset created') . ': '.$sName, 'fFieldsetId=' . $res->getId());
        exit(0);
    }
    // }}}

    // {{{ do_newfield
    function do_newfield() {
        $aErrorOptions = array(
			'redirect_to' => array('edit','fFieldsetId=' . $_REQUEST['fFieldsetId']),
		);	
	
        $is_lookup = false;
        $is_tree = false;
        if ($_REQUEST['type'] === "lookup") {
            $is_lookup = true;
        }
        if ($_REQUEST['type'] === "tree") {
            $is_lookup = true;
            $is_tree = true;
        }
		
		$sName = $this->oValidator->validateEntityName("DocumentField", "field", KTUtil::arrayGet($_REQUEST, 'name'), $aErrorOptions);
		
		$sDescription = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'description'), 
			KTUtil::meldOptions($aErrorOptions, array('message' => "You must provide a description")));
		
		
        $oFieldset = KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::createFromArray(array(
            'name' => $_REQUEST['name'],
            'datatype' => 'STRING',
	        'description' => $sDescription,
            'haslookup' => $is_lookup,
            'haslookuptree' => $is_tree,
            'parentfieldset' => $oFieldset->getId(),
        ));
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', _('Could not create field') . ': '.$_REQUEST['name'], 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        if ($is_lookup) {
            $this->successRedirectTo('editField', _('Field created') . ': '.$_REQUEST['name'], 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
        } else {
            $this->successRedirectTo('edit', _('Field created') . ': ' . $_REQUEST['name'], 'fFieldsetId=' . $oFieldset->getId());
        }
        exit(0);
    }
    // }}}

    // {{{ do_editField
    function do_editField() {
        $this->oPage->setBreadcrumbDetails(_("edit field"));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/metadata/editField');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'name' => $oField->getName()
        );
        $this->oPage->setBreadcrumbDetails(_('edit field'));
        
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
        $oField->setDescription($_REQUEST['description']);
        $res = $oField->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('editField', _('Could not save field changes'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
            exit(0);
        }
        $this->successRedirectTo('editField', _('Changes saved'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' . $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ do_addLookups
    function do_addLookups() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        if (empty($_REQUEST['value'])) {
            $this->errorRedirectTo('editField', _('Empty lookup not added'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        $oMetaData =& MetaData::createFromArray(array(
            'name' => $_REQUEST['value'],
            'docfieldid' => $oField->getId(),
        ));
        $this->successRedirectTo('editField', _('Lookup added'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ do_metadataMultiAction
    function do_metadataMultiAction() {
        $subaction = array_keys(KTUtil::arrayGet($_REQUEST, 'submit', array()));
        $this->oValidator->notEmpty($subaction, array("message" => _("No action specified")));
        $subaction = $subaction[0];
        $method = null;
        if (method_exists($this, 'lookup_' . $subaction)) {
            $method = 'lookup_' . $subaction;
        }
        $this->oValidator->notEmpty($method, array("message" => _("Unknown action specified")));
        return $this->$method();
    }
    // }}}
    
    // {{{ lookup_remove
    function lookup_remove() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $aMetadata = KTUtil::arrayGet($_REQUEST, 'metadata');
        if (empty($aMetadata)) {
            $this->errorRedirectTo('editField', _('No lookups selected'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        foreach ($_REQUEST['metadata'] as $iMetaDataId) {
            $oMetaData =& MetaData::get($iMetaDataId);
            $oMetaData->delete();
        }
        $this->successRedirectTo('editField', _('Lookups removed'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ lookup_disable
    function lookup_disable() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $aMetadata = KTUtil::arrayGet($_REQUEST, 'metadata');
        if (empty($aMetadata)) {
            $this->errorRedirectTo('editField', _('No lookups selected'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        foreach ($_REQUEST['metadata'] as $iMetaDataId) {
            $oMetaData =& MetaData::get($iMetaDataId);
            $oMetaData->setDisabled(true);
            $oMetaData->update();
        }
        $this->successRedirectTo('editField', _('Lookups disabled'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ lookup_enable
    function lookup_enable() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $aMetadata = KTUtil::arrayGet($_REQUEST, 'metadata');
        if (empty($aMetadata)) {
            $this->errorRedirectTo('editField', _('No lookups selected'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        foreach ($_REQUEST['metadata'] as $iMetaDataId) {
            $oMetaData =& MetaData::get($iMetaDataId);
            $oMetaData->setDisabled(false);
            $oMetaData->update();
        }
        $this->successRedirectTo('editField', _('Lookups enabled'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        exit(0);
    }
    // }}}

    // {{{ lookup_togglestickiness
    function lookup_togglestickiness() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& DocumentField::get($_REQUEST['fFieldId']);
        $aMetadata = KTUtil::arrayGet($_REQUEST, 'metadata');
        if (empty($aMetadata)) {
            $this->errorRedirectTo('editField', _('No lookups selected'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
        }
        foreach ($_REQUEST['metadata'] as $iMetaDataId) {
            $oMetaData =& MetaData::get($iMetaDataId);
            $bStuck = (boolean)$oMetaData->getIsStuck();
            $oMetaData->setIsStuck(!$bStuck);
            $oMetaData->update();
        }
        $this->successRedirectTo('editField', _('Lookup stickiness toggled'), 'fFieldsetId=' . $oFieldset->getId() . '&fFieldId=' .  $oField->getId());
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
            $this->errorRedirectTo('edit', _('Could not become conditional'), 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Became conditional'), 'fFieldsetId=' . $oFieldset->getId());
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
            $this->errorRedirectTo('edit', _('Could not stop being conditional'), 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Became no longer conditional'), 'fFieldsetId=' . $oFieldset->getId());
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
        $this->successRedirectTo('edit', _('Fields removed'), 'fFieldsetId=' . $oFieldset->getId());
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
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=edit&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'query' => 'action=manageConditional&fFieldsetId=' . $_REQUEST['fFieldsetId'],
            'name' => _('Manage conditional field'),
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

    // {{{ do_orderFields
    function do_orderFields() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $aFreeFieldIds = $_REQUEST['fFreeFieldIds'];
        if (empty($aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', 'No children fields selected', 'fFieldsetId=' . $oFieldset->getId());
        }
        $iParentFieldId = $_REQUEST['fParentFieldId'];
        if (in_array($aParentFieldId, $aFreeFieldIds)) {
            $this->errorRedirectTo('manageConditional', _('Field cannot be its own parent field'), 'fFieldsetId=' . $oFieldset->getId());
        }
        foreach ($aFreeFieldIds as $iChildFieldId) {
            $res = KTMetadataUtil::addFieldOrder($iParentFieldId, $iChildFieldId, $oFieldset);
            $this->oValidator->notError($res, array(
                'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
                'message' => _('Error adding Fields'),
            ));
        }
        $this->successRedirectTo('manageConditional', _('Fields ordered'), 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }
    // }}}

    // {{{ do_setMasterField
    function do_setMasterField() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oField =& $this->oValidator->validateField($_REQUEST['fFieldId']);

        $res = KTMetadataUtil::removeFieldOrdering($oFieldset);
        $oFieldset->setMasterFieldId($oField->getId());
        $res = $oFieldset->update();

        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => _('Error setting master field'),
        ));
        $this->successRedirectTo('manageConditional', _('Master field set'), 'fFieldsetId=' . $oFieldset->getId());
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
            $this->successRedirectTo('manageConditional', _('Set to complete'), 'fFieldsetId=' . $oFieldset->getId());
        }
        $oFieldset->setIsComplete(false);
        $oFieldset->update();
        // Success, as we want to save the incompleteness to the
        // database...
        $this->successRedirectTo('manageConditional', _('Could not to complete'), 'fFieldsetId=' . $oFieldset->getId());
    }
    // }}}

    // {{{ do_changeToSimple
    function do_changeToSimple() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oFieldset->setIsComplex(false);
        $res = $oFieldset->update();
        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => _('Error changing to simple'),
        ));
        $this->successRedirectTo('manageConditional', _('Changed to simple'), 'fFieldsetId=' . $oFieldset->getId());
    }
    // }}}

    // {{{ do_changeToComplex
    function do_changeToComplex() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $oFieldset->setIsComplex(true);
        $res = $oFieldset->update();
        $this->oValidator->notError($res, array(
            'redirect_to' => array('manageConditional', 'fFieldsetId=' . $oFieldset->getId()),
            'message' => _('Error changing to complex'),
        ));
        $this->successRedirectTo('manageConditional', _('Changed to complex'), 'fFieldsetId=' . $oFieldset->getId());
    }
    // }}}

    // {{{ do_delete
    function do_delete() {
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fFieldsetId']);
        $res = $oFieldset->delete();
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('main', ''),
            'message' => _('Could not delete fieldset'),
        ));
        $this->successRedirectToMain(_('Fieldset deleted'));
    }
    // }}}


// {{{ TREE
    // create and display the tree editing form.
    function do_editTree() {
        global $default;
        // extract.
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');
        $current_node = KTUtil::arrayGet($_REQUEST, 'current_node', 0);
        $subaction = KTUtil::arrayGet($_REQUEST, 'subaction');

        // validate
        if (empty($field_id)) { return $this->errorRedirectToMain(_("Must select a field to edit.")); }
        $oField =& DocumentField::get($field_id);
        if (PEAR::isError($oField)) { return $this->errorRedirectToMain(_("Invalid field.")); }

        // under here we do the subaction rendering.
        // we do this so we don't have to do _very_ strange things with multiple actions.
        //$default->log->debug("Subaction: " . $subaction);
        $fieldTree =& new MDTree();
        $fieldTree->buildForField($oField->getId());

        if ($subaction !== null) {
            $target = 'editTree';
            $msg = _('Changes saved.');
            if ($subaction === "addCategory") {
                $new_category = KTUtil::arrayGet($_REQUEST, 'category_name');
                if (empty($new_category)) { return $this->errorRedirectTo("editTree", _("Must enter a name for the new category."), array("field_id" => $field_id)); }
                else { $this->subact_addCategory($field_id, $current_node, $new_category, $fieldTree);}
                $msg = _('Category added'). ': ' . $new_category;
            }
            if ($subaction === "deleteCategory") {
                $this->subact_deleteCategory($fieldTree, $current_node);
                $current_node = 0;      // clear out, and don't try and render the newly deleted category.
                $msg = _('Category removed.');
            }
            if ($subaction === "linkKeywords") {
                $keywords = KTUtil::arrayGet($_REQUEST, 'keywordsToAdd');
                $this->subact_linkKeywords($fieldTree, $current_node, $keywords);
                $current_node = 0;      // clear out, and don't try and render the newly deleted category.
                $msg = _('Keywords added to category.');
            }
            if ($subaction === "unlinkKeyword") {
                $keyword = KTUtil::arrayGet($_REQUEST, 'keyword_id');
                $this->subact_unlinkKeyword($fieldTree, $keyword);
                $msg = _('Keyword moved to base of tree.');
            }
            // now redirect
            $query = 'field_id=' . $field_id;
            return $this->successRedirectTo($target, $msg, $query);
        }

        if ($fieldTree->root === null) {
            return $this->errorRedirectToMain(_("Error building tree. Is this a valid tree-lookup field?"));
        }

        // FIXME extract this from MDTree (helper method?)
        $free_metadata = MetaData::getList('document_field_id = '.$oField->getId().' AND (treeorg_parent = 0 OR treeorg_parent IS NULL)');

        // render edit template.
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/edit_lookuptrees");
        $renderedTree = $this->_evilTreeRenderer($fieldTree);

        $this->oPage->setTitle(_('Edit Lookup Tree'));

        //$this->oPage->requireJSResource('thirdparty/js/MochiKit/Base.js');
		
		if ($current_node == 0) { $category_name = 'Root'; }
		else {
			$oNode = MDTreeNode::get($current_node);
			$category_name = $oNode->getName();
		}
		
        $aTemplateData = array(
            "field" => $oField,
            "tree" => $fieldTree,
            "renderedTree" => $renderedTree,
            "currentNode" => $current_node,
			'category_name' => $category_name,
            "freechildren" => $free_metadata,
            "context" => $this,
        );
        return $oTemplate->render($aTemplateData);
    }

    function subact_addCategory($field_id, $current_node, $new_category, &$constructedTree) {
        $newCategory = MDTreeNode::createFromArray(array (
             "iFieldId" => $field_id,
             "sName" => $new_category,
             "iParentNode" => $current_node,
        ));
        if (PEAR::isError($newCategory))
        {
            return false;
        }
        $constructedTree->addNode($newCategory);
        return true;
    }

    function subact_deleteCategory(&$constructedTree, $current_node) {
        $constructedTree->deleteNode($current_node);
        return true;
    }

    function subact_unlinkKeyword(&$constructedTree, $keyword) {
        $oKW = MetaData::get($keyword);
        $constructedTree->reparentKeyword($oKW->getId(), 0);
        return true;
    }


    function subact_linkKeywords(&$constructedTree, $current_node, $keywords) {
        foreach ($keywords as $md_id)
        {
            $constructedTree->reparentKeyword($md_id, $current_node);
        }
        return true;
    }

    /* ----------------------- EVIL HACK --------------------------
     *
     *  This whole thing needs to replaced, as soon as I work out how
     *  to non-sucking Smarty recursion.
     */

    function _evilTreeRecursion($subnode, $treeToRender)
    {
        $treeStr = "<ul>";
        foreach ($treeToRender->contents[$subnode] as $subnode_id => $subnode_val)
        {
            if ($subnode_id !== "leaves") {
                $treeStr .= '<li class="treenode active"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subnode_val]->getName() . '</a>';
                $treeStr .= $this->_evilActionHelper($treeToRender->field_id, false, $subnode_val);
                $treeStr .= $this->_evilTreeRecursion($subnode_val, $treeToRender);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subnode_val as $leaf)
                {
                    $treeStr .= '<li class="leafnode">' . $treeToRender->lookups[$leaf]->getName();
                    $treeStr .= $this->_evilActionHelper($treeToRender->field_id, true, $leaf);
                    $treeStr .=  '</li>';            }
                }
        }
        $treeStr .= '</ul>';
        return $treeStr;

    }

    // I can't seem to do recursion in smarty, and recursive templates seems a bad solution.
    // Come up with a better way to do this (? NBM)
    function _evilTreeRenderer($treeToRender) {
        //global $default;
        $treeStr = "<!-- this is rendered with an unholy hack. sorry. -->";
        $stack = array();
        $exitstack = array();

        // since the root is virtual, we need to fake it here.
        // the inner section is generised.
        $treeStr .= '<ul class="kt_treenodes"><li class="treenode active"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">Root</a>';
        $treeStr .= ' (<a href="' . KTUtil::addQueryStringSelf('action=editTree&field_id='.$treeToRender->field_id.'&current_node=0') . '">edit</a>)';
        $treeStr .= '<ul>';
        //$default->log->debug("EVILRENDER: " . print_r($treeToRender, true));
        foreach ($treeToRender->getRoot() as $node_id => $subtree_nodes)
        {
            //$default->log->debug("EVILRENDER: ".$node_id." => ".$subtree_nodes." (".($node_id === "leaves").")");
            // leaves are handled differently.
            if ($node_id !== "leaves") {
                // $default->log->debug("EVILRENDER: " . print_r($subtree_nodes, true));
                $treeStr .= '<li class="treenode active"><a class="pathnode" onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subtree_nodes]->getName() . '</a>';
                $treeStr .= $this->_evilActionHelper($treeToRender->field_id, false, $subtree_nodes);
                $treeStr .= $this->_evilTreeRecursion($subtree_nodes, $treeToRender);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subtree_nodes as $leaf)
                {
                    $treeStr .= '<li class="leafnode">' . $treeToRender->lookups[$leaf]->getName();
                    $treeStr .= $this->_evilActionHelper($treeToRender->field_id, true, $leaf);
                    $treeStr .=  '</li>';
                }
            }
        }
        $treeStr .= '</ul></li>';
        $treeStr .= '</ul>';

        return $treeStr;
    }

    // don't hate me.
    function _evilActionHelper($iFieldId, $bIsKeyword, $current_node) {
        $actionStr = " (";
        if ($bIsKeyword === true) {
           $actionStr .= '<a href="' . KTUtil::addQueryStringSelf('action=editTree&field_id='.$iFieldId.'&keyword_id='.$current_node.'&subaction=unlinkKeyword') . '">unlink</a>';
        }
        else
        {
           $actionStr .= '<a href="' . KTUtil::addQueryStringSlef('action=editTree&field_id=' . $iFieldId . '&current_node=' . $current_node) .'">attach keywords</a> ';
           $actionStr .= '| <a href="' . KTUtil::addQueryStringSelf('action=editTree&field_id='.$iFieldId.'&current_node='.$current_node.'&subaction=deleteCategory') . '">delete</a>';
        }
        $actionStr .= ")";
        return $actionStr;
    }
// }}}
}

?>
