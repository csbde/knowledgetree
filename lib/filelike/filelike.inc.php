<?php
/**
 * $Id$
 *
 * Interface for representing file-like operations (open, read, write,
 * close) that may not deal with files on the filesystem (or URLs).
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

class KTFileLike {
    var $bSupportChunking = false;
    var $bIsFSFile = false;
    
    /**
     * Set up any resources needed to perform work.
     */
    function open($mode = "r") {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Take care of getting rid of any active resources.
     */
    function close() {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like fread
     */
    function read($iBytes) {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like fwrite
     */
    function write($sData) {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like file_get_contents
     */
    function get_contents() {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like file_get_contents
     */
    function put_contents() {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like feof
     */
    function eof() {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * If $bIsFSFile, returns the FSPath (for rename/move)
     */
    function getFSPath() {
        return PEAR::raiseError('Not implemented');
    }

    /**
     * Behaves like filesize
     */
    function filesize() {
        return PEAR::raiseError('Not implemented');
    }

}

?>
