<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
    var $confPath = '';

    /**
     * Get the path to the cache file for the config settings
     *
     * @return string
     */
    static function getCacheFilename()
    {
        if(ACCOUNT_ROUTING_ENABLED){
            return ACCOUNT_NAME . '-configcache';
        }

        $pathFile = KT_DIR .  '/config/cache-path';

        if(!file_exists($pathFile)){
            return false;
        }

        // Get the directory containing the file, append the file name
        $cacheFile = trim(file_get_contents($pathFile));
        // if we are on an account name routing system (i.e. a shared system,) use the account name to distinguish config cache files
        $cacheFile .= '/' . (defined('ACCOUNT_NAME') ? ACCOUNT_NAME : '') . 'configcache';

        // Ensure path is absolute
        $cacheFile = (!KTUtil::isAbsolutePath($cacheFile)) ? sprintf('%s/%s', KT_DIR, $cacheFile) : $cacheFile;

        return $cacheFile;
    }

    function setMemcache()
    {
        if(MemCacheUtil::isInitialized()){
            return true;
        }

        $this->confPath = '/etc/kt';

        $filename = $this->confPath . '/kt.cnf';

		$c = new Config;
        $root =& $c->parseConfig($filename, "IniCommented");

        if (PEAR::isError($root)) {
            return false;
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

        $server_list = $this->get('memcache/servers', false);
        if($server_list == false){
                return false;
        }
        $filename = $this->getCacheFilename();

        $server_arr = explode(';', $server_list);
        $servers = array();

        foreach ($server_arr as $server){

            $portArr = explode('|', $server);

            $servers[] = array(
                'url' => $portArr[0],
                'port' => (int)(isset($portArr[1])) ? $portArr[1] : 11211
                );
        }

        try {
            MemCacheUtil::init($servers);
        }catch (Exception $e){
            return false;
        }
        return true;
    }

    // FIXME nbm:  how do we cache errors here?
    function loadCache() {
        $filename = $this->getCacheFilename();
        if($filename === false){
            return false;
        }

        if(ACCOUNT_ROUTING_ENABLED){
            $config_str = MemCacheUtil::get($filename);
        }else{
            $config_str = file_get_contents($filename);
        }

        if(empty($config_str)){
            return false;
        }


        $config_cache = unserialize($config_str);
        $this->flat = $config_cache['flat'];
        $this->flatns = $config_cache['flatns'];
        $this->expanded = (isset($config_cache['expanded'])) ? $config_cache['expanded'] : array();
        $this->expanding = (isset($config_cache['expanding'])) ? $config_cache['expanding'] : array();

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

        $config_cache = serialize($config_cache);

        if(ACCOUNT_ROUTING_ENABLED){
            $this->setMemcache();
            MemCacheUtil::put($filename, $config_cache);
            return true;
        }

        file_put_contents($filename, $config_cache);
    }

    /**
     * Delete the cache so it can be refreshed on the next page load
     *
     * @param string $filename
     */
    function clearCache()
    {
        $filename = $this->getCacheFilename();

        if(ACCOUNT_ROUTING_ENABLED){
            MemCacheUtil::delete($filename);
            return true;
        }

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
                	if(ACCOUNT_ROUTING_ENABLED){
                		if($key=='dbName'){
                			// TODO : Testing purposes only, remove if statement only.
                			if(!isset($_SESSION[LIVE_DATABASE_OVERRIDE]))
                			{
                				$value=ACCOUNT_NAME;
                			}
                		}
                	}
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
            'port' => isset($this->flatns['db/dbPort']) ? $this->flatns['db/dbPort'] : ''
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
     * Set a config value called $var to $value
     * @param string $var config variable and group in string like "ui/mainLogoTitle"
     * @param string $value a string with the value you want for the config item.
     * @param integer $can_edit Default null. Determines if the setting is available for editing by the admin user.
     * @return boolean
     */
    function set($var = null, $value = null, $can_edit = null) {
        global $default;

        if ($var == null) {
            return false;
        }

        $varParts = explode('/', $var);
        $groupName = $varParts[0];
        $var = $varParts[1];

        if ($var == '' || $groupName == ''){
            //var and group must be set
            $default->log->error("config->set() requires the first parameter to be in the form 'groupName/configSetting'");
            return false;
        }

        $sql = "SELECT id from config_settings WHERE item = '$var' and group_name = '$groupName'";
        $configId = DBUtil::getOneResultKey($sql,'id');
        if (PEAR::isError($configId))
        {
            $default->log->error(sprintf(_kt("Couldn't get the config id:%s"), $configId->getMessage()));
            return false;
        }

        //If config var doesn't exist we create it
        if ($configId == null) {
            if(!is_numeric($can_edit)){
                $can_edit = 1;
            }
            $configId = DBUtil::autoInsert('config_settings', array('item' => $var ,'value' => $value, 'group_name' => $groupName, 'can_edit' => $can_edit));

            if (PEAR::isError($configId))
            {
                $default->log->error(sprintf(_kt("Couldn't insert config value:%s"), $configId->getMessage()));
                return false;
            }

        } else {

            $fieldValues = array('value' => $value);
            if(is_numeric($can_edit)){
                $fieldValues['can_edit'] = $can_edit;
            }
            $res = DBUtil::autoUpdate('config_settings', $fieldValues, $configId);
            if (PEAR::isError($res)) {
                $default->log->error(sprintf(_kt("Couldn't update config value: %s"), $res->getMessage()));
                return false;
            }
        }

        $this->clearCache();

        return true;
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

        $conf = $root->toArray();
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