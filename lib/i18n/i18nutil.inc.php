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

class KTi18nUtil {
    function setup() {
    }

    function getInstalledLocales() {
        global $default;
        // get a list of directories in ($default->fileSystemRoot . "/i18n")
        $aLocales = array();
        $aLocales["en"] = "en";
        $aLocales["en-GB"] = "en";
        $aLocales["en-UK"] = "en";
        $aLocales["en-US"] = "en";
        $aLocales["en-ZA"] = "en";
        $dir = KT_DIR . '/i18n';
        if ($handle = opendir($dir)) {
           while (false !== ($file = readdir($handle))) {
               if (in_array($file, array('.', '..', 'CVS'))) {
                   continue;
               }
               if (is_dir($dir . '/' . $file)) {
                   $i = strpos($file, '_');
                   if ($i) {
                       $aLocales[substr($file, 0, $i)] = $file;
                   }
                   $aLocales[strtr($file, '_', '-')] = $file;
               }
           }
           closedir($handle);
        }
        return $aLocales;
    }
}
