<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");

error_reporting(E_ALL);

KTHelpReplacement::createFromArray(array(
    'name' => 'foo',
    'description' => 'asdf qwer czxv',
));

?>
