<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");

error_reporting(E_ALL);

// var_dump(KTHelpReplacement::get(1));
$res = KTHelpEntity::createFromArray(array(
    'section' => 'foo',
    'filename' => 'bar',
));

?>
