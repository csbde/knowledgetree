<?php
/**
 * $Id$
 *
 * Expected form variables:
 *	o fDocumentID - primary key of document being editid
 * Optional form variables:
 *	o fFirstTime - set by addDocumentBL on first time uploads and forces the user to
 *				   fill out the generic meta data
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMetaData.inc");					
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("documentUI.inc");
	require_once("modifyGenericMetaDataUI.inc");
	
	if (Permission::userHasDocumentWritePermission($fDocumentID)) {
		$oDocument = Document::get($fDocumentID);
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getPage($fDocumentID, $oDocument->getDocumentTypeID(), $fFirstEdit));
		$main->setCentralPayload($oPatternCustom);
        if (isset($fFirstEdit)) {
            $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=modifyDocumentTypeMetaData&fDocumentID=$fDocumentID&fFirstEdit=1"));            
        } else {
		    $main->setFormAction("../store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID"));
        }
		$main->setHasRequiredFields(true);
		$main->render();
	}
	
}

?>
