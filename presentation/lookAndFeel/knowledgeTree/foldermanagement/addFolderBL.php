<?php
/**
* Business logic page that provides business logic for adding a folder (uses
* addFolderUI.inc for HTML)
*
* The following form variables are exptected:
*	o $fFolderID - id of the folder the user is currently in
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 27 January 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

require_once("../../../../config/dmsDefaults.php");
if (checkSession()) {	
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
	
	$oPatternCustom = & new PatternCustom();
	
	if (isset($fFolderID)) {
		require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
		require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
		require_once("$default->owl_fs_root/lib/foldermanagement/PhysicalFolderManagement.inc");		
		require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
		require_once("$default->owl_fs_root/presentation/Html.inc");
		require_once("addFolderUI.inc");	
		
		if (!isset($fFolderName)) {
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
			//we're still browsing			
			if (Permission::userHasFolderWritePermission($fFolderID)) {
				//if the user is allowed to add folders, then display the add button
				$oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));				
			} else {
				//otherwise just let the user browse
				$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
				$main->setErrorMessage("You do not have permission to create new folders in this folder");
			}
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
			$main->render();
		} else {
			//have a folder name to store
			if (Permission::userHasFolderWritePermission($fFolderID)) {
				if (Folder::folderExistsName($fFolderName, $fFolderID)) {
					require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
					$oPatternCustom->setHtml(renderBrowseAddPage($fFolderID));
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("There is another folder named $fFolderName in this folder already");
					$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
					$main->render();
				} else {
					$oParentFolder = Folder::get($fFolderID);
					//create the folder in the db, giving it the properties of it's parent folder					
					$oFolder = &new Folder($fFolderName, "", $fFolderID, $_SESSION["userID"], $oParentFolder->getDocumentTypeID(), $oParentFolder->getUnitID());
					if ($oFolder->create()) {
						//create the folder on the file system						
						if (PhysicalFolderManagement::createFolder(Folder::getFolderPath($oFolder->getID()))) {							
							redirect("$default->owl_root_url/control.php?action=browse&fBrowseType=folder&fFolderID=" . $oFolder->getID());
						} else {
							//if we couldn't do that, remove the folder from the db and report and error
							$oFolder->delete();
							require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
							$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
							$main->setCentralPayload($oPatternCustom);
							$main->setErrorMessage("There was an error creating the folder $fFolderName on the filesystem");
							$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
							$main->render();
						}
					} else {
						//if we couldn't create the folder in the db, report an error
						require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
						$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
						$main->setCentralPayload($oPatternCustom);
						$main->setErrorMessage("There was an error creating the folder $fFolderName in the database");
						$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
						$main->render();
					}
				}
			} else {
				//if the user doesn't have write permission for this folder,
				//give them only browse facilities
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
				$oPatternCustom->setHtml(renderBrowsePage($fFolderID));
				$main->setCentralPayload($oPatternCustom);
				$main->setErrorMessage("You do not have permission to create new folders in this folder");
				$main->setFormAction("addFolderBL.php?fFolderID=$fFolderID");
				$main->render();
			}
		}
	} else {
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No folder currently selected");
		$main->render();
	}
}

?>
