<?php
/**
 * $Id$
 *
 * Business logic concerned with the deletion of a folder.
 * Will use deleteFolderUI.inc for presentation functionality.
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
 * @package foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderDocTypeLink.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/PhysicalFolderManagement.inc");
require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteFolderUI.inc");

if (checkSession()) {
    // initialise custom pattern once
    $oPatternCustom = & new PatternCustom();
    
	if (isset($fFolderID)) {
		$oFolder = Folder::get($fFolderID);
		if (Permission::userHasFolderWritePermission($oFolder)) {
			if (isset($fDeleteConfirmed)) {
				// deletion of folder is confirmed
				
				if (isset($oFolder)) {
                    // check if there are any documents or folders in this folder
                    
					$sFolderPath = Folder::getFolderPath($fFolderID);
					if ($oFolder->delete()) {
						if (PhysicalFolderManagement::deleteFolder($sFolderPath)) {
							// successfully deleted the folder from the file system
							$default->log->info("deleteFolderBL.php successfully deleted folder " . $oFolder->getName() . " from parent folder " . Folder::getFolderPath($oFolder->getParentID()) . " id=" . $oFolder->getParentID());
                            
                            // delete folder collaboration entries
                            $aFolderCollaboration = FolderCollaboration::getList("WHERE folder_id=$fFolderID");
                            for ($i=0; $i<count($aFolderCollaboration); $i++) {
                                $aFolderCollaboration[$i]->delete();
                            }
                            
                            // delete folder document types link
                            $aFolderDocTypeLink = FolderDocTypeLink::getList("folder_id=$fFolderID");
                            for ($i=0; $i<count($aFolderDocTypeLink); $i++) {
                                $aFolderDocTypeLink[$i]->delete();
                            }                            
                            
                            // fire subscription alerts for parent folder subscriptions to the deleted folder
                            $count = SubscriptionEngine::fireSubscription($oFolder->getParentID(), SubscriptionConstants::subscriptionAlertType("RemoveChildFolder"),
                                     SubscriptionConstants::subscriptionType("FolderSubscription"),
                                     array( "removedFolderName" => $oFolder->getName(),
                                            "parentFolderName" => Folder::getFolderDisplayPath($oFolder->getParentID())));
                            $default->log->info("deleteFolderBL.php fired $count parent folder subscription alerts for removed folder " . $oFolder->getName());

                            // fire subscription alerts for the deleted folder
                            $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder"),
                                     SubscriptionConstants::subscriptionType("FolderSubscription"),
                                     array( "removedFolderName" => $oFolder->getName(),
                                            "parentFolderName" => Folder::getFolderDisplayPath($oFolder->getParentID())));
                            $default->log->info("deleteFolderBL.php fired $count parent folder subscription alerts for removed folder " . $oFolder->getName());
                            
                            // remove folder subscriptions for this folder
                            if (SubscriptionManager::removeSubscriptions($fFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
                                $default->log->info("deleteFolderBL.php removed all subscriptions for this folder");
                            } else {
                                $default->log->error("deleteFolderBL.php couldn't remove folder subscriptions");
                            }                            
                            
							// redirect to the browse folder page with the parent folder id 
							redirect("$default->rootUrl/control.php?action=browse&fFolderID=" . $oFolder->getParentID());
						} else {
							// could not delete the folder from the file system
							$default->log->error("deleteFolderBL.php Filesystem error deleting folder " . $oFolder->getName() . " from parent folder " . Folder::getFolderPath($oFolder->getParentID()) . " id=" . $oFolder->getParentID());
							// so reverse the folder deletion
							$oFolder->create();
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");																	
							$oPatternCustom->setHtml("");
							$main->setCentralPayload($oPatternCustom);
							$main->setErrorMessage(_("The folder could not be deleted from the file system"));
							$main->render();
						}
					} else {
						// could not delete the folder in the db
						$default->log->error("deleteFolderBL.php DB error deleting folder " . $oFolder->getName() . " from parent folder " . Folder::getFolderPath($oFolder->getParentID()) . " id=" . $oFolder->getParentID());
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
						$oPatternCustom->setHtml("");
						$main->setCentralPayload($oPatternCustom);
						$main->setErrorMessage(_("The folder could not be deleted from the database"));
						$main->render();
					}
				} else {
					// could not load folder object
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");									
					$oPatternCustom->setHtml("");
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage(_("An error occured whilst retrieving the folder from the database"));
					$main->render();
				}
			} else {
                // check if there are any folders or documents in this folder
                                    
                // get folders descended from this one
                $aFolderArray = Folder::getList("parent_id=$fFolderID");
                // get live documents in this folder
                $aLiveDocuments = Document::getList("folder_id=$fFolderID AND status_id=" . LIVE);
				// get archived documents in this folder                
                $aArchivedDocuments = Document::getList("folder_id=$fFolderID AND status_id=" . ARCHIVED);
                
                if (count($aFolderArray) > 0) {
                    $oPatternCustom->setHtml(getFolderNotEmptyPage($fFolderID,  count($aFolderArray), "folder(s)"));
                } else if (count($aLiveDocuments) > 0) {
                    $oPatternCustom->setHtml(getFolderNotEmptyPage($fFolderID, count($aLiveDocuments), "document(s)"));
                } else if (count($aArchivedDocuments) > 0) {
                    $oPatternCustom->setHtml(getFolderNotEmptyPage($fFolderID, "", " archived documents"));                	
                } else {
                	// check if this is a unit root folder before allowing deletion
                	$oFolder = Folder::get($fFolderID);
                	
					// check if this unit has any groups
					$aGroupUnitLink = GroupUnitLink::getList("unit_id=" . $oFolder->getUnitID());
					$bUnitHasGroups = count($aGroupUnitLink) > 0;
					   
                	if (Folder::folderIsUnitRootFolder($fFolderID) && $bUnitHasGroups) {
						// you can't delete a unit root folder
						$oPatternCustom->setHtml(statusPage("Delete Folder", "", "You can't delete this folder because it is a Unit Root Folder and in use.", "browse", "fFolderID=" . $iFolderID));

                	} else {
	                    // get confirmation first
	                    $oPatternCustom->setHtml(getConfirmPage($fFolderID, $oFolder->getName()));
                	}
                }
                // render the page
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $main->setCentralPayload($oPatternCustom);				
                $main->render();
			}
		} else {
			// user does not have permission to delete the folder
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml("");
			$main->setCentralPayload($oPatternCustom);
			$main->setErrorMessage(_("You do not have permission to delete this folder"));
			$main->render();
		}
	} else {
		// no folder selected for deletion
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
		$oPatternCustom = & new PatternCustom();							
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage(_("No folder currently selected"));
		$main->render();
	}
}
