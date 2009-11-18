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

require_once("Config.php");

require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once (KT_LIB_DIR. '/database/dbutil.inc');

class KTConfig {
    var $conf = array();
    var $aSectionFile;
    var $flat = array();
    var $flatns = array();
    var $expanded = array();
    var $expanding = array();

    /**
     * Get the path to the cache file for the config settings
     *
     * @return string
     */
    static function getCacheFilename()
    {
        $pathFile = KT_DIR .  '/config/cache-path';

        if(!file_exists($pathFile)){
            return false;
        }

        // Get the directory containing the file, append the file name
        $cacheFile = trim(file_get_contents($pathFile));
        $cacheFile .= '/configcache';

        // Ensure path is absolute
        $cacheFile = (!KTUtil::isAbsolutePath($cacheFile)) ? sprintf('%s/%s', KT_DIR, $cacheFile) : $cacheFile;

        return $cacheFile;
    }

    // FIXME nbm:  how do we cache errors here?
    function loadCache() {
        $filename = $this->getCacheFilename();
        if($filename === false){
            return false;
        }

        $config_str = file_get_contents($filename);
        $config_cache = unserialize($config_str);
        $this->flat = $config_cache['flat'];
        $this->flatns = $config_cache['flatns'];
        $this->expanded = $config_cache['expanded'];
        $this->expanding = $config_cache['expanding'];

        if(empty($this->flatns)){
            return false;
        }
        $this->populateDefault();
        return true;
    }

    function createCache() {
        $filename = $this->getCacheFilename();

        $config_cache = array();
        $config_cache['flat'] = $this->flat;
        $config_cache['flatns'] = $this->flatns;
        $config_cache['expanded'] = $this->expanded;
        $config_cache['expanding'] = $this->expanding;

        file_put_contents($filename, serialize($config_cache));
    }

    /**
     * Delete the cache so it can be refreshed on the next page load
     *
     * @param string $filename
     */
    function clearCache()
    {
        $filename = $this->getCacheFilename();
        if($filename !== false && file_exists($filename)){
            @unlink($filename);
        }
    }

	// {{{ readConfig
    function readConfig () {
        //Load config data from the database
        $sQuery = 'select group_name, item, value, default_value from config_settings';
        $confResult = DBUtil::getResultArray($sQuery);

        if(PEAR::isError($confResult)){
            return $confResult;
        }

        // Update the config array - overwrite the current settings with the settings in the database.
        foreach ($confResult as $confItem)
        {
            $this->setns($confItem['group_name'], $confItem['item'], $confItem['value'], $confItem['default_value']);
        }
        $this->populateDefault();
    }
    // }}}

    /**
     * Populate the global default array
     *
     */
    function populateDefault()
    {
        global $default;

        foreach($this->flatns as $sGroupItem => $sValue)
        {
        	$aGroupItemArray = explode('/', $sGroupItem);
        	$default->$aGroupItemArray[1] = $this->expand($this->flatns[$sGroupItem]);
        }
    }

	// {{{ readDBConfig()
	function readDBConfig()
	{
        $filename = $this->getConfigFilename();

		$c = new Config;
        $root =& $c->parseConfig($filename, "IniCommented");

        if (PEAR::isError($root)) {
            return $root;
        }

        $conf = $root->toArray();

        // Populate the flat and flatns array with the settings from the config file
        // These setting will be overwritten with the settings from the database.
        if(isset($conf['root']) && !empty($conf['root'])){
            foreach($conf['root'] as $group => $item){
                foreach ($item as $key => $value){
                    $this->setns($group, $key, $value, false);
                }
            }
        }
        $this->populateDefault();
	}
	// }}}

	function setupDB () {

        global $default;

        require_once('DB.php');

        // DBCompat allows phplib API compatibility
        require_once(KT_LIB_DIR . '/database/dbcompat.inc');
        $default->db = new DBCompat;

        // DBUtil is the preferred database abstraction
        require_once(KT_LIB_DIR . '/database/dbutil.inc');

        // KTEntity is the database-backed base class
        require_once(KT_LIB_DIR . '/ktentity.inc');

        $prefix = defined('USE_DB_ADMIN_USER')?'Admin':'';

		$sUser = 'db/dbUser';
		$sPass = 'db/dbPass';

		if ($prefix == 'Admin')
		{
			$sUser = 'db/dbAdminUser';
			$sPass = 'db/dbAdminPass';
		}
		$dsn = array(
            'phptype'  => $this->flatns['db/dbType'],
            'username' => $this->flatns[$sUser],
            'password' => $this->flatns[$sPass],
            'hostspec' => $this->flatns['db/dbHost'],
            'database' => $this->flatns['db/dbName'],
            'port' => $this->flatns['db/dbPort']
        );

        $options = array(
            'debug'       => 2,
            'portability' => DB_PORTABILITY_ERRORS,
            'seqname_format' => 'zseq_%s',
        );

        $default->_db = &DB::connect($dsn, $options);
        if (PEAR::isError($default->_db)) {
            // return PEAR error
            return $default->_db;
        }
        $default->_db->setFetchMode(DB_FETCHMODE_ASSOC);
    }

    function setns($seck, $k, $v, $bDefault = false) {
        // If the value is default then set it to the default value
        if ($v === 'default') {
            // If there is no default then ignore the value
            if($bDefault === false){
                return;
            }
            $v = $bDefault;
        }

        // If the value is true / false, set it as a boolean true / false
        if ($v === 'true') {
            $v = true;
        } elseif ($v === 'false') {
            $v = false;
        }

        // Set the config arrays
        $this->flat[$k] = $v;
        if (!is_null($seck)) {
            $this->flatns["$seck/$k"] = $v;
        }
        return;
    }

    function setdefaultns($seck, $k, $v) {
        $this->setns($seck, $k, $v, true);

        global $default;
        $default->$k = $this->expand($this->flatns["$seck/$k"]);
    }

    function expand($val) {
        if (strpos($val, '$') === false) {
            return $val;
        }
        $v = $val;
        while(($m = preg_match('/\$\{([^}]+)\}/', $v, $matches))) {
            array_push($this->expanding, $matches[1]);
            $r = $this->get($matches[1]);
            if (PEAR::isError($r)) {
                return $r;
            }
            $v = str_replace($matches[0], $r, $v);
            $this->expanded[$matches[1]] = $r;
        }
        return $v;
    }

    function get($var, $oDefault = null) {
	    if (array_key_exists($var, $this->flatns)) {
            return $this->expand($this->flatns[$var]);
        }
        if (array_key_exists($var, $this->flat)) {
            return $this->expand($this->flat[$var]);
        }
        return $oDefault;
    }

    /**
     * Return the location of the config.ini
     *
     * @return string
     */
    static function getConfigFilename()
    {
        $pathFile = KT_DIR . '/config/config-path';
        $configFile = trim(file_get_contents($pathFile));

        $configFile = (!KTUtil::isAbsolutePath($configFile)) ? sprintf('%s/%s', KT_DIR, $configFile) : $configFile;

        // Remove any double slashes
        $configFile = str_replace('//', '/', $configFile);
        $configFile = str_replace('\\\\', '\\', $configFile);

    	if (file_exists($configFile))
    	{
    		return $configFile;
    	}
    	else
    	{
    		return KT_DIR . DIRECTORY_SEPARATOR . $configFile;
    	}
    }

    /**
     * Load a config file
     * Used for the unit tests
     *
     * @param unknown_type $filename
     * @param unknown_type $bDefault
     * @return unknown
     */
    function loadFile($filename, $bDefault = false) {
        $c = new Config;
        $root =& $c->parseConfig($filename, "IniCommented");

        if (PEAR::isError($root)) {
            return $root;
        }

        $this->aFileRoot[$filename] =& $root;

        $conf =& $root->toArray();
        foreach ($conf["root"] as $seck => $secv) {
            $aSectionFile[$seck] = $filename;
            if (is_array($secv)) {
                foreach ($secv as $k => $v) {
                    $this->setns($seck, $k, $v);
                }
            } else {
                $this->setns(null, $seck, $secv);
            }
        }
        $this->conf = kt_array_merge($this->conf, $conf["root"]);
    }

    static function &getSingleton() {
    	static $singleton = null;

    	if (is_null($singleton))
    	{
    		$singleton = new KTConfig();
    	}
    	return $singleton;
    }
}


?>
