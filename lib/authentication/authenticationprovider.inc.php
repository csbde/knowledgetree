<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . "/dispatcher.inc.php");

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
        return $this->sName;
    }
    function getNamespace() {
        return $this->sNamespace;
    }

    function do_editSourceProvider() {
        return $this->errorRedirectTo('viewsource', _kt("Provider does not support editing"), 'source_id=' .  $_REQUEST['source_id']);
    }

    function do_performEditSourceProvider() {
        return $this->errorRedirectTo('viewsource', _kt("Provider does not support editing"), 'source_id=' .  $_REQUEST['source_id']);
    }
}
