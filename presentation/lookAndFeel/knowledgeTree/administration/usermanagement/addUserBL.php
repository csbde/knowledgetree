<?php
/**
 * $Id$
 *
 * Add a user.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.usermanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/groups/Group.inc");
require_once("$default->fileSystemRoot/lib/groups/GroupUserLink.inc");    
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");
require_once("addUserUI.inc");

if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    //create db object
    $oAuth = new $default->authenticationClass;
    // user attributes to search for
    if ($default->authenticationClass == "DBAuthenticator")  {
        $aAttributes = array ("username", "name", "email", "mobile", "email_notification", "sms_notification");
        $bLdap = false;
    } else {
        //if its using LDAP get these attributes
        // FIXME: move these to $default(ldapSettings.inc) and map them to DN, username, display name, email, mobile
        if ($default->ldapServerType == "ActiveDirectory") {
            $aAttributes = array ("dn", "samaccountname", "givenname", "sn", "userPrincipalName", "telephonenumber");
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
						if ($default->bNN4) {
                        	$main->setOnLoadJavaScript("disable(document.MainForm.fLdap);disable(document.MainForm.fUsername)");
						}
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
			if ($default->bNN4) {
            	$main->setOnLoadJavaScript("disable(document.MainForm.fLdap);disable(document.MainForm.fUsername)");
			}
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
        	// now add the user to the initial group
        	$default->log->info("adding user id " . $oUser->getID() . " to group id $fGroupID"); 
        	$oUserGroup = new GroupUserLink($fGroupID,$oUser->getID());
        	if ($oUserGroup->create()) {
            	$oPatternCustom->setHtml(getPageSuccess());
        	} else {
        		$oPatternCustom->setHtml(getPageGroupFail());
        	}
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
    $main->setHasRequiredFields(true);
    $main->render();
}
?>