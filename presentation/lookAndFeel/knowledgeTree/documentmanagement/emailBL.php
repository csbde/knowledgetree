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

KTUtil::extractGPC('fAttachDocument', 'fComment', 'fDocumentID', 'fEmailAddresses', 'fSendEmail', 'groupNewRight', 'userNewRight');

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
function sendGroupEmails($aGroupIDs, $oDocument, $sComment = "", $bAttachDocument) {
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
						sendEmail($aUsers[$j]->getEmail(), $aUsers[$j]->getName(), $oDocument->getID(), $oDocument->getName(), $sComment, $bAttachDocument);
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
function sendUserEmails($aUserIDs, $oDocument, $sComment = "", $bAttachDocument) {
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
					sendEmail($oDestUser->getEmail(), $oDestUser->getName(), $oDocument->getID(), $oDocument->getName(), $sComment, $bAttachDocument);
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
 * Sends emails to the manually entered email addresses
 */
function sendManualEmails($aEmailAddresses, $oDocument, $sComment = "", $bAttachDocument) {
	global $default;
	
    // loop through users
    foreach ($aEmailAddresses as $sEmailAddress) {
        $default->log->info("sendingEmail to address " .  $sEmailAddress);
        if (validateEmailAddress($sEmailAddress)) {	    
            sendEmail($sEmailAddress, $sEmailAddress, $oDocument->getID(), $oDocument->getName(), $sComment, $bAttachDocument);
        }
    }  	
}

/**
 * Constructs the email message text and sends the message
 */
function sendEmail($sDestEmailAddress, $sDestUserName, $iDocumentID, $sDocumentName, $sComment, $bAttachDocument = false) {
    if ($bAttachDocument !== true) {
        return sendEmailHyperlink($sDestEmailAddress, $sDestUserName, $iDocumentID, $sDocumentName, $sComment);
    } else {
        return sendEmailDocument($sDestEmailAddress, $sDestUserName, $iDocumentID, $sDocumentName, $sComment);
    }
}

function sendEmailDocument($sDestEmailAddress, $sDestUserName, $iDocumentID, $sDocumentName, $sComment) {
    global $default;
    global $emailerrors;
    $oSendingUser = User::get($_SESSION["userID"]);

    $sMessage = 'Your colleague, ' . $oSendingUser->getName() . ', wishes you to view the attached document entitled "' .  $sDocumentName . '".';
    $sMessage .= "\n\n";
	if (strlen($sComment) > 0) {
		$sMessage .= "<br><br>Comments:<br>$sComment";
	}
    $sTitle = "Document: " . $sDocumentName . " from " .  $oSendingUser->getName();
    $oEmail = new Email();
    $oDocument = Document::get($iDocumentID);
    $sDocumentPath = $oDocument->getPath();
    $sDocumentFileName = $oDocument->getFileName();
    $res = $oEmail->sendAttachment($sDestEmailAddress, $sTitle, $sMessage, $sDocumentPath, $sDocumentFileName);
    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $emailerrors[] = $res->getMessage();
        return $res;
    } else if ($res === false) {
        $default->log->error("Error sending email ($sTitle) to $sDestEmailAddress");
        $emailerrors[] = "Error sending email ($sTitle) to $sDestEmailAddress";
        return PEAR::raiseError("Error sending email ($sTitle) to $sDestEmailAddress");
    } else {
        $default->log->info("Send email ($sTitle) to $sDestEmailAddress");
    }

    // emailed link transaction
    $oDocumentTransaction = & new DocumentTransaction($iDocumentID, "Document link emailed to $sDestEmailAddress", EMAIL_ATTACH);
    if ($oDocumentTransaction->create()) {
        $default->log->debug("emailBL.php created email link document transaction for document ID=$iDocumentID");
    } else {
        $default->log->error("emailBL.php couldn't create email link document transaction for document ID=$iDocumentID");
    }
}

function sendEmailHyperlink($sDestEmailAddress, $sDestUserName, $iDocumentID, $sDocumentName, $sComment) {
    global $default;
    global $emailerrors;
    $oSendingUser = User::get($_SESSION["userID"]);
    
	$sMessage = "<font face=\"arial\" size=\"2\">";
    if ($sDestUserName) {
        $sMessage .= $sDestUserName . ",<br><br>";
    }
	$sMessage .= "Your colleague, " . $oSendingUser->getName() . ", wishes you to view the document entitled '" . $sDocumentName . "'.\n  ";
	$sMessage .= "Click on the hyperlink below to view it.";
	// add the link to the document to the mail
	$sMessage .= "<br>" . generateControllerLink("viewDocument", "fDocumentID=$iDocumentID", $sDocumentName);
	// add optional comment
	if (strlen($sComment) > 0) {
		$sMessage .= "<br><br>Comments:<br>$sComment";
	}
	$sMessage .= "</font>";
	$sTitle = "Link: " . $sDocumentName . " from " . $oSendingUser->getName();
	//email the hyperlink
	$oEmail = new Email();
    $res = $oEmail->send($sDestEmailAddress, $sTitle, $sMessage);
    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $emailerrors[] = $res->getMessage();
        return $res;
    } else if ($res === false) {
		$default->log->error("Error sending email ($sTitle) to $sDestEmailAddress");		
		$emailerrors[] = "Error sending email ($sTitle) to $sDestEmailAddress";
        return PEAR::raiseError("Error sending email ($sTitle) to $sDestEmailAddress");
    } else {
		$default->log->info("Send email ($sTitle) to $sDestEmailAddress");
	}
	  
	// emailed link transaction
	$oDocumentTransaction = & new DocumentTransaction($iDocumentID, "Document link emailed to $sDestEmailAddress", EMAIL_LINK);
	if ($oDocumentTransaction->create()) {
		$default->log->debug("emailBL.php created email link document transaction for document ID=$iDocumentID");                                    	
	} else {
		$default->log->error("emailBL.php couldn't create email link document transaction for document ID=$iDocumentID");
	}
}

$emailerrors = array();

if (checkSession()) {
    if (isset($fDocumentID)) {
        //get the document to send
        $oDocument = Document::get($fDocumentID);

        //if the user can view the document, they can email a link to it
        if (Permission::userHasDocumentReadPermission($oDocument)) {
            if (isset($fSendEmail)) {
	          	// explode group and user ids
	          	$aGroupIDs = explode(",", $groupNewRight);
	          	$aUserIDs = explode(",", $userNewRight);
	          	$aEmailAddresses = explode(" ", $fEmailAddresses);
	          	$default->log->info("Sending email to groups=$groupNewRight; users=$userNewRight; manual=$fEmailAddresses");
	          	
                //if we're going to send a mail, first make there is someone to send it to
                if ((count($aGroupIDs) > 1) || (count($aUserIDs) > 1) || (count($aEmailAddresses) != 0)) {
	            	// send group emails
	            	sendGroupEmails($aGroupIDs, $oDocument, $fComment, (boolean)$fAttachDocument);
	            	// send user emails
	            	sendUserEmails($aUserIDs, $oDocument, $fComment, (boolean)$fAttachDocument);
                    // send manual email addresses
	            	sendManualEmails($aEmailAddresses, $oDocument, $fComment, (boolean)$fAttachDocument);

                    if (count($emailerrors)) {
                        $_SESSION['KTErrorMessage'][] = join("<br />\n", $emailerrors);
                    }
	                    
                    //go back to the document view page
                    redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
				} else {
				    // ask the user to specify users or groups to send the mail to
				    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
				    $main->setErrorMessage(_("You must select either a group or a user to send an email link to."));
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
            $main->setErrorMessage(_("You do not have the permission to email a link to this document") . "\n");
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
