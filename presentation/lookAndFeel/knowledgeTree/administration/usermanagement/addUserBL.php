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
    $oAuth = new $default->authenticationClass;
    // user attributes to search for
    if ($default->authenticationClass == "DBAuthenticator")  {
        $aAttributes = array ("username", "name", "email", "mobile", "email_notification", "sms_notification");
        $bLdap = false;
    } else {
        //if its using LDAP get these attributes
        // TODO: make these user defined
        if ($default->ldapServerType == "ActiveDirectory") {
            $aAttributes = array ("dn", "samaccountname", "givenname", "sn", "mail", "telephonenumber");
        } else {
            $aAttributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");
        }
        $bLdap = true;
    }
    
    if (isset($fSearch)) {
        //get user name
        $sSearch = $fName;

        // search for users
        $aResults = $oAuth->searchUsers($sSearch, $aAttributes);

        //post array to page
        if (isset($aResults)) {
            if(count($aResults) == 0) {
                $oPatternCustom->setHtml(getPageUsernameNotFound());
            } else {
                if (count($aResults) > 1) {
                    // display results in a listbox                    
                    $oPatternCustom->setHtml(getSelectUserPage($aResults));
                    $main->setFormAction($_SERVER["PHP_SELF"]. "?fSelectedUser=1");
                } else {
                    if($bLdap) {
                        $oPatternCustom->setHtml(getDetailsLDAPPage($sSearch,$aResults, $oAuth->oLdap->getUserIdentifier()));
                        $main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1");
                    } else {
                        $oPatternCustom->setHtml(getDetailsDBPage($sSearch,$aResults));
                        $main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1&fFromDb=1");
                    }
                }
            }
        } else {
            $oPatternCustom->setHtml(getAddPageFail());
            $main->setFormAction($_SERVER["PHP_SELF"]);
        }
    } else if (isset($fSelectedUser)) {
        // user has been selected
        
        // retrieve user details
        $aResult = $oAuth->getUser($fName, $aAttributes);
        // display details page
        if ($bLdap) {
            $oPatternCustom->setHtml(getDetailsLDAPPage($fName,$aResult, $oAuth->oLdap->getUserIdentifier()));
            $main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1");
        } else {
            $oPatternCustom->setHtml(getDetailsDBPage($fName,$aResult));
            $main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1&fFromDb=1");
        }
        
    } else if(isset($fAddToDb)) {
        // if db authentication
        if(isset($fFromDb)) {
            $oUser = new User($fUsername,$fName,$fPassword,0,$fEmail,$fMobile,$fEmailNotification,$fSmsNotification,0,1,0);

        } else {
            $oUser = new User($fUsername,$fName,0,0,$fEmail,$fMobile,$fEmailNotification,$fSmsNotification,$fLdap,1,0);
        }

        if($oUser->create()) {
            $oPatternCustom->setHtml(getPageSuccess());
        } else {
            $oPatternCustom->setHtml(getPageFail());
        }
    } else {
    	if ($default->authenticationClass == "DBAuthenticator")  {
			$aAttributes = array("" => array ("username", "name", "email", "mobile", "email_notification", "sms_notification"));    		
            $oPatternCustom->setHtml(getDetailsDBPage(null,$aAttributes));
            $main->setFormAction($_SERVER["PHP_SELF"]. "?fAddToDb=1&fFromDb=1");
    	} else {
	        // if nothing happens...just reload edit page
	        $oPatternCustom->setHtml(getSearchPage(null));
	        $main->setFormAction($_SERVER["PHP_SELF"]. "?fSearch=1");
    	}
    }

    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>