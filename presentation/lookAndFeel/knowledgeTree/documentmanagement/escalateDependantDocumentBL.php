<?php
/**
 * $Id$
 *
 * Business logic for sending a reminder message to the user that was tasked with
 * creating a dependant document.
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
 * @author Michael Joseph, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("escalateDependantDocumentUI.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/email/Email.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DependantDocumentInstance.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

	$oPatternCustom = & new PatternCustom();
	$sTitle = _("Dependant Document Send Escalation Message");
	if ($fInstanceID) {
		$oDependantDocument = DependantDocumentInstance::get($fInstanceID);
		if ($oDependantDocument) {
			if ($fSendMessage) {
				$oUser = User::get($oDependantDocument->getUserID());
				if ($oUser) {			
					if ($oUser->getEmailNotification()) {
			            $oTemplateDocument = & Document::get($oDependantDocument->getTemplateDocumentID());
			            	            
                        $sMessage = "<font face=\"arial\" size=\"2\">";
						$oOriginatingUser = User::get($_SESSION["userID"]);
						$oParentDocument = Document::get($oDependantDocument->getParentDocumentID());	     
						$sMessage = $oUser->getName() . ", you have already received a request to create a new document for the document <br>" . $oParentDocument->getDisplayPath() . ".<br>" . 
									$oOriginatingUser->getName() . " has sent you a reminder message to create and upload this document :<br>";
                        if (strlen($fReminderMessage) > 0) {
                        	$sMessage .= "<br>Comments:<br>$fReminderMessage<br><br>";
                        }
						$sMessage .= generateLink("/control.php","action=dashboard","Log onto KnowledgeTree") . " and select the relevant link under the 'Dependant Documents' heading on your dashboard when you are ready to upload it.";
						if ($oTemplateDocument) {
							$sMessage .= "The document entitled " . generateLink("/control.php", "action=viewDocument&fDocumentID=" . $oTemplateDocument->getID(), $oTemplateDocument->getName()) . " " .
									     "can be used as a template";									
						}						                        
                        $sMessage .= "</font>";						
								
						$oEmail = & new Email();
						if ($oEmail->send($oUser->getEmail(), "Dependant document creation reminder message", $sMessage)) {
							//go back to the document page you were viewing
							redirect(generateControllerUrl("viewDocument", "fDocumentID=" . $oDependantDocument->getParentDocumentID() . "&fShowSection=linkedDocuments"));
						} else {
							$default->log->error("escalateDependantDocumentBL.php email sending failed");
							$oPatternCustom->setHtml(statusPage($sTitle, $sHeading, _("The escalation message could not be sent due to a system error sending the notification."), "viewDocument", "fDocumentID=" . $oDependantDocument->getParentDocumentID() . "&fShowSection=linkedDocuments"));
						}			
					} else {
						$default->log->info("escalateDependantDocumentBL.php user id (" . $oUser->getID() . ") doesn't have email notification on =" . arrayToString($oUser));
						$oPatternCustom->setHtml(statusPage($sTitle, $sHeading, _("The escalation message could not be sent because the user has disabled notification"), "viewDocument", "fDocumentID=" . $oDependantDocument->getParentDocumentID() . "&fShowSection=linkedDocuments"));
					}
				} else {
					$default->log->info("escalateDependantDocumentBL.php couldn't instantiate user object for id=$fUserID");
					$oPatternCustom->setHtml(statusPage($sTitle, "", _("The dependant document user information could not be found."), "viewDocument", "fDocumentID=" . $oDependantDocument->getParentDocumentID() . "&fShowSection=linkedDocuments"));
				}
			} else {	
				// display escalation form
				$oPatternCustom->setHtml(getPage($oDependantDocument));				
			}
		} else {
			//dependant document instantiation failed- generic error (statusPage)
			$oPatternCustom->setHtml(statusPage($sTitle, "", _("The dependant document information could not be found."), "browse"));
		}		
	} else {
		// error page, no instance id supplied- generic error
		$oPatternCustom->setHtml(statusPage($sTitle, "", _("The dependant document information could not be found."), "browse"));
	}
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction($_SERVER["PHP_SELF"]); // . "?fDocumentID=$fInstanceID&fForStore=1");
	$main->render();			
}
?>
