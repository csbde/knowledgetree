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
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTDocumentTypeDispatcher extends KTAdminDispatcher {

    var $sHelpPage = 'ktcore/admin/document types.html';

   // Breadcrumbs base - added to in methods
    function do_main () {

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Document Type Management'));

        $this->oPage->setBreadcrumbDetails(_kt('view types'));

        $addFields = array();
        $addFields[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the document type.'), 'name', null, $this->oPage, true);

        // Get document types
        $aDocumentTypes = DocumentType::getList();
        
        // Get document type ids associated with documents - allow delete on those not associated
        $aAssocDocs = DocumentType::getAssociatedTypes();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/list');
        $oTemplate->setData(array(
             'context' => $this,
            'document_types' => $aDocumentTypes,
            'associated_types' => $aAssocDocs,
            'add_fields' => $addFields,
        ));
        return $oTemplate;
    }

    function do_new() {
        $sName = $this->oValidator->validateEntityName('DocumentType', $_REQUEST['name'], array("redirect_to" => array("main")));

        $oDocumentType =& DocumentType::createFromArray(array(
            'name' => $sName,
        ));

        if (PEAR::isError($oDocumentType)) {
            $this->errorRedirectToMain(_kt('Could not create document type'));
            exit(0);
        }
        $this->successRedirectTo('edit', _kt('Document type created'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_delete() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        if ($oDocumentType->isUsed()) {
            $this->errorRedirectToMain(_kt('Document type still in use, could not be deleted'));
            exit(0);
        }
        $res = $oDocumentType->delete();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectToMain(_kt('Document type could not be deleted'));
            exit(0);
        }

        $this->successRedirectToMain(_kt('Document type deleted'));
        exit(0);
    }

    function do_disable() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);

        // The system default document type (ID 1) cannot be disabled
        if ($oDocumentType->getId() == '1') {
            $this->errorRedirectTo('main', _kt('The system default document type can not be disabled. You may, however, rename it.'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }

        $oDocumentType->setDisabled(true);
        $res = $oDocumentType->update();

        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('main', _kt('Could not disable document type'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }

        $this->successRedirectToMain(_kt('Document type disabled'));
        exit(0);
    }

    function do_enable() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);

        $oDocumentType->setDisabled(false);
        $res = $oDocumentType->update();

        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('main', _kt('Could not enable document type'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }

        $this->successRedirectToMain(_kt('Document type enabled'));
        exit(0);
    }

    function do_edit() {

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Document Type Management'));

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/edit');
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);


        $aCurrentFieldsets =& KTFieldset::getForDocumentType($oDocumentType);
        $aCurrentFieldsetIds = array_map(array("KTUtil", "getId"), $aCurrentFieldsets);
        $aAvailableFieldsets =& KTFieldset::getNonGenericFieldsets();
        $aAvailableFieldsetIds =& array_map(array("KTUtil", "getId"), $aAvailableFieldsets);
        $aAvailableFieldsetIds = array_diff($aAvailableFieldsetIds, $aCurrentFieldsetIds);

        $vocab = array();
        foreach ($aAvailableFieldsetIds as $iFieldsetId) {
            $oFieldset = KTFieldset::get($iFieldsetId);
            $vocab[$oFieldset->getId()] = $oFieldset->getName();
        }
        $aOptions = array();
        $aOptions['vocab'] = $vocab;
        $aOptions['multi'] = true;
        $aOptions['size'] = 5;
        $availableTypesWidget =& new KTLookupWidget(_kt('Available Fieldsets'), _kt('Select the fieldsets which you wish to associate with this document type'), 'fieldsetid[]', null, $this->oPage, true,
            null, null, $aOptions);

        $this->aBreadcrumbs[] = array(
            'name' => $oDocumentType->getName(),
        );
        $this->oPage->setBreadcrumbDetails(_kt('edit'));

        $oTemplate->setData(array(
            'context' => $this,
            'oDocumentType' => $oDocumentType,
            'sDocTypeName' => sanitizeForHTML($oDocumentType->getName()),
            'aCurrentFieldsets' => $aCurrentFieldsets,
            'bAnyFieldsets' => count($aAvailableFieldsets) > 0,
            'bAvailableFieldsets' => count($vocab) > 0,
            'availableWidget' => $availableTypesWidget,
        ));
        return $oTemplate;
    }

    function do_editobject() {
        $iDocumentTypeId = (int)$_REQUEST['fDocumentTypeId'];
        $oDocumentType =& DocumentType::get($iDocumentTypeId);

        $aErrorOptions = array(
            'redirect_to' => array('edit', sprintf('fDocumentTypeId=%d', $iDocumentTypeId)),
        );

        $sName = $this->oValidator->validateEntityName('DocumentType', $_REQUEST['name'], $aErrorOptions);

        $oDocumentType->setName($sName);
        $res = $oDocumentType->update();

        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', _kt('Could not save document type changes'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _kt('Name changed.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_removefieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $res = KTMetadataUtil::removeSetsFromDocumentType($oDocumentType, $_REQUEST['fieldsetid']);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', _kt('Changes not saved'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _kt('Fieldsets removed.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_addfieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $aFieldsetId = $_REQUEST['fieldsetid'];

        if(!count($aFieldsetId)) {
            $this->errorRedirectTo('edit', _kt('You must select at least one fieldset'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }

        $res = KTMetadataUtil::addSetsToDocumentType($oDocumentType, $aFieldsetId);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', _kt('Changes not saved'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _kt('Fieldsets associated.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function getFieldsetsForType($oType) {
        $aCurrentFieldsets = KTFieldset::getForDocumentType($oType);
        if (empty($aCurrentFieldsets)) {
            return _kt('No fieldsets');
        }

        $aNames = array();
        foreach ($aCurrentFieldsets as $oFieldset) {
            if (!PEAR::isError($oFieldset)) {
                $aNames[] = $oFieldset->getName();
            }
        }

        return implode(', ', $aNames);
    }
}

?>
