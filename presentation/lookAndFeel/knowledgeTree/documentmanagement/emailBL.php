<?php
/**
 * $Id$
 *
 * Business logic to email link to a document.
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
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/email/Email.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/groups/Group.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("emailUI.inc");

/**
 * Sends emails to the selected groups
 */
function sendGroupEmails($aGroupIDs, $oDocument, $sComment = "") {
	global $default;
	
    // loop through groups
    for ($i=0; $i<count($aGroupIDs); $i++) {
    	// validate the group id
    	if ($aGroupIDs[$i] > 0) {
		    $oDestGroup = Group::get($aGroupIDs[$i]);
		    $default->log->info("sendingEmail to group " . $oDestGroup->getName());
		    // for each group, retrieve all the users
		    $aUsers = $oDestGroup->getUsers();
		    // FIXME: this should send one email with multiple To: users
		    for ($j=0; $j<count($aUsers); $j++) {
	    		$default->log->info("sendingEmail to group-member " . $aUsers[$j]->getName() . " with email " . $aUsers[$j]->getEmail());	    	
			    // the user has an email address and has email notification enabled
				if (strlen($aUsers[$j]->getEmail())>0 && $aUsers[$j]->getEmailNotification()) {
					//if the to address is valid, send the mail
					if (validateEmailAddress($aUsers[$j]->getEmail())) {	    
						sendEmail($aUsers[$j]->getEmail(), $aUsers[$j]->getName(), $oDocument->getID(), $oDocument->getName(), $sComment);
					} else {
						$default->log->error("email validation failed for " . $aUsers[$j]->getEmail());
					}
				} else {
				$default->log->info("either " . $aUsers[$j]->getUserName() . " has no email address, or notification is not enabled");				
				}
		    }
    	} else {
    		$default->log->info("filtered group id=" . $aGroupIDs[$i]);
    	}
    }
}

/**
 * Sends emails to the selected users
 */
function sendUserEmails($aUserIDs, $oDocument, $sComment = "") {
	global $default;
	
    // loop through users
    for ($i=0; $i<count($aUserIDs); $i++) {
    	if ($aUserIDs[$i] > 0) {
		    $oDestUser = User::get($aUserIDs[$i]);
	    	$default->log->info("sendingEmail to user " . $oDestUser->getName() . " with email " . $oDestUser->getEmail());	    
		    // the user has an email address and has email notification enabled
			if (strlen($oDestUser->getEmail())>0 && $oDestUser->getEmailNotification()) {
				//if the to address is valid, send the mail
				if (validateEmailAddress($oDestUser->getEmail())) {	    
					sendEmail($oDestUser->getEmail(), $oDestUser->getName(), $oDocument->getID(), $oDocument->getName(), $sComment);
				}
			} else {
				$default->log->info("either " . $oDestUser->getUserName() . " has no email address, or notification is not enabled");
			}
    	} else {
    		$default->log->info("filtered user id=" . $aUserIDs[$i]);
    	}			
    }  	
}

/**
 * Constructs the email message text and sends the message
 */
function sendEmail($sDestEmailAddress, $sDestUserName, $fDocumentID, $sDocumentName, $sComment) {
    global $default;
    $oSendingUser = User::get($_SESSION["userID"]);
    
	$sMessage = "<font face=\"arial\" size=\"2\">";
	$sMessage .= $sDestUserName . ",<br><br>";
	$sMessage .= "Your colleague, " . $oSendingUser->getName() . ", wishes you to view the document entitled '" . $sDocumentName . "'.\n  ";
	$sMessage .= "Click on the hyperlink below to view it.";
	// add the link to the document to the mail
	$sMessage .= "<br>" . generateControllerLink("viewDocument", "fDocumentID=$fDocumentID", $sDocumentName);
	// add optional comment
	if (strlen($sComment) > 0) {
		$sMessage .= "<br><br>Comments:<br>$sComment";
	}
	$sMessage .= "</font>";
	$sTitle = "Link: " . $sDocumentName . " from " . $sSendingUserName;
	//email the hyperlink
	$oEmail = new Email();
	if ($oEmail->send($sDestEmailAddress, $sTitle, $sMessage)) {
		$default->log->info("Send email ($sTitle) to $sDestEmailAddress");
	} else {
		$default->log->error("Error sending email ($sTitle) to $sDestEmailAddress");		
	}
	  
	// emailed link transaction
	$oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document link emailed to $sDestEmailAddress", EMAIL_LINK);
	if ($oDocumentTransaction->create()) {
		$default->log->debug("emailBL.php created email link document transaction for document ID=$fDocumentID");                                    	
	} else {
		$default->log->error("emailBL.php couldn't create email link document transaction for document ID=$fDocumentID");
	}
}

if (checkSession()) {
    if (isset($fDocumentID)) {
        //get the document to send
        $oDocument = Document::get($fDocumentID);

        //if the user can view the document, they can email a link to it
        if (Permission::userHasDocumentReadPermission($fDocumentID)) {
            if (isset($fSendEmail)) {
	          	// explode group and user ids
	          	$aGroupIDs = explode(",", $groupNewRight);
	          	$aUserIDs = explode(",", $userNewRight);
	          	$default->log->info("Sending email to groups=$groupNewRight; users=$userNewRight");
	          	
                //if we're going to send a mail, first make there is someone to send it to
                if ((count($aGroupIDs) > 1) || (count($aUserIDs) > 1)) {
	            	// send group emails
	            	sendGroupEmails($aGroupIDs, $oDocument, $fComment);
	            	// send user emails
	            	sendUserEmails($aUserIDs, $oDocument, $fComment);
	                    
                    //go back to the document view page
                    redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
				} else {
				    // ask the user to specify users or groups to send the mail to
				    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
				    $main->setErrorMessage("You must select either a group or a user to send an email link to.");				    
				    $oPatternCustom = & new PatternCustom();
				    $oPatternCustom->setHtml(getDocumentEmailPage($oDocument));
				    $main->setCentralPayload($oPatternCustom);
	                $main->setOnLoadJavaScript("optGroup.init(document.forms[0]);optUser.init(document.forms[0]);");
	                $main->setHasRequiredFields(true);
	                $main->setAdditionalJavaScript(initialiseOptionTransferJavaScript());
				    $main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");	                
	                $main->setDHTMLScrolling(false);
				    $main->render();
				}
            } else {
                // display form
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getDocumentEmailPage($oDocument));
                $main->setCentralPayload($oPatternCustom);
                $main->setOnLoadJavaScript("optGroup.init(document.forms[0]);optUser.init(document.forms[0]);");
                $main->setHasRequiredFields(true);
                $main->setAdditionalJavaScript(initialiseOptionTransferJavaScript());
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");
                $main->setDHTMLScrolling(false);
                $main->render();
            }
        } else {
            require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
            require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
            $oPatternCustom = & new PatternCustom();
            $oPatternCustom->setHtml("");
            $main->setErrorMessage("You do not have the permission to email a link to this document\n");
            $main->setCentralPayload($oPatternCustom);
            $main->render();
        }
    }

}
function initialiseOptionTransferJavascript() {
	return "<script LANGUAGE=\"JavaScript\">\n" .
		"var optGroup = new OptionTransfer(\"groupSelect\",\"chosenGroups\");\n" .
		"optGroup.setAutoSort(true);\n" .
		"optGroup.setDelimiter(\",\");\n" .
		"optGroup.saveNewLeftOptions(\"groupNewLeft\");\n" .
		"optGroup.saveNewRightOptions(\"groupNewRight\");\n" .
		"optGroup.saveRemovedLeftOptions(\"groupRemovedLeft\");\n" .
		"optGroup.saveRemovedRightOptions(\"groupRemovedRight\");\n" .
		"optGroup.saveAddedLeftOptions(\"groupAddedLeft\");\n" .
		"optGroup.saveAddedRightOptions(\"groupAddedRight\");\n" .
					
		"var optUser = new OptionTransfer(\"userSelect\",\"chosenUsers\");\n" .
		"optUser.setAutoSort(true);\n" .
		"optUser.setDelimiter(\",\");\n" .
		"optUser.saveNewLeftOptions(\"userNewLeft\");\n" .
		"optUser.saveNewRightOptions(\"userNewRight\");\n" .
		"optUser.saveRemovedLeftOptions(\"userRemovedLeft\");\n" .
		"optUser.saveRemovedRightOptions(\"userRemovedRight\");\n" .
		"optUser.saveAddedLeftOptions(\"userAddedLeft\");\n" .	
		"optUser.saveAddedRightOptions(\"userAddedRight\");\n" .
	"</SCRIPT>";		
}
/** use regex to validate the format of the email address */
function validateEmailAddress($sEmailAddress) {
    $aEmailAddresses = array();
    if (strpos($sEmailAddress, ";")) {
        $aEmailAddresses = explode(";", $sEmailAddress);
    } else {
        $aEmailAddresses[] = $sEmailAddress;
    }
    $bToReturn = true;
    for ($i=0; $i<count($aEmailAddresses); $i++) {
        $bResult = ereg ("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $aEmailAddresses[$i] );
        $bToReturn = $bToReturn && $bResult;
    }
    return $bToReturn;
}

?>
