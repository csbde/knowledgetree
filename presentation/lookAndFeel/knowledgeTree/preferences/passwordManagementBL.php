<?php
/**
 * $Id$
 *
 * Change a user's password
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 * Modified 2004 by William Hawkins
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.usermanagement
 */
 
require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");	
	require_once("$default->fileSystemRoot/lib/users/User.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("passwordManagementUI.inc");	
	
	$oPatternCustom = & new PatternCustom();
	
	if (strcmp($default->authenticationClass,"DBAuthenticator") == 0) {
		//only update passwords if we are in db authentication mode
		$oUser = User::get($_SESSION["userID"]);
	 	if (isset($fForUpdate)) {
			//execute the update and return to the edit page??
	 		if (strlen($fNewPassword) > 0 && strlen($fNewPasswordConfirm) > 0) {
				//if passwords have been entered
				if (strcmp($fNewPassword, $fNewPasswordConfirm) == 0) {
					//if the password and its confirmation are the same		 				
					$oUser->setPassword($fNewPassword);
					if ($oUser->update()) {
						//successful update		 				
						$oPatternCustom->setHtml(getPasswordUpdateSuccessPage());
					} else {
						//update failed
						$oPatternCustom->setHtml(getPage($oUser->getName()));
						$main->setErrorMessage("An error occured while attempting to update the user's password");
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");							
			 		}
			 	} else {
			 		$oPatternCustom->setHtml(getPage($oUser->getName()));
			 		$main->setErrorMessage("The password and its confirmation do not match.  Please try again.");
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");
			 	} 
			} else {
				$oPatternCustom->setHtml(getPage($oUser->getName()));
		 		$main->setErrorMessage("Blank passwords are not valid.  Please try again.");
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");
			}	 		
		} else {					
	 		//show the form
			$oPatternCustom->setHtml(getPage($oUser->getName()));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fForUpdate=1");
		}
		
	} else {
		$oPatternCustom->setHtml(getPage($oUser->getName()));
		$main->setErrorMessage("Passwords can only be updated in Knowledge Tree when authentication is against the database, not against an LDAP server");
		$main->setFormAction($_SERVER["PHP_SELF"]);
	}	
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>
