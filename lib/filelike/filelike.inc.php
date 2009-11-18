<?php
/**
 * $Id$
 *
 * Interface for representing file-like operations (open, read, write,
 * close) that may not deal with files on the filesystem (or URLs).
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
