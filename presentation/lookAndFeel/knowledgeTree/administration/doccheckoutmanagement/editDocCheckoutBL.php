<?php
/**
* BL information for adding a Link
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editDocCheckoutUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");	
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/links/link.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	global $default;
	$oPatternCustom = & new PatternCustom();		
	
	if (isset($fDocumentID)) {	
		if (isset($fUpdate)) {	
			$oDocument = Document::get($fDocumentID);
			$oDocument->setIsCheckedOut(0);
			$oDocument->setCheckedOutUserID(-1);
			if ($oDocument->update()) {
				// checkout cancelled transaction
				$oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), "Document checked out cancelled", FORCE_CHECKIN);
				if ($oDocumentTransaction->create()) {
					$default->log->debug("editDocCheckoutBL.php created forced checkin document transaction for document ID=" . $oDocument->getID());                                    	
				} else {
					$default->log->error("editDocCheckoutBL.php couldn't create create document transaction for document ID=" . $oDocument->getID());
				}                                    
				$oPatternCustom->setHtml(getEditCheckoutSuccessPage());
			} else {
				$oPatternCustom->setHtml(getErrorPage("Error while trying to update the document checkout."));
			}
		} else {
			$oPatternCustom->addHtml(getEditCheckoutPage($fDocumentID));
			$main->setFormAction($_SERVER["PHP_SELF"]);
		}
	} else {
		// no document selected
		$oPatternCustom->setHtml(getErrorPage("No document selected to check back in"));
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
