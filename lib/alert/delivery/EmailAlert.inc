<?php
require_once(KT_LIB_DIR . '/email/Email.inc');
/**
 * $Id$
 *
 * Sends an email (subscription) alert.
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
