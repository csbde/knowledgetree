<?php
require_once('ajax.inc.php');

$what = AjaxSearchHelper::checkGET('what', AjaxSearchHelper::STATUS_MISSING_WHAT , _kt('What is required? fieldsets or fields?'));

switch ($what)
{
	case 'documenttypes':
		AjaxSearchHelper::getDocumentTypes();
	case 'documenttypefieldsets':
		$documentTypeID = AjaxSearchHelper::checkGET('documenttypeid', AjaxSearchHelper::STATUS_MISSING_DOCUMENT_TYPE, _kt('Document type id is not specified.'));
		AjaxSearchHelper::getDocumentTypeFieldsets($documentTypeID);
	case 'fieldsets':
		AjaxSearchHelper::getFieldsets();
	case 'fields':
		$fieldsetID = AjaxSearchHelper::checkGET('fieldsetid', AjaxSearchHelper::STATUS_MISSING_FIELDSET, _kt('Field set id is not specified.'));
		AjaxSearchHelper::getFields($fieldsetID);
	default:
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL , _kt('Nothing else is available'));
}



?>