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

function _kt($sContents, $sDomain = 'knowledgeTree') {
    $oReg =& KTi18nRegistry::getSingleton();
    $oi18n =& $oReg->geti18n($sDomain);
    return $oi18n->gettext($sContents);
}
