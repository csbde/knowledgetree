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
if (checkSession()) {
    if (isset($fDocumentID)) {
        require_once("$default->fileSystemRoot/lib/security/Permission.inc");
        require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
        require_once("$default->fileSystemRoot/lib/email/Email.inc");
        require_once("$default->fileSystemRoot/lib/users/User.inc");
        require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
        require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
        require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
        require_once("$default->fileSystemRoot/presentation/Html.inc");
        require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
        require_once("emailUI.inc");

        //get the document to send
        $oDocument = Document::get($fDocumentID);

        //if the user can view the document, they can email a link to it
        if (Permission::userHasDocumentReadPermission($fDocumentID)) {
            if (isset($fSendEmail)) {
                //if we're going to send a mail, first make sure the to address is set
                if (isset($fToEmail)) {
                    if (validateEmailAddress($fToEmail)) {
                        //if the to address is valid, send the mail
                        global $default;
                        $oUser = User::get($_SESSION["userID"]);
                        $sMessage = "<font face=\"arial\" size=\"2\">";
                        if (isset($fToName)) {
                            $sMessage .= "$fToName,<br><br>Your colleague, " . $oUser->getName() . ", wishes you to view the document entitled '" . $oDocument->getName() . "'.\n  Click on the hyperlink below to view it.";
                        } else {
                            $sMessage .= "Your colleague, " . $oUser->getName() . ", wishes you to view the document entitled '" . $oDocument->getName() . "'.\n  Click on the hyperlink below to view it.";
                        }
                        // add the link to the document to the mail
                        $sMessage .= "<br>" . generateControllerLink("viewDocument", "fDocumentID=$fDocumentID", $oDocument->getName());

                        // add optional comment
                        if (strlen($fComment) > 0) {
                        	$sMessage .= "<br><br>Comments:<br>$fComment";
                        }
                        $sMessage .= "</font>";
                        
                        $sTitle = "Link: " . $oDocument->getName() . " from " . $oUser->getName();
                        
                        //email the hyperlink
                        $oEmail = new Email();
                        $oEmail->send($fToEmail, $sTitle, $sMessage);
                        
	                    // emailed link transaction
	                    $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document link emailed to $fToEmail", EMAIL_LINK);
	                    if ($oDocumentTransaction->create()) {
	                    	$default->log->debug("emailBL.php created email link document transaction for document ID=$fDocumentID");                                    	
	                    } else {
	                    	$default->log->error("emailBL.php couldn't create email link document transaction for document ID=$fDocumentID");
	                    }
	                    
                        //go back to the document view page
                        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
                    } else {
                        //ask the user to enter a valid email address
                        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                        require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");


                        $oPatternCustom = & new PatternCustom();
                        $oUserArray = User::getList();
                        $oPatternCustom->setHtml(getDocumentEmailPage($oDocument,$oUserArray));
                        $main->setErrorMessage("The email address you entered was invalid.  Please enter<br> " .
                                               "an email address of the form someone@somewhere.some postfix");
                        $main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");
                        $main->setCentralPayload($oPatternCustom);
                        $main->render();
                    }
                }
            } else {
                //ask for an email address
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
				
				$oUserArray = User::getList();
                $oPatternCustom = & new PatternCustom();
                $oPatternCustom->setHtml(getDocumentEmailPage($oDocument,$oUserArray));
                $main->setCentralPayload($oPatternCustom);
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fSendEmail=1");
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
