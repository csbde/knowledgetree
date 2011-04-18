<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
function sendGroupEmails($groupIds, &$userEmails, &$emailErrors)
{
    global $default;

    // loop through groups
    for ($i = 0; $i < count($groupIds); ++$i) {
        // validate the group id
        if ($groupIds[$i] > 0) {
            $destGroup = Group::get($groupIds[$i]);

            $memberGroups = $destGroup->getMemberGroups();
            foreach ($memberGroups as $member) {
                $destinationGroups[] = $member;
            }
            $destinationGroups[] = $destGroup;

            $default->log->info('sendingEmail to group ' . $destGroup->getName());

            // for each group, retrieve all the users
            foreach ($destinationGroups as $group) {
                // Need to only retrieve users that are not diabled.
                $users = kt_array_merge($users, $group->getUsers());
            }

            // FIXME: this should send one email with multiple To: users
            // The FIX (26-09-2007): create an array of users to email
            for ($j = 0; $j < count($users); ++$j) {
                $default->log->info('sendingEmail to group-member ' . $users[$j]->getName() . ' with email ' . $users[$j]->getEmail());
                // the user has an email address and has email notification enabled
                if (strlen($users[$j]->getEmail()) > 0 && $users[$j]->getEmailNotification()) {
                    //if the to address is valid, send the mail
                    if (validateEmailAddress($users[$j]->getEmail())) {
                        // use the email address as the index to ensure the user is only sent 1 email
                        $userEmails[$users[$j]->getEmail()] = $users[$j]->getName();
                    }
                    else {
                        $default->log->error('email validation failed for ' . $users[$j]->getEmail());
                    }
                }
                else {
                    $default->log->info('either ' . $users[$j]->getUserName() . ' has no email address, or notification is not enabled');
                }
            }
        }
        else {
            $default->log->info('filtered group id=' . $groupIds[$i]);
        }
    }
}

/**
 * Sends emails to the selected users
 */
function sendUserEmails($userIds, &$userEmails, &$emailErrors)
{
    global $default;

    // loop through users
    for ($i = 0; $i < count($userIds); ++$i) {
        if ($userIds[$i] > 0) {
            $destUser = User::get($userIds[$i]);
            $default->log->info('sendingEmail to user ' . $destUser->getName() . ' with email ' . $destUser->getEmail());
            // the user has an email address and has email notification enabled
            if (strlen($destUser->getEmail()) > 0 && $destUser->getEmailNotification()) {
                //if the to address is valid, send the mail
                if (validateEmailAddress($destUser->getEmail())) {
                    // use the email address as the index to ensure the user is only sent 1 email
                    $userEmails[$destUser->getEmail()] = $destUser->getName();
                }
            }
            else {
                $default->log->info('either ' . $destUser->getUserName() . ' has no email address, or notification is not enabled');
            }
        }
        else {
            $default->log->info('filtered user id=' . $userIds[$i]);
        }
    }
}

/**
 * Sends emails to the manually entered email addresses
 */
function sendManualEmails($emailAddresses, &$userEmails, &$emailErrors)
{
    global $default;

    // loop through users
    foreach ($emailAddresses as $emailAddress) {
        $default->log->info('sendingEmail to address ' .  $emailAddress);
        if (validateEmailAddress($emailAddress)) {
            // use the email address as the index to ensure the user is only sent 1 email
            $userEmails[$emailAddress] = '';
        }
    }
}

function sendExternalEmails($emailAddressList, $documentId, $documentName, $comment, &$emailErrors)
{
    global $default;
    $sendingUser = User::get($_SESSION['userID']);

    // Create email content
    /*
    $message = '<font face="arial" size="2">';
    $message .= sprintf(_kt("Your colleague, %s, wishes you to view the document entitled '%s'."), $sendingUser->getName(), $documentName);
    $message .= " \n";
    $message .= _kt('Click on the hyperlink below to view it.') . '<br><br>';
    $msgEnd = '<br><br>' . _kt('Comments') . ':<br>' . $comment;
    $msgEnd .= '</font>';

    $title = sprintf(_kt("Link (ID %s): %s from %s"), $documentId, $documentName, $sendingUser->getName());
    */
    $title = sprintf(_kt("%s wants to share a document using KnowledgeTree"), $sendingUser->getName());

    $message = '<br>
	               &#160;&#160;&#160;&#160;' . _kt('Hello') . ',
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;' . sprintf(_kt('A KnowledgeTree user, %s, wants to share a document with you entitled "%s".'), $sendingUser->getName(), $documentName).'
	               <br />
	               <br />';
	if (strlen(trim($comment)) > 1) {
            $message .= '&#160;&#160;&#160;&#160;<b>'._kt('Message').':</b>
	               <br />
	               <br />
	               &#160;&#160;&#160;&#160;' . $comment . '
	               <br />
	               <br />';
	}
    $message .= '&#160;&#160;&#160;&#160;'._kt('<b>KnowledgeTree is easy to use open source document management software</b><br />&#160;&#160;&#160;&#160;that helps businesses collaborate, securely store all critical documents, address<br />&#160;&#160;&#160;&#160;compliance challenges, and improve business processes.').'
	               <br />
	               <br />';

    $emailFromAddress = null;
    $emailFrom = null;
    $config =& KTConfig::getSingleton();
    if (!$config->get('email/sendAsSystem')) {
        $emailFromAddress = $sendingUser->getEmail();
        $emailFrom = $sendingUser->getName();
    }

    $counter = 0;
    foreach ($emailAddressList as $address) {
        $mailer = new Email($emailFromAddress, $emailFrom);
        if (validateEmailAddress($address)) {
            // Add to list of addresses
            $destEmails .= (empty($destEmails)) ? $address : ', ' . $address;

            // Create uniqueish temporary session id
            $session = 'ktext_' . $documentId . time() . $counter++;

            // Create download link
            $downloadManager = new KTDownloadManager();
            $downloadManager->set_session($session);
            $link = $downloadManager->allow_download($documentId);

            //            $link = "<a href=\"{$link}\">{$link}</a>";
            $links = '&#160;&#160;&#160;&#160;<a href="http://www.knowledgetree.com/products">' . _kt('Learn More') . '</a>';
            $links.= "&#160;|&#160;<a href=\"{$link}\">" . _kt('View Document') . "</a>";
            $links .= '&#160;|&#160;<a href="https://www.knowledgetree.com/free-trial">' . _kt('Sign Up for a Free Trial') . '</a><br /><br />';

            //            $msg = $message . $link . $msgEnd;
            $msg = $message . $links;
            $res = $mailer->send(array($address), $title, $msg);

            if (PEAR::isError($res)) {
                $default->log->error($res->getMessage());
                $emailErrors[] = $res->getMessage();
            }
            else if ($res === false) {
                $default->log->error("Error sending email ($title) to $address");
                $emailErrors[] = "Error sending email ($title) to $address";
            }
        }

        $default->log->info("Send email ($title) to external addresses $destEmails");

        // emailed link transaction
        // need a document to do this.
        $document =& Document::get($documentId);

        $documentTransaction = new DocumentTransaction($document, sprintf(_kt("Document link emailed to external addresses %s. "), $destEmails) . $comment, 'ktcore.transactions.email_link');

        if ($documentTransaction->create()) {
            $default->log->debug("emailBL.php created email link document transaction for document ID=$documentId");
        }
        else {
            $default->log->error("emailBL.php couldn't create email link document transaction for document ID=$documentId");
        }
    }
}

/**
 * Constructs the email message text and sends the message
 */
function sendEmail($destEmailAddress, $documentId, $documentName, $comment, $attachDocument = false, &$emailErrors)
{
    if ($attachDocument !== true) {
        return sendEmailHyperlink($destEmailAddress, $documentId, $documentName, $comment, $emailErrors);
    }
    else {
        return sendEmailDocument($destEmailAddress, $documentId, $documentName, $comment, $emailErrors);
    }
}

function sendEmailDocument($destEmailAddress, $documentId, $documentName, $comment, &$emailErrors)
{
    global $default;

    $storageManager = KTStorageManagerUtil::getSingleton();
    // Get the email list as a string for the logs
    $destEmails = implode(',', $destEmailAddress);
    $sendingUser = User::get($_SESSION['userID']);

    $message .= sprintf(_kt("Your colleague, %s, wishes you to view the attached document entitled '%s'."), $sendingUser->getName(), $documentName);
    $message .= "\n\n";
	$message .= _kt('Click on the hyperlink below to view it.') . '<br>';
	// add the link to the document to the mail
	$message .= '<br>' . generateControllerLink('viewDocument', "fDocumentID=$documentId", $documentName, true);
	// add additional comment
	if (strlen(trim($comment)) > 1) {
		$message .= '<br><br><b>' . _kt('Message') . ':</b><br><br>' . nl2br($comment);
	}
    $title = sprintf(_kt("Document (ID %s): %s from %s"), $documentId, $documentName, $sendingUser->getName());

    $emailFromAddress = null;
    $emailFrom = null;
    $oConfig =& KTConfig::getSingleton();
    if (!$oConfig->get('email/sendAsSystem')) {
        $emailFromAddress = $sendingUser->getEmail();
        $emailFrom = $sendingUser->getName();
    }

    $mailer = new Email($emailFromAddress, $emailFrom);
    $document = Document::get($documentId);

    // Request a standard file path so that it can be attached to the
    // email
    $documentPath = $storageManager->createTemporaryFile($document);

    $documentFileName = $document->getFileName();
    $res = $mailer->sendAttachment($destEmailAddress, $title, $message, $documentPath, $documentFileName);

    // Tell the storage we don't need the temporary file anymore.
    $storageManager->deleteTemporaryFile($documentPath);

    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $emailErrors[] = $res->getMessage();
        return $res;
    }
    else if ($res === false) {
        $default->log->error("Error sending email ($title) to $destEmails");
        $emailErrors[] = "Error sending email ($title) to $destEmails";
        return PEAR::raiseError(sprintf(_kt("Error sending email (%s) to %s"), $title, $destEmails));
    }
    else {
        $default->log->info("Send email ($title) to $destEmails");
    }

    // emailed link transaction
    $documentTransaction = new DocumentTransaction($document, sprintf(_kt("Document copy emailed to %s. "), $destEmails) . $comment, 'ktcore.transactions.email_attachment');
    if ($documentTransaction->create()) {
        $default->log->debug("emailBL.php created email link document transaction for document ID=$documentId");
    }
    else {
        $default->log->error("emailBL.php couldn't create email link document transaction for document ID=$documentId");
    }
}

function sendEmailHyperlink($destEmailAddress, $documentId, $documentName, $comment, &$emailErrors)
{
    global $default;
    // Get the email list as a string for the logs
    $destEmails = implode(',', $destEmailAddress);
	$sendingUser = User::get($_SESSION['userID']);

    $message = '<font face="arial" size="2">';
    /*
    if ($sDestUserName) {
    $message .= $sDestUserName . ',<br><br>';
    }
    */
	$message .= sprintf(_kt("Your colleague, %s, wishes you to view the document entitled '%s'."), $sendingUser->getName(), $documentName);
	$message .= " \n";
	$message .= _kt('Click on the hyperlink below to view it.') . '<br>';
	// add the link to the document to the mail
	$message .= '<br>' . generateControllerLink('viewDocument', "fDocumentID=$documentId", $documentName, true);
	// add optional comment
	if (strlen(trim($comment)) > 1) {
		$message .= '<br><br><b>' . _kt('Message') . ':</b><br><br>' . nl2br($comment);
	}
	$message .= '</font>';
	$title = sprintf(_kt("Link (ID %s): %s from %s"), $documentId, $documentName, $sendingUser->getName());

	$emailFromAddress = null;
    $emailFrom = null;
    $config =& KTConfig::getSingleton();

    if (!$config->get('email/sendAsSystem')) {
        $emailFromAddress = $sendingUser->getEmail();
        $emailFrom = $sendingUser->getName();
    }

    $mailer = new Email($emailFromAddress, $emailFrom);

    $res = $mailer->send($destEmailAddress, $title, $message);
    if (PEAR::isError($res)) {
        $default->log->error($res->getMessage());
        $emailErrors[] = $res->getMessage();
        return $res;
    }
    else if ($res === false) {
        $default->log->error("Error sending email ($title) to $destEmails");
        $emailErrors[] = "Error sending email ($title) to $destEmails";
        return PEAR::raiseError(sprintf(_kt("Error sending email (%s) to %s"), $title, $destEmails));
    }
    else {
        $default->log->info("Send email ($title) to $destEmails");
    }

    // emailed link transaction
    // need a document to do this.
    $document =& Document::get($documentId);

    $documentTransaction = new DocumentTransaction($document, sprintf(_kt("Document link emailed to %s. "), $destEmails) . $comment, 'ktcore.transactions.email_link');

    if ($documentTransaction->create()) {
        $default->log->debug("emailBL.php created email link document transaction for document ID=$documentId");
    }
    else {
        $default->log->error("emailBL.php couldn't create email link document transaction for document ID=$documentId");
    }
}

function validateEmailAddress($emailAddress)
{
    $emailAddressList = array();
    if (strpos($emailAddress, ';')) {
        $emailAddressList = explode(';', $emailAddress);
    }
    else {
        $emailAddressList[] = $emailAddress;
    }

    $toReturn = true;
    for ($i = 0; $i < count($emailAddressList); ++$i) {
        $result = ereg("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $emailAddressList[$i]);
        $toReturn = $toReturn && $result;
    }

    return $toReturn;
}


class KTDocumentEmailAction extends KTDocumentAction {

    var $sName = 'ktcore.actions.document.email';
    var $sIconClass = 'email';
    var $sParentBtn = 'ktcore.actions.document.sharecontent';

    function getDisplayName() { return _kt('Email'); }

    function getInfo()
    {
        $config =& KTConfig::getSingleton();
        $emailServer = $config->get('email/emailServer');
        if ($emailServer == 'none' || empty($emailServer)) {
            return null;
        }

        return parent::getInfo();
    }

    function do_main()
    {
        $config = KTConfig::getSingleton();
        $allowAttachment = $config->get('email/allowAttachment', false);
        $allowEmailAddresses = $config->get('email/allowEmailAddresses', false);
        $onlyOwnGroups = $config->get('email/onlyOwnGroups', false);

        $fields = array();

        $options = array('selection_default' => 'Select group', 'optgroups' => false);
        $label['header'] = 'Groups';
        $label['text'] = 'Select other groups or users to include in this email';
        $fields[] =  KTJSONLookupWidget::getGroupsAndUsersWidget($label, 'email', 'all', array(), $options);

        // External email addresses can be added here
        if ($allowEmailAddresses) {
            $fields[] = new KTTextWidget(_kt('Email addresses'),
                _kt('Enter the email addresses of external recipients.'),
                'fEmailAddresses', '', $this->oPage,
                false, null, null, array('cols' => 60, 'rows' => 5)
            );
        }

        // Should the document be attached or just a link
        if ($allowAttachment) {
            $fields[] = new KTCheckboxWidget(_kt('Attach document'),
                _kt('Check to send as an attachment, uncheck to just send a link.'),
                'fAttachDocument', null, $this->oPage
            );
        }

        // Message to include in the email
        $fields[] = new KTTextWidget(_kt('Message'),
            _kt(''),
            'fComment', '', $this->oPage,
            false, null, null, array('cols' => 60, 'rows' => 5)
        );

        $template =& $this->oValidator->validateTemplate('ktstandard/action/email');
        $templateData = array(
            'context' => &$this,
            'fields' => $fields,
            'groups' => $groups,
            'users' => array(),
        );

        return $template->render($templateData);
    }

    function do_email()
    {
        $externalEmailAddresses = trim($_REQUEST['fEmailAddresses']);
        $attachDocument = $_REQUEST['fAttachDocument'];
        $comment = (empty($_REQUEST['fComment'])) ? ' ': trim($_REQUEST['fComment']);

        $groupIds = array();
        $userIds = array();

        $groups = trim($_REQUEST['groups_roles'], ',');
        if (!empty($groups)) {
            $groups = explode(',', $groups);
            foreach ($groups as $idString) {
                $idData = explode('_', $idString);
                $groupIds[] = $idData[1];
            }
        }

        $users = trim($_REQUEST['users'], ',');
        if (!empty($users)) { $userIds = explode(',', $users); }

        $config = KTConfig::getSingleton();
        $allowAttachment = $config->get('email/allowAttachment', false);
        $allowEmailAddresses = $config->get('email/allowEmailAddresses', false);

        if (empty($allowAttachment)) { $attachDocument = false; }

        $emailAddressList = array();
        if (!empty($allowEmailAddresses) && !empty($externalEmailAddresses)) {
            $addressList = explode("\n", $externalEmailAddresses);
            foreach ($addressList as $item) {
                $items = explode(' ', $item);
                $emailAddressList = array_merge($emailAddressList, $items);
            }
        }

        // if we're going to send a mail, first make there is someone to send it to
        if ((count($groupIds) == 0) && (count($userIds) == 0) && (count($emailAddressList) == 0)) {
            $this->errorRedirectToMain(_kt('No recipients set'), sprintf('fDocumentId=%d', $this->oDocument->getId()));
            exit(0);
        }

        $documentId = $this->oDocument->getID();
        $documentName = $this->oDocument->getName();

        $emailErrors = array();
        $userEmails = array();
        sendGroupEmails($groupIds, $userEmails, $emailErrors);
        sendUserEmails($userIds, $userEmails, $emailErrors);

        // send manual/external email addresses
        if ((boolean)$attachDocument) {
            sendManualEmails($emailAddressList, $userEmails, $emailErrors);
        }
        else if (!empty($emailAddressList)) {
            sendExternalEmails($emailAddressList, $documentId, $documentName, $comment, $emailErrors);
        }

        // get list of email addresses and send
        if (!empty($userEmails)) {
            // email addresses are in the keys -> extract the keys
            $listEmails = array_keys($userEmails);
            sendEmail($listEmails, $documentId, $documentName, $comment, (boolean)$attachDocument, $emailErrors);
        }

        // Display success or error, not both
        if (count($emailErrors)) {
            $_SESSION['KTErrorMessage'][] = join('<br />\n', $emailErrors);
        }
        else {
            $_SESSION['KTInfoMessage'][] = _kt('Email sent');
        }

        //go back to the document view page
        controllerRedirect('viewDocument', sprintf("fDocumentId=%d", $this->oDocument->getId()));
    }

}


class KTEmailPlugin extends KTPlugin {

    var $sNamespace = 'ktstandard.email.plugin';
    var $autoRegister = true;

    function KTEmailPlugin($filename = null)
    {
        $res = parent::KTPlugin($filename);
        $this->sFriendlyName = _kt('Email Plugin');
        return $res;
    }

    function setup()
    {
        $this->registerAction('documentaction', 'KTDocumentEmailAction', 'ktcore.actions.document.email');
    }

}

$registry =& KTPluginRegistry::getSingleton();
$registry->registerPlugin('KTEmailPlugin', 'ktstandard.email.plugin', __FILE__);
