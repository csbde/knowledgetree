<?php
require_once("../../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

// require_once(KT_DIR . "/FCKeditor/fckeditor.php");

function getItemData($iId) {
    $oTemplating = new KTTemplating;
    $oHelp =& KTHelpReplacement::get($iId);
    if (PEAR::isError($oHelp)) {
        $_SESSION['KTErrorMessage'][] = "Help for that item not found";
        exit(controllerRedirect('helpAdmin'));
    }

    // $oEditor = new FCKEditor("description");
    // $oEditor->Value = $oHelp->getDescription();
    // $oEditor->Height = 400;

    $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help_item");
    $aTemplateData = array(
        "help" => $oHelp,
        // "fck" => $oEditor,
    );
    return $oTemplate->render($aTemplateData);
}

function getData() {
    $oTemplating = new KTTemplating;
    $aHelps = KTHelpReplacement::getList();
    $oTemplate = $oTemplating->loadTemplate("ktcore/manage_help");
    $aTemplateData = array(
        "helps" => $aHelps,
    );

    return $oTemplate->render($aTemplateData);
}

$id = KTUtil::arrayGet($_REQUEST, 'id');
if ($id) {
    $action = KTUtil::arrayGet($_REQUEST, 'action');
    if ($action === "update") {
        $oHelp =& KTHelpReplacement::get($id);
        if (PEAR::isError($oHelp)) {
            $_SESSION['KTErrorMessage'][] = "Help for that item not found";
            exit(controllerRedirect('helpAdmin'));
        }
        $description = KTUtil::arrayGet($_REQUEST, 'description');
        if ($description) {
            $oHelp->setDescription($description);
            $res = $oHelp->update();
            if (PEAR::isError($res)) {
                $_SESSION['KTErrorMessage'][] = "Error updating
                    object";
                $data = getItemData($id);
            } else {
                $_SESSION['KTErrorMessage'][] = "Updated";
                exit(controllerRedirect('helpAdmin'));
            }
        } else {
            $_SESSION['KTErrorMessage'][] = "No description given";
            var_dump($_REQUEST);
            exit(0);
            $data = getItemData($id);
        }
    } else {
        $data = getItemData($id);
    }
} else {
    $data = getData();
}

$sectionName = "Administration";

require_once(KT_DIR . "/presentation/webpageTemplate.inc");
$main->bFormDisabled = true;
$main->setCentralPayload($data);
$main->render();

?>
