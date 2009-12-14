<?php
class Clienttools_Syslog{
	/** The default folder in which to put the log files **/
	private static $logFolder='/var/log/';
	private static $debugLogTemplate='[date] | [time] | INFO | [user] | [location] | [debug_message] | ([debug_data])';
	private static $errorLogTemplate='[date] | [time] | ERROR | [user] | [location] | [error_type] | [error_code] | [error_message] | [trace_info]';
	
	
	private static function parseTemplate($template=NULL,$data=NULL){
		$ret=null;
		if(is_array($data)){
			$txs=array_keys($data);
			$txd=array_values($data);
			$ret=str_replace($txs,$txd,$template);
		};
		return $ret;
	}
	
	
	/**
	 * Return the calculated log file name
	 * @return void
	 */
	private static function getLogFile(){
		$fileName=self::$logFolder.'kt_clienttools_'.date('Y-m-d').'.log.txt';
		return $fileName;
	}
	
	/**
	 * Return a boolean indicating whether error logging should be done
	 * @return boolean
	 */
	private static function doErrorLogging(){
//		$GLOBALS['default']['debugLevel'];		//Another less secure way of finding the configured debugLevel
		return KTConfig::getSingleton()->get('explorerCPSettings/debugLevel')=='error' || self::doDebugLogging();
	}
	
	/**
	 * Return a boolean indicating whether debug logging should be done
	 * @return boolean
	 */
	private static function doDebugLogging(){
		return KTConfig::getSingleton()->get('explorerCPSettings/debugLevel')=='debug';
	}
	
	public static function logInfo(){
		
	}
	
	public static function logError(){
		
	}
}
?>