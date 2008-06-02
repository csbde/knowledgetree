<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
    var $aFileRoot;
    var $flat = array();
    var $flatns = array();
    var $aDBConfig = array();
    var $aConfFile = array();

    // FIXME nbm:  how do we cache errors here?
    function loadCache($filename) {
        $config_str = file_get_contents($filename);
        $config_cache = unserialize($config_str);
        $this->flat = $config_cache['flat'];
        $this->flatns = $config_cache['flatns'];
        $this->expanded = $config_cache['expanded'];
        $this->expanding = $config_cache['expanding'];
        /*
        print "----- Me\n";
        unset($this->aFileRoot);
        unset($this->aSectionFile);
        var_dump($this);
        print "----- Cache\n";
        var_dump($config_cache);
        */

        return true;
    }
    	
	// {{{ readConfig
    function readConfig () {
        global $default;
        
        //Load config data from the database
        $sQuery = 'select group_name, item, value, default_value from config_settings';
        $confResult = DBUtil::getResultArray($sQuery);

        foreach ($confResult as $confItem) 
        {
            
            //if $aConfFile doesn't contain the value already set the value

            if(!isset($this->aConfFile[$confItem['group_name']][$confItem['item']]) || $this->aConfFile[$confItem['group_name']][$confItem['item']] == 'default')
            {
	            if($confItem['value'] != 'default')
	            {
	            	$this->flatns[$confItem['group_name'].'/'.$confItem['item']] = $confItem['value'];
	            	$this->flat[$confItem['item']] = $confItem['value'];

	            }
	            else
	            {
	            	$this->flatns[$confItem['group_name'].'/'.$confItem['item']] = $confItem['default_value'];
	            	$this->flat[$confItem['item']] = $confItem['default_value'];

	            }
            }
            else //if $aConfFile does have the value set $default and flatns with $aConfFile
            {
            	$this->flatns[$confItem['group_name'].'/'.$confItem['item']] = $this->aConfFile[$confItem['group_name']][$confItem['item']];
            	$this->flat[$confItem['item']] = $this->aConfFile[$confItem['group_name']][$confItem['item']];

            }
        }
        foreach($this->flatns as $sGroupItem => $sValue)
        {
        	$aGroupItemArray = explode('/', $sGroupItem);
        	$default->$aGroupItemArray[1] = $this->expand($this->flatns[$sGroupItem]);
        }
        
    }
    // }}}
	
	// {{{ readDBConfig()
	function readDBConfig()
	{
		$sConfigFile = trim(file_get_contents(KT_DIR .  '/config/config-path'));
        if (KTUtil::isAbsolutePath($sConfigFile)) {
            $res = $this->loadDBFile($sConfigFile);
        } else {
            $res = $this->loadDBFile(sprintf('%s/%s', KT_DIR, $sConfigFile));
        }
	}
	// }}}
	
	// {{{ loadDBFile()
	function loadDBFile($filename, $bDefault = false)
	{
		$c = new Config;
        $root =& $c->parseConfig($filename, "IniCommented");

        if (PEAR::isError($root)) {
            return $root;
        }

        $this->aFileRoot[$filename] =& $root;

        $conf =& $root->toArray();
        
        //Set the database specific config details here
        $this->aDBConfig = $conf['root']['db'];
        
        //load entire config file into $aConfFile array
        //These values will override those given by the database
        //This is in case the system fails and the user cannot get to the admin page
        //all items given the value default will be poplulated either by the DB or
        //by the setdefaultns function
        foreach($conf['root'] as $sItem => $sValue)
        {
        	$this->aConfFile[$sItem] = $sValue;
        }
                
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

		$sUser = 'dbUser';
		$sPass = 'dbPass';
		
		if ($prefix == 'Admin')
		{
			$sUser = 'dbAdminUser';
			$sPass = 'dbAdminPass';
		}
		$dsn = array(
            'phptype'  => $this->aDBConfig['dbType'],
            'username' => $this->aDBConfig[$sUser],
            'password' => $this->aDBConfig[$sPass],
            'hostspec' => $this->aDBConfig['dbHost'],
            'database' => $this->aDBConfig['dbName'],
            'port' => $this->aDBConfig['dbPort']
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

    function createCache($filename) {
        $config_cache = array();
        $config_cache['flat'] = $this->flat;
        $config_cache['flatns'] = $this->flatns;
        $config_cache['expanded'] = $this->expanded;
        $config_cache['expanding'] = $this->expanding;

        file_put_contents($filename, serialize($config_cache));


    }

    function setns($seck, $k, $v, $bDefault = false) {
        if ($v === "default") {
            return;
        } elseif ($v === "true") {
            $v = true;
        } elseif ($v === "false") {
            $v = false;
        }
        $this->flat[$k] = $v;
        if (!is_null($seck)) {
            $this->flatns["$seck/$k"] = $v;
        }
        return;
    }

    function setdefaultns($seck, $k, $v) {
        return $this->setns($seck, $k, $v, true);
    }

    var $expanded = array();
    var $expanding = array();
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
    	$configPath = file_get_contents(KT_DIR . '/config/config-path');

    	if (is_file($configPath))
    	{
    		return $configPath;
    	}
    	else
    	{
    		return KT_DIR . '/' . $configPath;
    	}
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
