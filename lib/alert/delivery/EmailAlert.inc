<?php
require_once(KT_LIB_DIR . '/email/Email.inc');
/**
 * $Id$
 *
 * Sends an email (subscription) alert.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.alert.delivery
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
