<?php
/**
 * $Id$
 *
 * Business logic concerned with the deletion of a document.  
 * Will use deleteDocumentUI for presentation information.  
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

KTUtil::extractGPC('fDeleteConfirmed', 'fDocumentIDs');

require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

require_once("$default->fileSystemRoot/presentation/Html.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteDocumentUI.inc");

$aNondeletedDocs = array();

if (checkSession()) {
	
  if (isset($fDocumentIDs)) {
    
    // Check permission and collaboration for all documents
    for ($i = 0; $i < count($fDocumentIDs); $i++) {

      $oDocument = Document::get($fDocumentIDs[$i]);
      if (!Permission::userHasDocumentWritePermission($oDocument)) {
	
	// user does not have permission to delete the document
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(renderErrorPage(_("You do not have, at least, permission to delete one document") . ": " . 
						 $oDocument->getName() . "<br>" . _("Please deselect it and retry.")));
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	return;

      } else {

            // check if there is collaboration for this document
	$aFolderUserRoles = FolderUserRole::getList("document_id = $fDocumentIDs[$i]");
            // check if any of them are active
            $bActive = false;
	for ($j=0; $j<count($aFolderUserRoles); $j++) {
	  $default->log->info("delDoc bActive=" . ($bActive ? "1" : "0") . ";folderUserRoleID=" . $aFolderUserRoles[$j]->getGroupFolderApprovalID() . "; active=" . ($aFolderUserRoles[$j]->getActive() ? "1" : "0"));
	  $bActive = $bActive || $aFolderUserRoles[$j]->getActive();
	}
	
	if ($bActive) {
	  
	  // there are active collaboration roles for this doc
	  require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
	  $oPatternCustom = & new PatternCustom();							
	  $oPatternCustom->setHtml(renderErrorPage(_("You can't, at least, delete one document") . ": " . 
						   $oDocument->getName() . "<br>" . _("It's still in collaboration")));
	  $main->setCentralPayload($oPatternCustom);
	  $main->render();
	  return;
	}
            }
    }


    /* Delete all files
       If an error occured while deleting a file, then:
       - make a rollback of the current file
       - insert document object in $aNondeletedDocs array
       - delete the other selected file

       At the end check the $aNondeletedDocs array
       - if is empty then OK
       - if is not empty then show the nondeleted files list
    */
    
    // Delete all files with possible rollback
                if (isset($fDeleteConfirmed)) {
      
      // deletion of all documents are confirmed
      for ($i = 0; $i < count($fDocumentIDs); $i++) {
	
	$oDocument = Document::get($fDocumentIDs[$i]);
                    if (isset($oDocument)) {
	  // New transaction
                        $sDocumentPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();					
	  $oDocumentTransaction = & new DocumentTransaction($fDocumentIDs[$i], "Document deleted", DELETE);
                        $oDocumentTransaction->create();
	  
                        // flip the status id
                        $oDocument->setStatusID(DELETED);
	  
                        // store
                        if ($oDocument->update()) {
                        	// now move the document to the delete folder
                            if (PhysicalDocumentManager::delete($oDocument)) {
                                // successfully deleted the document
	      $default->log->info("deleteDocumentBL.php successfully deleted document " . 
				  $oDocument->getFileName() . " from folder " . 
				  Folder::getFolderPath($oDocument->getFolderID()) . 
				  " id=" . $oDocument->getFolderID());
                                
                                // delete all collaboration roles
	      for ($j=0; $j<count($aFolderUserRoles); $j++) {
		$default->log->info("delDoc deleting folderuserroleID=" . $aFolderUserRoles[$j]->getGroupFolderApprovalID());
		$aFolderUserRoles[$j]->delete();
                                }
                                
                                // fire subscription alerts for the deleted document
	      $count = SubscriptionEngine::fireSubscription($fDocumentIDs[$i], 
							    SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument"),
                                         SubscriptionConstants::subscriptionType("DocumentSubscription"),
                                         array( "folderID" => $oDocument->getFolderID(),
                                                "removedDocumentName" => $oDocument->getName(),
                                                "folderName" => Folder::getFolderDisplayPath($oDocument->getFolderID())));
                                $default->log->info("deleteDocumentBL.php fired $count subscription alerts for removed document " . $oDocument->getName());
                                
                                // remove all document subscriptions for this document
	      if (SubscriptionManager::removeSubscriptions($fDocumentIDs[$i], SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
                                    $default->log->info("deleteDocumentBL.php removed all subscriptions for this document");
                                } else {
                                    $default->log->error("deleteDocumentBL.php couldn't remove document subscriptions");
                                }
                                
                            } else {
                                //could not delete the document from the file system
                                $default->log->error("deleteDocumentBL.php Filesystem error deleting document " . $oDocument->getFileName() . " from folder " . Folder::getFolderPath($oDocument->getFolderID()) . " id=" . $oDocument->getFolderID());
                                //reverse the document deletion
                                $oDocument->setStatusID(LIVE);
                                $oDocument->update();
                                //get rid of the document transaction
                                $oDocumentTransaction->delete();
	      
	      // Store the doc with problem
	      array_push($aNondeletedDocs, array($oDocument, _("Could not delete document on file system")));
	      	      
                            }
                        } else {
                            //could not update the documents status in the db
                            $default->log->error("deleteDocumentBL.php DB error deleting document " . $oDocument->getFileName() . " from folder " . Folder::getFolderPath($oDocument->getFolderID()) . " id=" . $oDocument->getFolderID());
                            
							//get rid of the document transaction
                            $oDocumentTransaction->delete();
	    
	    // Store the doc with problem
	    array_push($aNondeletedDocs, array($oDocument, _("Could not update document in database")));

                        }
                    } else {
                        //could not load document object
	  
	  // Store the doc with problem
	  array_push($aNondeletedDocs, array($oDocument, _("Could not load document in database")));
	  
                    }
                }

      // List nondeleted documents
      if (!empty($aNondeletedDocs) ) {

                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
                $oPatternCustom = & new PatternCustom();							
	  
	$sError = _("An error occured deleting the following document(s):") . "<br><br>";
	foreach ($aNondeletedDocs as $oDoc) {
	  $sError .= $oDoc[0]->getDisplayPath() . ":&nbsp;&nbsp;&nbsp;" .$oDoc[1] . "<br>";
	} 
	$sError .= "<br>" . _("The other documents are been deleted.");
	
	$oPatternCustom->addHtml(renderErrorPage($sError));
                $main->setCentralPayload($oPatternCustom);
                $main->render();

	reset($aNondeletedDocs);

      } else {
	// redirect to the browse folder page							
	redirect("$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID());
            }
      
      
		} else {
      //get confirmation first				
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
      $oPatternCustom->addHtml(getPage($fDocumentIDs));
			$main->setCentralPayload($oPatternCustom);
			$main->render();
		}
	} else {
		//no document selected for deletion
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		$oPatternCustom = & new PatternCustom();							
		$oPatternCustom->setHtml(renderErrorPage(_("No document currently selected")));
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
}

?>
