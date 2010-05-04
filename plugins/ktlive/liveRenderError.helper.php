<?php
if(!defined('LIVE_ACCOUNT_MISSING'))define('LIVE_ACCOUNT_MISSING',1);
if(!defined('LIVE_ACCOUNT_DISABLED'))define('LIVE_ACCOUNT_DISABLED',2);

class liveRenderError{
	public static function create($title=NULL,$description=NULL,$debugObject=NULL,$exitCode=NULL){
		$error=new stdClass();
		$error->title=$title;
		$error->description=$description;
		$error->debug=$debugObject;
		self::render($error,$exitCode);
	}
	
	private static function render($error=NULL,$exitCode=NULL){
		$exitCode=$exitCode?$exitCode:LIVE_ACCOUNT_MISSING;
		include('templates/RenderError.php');
		exit($exitCode);
	}
}

?>