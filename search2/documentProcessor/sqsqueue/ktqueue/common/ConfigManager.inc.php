<?php

if (defined('QCFG_MANAGER_INC')) {
    return;
}

define ('QCFG_MANAGER_INC', 1);
define ('QCFG_EMPTY', 0);
define ('QCFG_SUCCESS', 1);
define ('QCFG_FAILED', -1);

class ConfigManager {

    static private $configLocation;
    static private $configData;
    static private $status;
    static private $errorMessage;
    
    static private function init($configFile = null)
    {
        self::$configLocation = $configFile;
        self::$status = QCFG_EMPTY;
    }
    
    static public function load($configFile = null)
    {
        if (!empty($configFile)) {
            self::init($configFile);
        }
        
        if (empty(self::$configLocation) || !file_exists(self::$configLocation)) {
            self::$status = QCFG_FAILED;
            self::$errorMessage = 'Unable to load configuration file';
            return;
        }
        
        // load file
        $content = file(self::$configLocation);
        
        if (!is_array($content) || !count($content)) {
            self::$status = QCFG_FAILED;
            self::$errorMessage = 'Unable to load configuration file';
            return;
        }
        
        // parse into data array
        foreach($content as $line) {
            // skip blank lines
            if (preg_match('/^ *\r?\n?$/', $line)) {
                continue;
            }
            
            $line = preg_replace('/\r?\n/', '', $line);
            $match = array();
            // heading?
            if (preg_match('/^ *\[([^\]]*)\] *\r?\n?$/', $line, $match)) {
                self::$configData[$match[1]] = array();
                $heading = trim($match[1]);
            }
            // data
            else if (preg_match('/^ *([^=]*) *= *(.*) *\r?\n?$/', $line, $match)) {
                self::$configData[$heading][trim($match[1])] = trim($match[2]); 
            }
        }
    }
    
    static public function getSection($section)
    {
        return self::$configData[$section];
    }
    
    // NOTE that this will break on duplicated keys if a section is not specified - will always return the first found
    static public function getValue($cfgKey, $cfgSection = null)
    {
        foreach(self::$configData as $section => $sectionData) {
            // check section?
            if (!empty($cfgSection) && ($cfgSection!= $section)) continue;
            // check keys
            foreach($sectionData as $key => $value) {
                if ($key == $cfgKey) {
                    return $value;
                }
            }
        }
    }
    
    static public function error()
    {
        return self::$status;
    }
    
    static public function getErrorMessage()
    {
        return self::$errorMessage;
    }

}

?>