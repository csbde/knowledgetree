<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_DIR . '/lib/upgrades/UpgradeFunctions.inc.php');

$oUF = new UpgradeFunctions();
$aFuncs = $oUF->upgrades['2.0.6'];
foreach ($aFuncs as $sFunc) {
    $f = array('UpgradeFunctions', $sFunc);
    call_user_func($f);
}

?>
