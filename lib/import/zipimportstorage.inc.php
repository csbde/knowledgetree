<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Manages listing and contents for documents uploaded from a zip file
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

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

class KTZipImportStorage extends KTFSImportStorage {
    function KTZipImportStorage($sZipPath) {
        $this->sZipPath = $sZipPath;
    }

    function init() {
        $sTmpPath = tempnam('/tmp', 'zipimportstorage');
        if ($sTmpPath === false) {
            return PEAR::raiseError("Could not create temporary directory for zip storage");
        }
        if (!file_exists($this->sZipPath)) {
            return PEAR::raiseError("Zip file given does not exist");
        }
        unlink($sTmpPath);
        mkdir($sTmpPath, 0700);
        $this->sBasePath = $sTmpPath;
        $aArgs = array(
            "unzip",
            "-q", "-n",
            "-d", $sTmpPath,
            $this->sZipPath,
        );
        $sCommand = KTUtil::safeShellString($aArgs);
        $res = system($sCommand);
        if ($res === false) {
            return PEAR::raiseError("Could not retrieve contents from zip storage");
        }
    }
    function cleanup() {
        if ($this->sBasePath && file_exists($this->sBasePath)) {
            // XXX: Only works on Unix-like systems for now.
            system(KTUtil::safeShellString(array('/bin/rm', '-rf', $this->sBasePath)));
            $this->sBasePath = null;
        }
    }
}

?>
