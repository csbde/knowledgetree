<?php
/**
 * $Id$
 *
 * Business logic for requesting the creation of a new document that
 * will be linked to an existing one.
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
	require_once("createDependantDocumentUI.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/email/Email.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	
	if (isset($fForStore)) {
		$oDependantDocument = & new DependantDocumentInstance($fDocumentTitle, $fUserID, $fTargetDocumentID, $fDocumentID);		
		if ($oDependantDocument->create()) {		
			$oUser = User::get($fUserID);			
			if ($oUser->getEmailNotification()) {
				//notify the user by email if they wish to be notified by email	            
	            $oTemplateDocument = & Document::get($fTargetDocumentID);	            
	            
	            
				$sBody = $oUser->getName() . ", a step in the document collaboration process requires you to create a new document.  " .
								generateLink("/control.php","action=dashboard","Log onto KnowledgeTree") . " and select the relevant link under the 'Dependant Documents' heading on your dashboard when you are ready to upload it.  ";
								//if we have a template document
				if (!($oTemplateDocument === false)) {
					$sBody .= "The document entitled " . generateLink("/control.php", "action=viewDocument&fDocumentID=" . $oTemplateDocument->getID(), $oTemplateDocument->getName()) . " " .
								"can be used as a template";									
				}
						
				$oEmail = & new Email();
				$oEmail->send($oUser->getEmail(), "Dependant document creation required", $sBody);
			}
			//go back to the document page you were viewing
			redirect($default->rootUrl . "/control.php?action=viewDocument&fDocumentID=$fDocumentID");			
		} else {
			//dependant document creation failed - display an error message
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oDocument = Document::get($fDocumentID);
		
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $fDocumentID, $fUnitID, $fUserID, $fDocumentTitle, $fTemplateDocument));			
			$main->setCentralPayload($oPatternCustom);
			if ($default->bNN4) {
				$main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");
			}			
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
			$main->setErrorMessage("An error occurred whilst trying to create the dependant document");
			$main->render(); 
		}		
	} else {
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
		//we're browsing, so just display the page	
		$oDocument = Document::get($fDocumentID);
		
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $fDocumentID, $fUnitID, $fUserID, $fDocumentTitle, $fTemplateDocument));
		if ($default->bNN4) {
			$main->setOnLoadJavaScript("disable(document.MainForm.fTargetDocument)");
		}		
		$main->setCentralPayload($oPatternCustom);
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
		$main->render();			
	}
}
?>