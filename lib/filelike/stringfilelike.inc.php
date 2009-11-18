<?php
/**
 * $Id$
 *
 * Filelike wrapper for strings
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
