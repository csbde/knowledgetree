<?php
class system extends client_service{
	public function checkVersion(){
		global $default;
       	$user=$this->KT->get_user_object_by_username($this->AuthInfo['user']);
       	$versions=$this->handler->getServerVersions();
       	$bestVer=$versions[count($versions)-1];
       	$clientVer=$this->handler->getVersion();
		$ret=array(
			'response'			=>'pong',
			'loginLocation'		=> '/index.html',
			'versionok'			=>in_array($clientVer,$versions),
			'fullName'			=>PEAR::isError($user)?'':$user->getName(),
			'serverVersions'	=>$versions,
			'serverBestVersion'	=>$bestVer,
			'clientVersion'		=>$clientVer,
			'canUpgradeClient'	=>($clientVer<$bestVer?true:false),
			'canUpgradeServer'	=>($clientVer>$bestVer?true:false)
					
		);
		$this->setResponse($ret);
		return true;
	}
	
	public function jsondecode($params){
		$this->setResponse(@json_decode(trim($params['code'])));
	}
}

?>