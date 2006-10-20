<?php

/**
 * $Id: entity.php 5758 2006-07-27 10:17:43Z bshuttle $
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
        $_SESSION["ktentitylists"][$this->sCode] = $sList;
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
            return PEAR::raiseError(_kt("No such KTEntityList"));
        }
        $oList = unserialize($sList);
        return $oList;
    }
        
}

?>
