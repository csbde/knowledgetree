<?php
/**
* BL information for adding a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("addUserUI.inc");
	require_once("$default->fileSystemRoot/lib/users/User.inc");
	require_once("$default->fileSystemRoot/lib/security/permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();
	//create db object
    	$oDbAuth = new $default->authenticationClass;
    	
	if (isset($fSearch)) {
	
	//get user name
	$sSearch = $fName;	 
    	
    	// user attributes to search for
    		if ($default->authenticationClass == "DBAuthenticator") 	{
    		    	$aAttributes = array ("username", "name", "email", "mobile", "email_notification", "sms_notification");
    		    	$aResults = $oDbAuth->searchUsers($sSearch, $aAttributes);
    		    	$bLdap = false;
    		}else{
    		//if its using LDAP get these attributes
    	 		$aAttributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");
    	 		$aResults = $oDbAuth->searchUsers($sSearch, $aAttributes);
    	 		$bLdap = true;
    		}
     		//post array to page
    		if(isset($aResults))
    		{	if($bLdap == false){
				$oPatternCustom->setHtml(getDetailsDBPage($sSearch,$aResults));
				$main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1&fFromDb=1");
			}else{
				$oPatternCustom->setHtml(getDetailsLDAPPage($sSearch,$aResults));
				$main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1");
			}
			
			
		}else{
			$oPatternCustom->setHtml(getAddPageFail());
			$main->setFormAction($_SERVER["PHP_SELF"]);
		}
		
		
	}else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getAddPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]. "?fSearch=1");
			
	}		


	if(isset($fAddToDb))
	{	//if db authentication
		if(isset($fFromDb)){
			
			//User($sNewUserName, $sNewName, $sNewPassword, $iNewQuotaMax, $sNewEmail, $sNewMobile, $bNewEmailNotification, $bNewSmsNotification, $sNewLdapDn, $iNewMaxSessions, $iNewLanguageID) 
			$oUser = new User($fUsername,$fName,0,0,$fEmail,$fMobile,$fEmailNotification,$fSmsNotification,0,0,0);
			
		}else{
			$oUser = new User($fUsername,$fName,0,0,$fEmail,$fMobile,0,0,$fLdap,0,0);
		}
		
		if($oUser->create()){
			$oPatternCustom->setHtml(getPageSuccess());
		}
		else{
			$oPatternCustom->setHtml(getPageFail());
		}	
	}
	
	//$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/create.php?fRedirectURL=".urlencode("$default->rootUrl/control.php?action=addUnitSuccess&fUnit"));
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
