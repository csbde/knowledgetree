<?php

if(!defined('LIVE_ACCOUNT_ROUTING_OVERRIDE'))define('LIVE_ACCOUNT_ROUTING_OVERRIDE','LiveAccountRoutingOverrideAccount');

class liveAccountRouting{
	/**
	 * Temporary Help Function - will be removed
	 * TODO: Remove this function when done with dev.
	 */
	public static function out($var,$title=NULL){
		$rnd=uniqid('_');
		$msg=$title?"<H3 onclick=\"var e=document.getElementById('{$rnd}');e.style.display=(e.style.display=='none')?'block':'none'\" style=\"cursor: pointer;\">{$title}</H3>":"";
		$msg.="<pre id=\"{$rnd}\" style=\"display:none;\">".print_r($var,true)."</pre><hr />";
		echo $msg;
	}


	/**
	 * Get the current account name
	 * @return String
	 */
	public static function getAccountName(){
		$acct=null;
		$domain_parts = explode('.',strtolower($_SERVER['HTTP_HOST']));
		if (count($domain_parts) == 3 && $domain_parts[0]!= "www") {
			$acct = trim($domain_parts[0]);
		}
		if($_SESSION[LIVE_ACCOUNT_ROUTING_OVERRIDE])$acct=$_SESSION[LIVE_ACCOUNT_ROUTING_OVERRIDE];
//		self::out(array('domain parts'=>$domain_parts,'account'=>$acct),'Account Routing');
		return $acct;
	}
	
	/**
	 * Allow a session-based account name override
	 * @param $accountName		The account with which to override
	 * @return string			The account name before the override was done
	 */
	public static function overrideAccountName($accountName=NULL){
		$oldAcct=self::getAccountName();
		$_SESSION[LIVE_ACCOUNT_ROUTING_OVERRIDE]=$accountName;
		return $oldAcct;
	}
	
	/**
	 * Clear the account override
	 * @return string 			The account name before the override was cleared
	 */
	public static function clearAccountNameOverride(){
		$oldAcct=self::getAccountName();
		unset($_SESSION[LIVE_ACCOUNT_ROUTING_OVERRIDE]);
		return $oldAcct;
	}
	
}

?>