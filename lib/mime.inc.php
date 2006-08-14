<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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

    function getAllMimeTypes($sAdditional = '') {
        $sTable = KTUtil::getTableName('mimetypes');
        $aQuery = array("SELECT id, mimetypes FROM " . $sTable . ' ' .$sAdditional, array());
        $res = DBUtil::getResultArray($aQuery);
	return $res;
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
