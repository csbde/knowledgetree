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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTInterceptor extends KTStandardDispatcher {
    var $sName;
    var $sNamespace;

    // Whether we can have multiple instances or not.  Default to yes.
    var $bSingleton = false;

    function KTInterceptor() {
        return parent::KTStandardDispatcher();
    }

    function configure($aInfo) {
        $this->aInfo = $aInfo;
    }

    function getName() {
        return sanitizeForSQLtoHTML($this->sName);
    }

    function getNamespace() {
        return $this->sNamespace;
    }

    /**
     * Return a user object if the authentication succeeds
     */
    function authenticated() {
        return null;
    }

    /**
     * Get an opportunity to take over the request.
     * Remember to exit if you take over.
     */
    function takeOver() {
        return null;
    }

    function loginWidgets() {
        return null;
    }

    function alternateLogin() {
        return null;
    }
}

class KTNoLocalUser extends PEAR_Error {
    function KTNoLocalUser($aExtra = null) {
        parent::PEAR_Error(_kt('No local user with that username'));
        $this->aExtra = $aExtra;
    }
}
