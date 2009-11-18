<?php
/**
 * $Id$
 *
 * Filelike wrapper for normal files (and streams too)
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
            return PEAR::raiseError(_kt('Error opening file'));
        }
    }

    /**
     * Take care of getting rid of any active resources.
     */
    function close() {
        if (is_null($this->fh)) {
            return PEAR::raiseError(_kt('Not open'));
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
