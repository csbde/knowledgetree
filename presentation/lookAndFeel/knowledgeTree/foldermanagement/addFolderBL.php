<?php
/**
 * $Id$
 *
 * Business logic page that provides business logic for adding a folder (uses
 * addFolderUI.inc for HTML)
 *
 * The following form variables are expected:
 * o $fFolderID - id of the folder the user is currently in
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
 * @package foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

    $oPatternCustom = & new PatternCustom();

    if (isset($fFolderID)) {
        require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
		require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
        require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
		require_once("$default->fileSystemRoot/lib/foldermanagement/FolderDocTypeLink.inc");
        require_once("$default->fileSystemRoot/lib/foldermanagement/PhysicalFolderManagement.inc");
        require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
        require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
        require_once("$default->fileSystemRoot/presentation/Html.inc");
        require_once("addFolderUI.inc");

        if (!isset($fFolderName)) {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            //we're still browsing			
            if (Permission::userHasFolderWritePermission($fFolderID)) {
                //if the user is allowed to add folders, then display the add button
                $oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));
            } else {
                //otherwise just let the user browse
                $oPatternCustom->setHtml(renderBrowsePage($fFolderID));
                $main->setErrorMessage("You do not have permission to create new folders in this folder</td><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$fFolderID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a>");
            }
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
            $main->setHasRequiredFields(true);
            $main->render();
        } else {
        	// a document type has been specified
        	if (isset($fDocumentTypeID)) {
            //have a folder name to store
	            if (Permission::userHasFolderWritePermission($fFolderID)) {
	                //check for illegal characters in the folder name
	                
	                // strip slashes from the already EPGCS escaped form input
	                $sCheckFolderName = stripslashes($fFolderName);
	                if (strpos($sCheckFolderName, "\\") === false && strpos($sCheckFolderName, ">") === false &&
	                    strpos($sCheckFolderName, "<") === false && strpos($sCheckFolderName, ":") === false &&
	                    strpos($sCheckFolderName, "*") === false && strpos($sCheckFolderName, "?") === false &&
	                     strpos($sCheckFolderName, "|") === false && strpos($sCheckFolderName, "/") === false &&
						 strpos($sCheckFolderName, "\"") === false) {
							 
	                    if (Folder::folderExistsName($fFolderName, $fFolderID)) {
	                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	                        $oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));
	                        $main->setCentralPayload($oPatternCustom);
	                        $main->setErrorMessage("There is another folder named $fFolderName in this folder already");
	                        $main->setHasRequiredFields(true);
	                        $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
	                        $main->render();
	                    } else {						
	                        $oParentFolder = Folder::get($fFolderID);
	                        //create the folder in the db, giving it the properties of it's parent folder
	                        $oFolder = & new Folder($fFolderName, "", $fFolderID, $_SESSION["userID"], $oParentFolder->getUnitID());
	                        if ($oFolder->create()) {
								$oFolderDocTypeLink = & new FolderDocTypeLink($oFolder->getID(), $fDocumentTypeID);
								if ($oFolderDocTypeLink->create()) {
									//create the folder on the file system
									if (PhysicalFolderManagement::createFolder(Folder::getFolderPath($oFolder->getID()))) {
										$default->log->info("addFolderBL.php successfully added folder $fFolderName to parent folder " . Folder::getFolderPath($fFolderID) . " id=$fFolderID");
										
										// fire subscription alerts for the new folder
										$count = SubscriptionEngine::fireSubscription($oParentFolder->getID(), SubscriptionConstants::subscriptionAlertType("AddFolder"),
												 SubscriptionConstants::subscriptionType("FolderSubscription"),
												 array( "newFolderName" => $fFolderName,
														"parentFolderName" => $oParentFolder->getName()) );
										$default->log->info("addFolderBL.php fired $count subscription alerts for new folder $fFolderName");
										redirect("$default->rootUrl/control.php?action=editFolder&fFolderID=" . $oFolder->getID());
									} else {
										//if we couldn't do that
										$default->log->error("addFolderBL.php Filesystem error attempting to store folder name=$fFolderName in parent folder '" . Folder::getFolderPath($fFolderID) . "' id=$fFolderID");
										
										// remove the folder and its doc type link from the db and report and error
										$oFolderDocTypeLink->delete();
										$oFolder->delete();
										require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
										$main->setCentralPayload($oPatternCustom);
										$main->setErrorMessage("There was an error creating the folder $fFolderName on the filesystem");
										$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
										$main->setHasRequiredFields(true);									
										$main->render();
									}
								} else {
									//couldn't associate the chosen document type with this folder
									$default->log->error("addFolderBL.php DB error storing folder-document type link for folder name=$fFolderName in parent folder '" . Folder::getFolderPath($fFolderID) . "' folderID=$fFolderID; docTypeID=$fDocumentTypeID");
									
									//remove the folder from the database								
									$oFolder->delete();
									require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
									$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
									$main->setCentralPayload($oPatternCustom);
									$main->setErrorMessage("There was an error creating the folder $fFolderName on the filesystem");
									$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
									$main->setHasRequiredFields(true);
									$main->render();								
								}
	                        } else {
	                            //if we couldn't create the folder in the db, report an error
	                            $default->log->error("addFolderBL.php DB error attempting to store folder name=$fFolderName in parent folder '" . Folder::getFolderPath($fFolderID) . "' id=$fFolderID");
	                            									
	                            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	                            $oPatternCustom->setHtml(renderBrowsePage($fFolderID));
	                            $main->setCentralPayload($oPatternCustom);
	                            $main->setErrorMessage("There was an error creating the folder $fFolderName in the database");
	                            $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
	                            $main->render();
	                        }
	                    }
	                } else {
	                    //the user entered an illegal character in the folder name
	                    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	                    $oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));
	                    $main->setCentralPayload($oPatternCustom);
	                    $main->setErrorMessage("Folder not created. Folder names may not contain: '<', '>', '*', '/', '\', '|', '?' or '\"' ");
	                    $main->setHasRequiredFields(true);
	                    $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
	                    $main->render();
	                }
	
	            } else {
	                //if the user doesn't have write permission for this folder,
	                //give them only browse facilities
	                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	                $oPatternCustom->setHtml(renderBrowsePage($fFolderID));
	                $main->setCentralPayload($oPatternCustom);
	                $main->setErrorMessage("You do not have permission to create new folders in this folder");
	                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
	                $main->render();
	            }
        	} else {
	            //there are no document type assigned to this folder
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));
                $main->setCentralPayload($oPatternCustom);
                $main->setErrorMessage("You did not specify a document type.  If there are no system document types, please contact a System Administrator.");
                $main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
                $main->render();        		
        	}
        }
    } else {
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom->setHtml("");
        $main->setCentralPayload($oPatternCustom);
        $main->setErrorMessage("No folder currently selected");
        $main->render();
    }
}

?>
