<?php

require_once('../../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

class KTDocumentTypeDispatcher extends KTAdminDispatcher {
    function do_main () {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/list');
        $oTemplate->setData(array(
            'document_types' => DocumentType::getList(),
        ));
        return $oTemplate;
    }

    function do_new() {
        $sName = $_REQUEST['name'];
        $oDocumentType =& DocumentType::createFromArray(array(
            'name' => $sName,
        ));
        if (PEAR::isError($oDocumentType)) {
            $this->errorRedirectToMain('Could not create document type');
            exit(0);
        }
        $this->errorRedirectTo('edit', 'Document type created', 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_delete() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        if ($oDocumentType->isUsed()) {
            $this->errorRedirectToMain('Document type still in use, could not be deleted');
            exit(0);
        }
        $res = $oDocumentType->delete();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectToMain('Document type could not be deleted');
            exit(0);
        }
        
        $this->errorRedirectToMain('Document type deleted');
        exit(0);
    }

    function do_edit() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/edit');
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $aCurrentFieldsets =& KTFieldset::getForDocumentType($oDocumentType);
        $aAvailableFieldsets =& KTFieldset::getNonGenericFieldsets();
        $aAvailableFieldsets = array_diff($aAvailableFieldsets, $aCurrentFieldsets);
        $oTemplate->setData(array(
            'oDocumentType' => $oDocumentType,
            'aCurrentFieldsets' => $aCurrentFieldsets,
            'aAvailableFieldsets' => $aAvailableFieldsets,
        ));
        return $oTemplate;
    }

    function do_editobject() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $oDocumentType->setName($_REQUEST['name']);
        $res = $oDocumentType->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not save document type changes', 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->errorRedirectTo('edit', 'Changes saved', 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_removefieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $res = KTMetadataUtil::removeSetsFromDocumentType($oDocumentType, $_REQUEST['fieldsetid']);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', 'Changes not saved', 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->errorRedirectTo('edit', 'Changes saved', 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function do_addfieldsets() {
        $oDocumentType =& DocumentType::get($_REQUEST['fDocumentTypeId']);
        $res = KTMetadataUtil::addSetsToDocumentType($oDocumentType, $_REQUEST['fieldsetid']);
        if (PEAR::isError($res)) {
            var_dump($res);
            $this->errorRedirectTo('edit', 'Changes not saved', 'fDocumentTypeId=' . $oDocumentType->getId());
            exit(0);
        }
        $this->errorRedirectTo('edit', 'Changes saved', 'fDocumentTypeId=' . $oDocumentType->getId());
        exit(0);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }
}

$d =& new KTDocumentTypeDispatcher;
$d->dispatch();

?>
