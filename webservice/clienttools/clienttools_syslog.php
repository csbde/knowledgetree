<?php
class Clienttools_Syslog{
	/** The default folder in which to put the log files **/
	private static $logFolder='../../var/log/';
	private static $debugLogTemplate='[date] | [time] | INFO | [session] | [user] | [location] | [debug_message] | ([debug_data])';
	private static $traceLogTemplate='[date] | [time] | LOG | [session] | [user] | [location] | [trace_message]';
	private static $errorLogTemplate='[date] | [time] | ERROR | [session] | [user] | [location] | [error_detail] | ([error])';
	
	
	private static function parseTemplate($template=NULL,$data=NULL){
		$ret=null;
		if(is_array($data)){
			$txs=array_keys($data);
			$txd=array_values($data);
			$ret=str_replace($txs,$txd,$template);
		};
//		echo print_r(Array('s'=>$txs,'d'=>$txd),true)."\n\n\n\n\n\n";
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
	
	
	private static function writeLogLine($line=NULL){
//		echo('LOGFILE: '.realpath(self::getLogFile()));
		if($line){
			$fp=fopen(self::getLogFile(),'a');
			fwrite($fp,$line."\n");
			fclose($fp);
		}
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
	
	public static function logInfo($user,$location,$message,$data){
		$entry=self::parseTemplate(self::$debugLogTemplate,array(
			'date'	=>date('Y-m-d'),
			'time'	=>date('h:i:s'),
			'user'	=>$user,
			'session'=>session_id(),
			'location'=>$location,
			'debug_message'=>$message,
			'debug_data'=>json_encode($data)
		));
		
		self::writeLogLine($entry);
	}
	
	public static function logTrace($user,$location,$message){
		$entry=self::parseTemplate(self::$traceLogTemplate,array(
			'date'	=>date('Y-m-d'),
			'time'	=>date('h:i:s'),
			'user'	=>$user,
			'session'=>session_id(),
			'location'=>$location,
			'trace_message'=>$message,
		));
		
		self::writeLogLine($entry);
	}
	
	public static function logError($user=NULL,$location=NULL,$detail=NULL,$err=NULL){
		$entry=self::parseTemplate(self::$errorLogTemplate,array(
			'date'	=>date('Y-m-d'),
			'time'	=>date('h:i:s'),
			'user'	=>$user,
			'session'=>session_id(),
			'location'=>$location,
			'error_detail'=>json_encode($detail),
			'error'=>json_encode($err),
		));
		
		self::writeLogLine($entry);
		
	}
}
?>