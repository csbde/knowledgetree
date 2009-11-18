<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

/*  KT3 Fieldset (DISPLAY ONLY)
 *
 *  Very simple wrapper that establishes the absolutely basic API.
 *  FIXME:  do we want to include anything from Fieldset.inc?
 *
 *  each object's render() function takes a $aDocumentData, which includes
 *    "document" => $oDocument
 *    "document_id" => $iDocumentId
 *
 */

// boilerplate
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

require_once(KT_LIB_DIR . '/documentmanagement/MDTree.inc'); // :(



// data acquisition
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/widgets/FieldsetDisplayRegistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');


/* it may be useful to move this to a factory, eventually? */
function getWidgetForMetadataField($field, $current_value, $page, $errors = null, $vocab = null, $aOptions = null) {
    // all fields have these elements.
    $fieldLabel = $field->getName();
    $fieldDescription = $field->getDescription();
    $fieldValue = $current_value;
    $fieldErrors = $errors; // array of strings
    $fieldName = 'metadata_' . $field->getID();
    $fieldOptions = array();
    $fieldRequired = $field->getIsMandatory() || KTUtil::arrayGet($aOptions, 'required', false);
    if ($fieldRequired == 1) {
        $fieldRequired = true;
    }
    if ($errors === null) {
        $fieldErrors = array();
    } else {
        $fieldErrors = $errors;
    }


    // now we need to break, based on a few aspects of the oField (DocumentField)
    if ($field->getHasLookup()) {
        // could either be normal, or a tree.
        // ignore trees (for now).
        if (!$field->getHasLookupTree()) {
           // FIXME we need to somehow handle both value-value and id-value here
           // extract the lookup.
            if ($vocab === null) { // allow override
                $lookups = MetaData::getEnabledByDocumentField($field);
                $fieldOptions['vocab'] = array(); // FIXME handle lookups
                $fieldOptions['vocab'][''] = _kt('Select a value');
                foreach ($lookups as $md) {
                    $fieldOptions['vocab'][$md->getName()] = $md->getName();
                }
            } else {
                $fieldOptions['vocab'] = $vocab;
            }

            $oField = new KTLookupWidget($fieldLabel, $fieldDescription, $fieldName, $fieldValue, $page, $fieldRequired, null, $fieldErrors, $fieldOptions);
        } else {
            // FIXME vocab's are _not_ supported for tree-inputs.  this means conditional-tree-widgets are not unsupported.

            // for trees, we are currently brutal.
            $fieldTree = new MDTree();
            $fieldTree->buildForField($field->getId());
            $fieldTree->setActiveItem($current_value);
            $fieldOptions['tree'] = $fieldTree->_evilTreeRenderer($fieldTree, $fieldName);

            $oField = new KTTreeWidget($fieldLabel, $fieldDescription, $fieldName, $fieldValue, $page, $fieldRequired, null, $fieldErrors, $fieldOptions);
        }
    } else if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin') && $field->getHasInetLookup()){
        require_once(KT_DIR."/plugins/multiselect/InetWidgets.php");
        if ($vocab === null) { // allow override
            $lookups = MetaData::getEnabledByDocumentField($field);
            $fieldOptions['vocab'] = array(); // FIXME handle lookups
            foreach ($lookups as $md) {
                $fieldOptions['vocab'][$md->getName()] = $md->getName();
            }
        } else {
            $fieldOptions['vocab'] = $vocab;
        }
        $fieldOptions['multi'] = true;
        $fieldOptions['lookup_type'] = $field->getInetLookupType();
        $oField = new InetMultiselectWidget($fieldLabel, $fieldDescription, $fieldName, $fieldValue, $page, $fieldRequired, null, $fieldErrors, $fieldOptions);
    } else {
        $oField = new KTBaseWidget($fieldLabel, $fieldDescription, $fieldName, $fieldValue, $page, $fieldRequired, null, $fieldErrors, $fieldOptions);
    }

    return $oField;
}

/**
 * Compare the fields within a fieldset for alphabetising.
 *
 * @param object $a
 * @param object $b
 * @return integer
 */
function compareFieldSetField($a, $b)
{
    if ($a['field']->getName() == $b['field']->getName()) return 0;
    if ($a['field']->getName() < $b['field']->getName()) return -1;
    return 1;
}

// FIXME need to establish some kind of api to pass in i18n information.
class KTFieldsetDisplay {
    var $fieldset;

    function KTFieldsetDisplay($oFieldset) {
        $this->fieldset = $oFieldset;
    }


    function _dateHelper($dDate) {
        $dColumnDate = strtotime($dDate);
        return date('Y-m-d H:i', $dColumnDate);
    }


    function _sizeHelper($size) {
        $finalSize = $size;
        $label = 'b';

        if ($finalSize > 1000) { $label='Kb'; $finalSize = floor($finalSize/1000); }
        if ($finalSize > 1000) { $label='Mb'; $finalSize = floor($finalSize/1000); }
        return $finalSize . $label;
    }

    function _mimeHelper($iMimeTypeId) {
        // FIXME lazy cache this.
        // FIXME extend mime_types to have something useful to say.
        $sQuery = 'SELECT * FROM mime_types WHERE id = ?';
        $res = DBUtil::getOneResult(array($sQuery, array($iMimeTypeId)));

        if (PEAR::isError($res)) {
            return _kt('unknown type');
        }

        if (!empty($res['friendly_name'])) {
            return _kt($res['friendly_name']);
        } else {
            return sprintf(_kt('%s File'), strtoupper($res['filetypes']));
        }

    }


    // this should multiplex i18n_title
    function getTitle() { return $this->sTitle; }

    function render($aDocumentData) {
        return '<p class="ktError">Warning:  Abstract Fieldset created.</p>';
    }

    function renderComparison($aDocumentData, $aComparisonData) {
        return '<div class="ktError">Not implemented:  comparison rendering</div>';
    }

    // we need the $main to (potentially) add js, etc.
    function renderEdit($document_data) {
        return '<div class="ktError">Not Implemented: fieldset editing.</div>';
    }

    function setError($aError) {
        $this->aError = $aError;
    }
}

// The generic object
class GenericFieldsetDisplay extends KTFieldsetDisplay {

    // DON'T take anything.
    function GenericFieldsetDisplay() {

    }

    function render($aDocumentData) {
        // we do a fair bit of fetching, etc. in here.
        $document = $aDocumentData['document'];

        // creation
        $creator =& User::get($document->getCreatorId());
        if (PEAR::isError($creator)) {
           $creator_name = '<span class="ktError">' . _kt("Unable to find the document's creator") . '</span>';
        } else {
           $creator_name = $creator->getName();
        }
        $owner =& User::get($document->getOwnerId());
        if (PEAR::isError($owner)) {
           $owner_name = '<span class="ktError">' . _kt("Unable to find the document's owner") . '</span>';
        } else {
           $owner_name = $owner->getName();
        }
        $modified_user =& User::get($document->getModifiedUserId());
        if (PEAR::isError($modified_user)) {
           $modified_user_name = '<span class="ktError">' . _kt("Unable to find the document's modifier") . '</span>';
        } else {
           $modified_user_name = $modified_user->getName();
        }
        $creation_date = $this->_dateHelper($document->getCreatedDateTime());

        // last mod
        $last_modified_date = $this->_dateHelper($document->getLastModifiedDate());

        // document type // FIXME move this to view.php
        $document_type = $aDocumentData['document_type']->getName();

        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($document);
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($document);


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/generic');
        $aTemplateData = array(
            'context' => $this,
            'document_data' => $aDocumentData,
            'document' => $aDocumentData['document'],

            'filename' => $document->getFileName(),

            'creator' => $creator_name,
            'owner' => $owner_name,
            'creation_date' => $creation_date,

            'last_modified_by' => $modified_user_name,
            'last_modified_date' => $last_modified_date,

            'document_type' => $document_type,

            'workflow_state' => $oState,
            'workflow' => $oWorkflow,
        );

        return $oTemplate->render($aTemplateData);
    }

    function renderComparison($aDocumentData, $aComparisonData) {
// we do a fair bit of fetching, etc. in here.
        $document = $aDocumentData['document'];
        $comparison_document = $aComparisonData['document'];

        // creation
        $creator =& User::get($document->getCreatorId());
        if (PEAR::isError($creator)) {
           $creator = '<span class="ktError">' . _kt("Unable to find the document's creator") . '</span>';
        } else {
           $creator = $creator->getName();
        }
        $creation_date = $this->_dateHelper($document->getCreatedDateTime());

        // last mod
        $last_modified_date = $this->_dateHelper($document->getVersionCreated());
        $comparison_last_modified_date = $this->_dateHelper($comparison_document->getVersionCreated());

        // document type // FIXME move this to view.php
        $document_type = $aDocumentData['document_type']->getName();
        $comparison_document_type = $aComparisonData['document_type']->getName();

        $modified_user =& User::get($document->getVersionCreatorId());
        if (PEAR::isError($modified_user)) {
           $modified_user = "<span class='ktError'>" . _kt("Unable to find the document's modifier") . '</span>';
        } else {
           $modified_user = $modified_user->getName();
        }

        $owner_user =& User::get($document->getOwnerId());
        if (PEAR::isError($owner_user)) {
           $owner_user = "<span class='ktError'>" . _kt("Unable to find the document's owner") . '</span>';
        } else {
           $owner_user = $owner_user->getName();
        }



        $comparison_modified_user =& User::get($comparison_document->getVersionCreatorId());
        if (PEAR::isError($comparison_modified_user)) {
           $comparison_modified_user = "<span class='ktError'>" . _kt("Unable to find the document's modifier") . '</span>';
        } else {
           $comparison_modified_user = $comparison_modified_user->getName();
        }

        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($document);
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($document);
        $oComparisonWorkflow = KTWorkflowUtil::getWorkflowForDocument($comparison_document);
        $oComparisonState = KTWorkflowUtil::getWorkflowStateForDocument($comparison_document);


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/generic_versioned');
        $aTemplateData = array(
            'context' => $this,
            'document_data' => $aDocumentData,
            'document' => $aDocumentData['document'],

        'title' => $document->getName(),
        'comparison_title' => $comparison_document->getName(),

        'filename' => $document->getFileName(),
        'comparison_filename' => $comparison_document->getFileName(),

            'creator' => $creator,
            'creation_date' => $creation_date,
            'owner' => $owner_user,

            'last_modified_by' => $modified_user,
            'last_modified_date' => $last_modified_date,

            'comparison_last_modified_by' => $comparison_modified_user,
            'comparison_last_modified_date' => $comparison_last_modified_date,

            'document_type' => $document_type,
            'comparison_document_type' => $comparison_document_type,

            'workflow_state' => $oState,
            'comparison_workflow_state' => $oComparisonState,
            'workflow' => $oWorkflow,
            'comparison_workflow' => $oComparisonWorkflow,

            'comparison_document' => $aComparisonData['document'],
        );

        return $oTemplate->render($aTemplateData);
    }

    function renderEdit($document_data) {
        global $main; // FIXME remove direct access to $main
        $oField = new KTBaseWidget(_kt('Document Title'),
            sprintf(_kt("The document title is used as the main name of a document throughout %s."), APP_NAME),
            'generic_title', $document_data['document']->getName(), $main, true, null, array());
        $aFields = array($oField); // its the only one editable from the basic set (currently).

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/simple_editable');
        $aTemplateData = array(
            'context' => $this,
            'fields' => $aFields,
            'title' => _kt('Generic Document Information'),
            'description' => sprintf(_kt("The information in this section is stored by %s for every document."), APP_NAME),
        );
        return $oTemplate->render($aTemplateData);
    }
}


// The generic object
class SimpleFieldsetDisplay extends KTFieldsetDisplay {

    function render($aDocumentData) {
        // we do a fair bit of fetching, etc. in here.
        $document = $aDocumentData['document'];

        // we need to extract the fields.
        $fields =& $this->fieldset->getFields();


        // we now grab that subset of items which fit in here.
        // FIXME link value -> lookup where appropriate.
        // FIXME probably need to be more careful about the _type_ of field here.
        $fieldset_values = array();
        foreach ($fields as $oField) {
            $val = KTUtil::arrayGet($aDocumentData['field_values'], $oField->getId(), null);
            $fieldset_values[] = array('field' => $oField, 'value' => $val, );
        }

        // Alphabetise the metadata fields within a fieldset if set in config
        $oKTConfig =& KTConfig::getSingleton();
        $use_sort = $oKTConfig->get('ui/metadata_sort', false);

        if($use_sort){
            usort($fieldset_values, 'compareFieldSetField');
        }


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/simple');
        $aTemplateData = array(
            'context' => $this,
            'document_data' => $aDocumentData,
            'document' => $aDocumentData['document'],
            'fieldset' => $this->fieldset,
            'fieldset_values' => $fieldset_values,
            'description' => $this->fieldset->getDescription(),
        );
        return $oTemplate->render($aTemplateData);
    }

    function renderComparison($aDocumentData, $aComparisonData) {
        // we do a fair bit of fetching, etc. in here.
        $document = $aDocumentData['document'];

        // we need to extract the fields.
        $fields =& $this->fieldset->getFields();


        // we now grab that subset of items which fit in here.
        // FIXME link value -> lookup where appropriate.
        // FIXME probably need to be more careful about the _type_ of field here.
        $fieldset_values = array();
        foreach ($fields as $oField) {
            $curr_val = KTUtil::arrayGet($aDocumentData['field_values'], $oField->getId(), null);
            $old_val = KTUtil::arrayGet($aComparisonData['field_values'], $oField->getId(), null);
            $fieldset_values[] = array('field' => $oField, 'current_value' => $curr_val, 'previous_value' => $old_val);
        }


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/simple_versioned');
        $aTemplateData = array(
            'context' => $this,
            'document_data' => $aDocumentData,
            'document' => $aDocumentData['document'],
            'fieldset' => $this->fieldset,
            'fieldset_values' => $fieldset_values,
        );
        return $oTemplate->render($aTemplateData);
    }

    function renderEdit($document_data) {
        global $main; // FIXME remove direct access to $main

        $aFields = array();

        $fields =& $this->fieldset->getFields();

        foreach ($fields as $oField) {
            $val = KTUtil::arrayGet($document_data['field_values'], $oField->getId(), null);

            $has_errors = KTUtil::arrayGet($document_data['errors'], $oField->getId(),false);
            if ($has_errors !== false) {
                // FIXME when the actual errors (meaningful) are passed out, fix this.
                $errors = array(_kt('The system rejected your value for this field.'));
            } else {
                $errors = null;
            }

            $aFields[] = getWidgetForMetadataField($oField, $val, $main, $errors); // FIXME handle errors
        }
        $fieldset_name = $this->fieldset->getName();
        $fieldset_description = $this->fieldset->getDescription();
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/simple_editable');
        $aTemplateData = array(
            'context' => $this,
            'fields' => $aFields,
            'title' => $fieldset_name,
            'description' => $fieldset_description,
        );


        return $oTemplate->render($aTemplateData);
    }

}


// Handle the conditional case.
class ConditionalFieldsetDisplay extends SimpleFieldsetDisplay {

    function renderEdit($document_data) {
        global $main; // FIXME remove direct access to $main
        $oPage =& $main;

        // FIXME do this from inside the widgetry mojo.
        $oPage->requireCSSResource('resources/css/kt-treewidget.css');

        // FIXME this currently doesn't work, since we use NBM's half-baked Ajax on add/bulk ;)
        $oPage->requireJSResource('resources/js/taillog.js');
        $oPage->requireJSResource('resources/js/conditional_usage.js');

        $aFields = array();
        $fields =& $this->fieldset->getFields();
        $values = array();
        $errors = $document_data['errors'];

        $have_values = false;
        foreach ($fields as $oField) {
            $val = KTUtil::arrayGet($document_data['field_values'], $oField->getId(), null);
            if ($val !== null) {
                $have_values = true;

            }

            $values[$oField->getId()] =  $val;
        }


        // now, we need to do some extra work on conditional widgets.
        // how?

        $fieldset_name = $this->fieldset->getName();
        $fieldset_description = sprintf(_kt('%s') , $this->fieldset->getDescription()); // need a better approach.  how do we handle database-resident translations?
        $fieldset_description .= _kt('Note that the options which are available depends on previous choices within this fieldset.');

        // FIXME handle the editable case _with_ values.

        if ($have_values) {
            $oTemplating =& KTTemplating::getSingleton();
            $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/conditional_editable_values');
            $aTemplateData = array(
                'context' => $this,
                'fields' => $fields =& $this->fieldset->getFields(),
                'fieldset_id' => $this->fieldset->getId(),
                'title' => $fieldset_name,
                'description' => $fieldset_description,
                'values' => $values,
                'errors' => $errors,
            );

            return $oTemplate->render($aTemplateData);
        } // else {

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/fieldsets/conditional_editable');
        $aTemplateData = array(
            'context' => $this,
            'field' => $oField, // first field, widget.
            'fieldset_id' => $this->fieldset->getId(),
            'title' => $fieldset_name,
            'description' => $fieldset_description,
        );

        return $oTemplate->render($aTemplateData);
    }

}

?>
