<?php
require_once("$default->fileSystemRoot/lib/email/Email.inc");

/**
 * 
 * $Id$
 * 
 * Sends an email (subscription) alert.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.subscriptions.alert
 */
class EmailAlert {
    var $sAddress;
    var $sSubject;
    var $sContent;
    var $oEmail;

    /**
     * Default constructor
     *
     * @param string the address to send the email alert to
     * @param string the email subject
     * @param string the content to send.
     */
    function EmailAlert($sAddress, $sSubject, $sContent) {
        $this->sAddress = $sAddress;
        $this->sSubject = $sSubject;
        $this->sContent = $sContent;
        $this->oEmail= new Email();
    }

    /**
     * Sends the email.
     *
     * @return true on successful email send, false otherwise.
     */
    function send() {
        global $default;

        if ($this->oEmail->send($this->sAddress, $this->sSubject, $this->sContent)) {
            $default->log->debug("EmailAlert::EmailAlert successfully sent email to $this->sAddress");
            return true;
        } else {
            $default->log->error("EmailAlert::EmailAlert failed sending email to $this->sAddress, text=$this->sContent");
            return false;
        }
    }
}
