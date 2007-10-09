<?php

require_once('ajax.inc.php');

$folderID = AjaxSearchHelper::checkGET('folderid', AjaxSearchHelper::STATUS_MISSING_FOLDER, _kt('Folder id is not specified.'));
AjaxSearchHelper::getFolder($folderID);

?>