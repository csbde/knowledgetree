<?php
/**
 * $Id$
 *
 * Filelike wrapper for normal files (and streams too)
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
        $this->fh = @fopen($this->sFilename, $mode);
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
