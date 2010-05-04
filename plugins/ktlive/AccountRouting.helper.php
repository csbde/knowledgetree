<?php 
class AccountRouting{
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
	 * Account Routing: Detect Account
	 */
	public static function getAccountName(){
		$acct=null;
		$domain_parts = explode('.',strtolower($_SERVER['HTTP_HOST']));
		if (count($domain_parts) == 3 && $domain_parts[0]!= "www") {
			$acct = trim($domain_parts[0]);
		}
//		self::out(array('domain parts'=>$domain_parts,'account'=>$acct),'Account Routing');
		return $acct;
	}
}

?>