<?php

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");					
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("modifyGenericMetaDataUI.inc");
	
	if (Permission::userHasDocumentWritePermission($fDocumentID)) {
		$oDocument = Document::get($fDocumentID);
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getPage($fDocumentID, $oDocument->getDocumentTypeID(), $fFirstEdit));
		$main->setCentralPayload($oPatternCustom);			
		$main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID"));
		$main->setHasRequiredFields(true);
		$main->render();
	}
	
}

?>
