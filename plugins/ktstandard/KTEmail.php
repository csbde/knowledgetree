<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');

require_once(KT_LIB_DIR . '/email/Email.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_DIR . '/ktwebservice/KTDownloadManager.inc.php');

/**
 * Sends emails to the selected groups
 */
function sendGroupEmails($aGroupIDs, &$aUserEmails, &$aEmailErrors) {
	global $default;

    // loop through groups
    for ($i=0; $i<count($aGroupIDs); $i++) {
    	// validate the group id
    	if ($aGroupIDs[$i] > 0) {
		    $oDestGroup = Group::get($aGroupIDs[$i]);

		    $aMemberGroups = $oDestGroup->getMemberGroups();
		    foreach ($aMemberGroups as $member){
		    	$aDestinationGroups[] = $member;
		    }
		    $aDestinationGroups[] = $oDestGroup;

		    $default->log->info('sendingEmail to group ' . $oDestGroup->getName());
		    // for each group, retrieve all the users
		    foreach($aDestinationGroups as $oGroup){
                // Need to only retrieve users that are not diabled.
		    	$aUsers = kt_array_merge($aUsers, $oGroup->getUsers());
		    }

		    // FIXME: this should send one email with multiple To: users
		    // The FIX (26-09-2007): create an array of users to email
		    for ($j=0; $j<count($aUsers); $j++) {
	    		$default->log->info('sendingEmail to group-member ' . $aUsers[$j]->getName() . ' with email ' . $aUsers[$j]->getEmail());
			    // the user has an email address and has email notification enabled
				if (strlen($aUsers[$j]->getEmail())>0 && $aUsers[$j]->getEmailNotification()) {
					//if the to address is valid, send the mail
					if (validateEmailAddress($aUsers[$j]->getEmail())) {
					   // use the email address as the index to ensure the user is only sent 1 email
					   $aUserEmails[$aUsers[$j]->getEmail()] = $aUsers[$j]->getName();
					} else {
						$default->log->error('email validation failed for ' . $aUsers[$j]->getEmail());
					}
				} else {
				$default->log->info('either ' . $aUsers[$j]->getUserName() . ' has no email address, or notification is not enabled');
				}
		    }
    	} else {
    		$default->log->info('filtered group id=' . $aGroupIDs[$i]);
    	}
    }
}

/**
 * Sends emails to the selected users
 */
function sendUserEmails($aUserIDs, &$aUserEmails, &$aEmailErrors) {
	global $default;

    // loop through users
    for ($i=0; $i<count($aUserIDs); $i++) {
    	if ($aUserIDs[$i] > 0) {
		    $oDestUser = User::get($aUserIDs[$i]);
	    	$default->log->info('sendingEmail to user ' . $oDestUser->getName() . ' with email ' . $oDestUser->getEmail());
		    // the user has an email address and has email notification enabled
			if (strlen($oDestUser->getEmail())>0 && $oDestUser->getEmailNotification()) {
				//if the to address is valid, send the mail
				if (validateEmailAddress($oDestUser->getEmail())) {
				    // use the email address as the index to ensure the user is only sent 1 email
				    $aUserEmails[$oDestUser->getEmail()] = $oDestUser->getName();
				}
			} else {
				$default->log->info('either ' . $oDestUser->getUserName() . ' has no email address, or notification is not enabled');
			}
    	} else {
    		$default->log->info('filtered user id=' . $aUserIDs[$i]);
    	}
    }
}

/**
 * Sends emails to the manually entered email addresses
 */
function sendManualEmails($aEmailAddresses, &$aUserEmails, &$aEmailErrors) {
	global $default;

    // loop through users
    foreach ($aEmailAddresses as $sEmailAddress) {
        $default->log->info('sendingEmail to address ' .  $sEmailAddress);
        if (validateEmailAddress($sEmailAddress)) {
            // use the email address as the index to ensure the user is only sent 1 email
            $aUserEmails[$sEmailAddress] = '';
        }
    }
}

function sendExternalEmails($aEmailAddresses, $iDocumentID, $sDocumentName, $sComment, &$aEmailErrors){
    global $default;
    $oSendingUser = User::get($_SESSION['userID']);

    // Create email content
/*
    $sMessage = '<font face="arial" size="2">';
	$sMessage .= sprintf(_kt("Your colleague, %s, wishes you to view the document entitled '%s'."), $oSendingUser->getName(), $sDocumentName);
	$sMessage .= " \n";
	$sMessage .= _kt('Click on the hyperlink below to view it.') . '<br><br>';
    $sMsgEnd = '<br><br>' . _kt('Comments') . ':<br>' . $sComment;
	$sMsgEnd .= '</font>';

	$sTitle = sprintf(_kt("Link (ID %s): %s from %s"), $iDocumentID, $sDocumentName, $oSendingUser->getName());
*/
	$sTitle = sprintf(_kt("%s wants to share a document using KnowledgeTree"), $oSendingUser->getName());

	$sMessage = '<br>
	               &#160;&#160;&#160;&#160;'._kt('Hello').',
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;'.sprintf(_kt('A KnowledgeTree user, %s, wants to share a document with you entitled "%s".'), $oSendingUser->getName(), $sDocumentName).'
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;<b>'._kt('Message').':</b>
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;'.$sComment.'
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;'._kt('<b>KnowledgeTree is easy to use open source document management software</b><br />&#160;&#160;&#160;&#160;that helps businesses collaborate, securely store all critical documents, address<br />&#160;&#160;&#160;&#160;compliance challenges, and improve business processes.').'
	               <br />
	               <br />';

	$sEmail = null;
    $sEmailFrom = null;
    $oConfig =& KTConfig::getSingleton();
    if (!$oConfig->get('email/sendAsSystem')) {
        $sEmail = $oSendingUser->getEmail();
        $sEmailFrom = $oSendingUser->getName();
    }

    $iCounter = 0;
    foreach ($aEmailAddresses as $sAddress){
        $oEmail = new Email($sEmail, $sEmailFrom);
        if(validateEmailAddress($sAddress)){
            // Add to list of addresses
            $sDestEmails .= (empty($sDestEmails)) ? $sAddress : ', '.$sAddress;

            // Create uniqueish temporary session id
            $session = 'ktext_'.$iDocumentID.time().$iCounter++;

            // Create download link
            $oDownloadManager = new KTDownloadManager();
            $oDownloadManager->set_session($session);
            $link = $oDownloadManager->allow_download($iDocumentID);

//            $link = "<a href=\"{$link}\">{$link}</a>";
            $links = '&#160;&#160;&#160;&#160;<a href="http://www.knowledgetree.com/products">'._kt('Learn More').'</a>';
            $links.= "&#160;|&#160;<a href=\"{$link}\">"._kt('View Document')."</a>";
            $links .= '&#160;|&#160;<a href="http://www.knowledgetree.com/node/39">'._kt('Download Free Trial').'</a><br /><br />';

//            $sMsg = $sMessage.$link.$sMsgEnd;
            $sMsg = $sMessage.$links;
            $res = $oEmail->send(array($sAddress), $sTitle, $sMsg);

            if (PEAR::isError($res)) {
                $default->log->error($res->getMessage());
                $aEmailErrors[] = $res->getMessage();
            } else if ($res === false) {
        		$default->log->error("Error sending email ($sTitle) to $sAddress");
        		$aEmailErrors[] = "Error sending email ($sTitle) to $sAddress";
            }
        }
        $default->log->info("Send email ($sTitle) to external addresses $sDestEmails");

    	// emailed link transaction
    	// need a document to do this.
    	$oDocument =& Document::get($iDocumentID);

        $oDocumentTransaction = new DocumentTransaction($oDocument, sprintf(_kt("Document link emailed to external addresses %s. "), $sDestEmails).$sComment, 'ktcore.transactions.email_link');

        if ($oDocumentTransaction->create()) {
    		$default->log->debug("emailBL.php created email link document transaction for document ID=$iDocumentID");
    	} else {
    		$default->log->error("emailBL.php couldn't create email link document transaction for document ID=$iDocumentID");
    	}
    }
}

/**
 * Constructs the email message text and sends the message
 */
function sendEmail($aDestEmailAddress, $iDocumentID, $sDocumentName, $sComment, $bAttachDocument = false, &$aEmailErrors) {
    if ($bAttachDocument !== true) {
        return sendEmailHyperlink($aDestEmailAddress, $iDocumentID, $sDocumentName, $sComment, $aEmailErrors);
    } else {
        return sendEmailDocument($aDestEmailAddress, $iDocumentID, $sDocumentName, $sComment, $aEmailErrors);
    }
}

function sendEmailDocument($aDestEmailAddress, $iDocumentID, $sDocumentName, $sComment, &$aEmailErrors) {
    global $default;
    // Get the email list as a string for the logs
    $sDestEmails = implode(',', $aDestEmailAddress);
    $oSendingUser = User::get($_SESSION['userID']);

    $sMessage .= sprintf(_kt("Your colleague, %s, wishes you to view the attached document entitled '%s'."), $oSendingUser->getName(), $sDocumentName);
    $sMessage .= "\n\n";
	$sMessage .= _kt('Click on the hyperlink below to view it.') . '<br>';
	// add the link to the document to the mail
	$sMessage .= '<br>' . generateControllerLink('viewDocument', "fDocumentID=$iDocumentID", $sDocumentName, true);
	// add additional comment
	if (strlen($sComment) > 0) {
		$sMessage .= '<br><br>' . _kt('Comments') . ':<br>' . nl2br($sComment);
	}
    $sTitle = sprintf(_kt("Document (ID %s): %s from %s"), $iDocumentID, $sDocumentName, $oSendingUser->getName());

    $sEmail = null;
    $sEmailFrom = null;
    $oConfig =& KTConfig::getSingleton();
    if (!$oConfig->get('email/sendAsSystem')) {
        $sEmail = $oSendingUser->getEmail();
        $sEmailFrom = $oSendingUser->getName();
    }
    $oEmail = new Email($sEmail, $sEmailFrom);
    $oDocument = Document::get($iDocumentID);

    // Request a standard file path so that it can be attached to the
    // email
    $oStorage =& KTStorageManagerUtil::getSingleton();
    $sDocumentPath = $oStorage->temporaryFile($oDocument);

    $sDocumentFileName = $oDocument->getFileName();
    $res = $oEmail->sendAttachment($aDestEmailAddress, $sTitle, $sMessage, $sDocumentPath, $sDocumentFileName);

    // Tell the storage we don't need the temporary file anymore.
    $oStorage->freeTemporaryFile($sDocumentPath);

    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $aEmailErrors[] = $res->getMessage();
        return $res;
    } else if ($res === false) {
        $default->log->error("Error sending email ($sTitle) to $sDestEmails");
        $aEmailErrors[] = "Error sending email ($sTitle) to $sDestEmails";
        return PEAR::raiseError(sprintf(_kt("Error sending email (%s) to %s"), $sTitle, $sDestEmails));
    } else {
        $default->log->info("Send email ($sTitle) to $sDestEmails");
    }

    // emailed link transaction
	$oDocumentTransaction = new DocumentTransaction($oDocument, sprintf(_kt("Document copy emailed to %s. "), $sDestEmails).$sComment, 'ktcore.transactions.email_attachment');
    if ($oDocumentTransaction->create()) {
        $default->log->debug("emailBL.php created email link document transaction for document ID=$iDocumentID");
    } else {
        $default->log->error("emailBL.php couldn't create email link document transaction for document ID=$iDocumentID");
    }
}

function sendEmailHyperlink($aDestEmailAddress, $iDocumentID, $sDocumentName, $sComment, &$aEmailErrors) {
    global $default;
    // Get the email list as a string for the logs
    $sDestEmails = implode(',', $aDestEmailAddress);
    $oSendingUser = User::get($_SESSION['userID']);

	$sMessage = '<font face="arial" size="2">';
    /*
    if ($sDestUserName) {
        $sMessage .= $sDestUserName . ',<br><br>';
    }
    */
	$sMessage .= sprintf(_kt("Your colleague, %s, wishes you to view the document entitled '%s'."), $oSendingUser->getName(), $sDocumentName);
	$sMessage .= " \n";
	$sMessage .= _kt('Click on the hyperlink below to view it.') . '<br>';
	// add the link to the document to the mail
	$sMessage .= '<br>' . generateControllerLink('viewDocument', "fDocumentID=$iDocumentID", $sDocumentName, true);
	// add optional comment
	if (strlen($sComment) > 0) {
		$sMessage .= '<br><br>' . _kt('Comments') . ':<br>' . nl2br($sComment);
	}
	$sMessage .= '</font>';
	$sTitle = sprintf(_kt("Link (ID %s): %s from %s"), $iDocumentID, $sDocumentName, $oSendingUser->getName());
	//email the hyperlink
    //
    $sEmail = null;
    $sEmailFrom = null;
    $oConfig =& KTConfig::getSingleton();
    if (!$oConfig->get('email/sendAsSystem')) {
        $sEmail = $oSendingUser->getEmail();
        $sEmailFrom = $oSendingUser->getName();
    }
    $oEmail = new Email($sEmail, $sEmailFrom);

    $res = $oEmail->send($aDestEmailAddress, $sTitle, $sMessage);
    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $aEmailErrors[] = $res->getMessage();
        return $res;
    } else if ($res === false) {
		$default->log->error("Error sending email ($sTitle) to $sDestEmails");
		$aEmailErrors[] = "Error sending email ($sTitle) to $sDestEmails";
        return PEAR::raiseError(sprintf(_kt("Error sending email (%s) to %s"), $sTitle, $sDestEmails));
    } else {
		$default->log->info("Send email ($sTitle) to $sDestEmails");
	}

	// emailed link transaction
	// need a document to do this.
	$oDocument =& Document::get($iDocumentID);

    $oDocumentTransaction = new DocumentTransaction($oDocument, sprintf(_kt("Document link emailed to %s. "), $sDestEmails).$sComment, 'ktcore.transactions.email_link');

    if ($oDocumentTransaction->create()) {
		$default->log->debug("emailBL.php created email link document transaction for document ID=$iDocumentID");
	} else {
		$default->log->error("emailBL.php couldn't create email link document transaction for document ID=$iDocumentID");
	}
}

function validateEmailAddress($sEmailAddress) {
    $aEmailAddresses = array();
    if (strpos($sEmailAddress, ';')) {
        $aEmailAddresses = explode(';', $sEmailAddress);
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

class KTDocumentEmailAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.email';

    function getDisplayName() {
        return _kt('Email');
    }

    function getInfo() {
        $oConfig =& KTConfig::getSingleton();
        $sEmailServer = $oConfig->get('email/emailServer');
        if ($sEmailServer == 'none') {
            return null;
        }
        if (empty($sEmailServer)) {
            return null;
        }

        return parent::getInfo();
    }

    function do_main() {
        $oConfig = KTConfig::getSingleton();
        $bAttachment = $oConfig->get('email/allowAttachment', false);
        $bEmailAddresses = $oConfig->get('email/allowEmailAddresses', false);
        $bOnlyOwnGroup = $oConfig->get('email/onlyOwnGroups', false);


        $fields = array();

    	$fields[] = new KTJSONLookupWidget(_kt('Groups'), '',
    					      'groups', '', $this->oPage, false, null, null,
    					      array('action'=>sprintf('getGroups&fDocumentId=%d', $this->oDocument->getId()),
    						    'assigned' => array(),
    						    'multi'=>'true',
    						    'size'=>'8'));

    	$fields[] = new KTJSONLookupWidget(_kt('Users'), '',
    					      'users', '', $this->oPage, false, null, null,
    					      array('action'=>sprintf('getUsers&fDocumentId=%d', $this->oDocument->getId()),
    						    'assigned' => array(),
    						    'multi'=>'true',
    						    'size'=>'8'));


        if ($bAttachment) {
            $fields[] = new KTCheckboxWidget(_kt('Attach document'),
					     _kt('By default, documents are sent as links into the document management system.  Select this option if you want the document contents to be sent as an attachment in the email.'),
					     'fAttachDocument', null, $this->oPage);
        }
        if ($bEmailAddresses) {
            $fields[] = new KTTextWidget(_kt('Email addresses'),
					 _kt('Documents can be emailed to external users by entering their email addresses below'),
					 'fEmailAddresses', '', $this->oPage,
					 false, null, null, array('cols' => 60, 'rows' => 5));
        }

        $fields[] = new KTTextWidget(_kt('Comment'),
				     _kt('A message for those who receive the document'),
				     'fComment', '', $this->oPage,
				     true, null, null, array('cols' => 60, 'rows' => 5));


        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/email');
        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'groups' => $aGroups,
            'users' => $aUsers,
        );
        return $oTemplate->render($aTemplateData);
    }

    function json_getGroups() {
        $oConfig = KTConfig::getSingleton();
        $bOnlyOwnGroup = $oConfig->get('email/onlyOwnGroups', false);
        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        $aGroupList = array('off'=> _kt('-- Please filter --'));

        if($sFilter && trim($sFilter)) {
            $sWhere = sprintf('name LIKE "%%%s%%"', $sFilter);
            if ($bOnlyOwnGroup != true) {
                $aGroups = Group::getList($sWhere);
            } else {
                $aGroups = GroupUtil::listGroupsForUser($this->oUser, array('where' => $sWhere));
            }
            $aGroupList = array();
            foreach($aGroups as $g) {
                $aGroupList[$g->getId()] = $g->getName();
            }
        }

	return $aGroupList;
    }


    function json_getUsers() {
        $oConfig = KTConfig::getSingleton();
        $bOnlyOwnGroup = $oConfig->get('email/onlyOwnGroups', false);

        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        $aUserList = array('off' => _kt('-- Please filter --'));

        if($sFilter && trim($sFilter)) {
            $sWhere = sprintf('name LIKE \'%%%s%%\' AND disabled = \'0\'', $sFilter);
            if ($bOnlyOwnGroup != true) {
                $aUsers = User::getEmailUsers($sWhere);
            } else {
                $aGroups = GroupUtil::listGroupsForUser($this->oUser);
                $aMembers = array();
                foreach ($aGroups as $oGroup) {
                    $aMembers = kt_array_merge($aMembers, $oGroup->getMembers());
                }
                $aUsers = array();
                $aUserIds = array();
                foreach ($aMembers as $oUser) {
                    if (in_array($oUser->getId(), $aUserIds)) {
                        continue;
                    }
                    $aUsers[] = $oUser;
                }
            }

            $aUserList = array();
            foreach($aUsers as $u) {
                $aUserList[$u->getId()] = $u->getName();
            }
        }

        return $aUserList;
    }


    function do_email() {
        $groupNewRight = trim($_REQUEST['groups_items_added'], chr(160));

        $userNewRight = trim($_REQUEST['users_items_added'], chr(160));

        $fEmailAddresses = trim($_REQUEST['fEmailAddresses']);
        $fAttachDocument = $_REQUEST['fAttachDocument'];
        $fComment = $this->oValidator->validateString($_REQUEST['fComment'],
			array('redirect_to'=>array('', sprintf('fDocumentId=%d', $this->oDocument->getId()))));

        // explode group and user ids
        $aGroupIDs = array();
        $aUserIDs = array();
        $aEmailAddresses = array();
        if (!empty($groupNewRight)) {
            $aGroupIDs = explode(',', $groupNewRight);
        }
        if (!empty($userNewRight)) {
            $aUserIDs = explode(',', $userNewRight);
        }
        if (!empty($fEmailAddresses)) {
            $aAddresses = explode("\n", $fEmailAddresses);
            foreach ($aAddresses as $item){
                $aItems = explode(' ', $item);
                $aEmailAddresses = array_merge($aEmailAddresses, $aItems);
            }
        }

        $oConfig = KTConfig::getSingleton();
        $bAttachment = $oConfig->get('email/allowAttachment', false);
        $bEmailAddresses = $oConfig->get('email/allowEmailAddresses', false);

        if (empty($bAttachment)) {
            $fAttachDocument = false;
        }
        if (empty($bEmailAddresses)) {
            $aEmailAddresses = array();
        }


        //if we're going to send a mail, first make there is someone to send it to
        if ((count($aGroupIDs) == 0) && (count($aUserIDs) == 0) && (count($aEmailAddresses) == 0)) {
            $this->errorRedirectToMain(_kt('No recipients set'), sprintf('fDocumentId=%d', $this->oDocument->getId()));
            exit(0);
        }

        $iDocumentID = $this->oDocument->getID();
        $sDocumentName = $this->oDocument->getName();

        $aEmailErrors = array();
        $aUserEmails = array();
        // send group emails
        sendGroupEmails($aGroupIDs, $aUserEmails, $aEmailErrors);
        // send user emails
        sendUserEmails($aUserIDs, $aUserEmails, $aEmailErrors);
        // send manual/external email addresses
        if((boolean)$fAttachDocument){
            sendManualEmails($aEmailAddresses, $aUserEmails, $aEmailErrors);
        }else{
            sendExternalEmails($aEmailAddresses, $iDocumentID, $sDocumentName, $fComment, $aEmailErrors);
        }

        // get list of email addresses and send
        if(!empty($aUserEmails)){
            // email addresses are in the keys -> extract the keys
            $aListEmails = array_keys($aUserEmails);
            sendEmail($aListEmails, $iDocumentID, $sDocumentName, $fComment, (boolean)$fAttachDocument, $aEmailErrors);
        }
        // Display success or error, not both
        if (count($aEmailErrors)) {
            $_SESSION['KTErrorMessage'][] = join('<br />\n', $aEmailErrors);
        } else {
            $_SESSION['KTInfoMessage'][] = _kt('Email sent');
        }
        //go back to the document view page
        controllerRedirect('viewDocument', sprintf("fDocumentId=%d", $this->oDocument->getId()));
    }
}

class KTEmailPlugin extends KTPlugin {
    var $sNamespace = 'ktstandard.email.plugin';
    var $autoRegister = true;

	function KTEmailPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Email Plugin');
        return $res;
    }

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentEmailAction', 'ktcore.actions.document.email');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTEmailPlugin', 'ktstandard.email.plugin', __FILE__);

