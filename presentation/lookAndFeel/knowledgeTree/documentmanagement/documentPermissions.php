<?php

require_once("../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/groups/Group.inc");

require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Manage Documents";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

function displayFolderPathLink($aPathArray, $aPathNameArray, $sLinkPage = "") {
    global $default;
    if (strlen($sLinkPage) == 0) {
        $sLinkPage = $_SERVER["PHP_SELF"];
    }
    $default->log->debug("displayFolderPathLink: slinkPage=$sLinkPage");
    // display a separate link to each folder in the path
    for ($i=0; $i<count($aPathArray); $i++) {
        $iFolderID = $aPathArray[$i];
        // retrieve the folder name for this folder
        $sFolderName = $aPathNameArray[$i];
        // generate a link back to this page setting fFolderID
        $sLink = generateLink($sLinkPage,
                              "fBrowseType=folder&fFolderID=$iFolderID",
                              $sFolderName);
        $sPathLinks = (strlen($sPathLinks) > 0) ? $sPathLinks . " > " . $sLink : $sLink;
    }
    return $sPathLinks;
}


class DocumentPermissionsDispatcher extends KTStandardDispatcher {
    function do_main() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/document/document_permissions");
        $oDocument = Document::get($_REQUEST['fDocumentID']);
        $oPO = KTPermissionObject::get($oDocument->getPermissionObjectID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aIDs = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionID] = array();
            foreach ($aIDs as $iID) {
                $aMapPermissionGroup[$iPermissionID][$iID] = true;
            }
        }
        $aMapPermissionUser = array();
        $aUsers = User::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermissionID = $oPermission->getID();
            foreach ($aUsers as $oUser) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oDocument)) {
                    $aMapPermissionUser[$iPermissionID][$oUser->getID()] = true;
                }
            }
        }

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $oDocument) {
            $bEdit = true;
        } else {
            $iInheritedFolderID = $oInherited->getID();
            $sInherited = displayFolderPathLink(Folder::getFolderPathAsArray($iInheritedFolderID),
                        Folder::getFolderPathNamesAsArray($iInheritedFolderID),
                        "$default->rootUrl/control.php?action=editFolderPermissions");
            $bEdit = false;
        }

        $aTemplateData = array(
            "permissions" => $aPermissions,
            "groups" => Group::getList(),
            "iDocumentID" => $_REQUEST['fDocumentID'],
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "users" => $aUsers,
            "aMapPermissionUser" => $aMapPermissionUser,
            "edit" => $bEdit,
            "inherited" => $sInherited,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    function do_update() {
        $oDocument = Document::get($_REQUEST['fDocumentID']);
        $oPO = KTPermissionObject::get($oDocument->getPermissionObjectID());
        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermID = $oPermission->getID();
            $aAllowed = KTUtil::arrayGet($aFoo, $iPermID, array());
            KTPermissionUtil::setPermissionForID($oPermission, $oPO, $aAllowed);
        }
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        return $this->errorRedirectToMain('Permissions updated',
                array('fDocumentID' => $oDocument->getID()));
    }

    function do_copyPermissions() {
        $oDocument = Document::get($_REQUEST['fDocumentID']);
        KTPermissionUtil::copyPermissionObject($oDocument);
        return $this->errorRedirectToMain('Permissions updated',
                array('fDocumentID' => $oDocument->getID()));
    }

    function do_inheritPermissions() {
        $oDocument = Document::get($_REQUEST['fDocumentID']);
        KTPermissionUtil::inheritPermissionObject($oDocument);
        return $this->errorRedirectToMain('Permissions updated',
                array('fDocumentID' => $oDocument->getID()));
    }

    
}

$oDispatcher = new DocumentPermissionsDispatcher;
$oDispatcher->dispatch();

?>
