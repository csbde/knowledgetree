<?php
/**
 * $Id$
 *
 * Interface for representing file-like operations (open, read, write,
 * close) that may not deal with files on the filesystem (or URLs).
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

class KTFileLike {
    var $bSupportChunking = false;
    var $bIsFSFile = false;
    
    /**
     * Set up any resources needed to perform work.
     */
    function open($mode = "r") {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Take care of getting rid of any active resources.
     */
    function close() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like fread
     */
    function read($iBytes) {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like fwrite
     */
    function write($sData) {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like file_get_contents
     */
    function get_contents() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like file_get_contents
     */
    function put_contents() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like feof
     */
    function eof() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * If $bIsFSFile, returns the FSPath (for rename/move)
     */
    function getFSPath() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

    /**
     * Behaves like filesize
     */
    function filesize() {
        return PEAR::raiseError(_kt('Not implemented'));
    }

}

?>
