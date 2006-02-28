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

/* help has changed significantly.  see /help.php */

require_once(KT_LIB_DIR . "/database/dbutil.inc");

class KTHelp {

    function getHelpSubPath($sHelpFile) {
        if (empty($sHelpFile)) { return false; }
        $path_segments = explode("/", $sHelpFile);
        // cannot be empty, must contain at _least_ 1 item.
        if (empty($path_segments[0])) {
            $path_segments = array_slice($path_segments,1);
        }
        
        if (empty($path_segments) or (count($path_segments) < 2)) {
            return false;             // FIXME use PEAR::Error
        }
        
        // we now assume that path_segments[0] is the module
        // path_segments[1..] is the subpath.  we need to insert the LANG
        
        $lang_code = 'EN'; // FIXME extract the lang from the environ (?)
        
        $final_path = array(null,'kthelp', $path_segments[0]);
        $final_path[] = $lang_code;
        $final_path = array_merge($final_path, array_slice($path_segments, 1));
        
        $help_path = implode('/',$final_path);
        
        return $help_path;
    }

    function getHelpFromFile($sHelpFile) {
        if (empty($sHelpFile)) { return false; }
        $help_path = KTHelp::getHelpSubPath($sHelpFile);
        
        $fspath = KT_DIR . $help_path;      // FIXME use OS.path_sep equivalent?
        
        if (!file_exists($fspath)) {
            return false;
        } 
        
        if (KTHelp::isImageFile($help_path)) {
            return false; // can't - not what users expect.
        }
        
        // now we ASSUME its html:  we'll fail anyway if we aren't.
        $handle = fopen($fspath, "r");
        $contents = fread($handle, filesize($fspath));
        fclose($handle);
            
        $info = KTHelp::_parseHTML($contents);
            
        $body = KTUtil::arrayGet($info,'body');
        if (empty($body)) {
            return false;
        } 
        
        $info['name'] = $help_path; // set so we can save into db if needed.
        
        return $info;
    }

    // world's simplest (and possibly worst) regex-split.
    function _parseHTML($sHTML) {
        $title_array = preg_split('#</?title>#',$sHTML,-1,PREG_SPLIT_NO_EMPTY);
        $body_array = preg_split('#</?body>#',$sHTML,-1,PREG_SPLIT_NO_EMPTY);
        
        $res = array();
        if (count($title_array) > 2) {
            $res['title'] = $title_array[1];
        }
        
        if (count($body_array) > 2) {
            $res['body'] = $body_array[1];
        }
        
        //var_dump($body_array);
        return $res;
    }
    
    function isImageFile($sHelpPath) {
        // from pluginutil.inc.php
        $fspath = KT_DIR . $sHelpPath;
        
        $pi = pathinfo($fspath);
        $mime_type = "";
        $sExtension = KTUtil::arrayGet($pi, 'extension');
        if (!empty($sExtension)) {
            $mime_type = DBUtil::getOneResultKey(array("SELECT mimetypes FROM " . KTUtil::getTableName('mimetypes') . " WHERE LOWER(filetypes) = ?", $sExtension), "mimetypes");
        }
        
        if (($mime_type == 'image/png') || ($mime_type == 'image/gif') || ($mime_type == 'image/jpeg')) {
            return true;        
        }
        
        return false;
    }
    
    function outputHelpImage($sHelpPath) {
        $fspath = KT_DIR . $sHelpPath;
    
    
        header("Content-Type: $mime_type");
        header("Content-Length: " . filesize($fspath));
        readfile($fspath);   // does this output it?!
        exit(0);
    }

}

?>
