<?php

if (defined('QCFG_MANAGER_INC')) {
    return;
}

define ('QCFG_MANAGER_INC', 1);
define ('QCFG_EMPTY', 0);
define ('QCFG_SUCCESS', 1);
define ('QCFG_FAILED', -1);

/**
 * Utility Class to load configuration files in .ini format
 */
class ConfigManager {

    /**
     * Location of the config file
     *
     * @var string
     */
    static private $configLocation;
    /**
     * Loaded config data
     *
     * @var array
     */
    static private $configData;
    /**
     * Load status
     *
     * @var int
     */
    static private $status;
    /**
     * Error message on config read error
     *
     * @var string
     */
    static private $errorMessage;
    
    /**
     * Initialise the config manager - sets the config file to be loaded and sets status to "unread"
     *
     * @param string $configFile path to the config file
     */
    static private function init($configFile)
    {
        self::$configLocation = $configFile;
        self::$status = QCFG_EMPTY;
    }
    
    /**
     * Loads the specified config file
     *
     * @param string $configFile path to the config file [optional]
     */
    static public function load($configFile = null)
    {
        if (!empty($configFile)) {
            self::init($configFile);
        }
        
        // cannot find config file
        if (empty(self::$configLocation) || !file_exists(self::$configLocation)) {
            self::$status = QCFG_FAILED;
            self::$errorMessage = 'Unable to find configuration file: ' . self::$configLocation;
            return;
        }
        
        // load file
        $content = file(self::$configLocation);
        
        // unable to load config file
        if (!is_array($content) || !count($content)) {
            self::$status = QCFG_FAILED;
            self::$errorMessage = 'Unable to load configuration file: ' . self::$configLocation;
            return;
        }
        
        // clear any previously loaded data
        self::$configData = array();
        
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
            // comment line
            else if (preg_match('/^(#.*)\r?\n?$/', $line, $match)) {
                self::$configData[$heading]['QcfgComments'][] = trim($match[1]);
            }
        }
        
        self::$status = QCFG_SUCCESS;
    }
    
    /**
     * Fetch a config section which may contain one or more values
     * Use this when you want the entire section and not just a single value
     *
     * @param string $section the section identifier
     * @return array $configData[$section] the requested section of the config file
     */
    static public function getSection($section)
    {
        $data = self::$configData[$section];
        if (isset($data['QcfgComments'])) {
            unset($data['QcfgComments']);
        }
        
        return $data;
    }
    
    /**
     * Fetch a single value from a an optionally specified section
     * If a section is not specified then the first matching value in any section is returned
     *
     * @param string $cfgKey the key by which to find the value
     * @param string $cfgSection the section in which to look [optional]
     * @return string $value
     */
    static public function getValue($cfgKey, $cfgSection = null)
    {
        foreach (self::$configData as $section => $sectionData) {
            // check section?
            if (!empty($cfgSection) && ($cfgSection!= $section)) continue;
            // check keys
            foreach ($sectionData as $key => $value) {
                // skip comments
                if ($key == 'QcfgComments') {
                    continue;
                }
                if ($key == $cfgKey) {
                    return $value;
                }
            }
        }
    }
    
    /**
     * Gets ALL config data loaded
     */
    static public function getConfigData()
    {
        return self::$configData;
    }
    
    /**
     * Sets the location of the config file
     *
     * @param string $config Path to the config file
     */
    static public function setConfig($config)
    {
        self::$configLocation = $config;
    }
    
    /**
     * Sets config data, overwriting what is currently stored
     *
     * @param array $configData
     * 
     * TODO format checking - reject if not in ConfigManager accepted format
     */
    static public function setConfigData($configData)
    {
        self::$configData = $configData;
    }
    
    /**
     * Writes current data to the current config file or as specified
     */
    static public function writeConfig()
    {        
        $file = fopen(self::$configLocation, 'w');
        if (!$file) {
            self::$status = QCFG_FAILED;
            return;
        }
        foreach (self::$configData as $section => $sectionData) {
            fwrite($file, "[$section]\n");
            
            // write comment lines
            if (isset($sectionData['QcfgComments'])) {
                foreach ($sectionData['QcfgComments'] as $comment) {
                    fwrite($file, "$comment\n");
                }
                unset($sectionData['QcfgComments']);
            }
            
            foreach ($sectionData as $key => $value) {
                fwrite($file, "$key = $value\n");
            }
            // newline...
            fwrite($file, "\n");
        }
        fclose($file);
    }
    
    /**
     * Checks whether an error occurred in loading or reading the config file
     *
     * @return int self::$status
     */
    static public function error()
    {
        return self::$status == QCFG_FAILED;
    }
    
    /**
     * Gets the error message associated with the detected error
     *
     * @return string self::$errorMessage
     */
    static public function getErrorMessage()
    {
        return self::$errorMessage;
    }

}

?>