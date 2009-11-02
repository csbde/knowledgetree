<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * John
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

class KTEntityList {

    var $sCode = null;
    var $aDocumentIds = array();
    var $aFolderIds = array();

    function KTEntityList($aDocumentIds = false, $aFolderIds = false) {
        $this->sCode = KTUtil::randomString();

        if($aDocumentIds !== false) {
            $this->aDocumentIds =& $aDocumentIds;
        }
        if($aFolderIds !== false) {
            $this->aFolderIds =& $aFolderIds;
        }

        $this->serialize();
    }

    // serialize to session
    function serialize() {
        $sList = serialize($this);
        $_SESSION['ktentitylists'][$this->sCode] = $sList;
    }   

    // get the storage code
    function getCode() {
        return $this->sCode;
    }

    function &getDocumentIds() {
        return $this->aDocumentIds;
    }
    function &getFolderIds() {
        return $this->aFolderIds;
    }

    // static accessor
    function &retrieveList($sCode) {
        $sList = KTUtil::arrayGet($_SESSION['ktentitylists'], $sCode, False);
        if($sList === False) {
            return PEAR::raiseError(_kt('No such KTEntityList'));
        }
        $oList = unserialize($sList);
        return $oList;
    }
        
}

?>
