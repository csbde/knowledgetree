<?php

require_once('ajax.inc.php');

$userID = AjaxSearchHelper::getSessionUser();

AjaxSearchHelper::getSavedSearches($userID);

?>