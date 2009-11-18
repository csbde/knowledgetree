<?php
/**
* Base service class for CMIS wrapper API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package KTCMIS
* @version Version 0.9
*/

require_once(realpath(dirname(__FILE__) . '/../../../config/dmsDefaults.php'));
require_once(KT_DIR . '/ktapi/ktapi.inc.php');

define ('CMIS_DIR', KT_LIB_DIR . '/api/ktcmis');
require_once(CMIS_DIR . '/exceptions/PermissionDeniedException.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

/**
 * Base class for all KT CMIS classes
 * Handles authentication
 * 
 * This class is required for all CMIS Service classes
 */
class KTCMISBase {

    // we want all child classes to share the ktapi and session instances, no matter where they are set from,
    // so we declare them as static
    static protected $ktapi;
    static protected $session;

    public function __construct(&$ktapi = null, $username = null, $password = null)
    {
        // TODO confirm KTAPI instance active??? shouldn't really be responsibility of this code
        if (is_null($ktapi) && (!is_null($username) && !is_null($password))) {
            $this->startSession($username, $password);
        }
        else if (!is_null($ktapi)) {
            self::$ktapi = $ktapi;
            self::$session = self::$ktapi->get_session();
        }
    }

    // TODO this probably does not belong here??? probably should require all auth external, handled by transport protocol.
    //      perhaps simple refusal to execute without valid session?
    // NOTE left in to allow transport protocol to delegate auth to this level, but not actually used in any code at present
    public function startSession($username, $password)
    {
        // attempt to recover session if one exists
        if (!is_null(self::$session) && !PEAR::isError(self::$session))
        {
            self::$session =& self::$ktapi->get_active_session(self::$session->get_sessionid());
        }

        // start new session if no existing session or problem getting existing session (expired, etc...)
        if (is_null(self::$session) || PEAR::isError(self::$session))
        {
            self::$ktapi = new KTAPI();
            self::$session =& self::$ktapi->start_session($username, $password);
        }

        // failed authentication?
        if (PEAR::isError(self::$session))
        {
            throw new PermissionDeniedException('You must be authenticated to perform this action');
        }
        
        return self::$session;
    }

    public function setInterface(&$ktapi = null)
    {
        if (!is_null($ktapi)) {
            self::$ktapi = $ktapi;
        }
    }

    public function getInterface()
    {
        return self::$ktapi;
    }

    public function getSession()
    {
        return self::$session;
    }

    // TODO what about destroying sessions? only on logout (which is not offered by the CMIS clients tested so far)
}

?>