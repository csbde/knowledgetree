<?php
/**
 * $Id$
 *
 * Represents a file on disk.
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
 * @author Adam Monsen
 * @package lib.documentmanagement
 */ 

class File {
    /** file name */
    var $sFilename;
    /** file size */
    var $iSize = 0;
    /** file descriptor */
    var $fd = NULL;
    /** status: fopen() was attempted */
    var $bTriedOpen = FALSE;

    /**
     * Constructor.
     *
     * @param string filename
     */
    function File ($sFilename) {
        $this->sFilename = $sFilename;
    }

    /**
     * Uses fopen() to open a filehandle. Stores
     * opened filehandle in $this->fd
     *
     * @param string mode, as accepted by fopen()
     * @return boolean return value from fopen()
     */
    function open($sMode) {
        $bSucceeded = FALSE;

        $handle = fopen($this->sFilename, $sMode);
        if ($handle) {
            $this->fd = $handle;
            $bSucceeded = TRUE;
        }
        $this->bTriedOpen = TRUE;

        return $bSucceeded;
    }

    /**
     * Closes filehandle and calls resetFile()
     *
     * @return boolean status from fclose()
     */
    function close() {
        $bStatus = fclose($this->fd);
        $this->resetFile();
        return $bStatus;
    }

    /**
     * Sets $this->fd to NULL and resets
     * $this->bTriedOpen flag to FALSE.
     *
     * @return boolean status from fclose()
     */
    function resetFile() {
        $this->fd = NULL;
        $this->bTriedOpen = FALSE;
    }
}

?>
