<?php
/**
 * $Id$
 *
 * Sends an email (subscription) alert.
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
 */

require_once(KT_LIB_DIR . '/email/Email.inc');

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
