<?php
require_once('ajax.inc.php');

$userID = AjaxSearchHelper::getSessionUser();
$txtQuery = AjaxSearchHelper::checkGET('txtQuery',AjaxSearchHelper::STATUS_MISSING_QUERY ,_kt('Query is empty'));

AjaxSearchHelper::parseQuery($txtQuery, false);

if (array_key_exists('iSavedId',$_GET))
{
	$iSavedId = AjaxSearchHelper::checkGET('iSavedId', AjaxSearchHelper::STATUS_MISSING_SAVED, _kt('Saved search ID is missing'));

	if (!is_numeric($iSavedId))
	{
		AjaxHelper::ajaxResponse(AjaxSearchHelper::STATUS_MISSING_SAVED, _kt('Saved search ID is not numeric') );
	}

	AjaxSearchHelper::updateQuery($iSavedId, $txtQuery, $userID);

}
else
{
	$txtName = AjaxSearchHelper::checkGET('txtName',AjaxSearchHelper::STATUS_MISSING_NAME ,_kt('Query name is empty'));

	AjaxSearchHelper::saveQuery($txtName, $txtQuery, $userID);

}












?>