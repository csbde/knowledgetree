<?php

class liveAccounts{
	/**
	 * Detect whether the supplied account name exists
	 * 
	 * @param String $accountName	The name of the account to test
	 * @return boolean
	 */
	public static function accountExists($accountName=NULL){
		$exists=false;
		if($accountName)$exists=true;
		
		//TEST OVERRIDE
//		$exists=false;
		
		return $exists;
	}
	
	public static function accountEnabled($accountName=NULL){
		$enabled=false;
		if($accountName)$enabled=true;
		
		//TEST OVERRIDE
		$enabled=false;
		
		return $enabled;
	}
}

?>