<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class KTDocumentTypeDispatcher extends KTAdminDispatcher {

   // Breadcrumbs base - added to in methods
    function do_main () {

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Document Type Management'));
        
        $this->oPage->setBreadcrumbDetails('view types');
    
        $addFields = array();
        $addFields[] = new KTStringWidget(_kt('Name'), _('A short, human-readable name for the document type.'), 'name', null, $this->oPage, true);
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/list');
        $oTemplate->setData(array(
            'document_types' => DocumentType::getList(),
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
        $availableTypesWidget =& new KTLookupWidget(_kt('Available Fieldsets'), _('Select the fieldsets which you wish to associate with this document type'), 'fieldsetid[]', null, $this->oPage, true,
            null, null, $aOptions);
        
        $this->aBreadcrumbs[] = array(
            'name' => $oDocumentType->getName(),
        );        
        $this->oPage->setBreadcrumbDetails(_kt('edit'));
        
        $oTemplate->setData(array(
            'oDocumentType' => $oDocumentType,
            'aCurrentFieldsets' => $aCurrentFieldsets,
            'aAvailableFieldsets' => $aAvailableFieldsets,
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
}

?>
