<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

error_reporting(E_ALL);

$oPermissionObject = KTPermissionObject::get(22);
$oPermission = KTPermission::getByName('ktcore.permissions.read');
/*$oPermissionAssignment = KTPermissionAssignment::createFromArray(array(
    'permissionid' => $oPermission->getId(),
    'permissionobjectid' => $oPermissionObject->getId(),
));*/
// $oPermissionAssignment = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPermissionObject);
$aAllowed = array("group" => array(1,2,3,4));
KTPermissionUtil::setPermissionForID($oPermission, $oPermissionObject, $aAllowed);

?>
