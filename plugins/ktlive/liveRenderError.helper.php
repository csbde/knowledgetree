<?php
if(!defined('LIVE_ACCOUNT_MISSING'))define('LIVE_ACCOUNT_MISSING',1);
if(!defined('LIVE_ACCOUNT_DISABLED'))define('LIVE_ACCOUNT_DISABLED',2);
if(!defined('AMAZON_CREDENTIALS_MISSING'))define('AMAZON_CREDENTIALS_MISSING',3);

class liveRenderError{
	/**
	 * Create a liveError page
	 * 
	 * @param $title			The title of the message 
	 * @param $description		The error description
	 * @param $debugObject		Any variable you wish to unpack in the error message
	 * @param $exitCode			The exit code managed by the defines at the top
	 * @return void
	 */
	public static function create($title=NULL,$description=NULL,$debugObject=NULL,$exitCode=NULL){
		$error=new stdClass();
		$error->title=$title;
		$error->description=$description;
		$error->debug=$debugObject;
		self::render($error,$exitCode);
	}
	
	/**
	 * Private function that renders the error object using a template
	 * @param $error	The error object (from self::create)
	 * @param $exitCode	The exitcode to use when exiting php
	 * @return void
	 */
	private static function render($error=NULL,$exitCode=NULL){
		$exitCode=$exitCode?$exitCode:LIVE_ACCOUNT_MISSING;
		include('templates/RenderError.php');
		exit($exitCode);
	}
}

?>