<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/upgrades/upgrade.inc.php');

foreach (describeUpgrade('2.0.5', '2.0.6') as $step) {
    print $step->getDescriptor() . "\n";
    $step->performUpgrade();
}

?>
