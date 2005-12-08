<?php

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/groups/Group.inc");

require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

$oRegistry =& KTPluginRegistry::getSingleton();
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

class KTDocumentPermissionsAction extends KTDocumentAction {
    var $sBuiltInAction = 'editDocumentPermissions';
    var $sDisplayName = 'Permissions';
    var $sName = 'ktcore.actions.document.permissions';

    function do_main() {
        $oTemplate = $this->oValidator->validateTemplate("ktcore/document/document_permissions");
        $oPO = KTPermissionObject::get($this->oDocument->getPermissionObjectID());
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
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oDocument)) {
                    $aMapPermissionUser[$iPermissionID][$oUser->getID()] = true;
                }
            }
        }

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $this->oDocument) {
            $bEdit = true;
        } else {
            $iInheritedFolderID = $oInherited->getID();
            /* $sInherited = displayFolderPathLink(Folder::getFolderPathAsArray($iInheritedFolderID),
                        Folder::getFolderPathNamesAsArray($iInheritedFolderID),
                        "$default->rootUrl/control.php?action=editFolderPermissions");*/
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());
            $bEdit = false;
        }

        $aTemplateData = array(
            "context" => $this,
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
}
$oPlugin->registerAction('documentaction', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions');

