<?php
/**
 * $Id$
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
    function getMimeTypeID($sMimeType, $sFileName, $sTempFile = null) {
    	global $default;
    	$sTable = KTUtil::getTableName('mimetypes');

    		// check by file extension
    		$sExtension = KTMime::stripAllButExtension($sFileName);
    		$res = DBUtil::getOneResultKey(array("SELECT id FROM " . $sTable . " WHERE LOWER(filetypes) = ?", array($sExtension)),'id');
    		if (PEAR::isError($res) || empty($res))
    		{
    			; // pass ?!
    		}
    		else {
    			return $res;
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

    	if (!is_null($sTempFile))
    	{
    	    // The default is a binary file, so if mime magic can resolve better, lets try...
    	    $sMimeType = KTMime::getMimeTypeFromFile($sTempFile);
    	    if (!empty($sMimeType))
    	    {
    	        $res = DBUtil::getResultArray(array("SELECT id FROM " . $sTable . " WHERE mimetypes = ?", array($sMimeType)));
    	        if (PEAR::isError($res)) {
    	            ; // pass ?!
    	        }
    	        if (count($res) != 0) {
    	            return $res[0]['id'];
    	        }
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
                return sprintf(_kt('%s') , $friendly_name);
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
            // NOTE: fileinfo doesn't like all magic files. ensure it is pointing to a compatible one if it does not work.

            // first check the path in the stack
        	$defaultMagicPath = KT_DIR . '/../php/extras/magic';
        	$defaultMagicPath = realpath($defaultMagicPath);

        	// if not available, attempt path from config
        	if ($defaultMagicPath === false) {
        	    $oKTConfig =& KTConfig::getSingleton();
        	    $defaultMagicPath = $oKTConfig->get('magicDatabase');

        	    if (!file_exists($defaultMagicPath)) {
        	        $defaultMagicPath = false;
        	    }
        	}

        	// attempt file info if magic file is resolved
        	if ($defaultMagicPath) {
        	    $res = @finfo_open(FILEINFO_MIME, $defaultMagicPath);
                $sType = @finfo_file($res, $sFileName);

                // saw mention that finfo_file() can return empty string under windows
                if (empty($sType)) {
                    $sType = false;
                }
        	}
        }

        /*
        Removing this as it has been causing issues. It returns a word mimetype for unrecognised file types
        if (!$sType && OS_UNIX) {
            if (file_exists('/usr/bin/file')) {
                $aCmd = array('/usr/bin/file', '-bi', $sFileName);
                $sCmd = KTUtil::safeShellString($aCmd);
                $sPossibleType = @exec($sCmd);
                if (preg_match('#^[^/]+/[^/*]+$#', $sPossibleType)) {
                    $sType = $sPossibleType;
                }
            }
        }
        */

        if ($sType) {
            $iSpacePos = strpos($sType, ' ');
            if ($iSpacePos !== false)
            {
                $sType = substr($sType, 0, $iSpacePos);
            }
            return preg_replace('/;.*$/', '', $sType);
        }

        return null;
    }

    function getIconPath($iMimeTypeId, $type = null) {
        $icon = KTUtil::arrayGet($GLOBALS['_KT_icon_path_cache'], $iMimeTypeId);
        if (empty($icon)) {
            $GLOBALS['_KT_icon_path_cache'][$iMimeTypeId] = KTMime::_getIconPath($iMimeTypeId);
            $icon = $GLOBALS['_KT_icon_path_cache'][$iMimeTypeId];
        }

        if(!empty($type)){
            $icon .= '_'.$type;
        }
        return $icon;
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

    /**
     * getAllMimeTypesInformation is a staic function used to get a fuller set of
     * information on the mime types held in the database.
     *
     */
    function getAllMimeTypesInformation()
    {
    	$sTable = KTUtil::getTableName('mimetypes');
    	$aQuery = array('SELECT MT.id, MT.filetypes, MT.mimetypes, MT.icon_path, MT.friendly_name, ME.name as extractor FROM '
    	. $sTable .' MT LEFT JOIN mime_extractors ME ON(MT.extractor_id = ME.id) ORDER BY MT.filetypes', array());
        $res = DBUtil::getResultArray($aQuery);
		return $res;
    }

    /**
     * get all information on all the extractors in the database
     *
     */
    function getMimeExtractorInformation()
    {
    	$aQuery = array('SELECT id, name, active FROM mime_extractors ORDER BY name', array());
        $res = DBUtil::getResultArray($aQuery);
		return $res;
    }

    /**
     *give the mimetype name and get the friendly names and the extensions
     *
     */
    function getFriendlyNameAndExtension($sMimeType)
    {
    	$sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT friendly_name, filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($sMimeType));
        $res = DBUtil::getResultArray($aQuery);
        return $res;
    }

    /**
     * Gets the file type / extension based on the mimetype id
     *
     * @param int $iMimeTypeID
     */
    function getFileType($iMimeTypeID)
    {
    	$sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT filetypes FROM " . $sTable . " WHERE id = ?";
        $aQuery = array($sQuery, array($iMimeTypeID));
        $ext = DBUtil::getOneResultKey($aQuery, 'filetypes');
        return $ext;
    }
}

$_KT_icon_path_cache = array();
