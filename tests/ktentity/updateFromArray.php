<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");

error_reporting(E_ALL);

$oObject = new KTHelpReplacement;
$oObject->load(1);
$oObject->updateFromArray(array(
    'name' => 'qwerasdf2',
));

?>
