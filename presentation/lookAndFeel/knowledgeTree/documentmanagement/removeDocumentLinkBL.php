<?php

/**
 * $Id$
 *  
 * Business logic for unlinking a parent document from a child documenbt
 *
 * Expected form variables:
 *	$fDocumentLinkID - primary key of document link to delete
 *	$fChildDocumentID - primary key of child document to which parent document is linked
 *	$fParentDocumentID - primary key of parent document
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */
require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");	
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentLink.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("documentUI.inc");
	require_once("removeDocumentLinkUI.inc");

	if (Permission::userHasDocumentWritePermission($fParentDocumentID)) {
		if (isset($fForDelete)) {
			//deleting a document link
			$oDocumentLink = DocumentLink::get($fDocumentLinkID);
			if ($oDocumentLink->delete()) {
				redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fParentDocumentID");				
			} else {
				//an error occured whilst trying to delete the document link
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
				$oParentDocument = Document::get($fParentDocumentID);
				$oChildDocument = Document::get($fChildDocumentID);
			
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($oParentDocument->getName(), $oChildDocument->getName(), $fParentDocumentID));
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentLinkID=$fDocumentLinkID&fParentDocumentID=$fParentDocumentID&fChildDocumentID=$fChildDocumentID&fForDelete=1");
				$main->setErrorMessage("An error occured whilst attempting to delete the link between the two documents");	
				$main->render();				
			}			
		} else {
			//user has document write permission and can therefore remove the
			//link between the two documents		
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
			$oParentDocument = Document::get($fParentDocumentID);
			$oChildDocument = Document::get($fChildDocumentID);
			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oParentDocument->getName(), $oChildDocument->getName(), $fParentDocumentID));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentLinkID=$fDocumentLinkID&fParentDocumentID=$fParentDocumentID&fChildDocumentID=$fChildDocumentID&fForDelete=1");	
			$main->render();	
		}
	} else {
		//user does not have permission to be here
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("You do not have permission to delete links between documents");			
		$main->render();
	}
}
?>