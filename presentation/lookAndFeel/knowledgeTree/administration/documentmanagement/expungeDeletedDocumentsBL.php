<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");
require_once("expungeDeletedDocumentsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *  
 * Business logic for expunging deleted documents.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    $oContent = new PatternCustom();
    
	if ($fDocumentIDs) {
		// got some documents to expunge

		// instantiate document objects
		$aDocuments = array();
        for ($i = 0; $i < count($fDocumentIDs); $i++) {
        	$aDocuments[] = & Document::get($fDocumentIDs[$i]);
        }

		if ($fConfirm) {
			$aErrorDocuments = array();
			$aSuccessDocuments = array();			
			// delete the specified documents
			for ($i=0; $i<count($aDocuments); $i++) {
				if (PhysicalDocumentManager::expunge($aDocuments[$i])) {
					// delete this from the db now
					if ($aDocuments[$i]->delete()) {
						// removed succesfully
						$aSuccessDocuments[] = $aDocuments[$i]->getDisplayPath();

						// remove any document data
						$aDocuments[$i]->cleanupDocumentData($fDocumentIDs[$i]);

                        // delete the corresponding web document entry
                        $oWebDocument = WebDocument::get(lookupID($default->owl_web_documents_table, "document_id", $fDocumentIDs[$i]));
                        $oWebDocument->delete();
												
						// store an expunge transaction
                        $oDocumentTransaction = & new DocumentTransaction($fDocumentIDs[$i], "Document expunged", EXPUNGE);
                        $oDocumentTransaction->create();
                        
					} else {
						$default->log->error("expungeDeletedDocumentsBL.php couldn't rm docID=" . $fDocumentIDs[$i] . " from the db");
						$aErrorDocuments[] = $aDocuments[$i]->getDisplayPath();
					}
				} else {
					$default->log->error("expungeDeletedDocumentsBL.php couldn't rm docID=" . $fDocumentIDs[$i] . " from the filesystem");
					$aErrorDocuments[] = $aDocuments[$i]->getDisplayPath();
				}
			}
			// display results page
			$oContent->setHtml(renderStatusPage($aSuccessDocuments, $aErrorDocuments));
		} else {
			// ask for confirmation
			$oContent->setHtml(renderConfirmDocuments($aDocuments));
		}
	} else {
		// redirect to list deleted documents page
		controllerRedirect("deletedDocuments", "");
	}
	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER["PHP_SELF"]);
	$main->render();
}
?>