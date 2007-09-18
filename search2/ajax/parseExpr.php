<?php

require_once('ajax.inc.php');

$txtQuery = AjaxSearchHelper::checkGET('txtQuery',AjaxSearchHelper::STATUS_MISSING_QUERY ,_kt('Query is empty'));

AjaxSearchHelper::parseQuery($txtQuery);

?>