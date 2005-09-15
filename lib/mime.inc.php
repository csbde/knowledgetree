<?php

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
    * Get the default mime type, which is text/plain
    *
    * @return int default mime type
    *
    */
    function getDefaultMimeTypeID() {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT id FROM " . $default->mimetypes_table . " WHERE mimetypes = 'text/plain'");/*ok*/
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

        if (!$sType && function_exists('mime_content_type')) {
            $sType = @mime_content_type($sFileName);
        }

        if ($sType) {
            return preg_replace('/;.*$/', '', $sType);
        }

        return NULL;
    }

    /**
    * Strip all but the extension from a file. For instance, input of
    * 'foo.tif' would return 'tif'.
    *
    * @param string filename
    * @return string extension for given file, without filename itself
    */
    function stripAllButExtension($sFileName) {
        return strtolower(substr($sFileName, strpos($sFileName, ".")+1, strlen($sFileName) - strpos($sFileName, ".")));
    }
}
