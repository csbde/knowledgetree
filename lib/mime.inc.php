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
        $sTable = KTUtil::getTableName('mimetypes');
        $bOfficeDocument = false;

        // application/msword seems to be set by all Office documents
        if ($sMimeType == "application/msword") {
            $bOfficeDocument = true;
        }

        if ($bOfficeDocument || (!$sMimeType)) {
          // check by file extension
          $sExtension = KTMime::stripAllButExtension($sFileName);
          $res = DBUtil::getResultArray(array("SELECT id FROM " . $sTable . " WHERE LOWER(filetypes) = ?", array($sExtension)));
          if (PEAR::isError($res)) {
              ; // pass ?!
          } 
          if (count($res) != 0) {
              return $res[0]['id'];
          }
        }

        // get the mime type id
        if (isset($sMimeType)) {
            $res = DBUtil::getResultArray(array("SELECT id FROM " . $sTable . " WHERE mimetypes = ?", array($sMimeType)));
            if (PEAR::isError($res)) {
                ; // pass ?!
            } 
            if (count($res) != 0) {
                return $res[0]['id'];
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
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT id FROM " . $sTable . " WHERE mimetypes = 'application/octet-stream'";
        $aQuery = array($sQuery, array());
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) { 
            return $res;
        } else {
            return $res[0]['id'];
        }
    }

    function getMimeTypeName($iMimeTypeID) {
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT mimetypes FROM " . $sTable . " WHERE id = ?";
        $aQuery = array($sQuery, array($iMimeTypeID));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) { 
            return $res;
        } else if (count($res) != 0){
            return $res[0]['mimetypes'];
        }
        return "application/octet-stream";
    }
    
    function getFriendlyNameForString($sMimeType) {
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT friendly_name, filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($sMimeType));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) { 
            return $res;
        } else if (count($res) != 0){
            $friendly_name = $res[0]['friendly_name'];
            if (!empty($friendly_name)) { 
                return _kt($friendly_name);
            } else {
                return sprintf(_kt('%s File'), strtoupper($res[0]['filetypes']));             
            }
        }
        
        
        return _kt('Unknown Type');
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

    function getIconPath($iMimeTypeId) {
        $cached = KTUtil::arrayGet($GLOBALS['_KT_icon_path_cache'], $iMimeTypeId);
        if (!empty($cached)) {
            return $cached;
        }
        $GLOBALS['_KT_icon_path_cache'][$iMimeTypeId] = KTMime::_getIconPath($iMimeTypeId);
        return $GLOBALS['_KT_icon_path_cache'][$iMimeTypeId];
    }

    function _getIconPath($iMimeTypeId) {
        $sQuery = 'SELECT icon_path FROM mime_types WHERE id = ?';
        $res = DBUtil::getOneResult(array($sQuery, array($iMimeTypeId)));

        if ($res['icon_path'] !== null) {
           return $res['icon_path'];
        } else {
           return 'unspecified_type';
        }
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

$_KT_icon_path_cache = array();
