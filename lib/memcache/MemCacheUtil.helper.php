<?php
/**
 * This memcache helper assumes the old memcache library NOT memcached
 * @author mark
 *
 */

//TODO: change the connection type to addServer (with weights) and alter the failover method to rely on memcache not the current method
//class MemCacheUtil {
//	const FAILOVER=true;
//
//	private static $servers=NULL;
//	private static $primary=NULL;
//	private static $instances=NULL;
//
//
//	private function __construct(){
//		//Keep this class static
//	}
//
//	/**
//	 * Initialize the servers
//	 *
//	 * @param $servers		An array containing arrays with url | port info
//	 * @return unknown_type
//	 */
//	public static function init($servers=NULL){
//	    if(self::isInitialized()){
//	        return true;
//	    }
//
//		if(is_array($servers)){
//			foreach($servers as $server){
//				if(is_array($server)){
//					if(isset($server['url']) && isset($server['port'])){
//						if(!is_array(self::$servers))self::$servers=array();
//						$idx=count(self::$servers);
//						self::$servers[$idx]=array('url'=>$server['url'],'port'=>$server['port']);
//						self::$instances[$idx]=memcache_connect($server['url'],$server['port']);
//						memcache_add_server(self::$instances[$idx],$server['url'],$server['port'],2,2);
//					}else{
//						throw new Exception("Memcache server item must be an array containing 'url' and 'port' as keys.");
//					}
//				}else{
//					throw new Exception("Memcache server item must be an array containing 'url' and 'port' as keys.");
//				}
//			}
//		}
//	}
//
//	public static function isInitialized(){
//		return is_array(self::$instances);
//	}
//
//
//	public static function instance(){
//		if(self::$primary==NULL)self::$primary=rand(0,count(self::$instances)-1);
//		$cnt=0;
//		while(self::$instances[self::$primary]->getServerStatus(self::$servers[self::$primary]['url'],self::$servers[self::$primary]['port'])!==0 && $cnt<count(self::$servers)-1){
//			$cnt++;
//			self::$primary=self::$primary >= count(self::$servers)-1?0:(self::$primary+1);
////			echo 'Primary Now: '.self::$primary.'<br />';
//		}
//		if($cnt<count(self::$servers)){
//			return self::$instances[self::$primary];
//		}else{
//			throw new Exception('Failed to find an available memcache server');
//		}
//	}
//
//	public static function put($key,$value){
//		if(self::isInitialized()){
//			if(self::FAILOVER){
//				foreach(self::$instances as $idx=>$instance){
//					if($instance->getServerStatus(self::$servers[$idx]['url'],self::$servers[$idx]['port'])){
//						$instance->set($key,$value);
//					}
//				}
//			}else{
//				//TODO: make primary a function and test for server available first then roundrobbin until server avail
//				self::instance()->set($key,$value);
//			}
//		}else{
//			throw new Exception("Cannot use MC before you init it with the server list");
//		}
//	}
//
//	public static function replace($key,$value){
//		if(self::isInitialized()){
//			if(self::FAILOVER){
//				foreach(self::$instances as $idx=>$instance){
//					if($instance->getServerStatus(self::$servers[$idx]['url'],self::$servers[$idx]['port'])){
//						$instance->replace($key,$value);
//					}
//				}
//			}else{
//				//TODO: make primary a function and test for server available first then roundrobbin until server avail
//				self::instance()->replace($key,$value);
//			}
//		}else{
//			throw new Exception("Cannot use MC before you init it with the server list");
//		}
//	}
//
//	public static function get($key){
//		//TODO: make primary a function and test for server available first then roundrobbin until server avail
//		return self::instance()->get($key);
//	}
//
//	/**
//	 * Remove a cached item of data from memcache
//	 *
//	 * @param $key
//	 * @return unknown_type
//	 */
//	public static function delete($key){
//	    if(self::isInitialized()){
//    		foreach(self::$instances as $instance){
//    			$res = $instance->delete($key);
//    		}
//	    }
//	}
//
//}
class MemCacheUtil extends ktMemcachePool{}

?>