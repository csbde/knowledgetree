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
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/links/link.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	global $default;
	$oPatternCustom = & new PatternCustom();		
	
	$oPatternCustom->addHtml(renderHeading("Edit Document Checkout"));
	
	if (isset($fUpdate)){	
		if (isset($fDocID)){			
			$oDoc = Document::get($fDocID);
			
			if (($oDoc->getIsCheckedOut() > 0 && $fDocCheckout=="on" ) ||
				($oDoc->getIsCheckedOut() == 0 && $fDocCheckout=="" )){
				$main->setErrorMessage("No changes were made to the document checkout.");
			} else {
				if ($fDocCheckout=="on"){
					$oDoc->setIsCheckedOut(1);				
				}else {
					$oDoc->setIsCheckedOut(0);
					$oDoc->setCheckedOutUserID(-1);
				}				
				if ($oDoc->update()){
					$oPatternCustom->addHtml(getEditCheckoutSuccessPage());
				} else {
					$main->setErrorMessage("Error while trying to update the document checkout.");
				}
			}			
		}
	} else if (isset($fDocID)){
		$oPatternCustom->addHtml($fDocCheckout . getEditCheckoutPage($fDocID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdate=1&fDocID=$fDocID");
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
