<?php

require_once(realpath(dirname(__FILE__)) . '/KTWebService.inc.php');

// Instantiate the webservice
$ws = new WebService();
$ws->handle();

exit();

?>
