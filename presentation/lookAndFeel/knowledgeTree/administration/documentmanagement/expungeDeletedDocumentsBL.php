<?php

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fConfirm', 'fDocumentIDs');

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
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.documentmanagement
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

					// store an expunge transaction
                       $oDocumentTransaction = & new DocumentTransaction($fDocumentIDs[$i], "Document expunged", EXPUNGE);
                       $oDocumentTransaction->create();

					// delete this from the db now
					if ($aDocuments[$i]->delete()) {
						// removed succesfully
						$aSuccessDocuments[] = $aDocuments[$i]->getDisplayPath();

						// remove any document data
						$aDocuments[$i]->cleanupDocumentData($fDocumentIDs[$i]);

                        // delete the corresponding web document entry
                        $oWebDocument = WebDocument::get(lookupID($default->web_documents_table, "document_id", $fDocumentIDs[$i]));
                        $oWebDocument->delete();
												
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
