<?php
/**
 * $Id$
 *
 * Business logic data used to modify documents (will use modifyUI.inc)
 *
 * Expected form variables:
 *	o fDocumentID - primary key of document being edited
 * Optional form variables
 *	o fForUpdate - generated when user clicks update on page and results in database update
 *	o fFirstEdit - generated from the document upload page when the user first uploads a document.
 *				   Is used to force the user to enter the necessary generic meta data.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");    
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/modifyUI.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");				
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	if (Permission::userHasDocumentWritePermission($fDocumentID)) {
		//if the user has write permission
		$oDocument = & Document::get($fDocumentID);
		if (isset($fForUpdate)) {
			//if the user is updating the values
			$oDocument->setName($fDocumentName);
			
			if ($oDocument->getDocumentTypeID() != $fDocumentTypeID) {
				//the user has changed the document type
				//get rid of all the old document type entries
				$oDocument->removeInvalidDocumentTypeEntries();
				$oDocument->setDocumentTypeID($fDocumentTypeID);
				$bUpdateMetaData = true;
			}
			
			if ($oDocument->update()) {
                // fire subscription alerts for the modified document
                $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                         array( "folderID" => $oDocument->getFolderID(),
                                "modifiedDocumentName" => $oDocument->getName()));
                $default->log->info("modifyBL.php fired $count subscription alerts for modified document " . $oDocument->getName());
                
				//on successful update, redirect to the view page
				if (isset($bUpdateMetaData)) {
					controllerRedirect("modifyDocumentTypeMetaData", "fDocumentID=" . $oDocument->getID() . "&fFirstEdit=1");
				} else {
					controllerRedirect("viewDocument", "fDocumentID=" . $oDocument->getID());
				}
			} else {				
				//display the update page with an error message
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();				
				$oPatternCustom->setHtml(renderPage($oDocument, $oDocument->getDocumentTypeID(), $fFirstEdit));
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);	
				if (isset($fFirstEdit)) {
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1&fFirstEdit=1");
				} else {
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");
				}	
				$main->setHasRequiredFields(true);
				$main->setErrorMessage("An error occured while attempting to update the document");
				$main->render();
			}
			
		} else {
			//display the update page
			$oDocument = & Document::get($fDocumentID);
			$oPatternCustom = & new PatternCustom();
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
			$oPatternCustom->setHtml(renderPage($oDocument, $oDocument->getDocumentTypeID(), $fFirstEdit));
			$main->setCentralPayload($oPatternCustom);
			$main->setHasRequiredFields(true);	
			if (isset($fFirstEdit)) {
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1&fFirstEdit=1");
			} else {
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");
			}
				
			$main->setHasRequiredFields(true);
			$main->render();
		}
	} else {
		//user doesn't have permission to edit this page
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);		
		$main->setErrorMessage("You do not have permission to edit this document");
		$main->render();
	}
}

?>
