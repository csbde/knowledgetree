<?php

class ktMemcachePool{
	const PERSIST=true;
	const WEIGHT=1;
	const TIMEOUT=0.1;
	const RETRY_INTERVAL=15;
	const COMPRESS=true;
	const SELF_REPLICATION=true;
	const THROW_NO_SERVER_EXCEPTION=false;
	
	private $servers=array();
	private $mc=NULL;
	
	public static $instance=NULL;
	public static $enabled=false;
	public static $errors=array();
	
	private function __construct($servers){
		if(!function_exists('memcache_connect')){
			self::$errors[]="MEMCACHE PHP Extension not included";
			return;
		}
		
		ini_set('memcache.allow_failover', true);
		ini_set('memcache.hash_strategy', 'consistent');		
		
		$this->mc=new memcache();
		$_test=new memcache();
		
		if(is_array($servers)){
			self::$enabled=true;
			foreach($servers as $server){
				if(is_array($server)){
					if(isset($server['url']) && isset($server['port'])){
						$host=$server['url'];
						$port=$server['port'];
						
//						echo '<pre><h3>Servers</h3>'.print_r($servers,true).'</pre><hr />';
						
						if(@$_test->connect($host,$port)){
							$this->servers[]=array('url'=>$host,'port'=>$port);
							$this->mc->addServer($server['url'],$server['port'],self::PERSIST,self::WEIGHT,self::TIMEOUT,self::RETRY_INTERVAL);
						}else{
							self::$errors[]="MEMCACHE - Failed to connect to {$host}::{$port}";
						}
					}else{
						throw new Exception("Memcache server item must be an array containing 'url' and 'port' as keys.");
					}
				}else{
					throw new Exception("Memcache server item must be an array containing 'url' and 'port' as keys.");
				}
			}
		}

		if(count($this->servers)<=0){
			self::$enabled=false;
			self::$errors[]="MEMCACHE - No Memcache Servers Available.";
			if(self::THROW_NO_SERVER_EXCEPTION) throw new Exception("No Memcache Servers Available.");
		}
	}
	
	public static function init($servers){
		$class=__CLASS__;
		self::$instance=new $class($servers);
		return self::$enabled;
	}
	
	private static function instance(){
		if(isset(self::$instance)){
			return self::$instance;
		}else{
			throw new Exception("Cannot use ".__CLASS__." without first initializing (".__CLASS__."::init(\$serverArray))");
		}
	}
	
	public static function get($key=NULL){
		return self::instance()->_get($key);
	}
	
	public static function set($key=NULL,$value=NULL){
		return self::instance()->_set($key,$value);
	}
	
	public static function clear($key=NULL){
		return self::instance()->_clear($key);
	}
	
	public static function getServers(){
		return self::instance()->servers;
	}
	
	/***************************************************************************** /
		Private Functions for use only by the singleton (self::$instance)
	/*****************************************************************************/
	
	private function _get($key=NULL){
		if($key){
			return @$this->mc->get($key);
		}else{
			return NULL;
		}	
	}
	
	private function _set($key,$value){
		$ret=false;
		if(self::SELF_REPLICATION){
	
			foreach($this->servers as $server){
				$_test=new memcache();
				$host=$server['url'];
				$port=$server['port'];
				if($_test->connect($host,$port)){
					if(@$_test->get($key)!=false){
						$ret=$_test->replace($key,$value);
					}else{
						$ret=$_test->set($key,$value);
					}
				}
			}
		}else{
			if($this->get($key)==false){
				$ret=$this->mc->set($key,$value,self::COMPRESS,50);
			}else{
				$ret=$this->mc->replace($key,$value);
			}
		}
		return $ret;
	}
	
	private function _clear($key){
		$this->set($key,'');
	}
}

?>