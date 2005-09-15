<?php
/**
 * $Id$
 *
 * Filelike wrapper for normal files (and streams too)
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/filelike/filelike.inc.php');

class KTFSFileLike extends KTFileLike {
    var $bSupportChunking = true;
    var $fh;
    var $sFilename;

    var $bIsFSFile = true;

    function KTFSFileLike ($sFilename) {
        $this->sFilename = $sFilename;
    }

    function getFSPath() {
        return $this->sFilename;
    }
    
    /**
     * Set up any resources needed to perform work.
     */
    function open($mode = "r") {
        $this->fh = fopen($this->sFilename, $mode);
        if ($this->fh === false) {
            $this->fh = null;
            return PEAR::raiseError('Error opening file');
        }
    }

    /**
     * Take care of getting rid of any active resources.
     */
    function close() {
        if (is_null($this->fh)) {
            return PEAR::raiseError('Not open');
        }
        return fclose($this->fh);
    }

    /**
     * Behaves like fread
     */
    function read($iBytes) {
        return fread($this->fh, $iBytes);
    }

    /**
     * Behaves like fwrite
     */
    function write($sData) {
        return fwrite($this->fh, $sData);
    }

    function get_contents() {
        return file_get_contents($this->sFilename);
    }

    function put_contents($sData) {
        return file_put_contents($this->sFilename, $sData);
    }

    function eof() {
        return feof($this->fh);
    }

    function filesize() {
        return filesize($this->sFilename);
    }
}

?>
