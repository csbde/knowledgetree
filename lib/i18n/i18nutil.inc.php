<?php

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
