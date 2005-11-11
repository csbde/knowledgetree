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
require_once(KT_LIB_DIR . "/permissions/permissiondynamiccondition.inc.php");

require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

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
        $iFolderId = $aPathArray[$i];
        // retrieve the folder name for this folder
        $sFolderName = $aPathNameArray[$i];
        // generate a link back to this page setting fFolderId
        $sLink = generateLink($sLinkPage,
                              "fBrowseType=folder&fFolderID=$iFolderId",
                              $sFolderName);
        $sPathLinks = (strlen($sPathLinks) > 0) ? $sPathLinks . " > " . $sLink : $sLink;
    }
    return $sPathLinks;
}


class FolderPermissions extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        if (KTUtil::arrayGet($_REQUEST, 'fFolderID')) {
            $_REQUEST['fFolderId'] = $_REQUEST['fFolderID'];
        }
        $this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);
        return true;
    }
    function do_main() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/permissions");
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorId());
            $iPermissionId = $oPermission->getId();
            $aIds = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionId] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionGroup[$iPermissionId][$iId] = true;
            }
        }
        $aMapPermissionUser = array();
        $aUsers = User::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermissionId = $oPermission->getId();
            foreach ($aUsers as $oUser) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oFolder)) {
                    $aMapPermissionUser[$iPermissionId][$oUser->getId()] = true;
                }
            }
        }

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $this->oFolder) {
            $bEdit = true;
        } else {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = displayFolderPathLink(Folder::getFolderPathAsArray($iInheritedFolderId),
                        Folder::getFolderPathNamesAsArray($iInheritedFolderId),
                        "$default->rootUrl/control.php?action=editFolderPermissions");
            $bEdit = false;
        }

        $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
        $aTemplateData = array(
            "permissions" => $aPermissions,
            "groups" => Group::getList(),
            "iFolderId" => $this->oFolder->getId(),
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "users" => $aUsers,
            "aMapPermissionUser" => $aMapPermissionUser,
            "edit" => $bEdit,
            "inherited" => $sInherited,
            "conditions" => KTSavedSearch::getConditions(),
            "dynamic_conditions" => $aDynamicConditions,
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
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermId = $oPermission->getId();
            $aAllowed = KTUtil::arrayGet($aFoo, $iPermId, array());
            KTPermissionUtil::setPermissionForId($oPermission, $oPO, $aAllowed);
        }
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_copyPermissions() {
        KTPermissionUtil::copyPermissionObject($this->oFolder);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $oFolder->getId()));
    }

    function do_inheritPermissions() {
        KTPermissionUtil::inheritPermissionObject($this->oFolder);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        $oCondition =& $this->oValidator->validateCondition($_REQUEST['fConditionId']);
        $aPermissionIds = $_REQUEST['fPermissionIds'];
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());

        $oDynamicCondition = KTPermissionDynamicCondition::createFromArray(array(
            'groupid' => $oGroup->getId(),
            'conditionid' => $oCondition->getId(),
            'permissionobjectid' => $oPO->getId(),
        ));
        $aOptions = array(
            'redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $this->oValidator->notError($oDynamicCondition, $aOptions);
        $res = $oDynamicCondition->saveAssignment($aPermissionIds);
        $this->oValidator->notError($res, $aOptions);
        $this->successRedirectToMain("Dynamic permission added", "fFolderId=" . $this->oFolder->getId());
    }
}

$oDispatcher = new FolderPermissions;
$oDispatcher->dispatch();

?>
