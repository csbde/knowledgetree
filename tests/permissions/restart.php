<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/upgrades/UpgradeFunctions.inc.php');

error_reporting(E_ALL);

DBUtil::runQuery("UPDATE folders SET permission_object_id = NULL");
DBUtil::runQuery("UPDATE documents SET permission_object_id = NULL");
DBUtil::runQuery("UPDATE documents SET permission_object_id = NULL");
DBUtil::runQuery("UPDATE folders SET permission_object_id = NULL");
DBUtil::runQuery("TRUNCATE permission_assignments");
DBUtil::runQuery("TRUNCATE permission_descriptors");
DBUtil::runQuery("TRUNCATE permission_objects");
DBUtil::runQuery("TRUNCATE permission_descriptor_groups");

?>
