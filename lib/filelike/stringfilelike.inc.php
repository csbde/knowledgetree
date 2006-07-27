<?php
/**
 * $Id$
 *
 * Filelike wrapper for strings
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/filelike/filelike.inc.php');

class KTStringFileLike extends KTFileLike {
    var $bSupportChunking = true;
    var $bIsFSFile = false;
    var $iPos = 0;
    var $iLen = 0;

    function KTStringFileLike ($sString) {
        $this->sString = $sString;
        $this->iLen = strlen($sString);
    }

    /**
     * Set up any resources needed to perform work.
     */
    function open($mode = "r") { }

    /**
     * Take care of getting rid of any active resources.
     */
    function close() { }

    /**
     * Behaves like fread
     */
    function read($iBytes) {
        if (($this->iPos + $iBytes) > $this->iLen) {
            $iBytes = $this->iLen - $this->iPos;
        }
        $sRet = substr($this->sString, $this->iPos, $iBytes);
        $this->iPos += $iBytes;
        return $sRet;
    }

    /**
     * Behaves like fwrite
     */
    function write($sData) {
        $this->sString .= $sData;
        return true;
    }

    function get_contents() {
        return $this->sString;
    }

    function put_contents($sData) {
        $this->sString = $sData;
        return true;
    }

    function eof() {
        if ($this->iPos >= $this->iLen) {
            return true;
        }
        return false;
    }

    function filesize() {
        return strlen($this->sString);
    }
}

?>
