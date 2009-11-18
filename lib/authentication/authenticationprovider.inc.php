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

class KTAuthenticationProvider extends KTStandardDispatcher {
    var $sName;
    var $sNamespace;
    var $bHasSource = false;
    var $bUserSource = true;
    var $bGroupSource = false;

    function KTAuthenticationProvider() {
        return parent::KTStandardDispatcher();
    }

    function configure($aInfo) {
        $this->aInfo = $aInfo;
    }

    function &getAuthenticator($oSource) {
        // Not implemented
        return null;
    }

    function &getSource() {
        if (empty($bHasSource)) {
            return null;
        }
        return $this;
    }

    /**
     * Gives the provider a chance to show something about how the
     * authentication source is set up.  For example, describing the
     * server settings for an LDAP authentication source.
     */
    function showSource($oSource) {
        return null;
    }

    /**
     * Gives the provider a chance to show something about how the
     * user's authentication works.  For example, providing a link to a
     * page to allow the admin to change a user's password.
     */
    function showUserSource($oUser, $oSource) {
        return null;
    }

    function getName() {
        return sanitizeForSQLtoHTML($this->sName);
    }
    function getNamespace() {
        return $this->sNamespace;
    }

    function do_editSourceProvider() {
        return $this->errorRedirectTo('viewsource', _kt('Provider does not support editing'), 'source_id=' .  $_REQUEST['source_id']);
    }

    function do_performEditSourceProvider() {
        return $this->errorRedirectTo('viewsource', _kt('Provider does not support editing'), 'source_id=' .  $_REQUEST['source_id']);
    }

    /**
     * Perform provider-specific on-logout activities
     *
     * @param   User    The user who has just logged in
     */
    function login($oUser) {
    }

    /**
     * Perform provider-specific on-logout activities
     *
     * @param   User    The user who is about to be logged out
     */
    function logout($oUser) {
    }

    /**
     * Perform any provider-specific per-request activities
     *
     * @param   User    The user who is about to be logged out
     */
    function verify($oUser) {
    }

    function autoSignup($sUsername, $sPassword, $aExtra, $oSource) {
        return false;
    }
}
