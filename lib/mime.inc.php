<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

/**
 * This is a temporary location for these functions.
 */

class KTMime {
    /**
     * Get the mime type primary key for a specific mime type
     *
     * @param string detected mime type
     * @param string filename
     * @return int mime type primary key if found, else default mime type primary key (text/plain)
     */
    function getMimeTypeID($sMimeType, $sFileName) {
        global $default;
        $sql = $default->db;
        $bOfficeDocument = false;

        // application/msword seems to be set by all Office documents
        if ($sMimeType == "application/msword") {
            $bOfficeDocument = true;
        }

        if ($bOfficeDocument || (!$sMimeType)) {
          // check by file extension
          $sExtension = KTMime::stripAllButExtension($sFileName);
          $sql->query(array("SELECT id FROM " . $default->mimetypes_table . " WHERE LOWER(filetypes) = ?", $sExtension));/*ok*/
          if ($sql->next_record()) {
              return $sql->f("id");
          }
        }

        // get the mime type id
        if (isset($sMimeType)) {
            $sql->query(array("SELECT id FROM " . $default->mimetypes_table . " WHERE mimetypes = ?", $sMimeType));/*ok*/
            if ($sql->next_record()) {
                return $sql->f("id");
            }
        }

        //otherwise return the default mime type
        return KTMime::getDefaultMimeTypeID();
    }

    /**
    * Get the default mime type, which is application/octet-stream
    *
    * @return int default mime type
    *
    */
    function getDefaultMimeTypeID() {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT id FROM " . $default->mimetypes_table . " WHERE mimetypes = 'application/octet-stream'");
        $sql->next_record();
        //get the mime type id
        return $sql->f("id");
    }

    function getMimeTypeName($iMimeTypeID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT mimetypes FROM " . $default->mimetypes_table . " WHERE id = ?", $iMimeTypeID));/*ok*/
        if ($sql->next_record()) {
            return $sql->f("mimetypes");
        }
        return "application/octet-stream";
    }
    
    function getFriendlyNameForString($sMimeType) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT friendly_name, filetypes FROM " . $default->mimetypes_table . " WHERE mimetypes = ?", $sMimeType));/*ok*/
        if ($sql->next_record()) {
		    $friendly_name = $sql->f("friendly_name");
            if (!empty($friendly_name)) { 
                return _($sql->f("friendly_name"));
            } else {
		        return sprintf(_('%s File'), strtoupper($sql->f('filetypes')));            
            }
		}
        
        
        return _('Unknown Type');
    }

    /**
    * Try well-defined methods for getting the MIME type for a file on disk.
    * First try PECL's Fileinfo library, then try mime_content_type() builtin.
    * If neither are available, returns NULL.
    *
    * @param string file on disk
    * @return string mime time for given filename, or NULL
    */
    function getMimeTypeFromFile($sFileName) {
        if (extension_loaded('fileinfo')) {
            $res = finfo_open(FILEINFO_MIME);
            $sType = finfo_file($res, $sFileName);
        }

        if (!$sType) {
            if (file_exists('/usr/bin/file')) {
                $aCmd = array('/usr/bin/file', '-bi', $sFileName);
                $sCmd = KTUtil::safeShellString($aCmd);
                $sPossibleType = @exec($sCmd);
                if (preg_match('#^[^/]+/[^/*]+$#', $sPossibleType)) {
                    $sType = $sPossibleType;
                }
            }
        }

        if ($sType) {
            return preg_replace('/;.*$/', '', $sType);
        }

        return null;
    }

    /**
    * Strip all but the extension from a file. For instance, input of
    * 'foo.tif' would return 'tif'.
    *
    * @param string filename
    * @return string extension for given file, without filename itself
    */
    function stripAllButExtension($sFileName) {
        return strtolower(substr($sFileName, strrpos($sFileName, ".")+1, strlen($sFileName) - strrpos($sFileName, ".")));
    }
}
