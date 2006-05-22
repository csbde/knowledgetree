<?php
/**
 * $Id$
 *
 * Filelike wrapper for strings
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
