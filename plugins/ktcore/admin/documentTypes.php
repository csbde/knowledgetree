<?php

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

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Document Type Management'));
        
        $this->oPage->setBreadcrumbDetails('view types');
    
        $addFields = array();
        $addFields[] = new KTStringWidget(_('Name'),_('A short, human-readable name for the document type.'), 'name', null, $this->oPage, true);
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/list');
        $oTemplate->setData(array(
            'document_types' => DocumentType::getList(),
            'add_fields' => $addFields,
        ));
        return $oTemplate;
    }

    function do_new() {
        $sName = $this->oValidator->validateEntityName('DocumentType', 'document type', $_REQUEST['name'], array("redirect_to" => array("main")));
        
        $oDocumentType =& DocumentType::createFromArray(array(
            'name' => $sName,
        ));

        if (PEAR::isError($oDocumentType)) {
            $this->errorRedirectToMain(_('Could not create document type'));
            exit(0);
        }
        $this->successRedirectTo('edit', _('Document type created'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_delete() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        if ($oDocumentType->isUsed()) {
            $this->errorRedirectToMain(_('Document type still in use, could not be deleted'));
            exit(0);
        }
        $res = $oDocumentType->delete();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectToMain(_('Document type could not be deleted'));
            exit(0);
        }
        
        $this->successRedirectToMain(_('Document type deleted'));
        exit(0);
    }

    function do_disable() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        
        $oDocumentType->setDisabled(true);
        $res = $oDocumentType->update();
        
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('main', _('Could not disable document type'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        
        $this->successRedirectToMain(_('Document type disabled'));
        exit(0);
    }

    function do_enable() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        
        $oDocumentType->setDisabled(false);
        $res = $oDocumentType->update();
        
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('main', _('Could not enable document type'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        
        $this->successRedirectToMain(_('Document type enabled'));
        exit(0);
    }

    function do_edit() {
        
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Document Type Management'));
        
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
        $availableTypesWidget =& new KTLookupWidget(_('Available Fieldsets'),_('Select the fieldsets which you wish to associate with this document type'), 'fieldsetid[]', null, $this->oPage, true,
            null, null, $aOptions);
        
        $this->aBreadcrumbs[] = array(
            'name' => $oDocumentType->getName(),
        );        
        $this->oPage->setBreadcrumbDetails(_('edit'));
        
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

        $sName = $this->oValidator->validateEntityName('DocumentType', 'document type', $_REQUEST['name'], $aErrorOptions);

        $oDocumentType->setName($sName);
        $res = $oDocumentType->update();
        
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', _('Could not save document type changes'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Name changed.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_removefieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $res = KTMetadataUtil::removeSetsFromDocumentType($oDocumentType, $_REQUEST['fieldsetid']);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', _('Changes not saved'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Fieldsets removed.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_addfieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $aFieldsetId = $_REQUEST['fieldsetid'];
        
        if(!count($aFieldsetId)) {
            $this->errorRedirectTo('edit', _('You must select at least one fieldset'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        
        $res = KTMetadataUtil::addSetsToDocumentType($oDocumentType, $aFieldsetId);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', _('Changes not saved'), 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->successRedirectTo('edit', _('Fieldsets associated.'), 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }
}

?>
