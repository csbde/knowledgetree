<?php

/**
* Business logic concerned with the deletion of a document.  
* Will use documentDeleteUI for presentation information
*
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 19 February 2003 
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/

require_once("../../../../config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/users/User.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");

require_once("$default->owl_fs_root/presentation/Html.inc");

require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteDocumentUI.inc");

if (checkSession()) {
	if (isset($fDocumentID)) {
		if (Permission::userHasDocumentWritePermission($fDocumentID)) {
			if (isset($fDeleteConfirmed)) {
				//deletion of document is confirmed
				$oDocument = Document::get($fDocumentID);
				if (isset($oDocument)) {
					$sDocumentPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();
					if ($oDocument->delete()) {
						if (unlink($sDocumentPath)) {
							//successfully deleted the document from the file system
							redirect("$default->owl_root_url/control.php?action=browse&fFolderID=" . $oDocument->getFolderID());
						} else {
							//could not delete the document from the file system
							//reverse the document deletion
							$oDocument->create();
							require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
							$oPatternCustom = & new PatternCustom();							
							$oPatternCustom->setHtml("");
							$main->setCentralPayload($oPatternCustom);
							$main->setErrorMessage("The document could not be deleted from the file system");
							$main->render();
						}
					}
				} else {
					//could not load document object
					require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
					$oPatternCustom = & new PatternCustom();							
					$oPatternCustom->setHtml("");
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("An error occured whilst retrieving the document from the database");
					$main->render();
				}
			} else {
				//get confirmation first				
				require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
				$oPatternCustom = & new PatternCustom();
				$oDocument = Document::get($fDocumentID);
				$oPatternCustom->setHtml(getPage($fDocumentID, $oDocument->getFolderID(), $oDocument->getName()));				
				$main->setCentralPayload($oPatternCustom);				
				$main->render();
			}
		} else {
			//user does not have permission to delete the document
			require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml("");
			$main->setCentralPayload($oPatternCustom);
			$main->setErrorMessage("You do not have permission to delete this document");
			$main->render();
		}
	} else {
		//no document selected for deletion
		require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");			
		$oPatternCustom = & new PatternCustom();							
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No document currently selected");
		$main->render();
	}
}
