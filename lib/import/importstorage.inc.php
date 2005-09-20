<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Interface for representing a method of listing and importing
 * documents from storage.
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

class KTImportStorage {
    function listDocuments($sFolderPath) {
        return PEAR::raiseError('Not implemented');
    }

    function listFolders($sFolderPath) {
        return PEAR::raiseError('Not implemented');
    }

    function getDocumentInfo($sDocumentPath) {
        return PEAR::raiseError('Not implemented');
    }

    function init() {
        return true;
    }

    function cleanup() {
        return true;
    }
}

class KTImportStorageInfo {
    /**
     * File name to store in the repository.
     */
    var $sFilename;

    /**
     * Ordered array (oldest to newest) of KTFileLike objects that can
     * get the contents for versions of the given file.
     */
    var $aVersions;

    function KTImportStorageInfo ($sFilename, $aVersions) {
        $this->sFilename = $sFilename;
        $this->aVersions = $aVersions;
    }

    function getFilename() {
        return $this->sFilename;
    }
}

?>
