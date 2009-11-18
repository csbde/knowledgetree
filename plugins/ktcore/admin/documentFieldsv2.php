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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

class KTDocumentFieldDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
	var $bHaveConditional = null;
    var $sHelpPage = 'ktcore/admin/document fieldsets.html';

    function predispatch() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Document Field Management'));
        $this->persistParams(array('fFieldsetId'));

        $this->oFieldset = KTFieldset::get(KTUtil::arrayGet($_REQUEST, 'fFieldsetId'));
        if (PEAR::isError($this->oFieldset)) {
            $this->oFieldset = null;
            unset($_REQUEST['fFieldset']); // prevent further attacks.
        } else {
            $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf($this->meldPersistQuery("","edit")), 'name' => $this->oFieldset->getName());
        }
        $this->bHaveConditional = KTPluginUtil::pluginIsActive('ktextra.conditionalmetadata.plugin');
    }


    function do_main () {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/metadata/admin/list');

        $oTemplate->setData(array(
		    'context' => $this,
            'fieldsets' => KTFieldset::getList("disabled != true AND namespace != 'tagcloud'"),
        ));
        return $oTemplate;
    }

    function form_create() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.fieldsets.create',
            'label' => _kt("Create New Fieldset"),
            'submit_label' => _kt('Create Fieldset'),
            'cancel_action' => 'main',
            'fail_action' => 'newfieldset',
            'action' => 'create',
            'context' => $this,
        ));


        // construct the widget set.
        // we use a slight variation here, because "type" is only present in certain circumstances.
        $widgets = array(
            array('ktcore.widgets.string',array(
                'label' => _kt("Fieldset Name"),
                'name' => 'name',
                'required' => true,
                'description' => _kt("Each fieldset needs a unique name."),
            )),
            array('ktcore.widgets.text',array(
                'label' => _kt("Description"),
                'name' => 'description',
                'required' => true,
                'description' => _kt("In order to ensure that the data that users enter is useful, it is essential that you provide a good example."),
            )),
        );
        if ($this->bHaveConditional) {

            // FIXME get this from some external source.
            $type_vocab = array(
                'normal' => _kt("Normal"),
                'conditional' => _kt("Conditional"),
            );

            $widgets[] = array('ktcore.widgets.selection', array(
                'label' => _kt("Fieldset Type"),
                'use_simple' => false,
                'description' => _kt("It is possible to create different types of fieldsets.  The most common kind is a \"normal\" fieldset, which can be configured to have different kinds of fields.  The administrator may have installed additional plugins which provide different types of fieldsets."),
                'important_description' => _kt('Note that it is not possible to convert between different types of fieldsets, so please choose carefully.'),
                'name' => 'fieldset_type',
                'required' => true,
                'value' => 'normal',
                'vocab' => $type_vocab,
            ));
        }

        $widgets[] = array('ktcore.widgets.boolean',array(
                'label' => _kt("Generic"),
                'name' => 'generic',
                'description' => _kt("A generic fieldset is one that is available for every document by default. These fieldsets will be available for users to edit and add for every document in the document management system."),
            ));

        $oForm->setWidgets($widgets);

        // similarly, we construct validators here.
        $validators = array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name',
            )),
            array('ktcore.validators.string', array(
                'test' => 'description',
                'output' => 'description',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'generic',
                'output' => 'generic',
            )),
        );

        if ($this->bHaveConditional) {
            $validators[] = array('ktcore.validators.string', array(
                'test' => 'fieldset_type',
                'output' => 'fieldset_type',
            ));
        }

        $oForm->setValidators($validators);

        return $oForm;
    }

    function do_newfieldset() {
        $this->oPage->setBreadcrumbDetails(_kt("Create New Fieldset"));
        $oForm = $this->form_create();

        return $oForm->render();
    }

    function do_create() {
        $oForm = $this->form_create();
        $res = $oForm->validate();

        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        if (!empty($data['name'])) {
            $oFieldset = KTFieldset::getByName($data['name']);
            if (!PEAR::isError($oFieldset)) {
                // means we're looking at an existing name
                $extra_errors['name'] = _kt("There is already a fieldset with that name.");
            }
        }

        $is_conditional = false;
        // FIXME this is inelegant.  get it from somewhere else.
        if ($this->bHaveConditional && ($data['fieldset_type'] == 'conditional')) {
            $is_conditional = true;
        }


        if (!empty($errors) || !empty($extra_errors)) {
            return $oForm->handleError(null, $extra_errors);
        }

        // we also need a namespace.
        $temp_name = $data['name'];
        $namespace = KTUtil::nameToLocalNamespace('fieldsets', $temp_name);
        $oOldFieldset = KTFieldset::getByNamespace($namespace);

        while (!PEAR::isError($oOldFieldset)) {
            $temp_name .= '_';
            $namespace = KTUtil::nameToLocalNamespace('fieldsets', $temp_name);
            $oOldFieldset = KTFieldset::getByNamespace($namespace);
        }

        // we now know its a non-conflicting one.
        // FIXME handle conditional fieldsets, which should be ... a different object.
        $oFieldset = KTFieldset::createFromArray(array(
            "name" => $data['name'],
	    	"description" => $data['description'],
            "namespace" => $namespace,
            "mandatory" => false,       // FIXME deprecated
	    	"isConditional" => $is_conditional,   // handle this
            "isGeneric" => $data['generic'],
            "isComplete" => false,
            "isComplex" => false,
            "isSystem" => false,
        ));
        if (PEAR::isError($oFieldset)) {
            return $oForm->handleError(sprintf(_kt("Failed to create fieldset: %s"), $oFieldset->getMessage()));
        }

        $this->successRedirectTo('edit',_kt("Fieldset created."), sprintf('fFieldsetId=%d', $oFieldset->getId()));
    }

	function getTypesForFieldset($oFieldset) {
	    global $default;
	    if ($oFieldset->getIsGeneric()) {
		    return _kt('All types use this generic fieldset.');
		}

	    $types = $oFieldset->getAssociatedTypes();
		if (PEAR::isError($types)) {
		    $default->log->debug('Fieldsets admin: Error retrieving list of associated document types.');
		    return _kt('Error retrieving list of types.');
		}
		if (empty($types)) {
		    return _kt('None');
		}

		$aNames = array();
		foreach ($types as $oType) {
		    if (!PEAR::isError($oType)) {
    		    $aNames[] = $oType->getName();
    		}else{
    		    $default->log->debug('Fieldsets admin: Document type gives error: '.$oType->getMessage());
    		}
		}

		$list = implode(', ', $aNames);
		$length = mb_strlen($list);

		if($length < 50){
		    return $list;
		}
		$default->log->debug('Fieldsets admin: wrapping the list of doc types from length '.$length);

		// Wrap the list to 50 characters per line
		$wrapList = '';
		$cut = 0;
		while ($length > 50 && $cut !== false){
		    $cut = strpos($list, ' ', 50);
		    $wrapList .= mb_strcut($list, 0, $cut);
		    $wrapList .= '<br />';
		    $list = mb_strcut($list, $cut);
		    $length = mb_strlen($list);
		}
		$wrapList .= $list;

		return $wrapList;
	}


    function do_edit() {
        // here we engage in some major evil.
        // we check for the subevent var
        // and subdispatch if appropriate.
        //
        // saves a little code-duplication (actually, a lot of code-duplication)

        // FIXME this is essentially a stub for the fieldset-delegation code.
        if ($this->oFieldset->getIsConditional()) {
            require_once('fieldsets/conditional.inc.php');
            $oSubDispatcher = new ConditionalFieldsetManagementDispatcher;
        } else {
            require_once('fieldsets/basic.inc.php');
            $oSubDispatcher = new BasicFieldsetManagementDispatcher;
        }

        $subevent_var = 'fieldset_action';
        $subevent = KTUtil::arrayGet($_REQUEST, $subevent_var);
        if (!empty($subevent)) {
            // do nothing, since this will handle everything
            $this_url = KTUtil::addQueryStringSelf($this->meldPersistQuery("","edit"));
            $oSubDispatcher->redispatch($subevent_var, null, $this, $this_url);
            exit(0);
        } else {
            // what we want is the "additional info" section
            $additional = $oSubDispatcher->describe_fieldset($this->oFieldset);
        }

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/metadata/admin/edit');
        $oTemplate->setData(array(
            'context' => $this,
            'fieldset_name' => $this->oFieldset->getName(),
            'additional' => $additional,
        ));
        return $oTemplate->render();
    }

    function do_delete() {
        $this->startTransaction();

        // check if fieldset is associated with a document type - remove association
        $types = $this->oFieldset->getAssociatedTypes();
        $sFieldSetId = $this->oFieldset->getId();
        if(!PEAR::isError($types) AND !empty($types)){
            foreach($types as $oType){
                $res = KTMetadataUtil::removeSetsFromDocumentType($oType, $sFieldSetId);
            }
        }

        $res = $this->oFieldset->delete('true');
        $this->oValidator->notErrorFalse($res, array(
            'redirect_to' => array('main', ''),
            'message' => _kt('Could not delete fieldset'),
        ));
        $this->successRedirectToMain(_kt('Fieldset deleted'));
    }

    function form_edit() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.fieldsets.edit',
            'label' => _kt("Change Fieldset Details"),
            'submit_label' => _kt('Update Fieldset'),
            'cancel_action' => 'edit',
            'fail_action' => 'editfieldset',
            'action' => 'savefieldset',
            'context' => $this,
        ));


        // construct the widget set.
        // we use a slight variation here, because "type" is only present in certain circumstances.
        $widgets = array(
            array('ktcore.widgets.string',array(
                'label' => _kt("Fieldset Name"),
                'name' => 'name',
                'required' => true,
                'description' => _kt("Each fieldset needs a unique name."),
                'value' => sanitizeForHTML($this->oFieldset->getName()),
            )),
            array('ktcore.widgets.text',array(
                'label' => _kt("Description"),
                'name' => 'description',
                'required' => true,
                'description' => _kt("In order to ensure that the data that users enter is useful, it is essential that you provide a good example."),
                'value' => sanitizeForHTML($this->oFieldset->getDescription()),
            )),
        );

        $widgets[] = array('ktcore.widgets.boolean',array(
                'label' => _kt("Generic"),
                'name' => 'generic',
                'description' => _kt("A generic fieldset is one that is available for every document by default. These fieldsets will be available for users to edit and add for every document in the document management system."),
                'value' => $this->oFieldset->getIsGeneric(),
            ));

        $oForm->setWidgets($widgets);

        // similarly, we construct validators here.
        $validators = array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name',
            )),
            array('ktcore.validators.string', array(
                'test' => 'description',
                'output' => 'description',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'generic',
                'output' => 'generic',
            )),
        );

        $oForm->setValidators($validators);

        return $oForm;
    }

    function do_editfieldset() {
        $oForm = $this->form_edit();
        $this->oPage->setBreadcrumbDetails(_kt('edit fieldset'));
        return $oForm->renderPage(_kt("Edit Fieldset"));
    }

    function do_savefieldset() {
        $oForm = $this->form_edit();
        $res = $oForm->validate();

        $data = $res['results'];
        $errors = $res['errors'];
        $extra_errors = array();

        // check that the fieldset name either hasn't changed, or doesn't exist.
        if ($data['name'] != $this->oFieldset->getName()) {
            $oOldFieldset = KTFieldset::getByName($data['name']);
            // If the fieldset exists throw an error. Mysql doesn't distinguish between Ž and e so check the names are different in php.
            if (!PEAR::isError($oOldFieldset) && $oOldFieldset->getName() == $data['name']) {
                $extra_errors['name'][] = _kt("A fieldset with that name already exists.");
            }
        }

        if (!empty($errors) || !empty($extra_errors)) {
            return $oForm->handleError(null, $extra_errors);
        }

        $this->startTransaction();

        $this->oFieldset->setName($data['name']);
        $this->oFieldset->setDescription($data['description']);
        $bGeneric = $data['generic'];
        if ($bGeneric != $this->oFieldset->getIsGeneric() && $bGeneric == true) {
            // delink it from all doctypes.
            $aTypes = $this->oFieldset->getAssociatedTypes();
            foreach ($aTypes as $oType) {
                $res = KTMetadataUtil::removeSetsFromDocumentType($oType, $this->oFieldset->getId());
                if (PEAR::isError($res)) {
                    $this->errorRedirectTo('edit', _kt('Could not save fieldset changes'));
                    exit(0);
                }
            }
        }

        $this->oFieldset->setIsGeneric($data['generic']);

        $res = $this->oFieldset->update();
        if (PEAR::isError($res)) {
            $this->errorRedirectTo('edit', _kt('Could not save fieldset changes'));
            exit(0);
        }

        return $this->successRedirectTo('edit', _kt("Fieldset details updated."));
    }
}

?>
