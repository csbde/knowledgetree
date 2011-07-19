<?php

class KTMemcache
{
    protected static $ktMemcache;
    protected $memcache;
    protected $isEnabled = false;
    protected $serverList = array();
    protected $configuredServers;
    protected static $errors = array();
    protected static $extensionDisabled = false;
     
	private $persist = true;
	private $weight = 1;
	private $timeout = 0.1;
	private $retryInterval = 15;

    protected function __construct($configuredServers = null)
    {
    	if (self::$extensionDisabled) {
    		$this->isEnabled = false;
    		$this->memcache = false;
    		
    		return;
    	}
    	
    	if (!empty($configuredServers)) {
    		$this->configuredServers = $configuredServers;
    	}
    	
        $this->isEnabled = $this->connect();
    }
    
    public function isEnabled()
    {
    	return $this->isEnabled;
    }

    public function getErrors()
    {
    	return self::$errors;
    }
    
    public function get($key)
    {
    	$value = $this->memcache->get($key);
    	return $value;
    }
    
    public function set($key, $value = null, $expiration = 0)
    {
		foreach($this->serverList as $server) {
			$host = $server['url'];
			$port = $server['port'];
			
			$_test = new memcache();
			if (!$_test->connect($host, $port)) {
				continue;
			}
			
			if ($_test->get($key) === false) {
				$_test->set($key, $value, null, $expiration);
			}
			else {
				$_test->replace($key, $value, null, $expiration);
			}
		}
    }
    
    public function delete($key, $timeout = 0)
    {
    	if ($timeout == 0) {
    		$timeout = 1;
    	}
    	$this->set($key, '', $timeout);
    }
    
	/**
	 * @param string $configuredServers Optional. Used when overriding the memcache server list stored in the conf file.
	 */
    public static function getKTMemcache($configuredServers = null)
    {
        if (!isset(self::$ktMemcache) || empty(self::$ktMemcache)){
        	$extension = self::checkExtension();
        	
        	if ($extension === false) {
        		self::$extensionDisabled = true;
        	}
        	
            self::$ktMemcache = ($extension == 'memcached') ?  new KTMemcached($configuredServers) : new KTMemcache($configuredServers);
        }
        if (!isset(self::$ktMemcache->memcache)){
        	$this->isEnabled = self::$ktMemcache->connect();
        }
        
        return self::$ktMemcache;
    }
    
    private static function checkExtension()
    {
    	if (extension_loaded('memcache')) {
    		return 'memcache';
    	}
    	else if (extension_loaded('memcached')) {
    		return 'memcached';
    	}
		self::$errors[] = "MEMCACHE PHP Extension is not enabled";
    		
		return false;
    }
    
    protected function connect()
    {
    	$servers = $this->getServerList();
    	
    	if (!$servers || empty($servers)) {
    		self::$errors[] = 'No Memcache servers have been defined.';
    		
    		return false;
    	}
    	
    	// Allow failover to other servers in the pool
    	// Prevent keys being remapped on failover
    	ini_set('memcache.allow_failover', true);
		ini_set('memcache.hash_strategy', 'consistent');
    	
		$this->memcache = new memcache();
		$_test = new memcache();
		
		foreach($servers as $server) {
			if (!is_array($server)) {
				continue;
			}
			
			$host = $server['url'];
			$port = $server['port'];
			
			if (@$_test->connect($host, $port)) {
				$this->serverList[] = $server;
				$this->memcache->addServer($server['url'], $server['port'], $this->persist, $this->weight, $this->timeout, $this->retryInterval);
			}
			else {
				self::$errors[] = "MEMCACHE - Failed to connect to {$host}:{$port}";
			}
		}
		
		
		if (count($this->serverList) <= 0) {
			self::$errors[] = "MEMCACHE - No Memcache Servers Available.";
			
			return false;
		}
		
		return true;
    }
    
    protected function getServerList()
    {
    	if (!empty($this->configuredServers) && is_string($this->configuredServers)) {
    		$serverList = $this->configuredServers;
    	}
    	else {
	    	$config = KTConfig::getSingleton();
	    	$result = $config->parseKTCnf();
	    	
	    	if (!$result) {
	    		return false;
	    	}
	
	        $serverList = $config->get('memcache/servers', false);
	        $this->configuredServers = $serverList;
    	}

        if ($serverList == false) {
            return false;
        }

        $serverList = explode('|', $serverList);
        $servers = array();

        foreach ($serverList as $server) {
            if (empty($server)) {
                continue;
            }

            $hostPort = explode(':', $server);

            $servers[] = array(
                'url' => $hostPort[0],
                'port' => (int)(isset($hostPort[1])) ? $hostPort[1] : 11211
                );
        }

        return $servers;
    }
}

class KTMemcached extends KTMemcache 
{
    protected function connect()
    {
    	$servers = $this->getServerList();
    	
    	if (!$servers || empty($servers)) {
    		self::$errors[] = 'No Memcache servers have been defined.';
    		
    		return false;
    	}
    	
        $this->memcache = new Memcached();

        foreach ($servers as $server) {
        	if (!is_array($server)) {
				continue;
			}
			
			$host = $server['url'];
			$port = $server['port'];
			
            $result = $this->memcache->addServer($host, $port);

            if (!$result){
                self::$errors[] = "MEMCACHE - Failed to connect to {$host}:{$port}";
            }
            else {
            	$this->serverList[] = $server;
            }
        }

		if (count($this->serverList) <= 0) {
			self::$errors[] = "MEMCACHE - No Memcache Servers Available.";
			
			return false;
		}
		
		return true;
    }
    
    public function set($key, $value = null, $expiration = 0)
    {
    	$result = $this->memcache->replace($key, $value, $expiration);
    	if ($result == false) {
    		$this->memcache->set($key, $value, $expiration);
    	}
    }
    
    public function delete($key, $timeout = 0)
    {
    	$this->memcache->delete($key, $timeout);
    }
    
    public function flush()
    {
        $res = $this->memcache->flush();
        return $res;
    }

}

?>