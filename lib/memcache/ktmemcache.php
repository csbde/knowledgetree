<?php

class KTMemcache
{
    protected static $ktMemcache;
    protected $memcache;
    protected $isEnabled = false;
    protected $serverList = array();
    protected static $errors = array();
    protected static $extensionDisabled = false;
     
	private $persist = true;
	private $weight = 1;
	private $timeout = 0.1;
	private $retryInterval = 15;

    protected function __construct()
    {
    	if (self::$extensionDisabled) {
    		$this->isEnabled = false;
    		$this->memcache = false;
    		
    		return;
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
			
			if ($this->get($key) === false) {
				$this->memcache->set($key, $value, null, $expiration);
			}
			else {
				$this->memcache->replace($key, $value, null, $expiration);
			}
		}
    }
    
    public function delete($key, $timeout = 0)
    {
    	if ($timeout == 0) {
    		$timeout = 1;
    	}
    	$this->memcache->set($key, '', $timeout);
    }
    
    public static function getKTMemcache()
    {
        if (!isset(self::$ktMemcache) || empty(self::$ktMemcache)){
        	$extension = self::checkExtension();
        	
        	if ($extension === false) {
        		self::$extensionDisabled = true;
        	}
        	
            self::$ktMemcache = ($extension == 'memcached') ?  new KTMemcached() : new KTMemcache();
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
    	$config = KTConfig::getSingleton();
    	$result = $config->parseKTCnf();
    	
    	if (!$result) {
    		return false;
    	}

        $serverList = $config->get('memcache/servers', false);

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