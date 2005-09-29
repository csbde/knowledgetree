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
    function do_main () {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/fields/list');
        $oTemplate->setData(array(
            'fieldsets' => KTFieldset::getList(),
        ));
        return $oTemplate;
    }

    function do_edit() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/fields/edit');
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oTemplate->setData(array(
            'oFieldset' => $oFieldset,
        ));
        return $oTemplate;
    }

    function do_editobject() {
        $oFieldset =& KTFieldset::get($_REQUEST['fFieldsetId']);
        $oFieldset->setName($_REQUEST['name']);
        $oFieldset->setNamespace($_REQUEST['namespace']);
        $res = $oFieldset->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->errorRedirectTo('edit', 'Could not save fieldset changes', 'fFieldsetId=' . $oFieldset->getId());
            exit(0);
        }
        $this->errorRedirectTo('edit', 'Changes saved', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }

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
        $this->errorRedirectTo('edit', 'Field created', 'fFieldsetId=' . $oFieldset->getId());
        exit(0);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }
}

$d =& new KTDocumentFieldDispatcher;
$d->dispatch();

?>
