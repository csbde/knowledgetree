<?php
/**
 * $Id$
 *
 * Contains utility functions for bulk upload functionality.
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

require_once("$default->fileSystemRoot/lib/documentmanagement/File.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");

/* try to load the PECL zip extension, available here: http://pecl.php.net/package/zip */
if (!extension_loaded('zip')) {
    @dl('zip.' . PHP_SHLIB_SUFFIX);
}

class BulkUploadManager {

    /**
     * Determines if the application is capable of bulk upload. Currently,
     * the only requirement is that the PECL 'zip' extension is loaded.
     *
     * @return boolean TRUE if system supports bulk upload, FALSE otherwise
     */
    function isBulkUploadCapable() {
        global $default;

        if (BulkUploadManager::_hasZipExtension() !== true) {
            $default->log->info('BulkUploadManager: zip extension not loaded.');
        } else {
            return true;
        }

        if (BulkUploadManager::_hasUnzipCommand() !== true) {
            $default->log->info('BulkUploadManager: unzip command not available.');
        } else {
            return true;
        }

        $default->log->info('ZIP file bulk upload is not possible');
        return false;
    }

    function _hasZipExtension() {
        global $default;
        $bHaveZipExtension = true;

        if (!extension_loaded('zip')) {
            $bHaveZipExtension = false;
        }

        return $bHaveZipExtension;
    }

    function _hasUnzipCommand() {
        global $default;
        global $_BulkUploadManager_unzipCommand;
        $bHaveUnzipCommand = false;

        if (file_exists($default->unzipCommand)) {
            $_BulkUploadManager_unzipCommand = $default->unzipCommand;
            return true;
        }

        $paths = split(PATH_SEPARATOR, $default->execSearchPath);
        foreach ($paths as $path) {
            if (file_exists($path . '/' . $default->unzipCommand)) {
                $_BulkUploadManager_unzipCommand = $path . '/' . $default->unzipCommand;
                return true;
            }
        }

        return false;
    }

    function unzipToTempDir($sArchiveFilename, $sArchiveRealname, $bBailOnError = FALSE) {
        if (BulkUploadManager::_hasZipExtension() === true) {
            return BulkUploadManager::_unzipToTempDirZipExtension($sArchiveFilename, $sArchiveRealname, $bBailOnError);
        }
        if (BulkUploadManager::_hasUnzipCommand() === true) {
            return BulkUploadManager::_unzipToTempDirUnzipCommand($sArchiveFilename, $sArchiveRealname, $bBailOnError);
        }
        return false;
    }

    /**
     * Unzips a ZIP archive to a temporary directory using the zip
     * extension.
     *
     * The "friendly name" (second parameter) is useful in cases where a ZIP
     * file was uploaded via HTTP and the original ZIP filename is different
     * from the temporary file where the HTTP uploaded-ZIP was stored.
     *
     * @param string ZIP archive filename, a "friendly name" for the zip file.
     * @param string ZIP archive filename, the actual file on disk
     * @param boolean if the function should return right when an error occurs.
     *     Optional, default is FALSE.
     * @return array File objects, one for each file found in the ZIP archive
     */
    function _unzipToTempDirZipExtension($sArchiveFilename, $sArchiveRealname, $bBailOnError = FALSE) {
        global $default;

        /* OPEN ZIP */
        $zip = zip_open($sArchiveFilename);
        if (!$zip) {
            $default->log->info("BulkUploadManager: unable to open zip file: $sArchiveFilename");
            if ($bBailOnError) return;
        }

        /* CREATE TEMP FILE */
        // this should work on Windows, too. See http://php.net/tempnam
        $sTempFilename = tempnam('/tmp', 'kt_unzipdir');
        if (!$sTempFilename) {
            $default->log->error("BulkUploadManager: couldn't create temporary file");
            if ($bBailOnError) return;
        }

        /* UNLINK TEMP FILE */
        $default->log->debug("BulkUploadManager: tempnam() returned: $sTempFilename");
        $default->log->debug("BulkUploadManager: unlinking tempfile $sTempFilename");
        if (!unlink($sTempFilename)) {
            $default->log->info("BulkUploadManager: couldn't unlink $sTempFilename");
            if ($bBailOnError) return;
        }

        /* CREATE TEMP DIR OF SAME NAME AS UNLINKED TEMP FILE */
        $sTempDirname = $sTempFilename;
        $default->log->debug("BulkUploadManager: creating new directory: $sTempDirname");
        if (!mkdir($sTempDirname)) {
            $default->log->info("BulkUploadManager: couldn't make directory: $sTempDirname");
            if ($bBailOnError) return;
        }

        /* UNZIP FILES TO TEMP DIR */
        $aIndividualFiles = array();
        while ($zip_entry = zip_read($zip)) {
            $sFilename = zip_entry_name($zip_entry);
            $iUncompressedSize = zip_entry_filesize($zip_entry);

            if ($iUncompressedSize < 1) {
                $default->log->info("BulkUploadManager: Found a zero-byte file in $sArchiveFilename. User might've tried to upload a compressed file with subdirectories");
                if ($bBailOnError) return; else continue;
            }

            if (!zip_entry_open($zip, $zip_entry, "rb")) {
                $default->log->error("BulkUploadManager: Unable to open zip entry $sFilename from archive $sArchiveFilename");
                if ($bBailOnError) return; else continue;
            }

            $default->log->info("BulkUploadManager: Writing $sFilename from archive $sArchiveFilename (originally '$sArchiveRealname') to disk in direcory $sTempDirname");

            $sUnpackedName = $sTempDirname . DIRECTORY_SEPARATOR . $sFilename;
            $oFile = & new File($sUnpackedName);
            $oFile->iSize = $iUncompressedSize;
            $oFile->open('wb');

            if (!$oFile->fd) {
                $default->log->error("BulkUploadManager: unable to open $sUnpackedName");
                if ($bBailOnError) return; else continue;
            }

            $bWriteError = fwrite($oFile->fd, zip_entry_read($zip_entry, $iUncompressedSize), $iUncompressedSize) === FALSE;
            if ($bWriteError) {
                $default->log->error("BulkUploadManager: unable to write $oFile->sFilename");
                if ($bBailOnError) return; else continue;
            }

            $oFile->close();
            array_push($aIndividualFiles, $oFile);
        }

        return $aIndividualFiles;
    }

    /**
     * Unzips a ZIP archive to a temporary directory using the unzip
     * command.
     *
     * The "friendly name" (second parameter) is useful in cases where a ZIP
     * file was uploaded via HTTP and the original ZIP filename is different
     * from the temporary file where the HTTP uploaded-ZIP was stored.
     *
     * @param string ZIP archive filename, a "friendly name" for the zip file.
     * @param string ZIP archive filename, the actual file on disk
     * @param boolean if the function should return right when an error occurs.
     *     Optional, default is FALSE.
     * @return array File objects, one for each file found in the ZIP archive
     */
    function _unzipToTempDirUnzipCommand($sArchiveFilename, $sArchiveRealname, $bBailOnError = FALSE) {
        global $default, $_BulkUploadManager_unzipCommand;

        /* CREATE TEMP FILE */
        // this should work on Windows, too. See http://php.net/tempnam
        $sTempFilename = tempnam('/tmp', 'kt_unzipdir');
        if (!$sTempFilename) {
            $default->log->error("BulkUploadManager: couldn't create temporary file");
            if ($bBailOnError) return;
        }

        /* UNLINK TEMP FILE */
        $default->log->debug("BulkUploadManager: tempnam() returned: $sTempFilename");
        $default->log->debug("BulkUploadManager: unlinking tempfile $sTempFilename");
        if (!unlink($sTempFilename)) {
            $default->log->info("BulkUploadManager: couldn't unlink $sTempFilename");
            if ($bBailOnError) return;
        }

        /* CREATE TEMP DIR OF SAME NAME AS UNLINKED TEMP FILE */
        $sTempDirname = $sTempFilename;
        $default->log->debug("BulkUploadManager: creating new directory: $sTempDirname");
        if (!mkdir($sTempDirname)) {
            $default->log->info("BulkUploadManager: couldn't make directory: $sTempDirname");
            if ($bBailOnError) return;
        }

        /* UNZIP FILES TO TEMP DIR */
        $aIndividualFiles = array();

        $command = $_BulkUploadManager_unzipCommand . " -q -j -n -d " . escapeshellarg($sTempDirname) . " " . escapeshellarg($sArchiveFilename);
        $default->log->debug("BulkUploadManager: command is $command");

        system($command);

        $dh = opendir($sTempDirname);
        while (false !== ($filename = readdir($dh))) {
            $filename = $sTempDirname . '/' . $filename;
            if (is_file($filename)) {
                $oFile = &new File ($filename);
                $oFile->iSize = filesize($filename);
                $aIndividualFiles[] = $oFile;
            }
        }

        return $aIndividualFiles;
    }
}

?>
