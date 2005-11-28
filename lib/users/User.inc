<?php
/**
 * $Id$
 *
 * Represents a user as per the users table in the database.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.unitmanagement
 */
 
require_once(KT_LIB_DIR . '/database/dbutil.inc'); 
 
class User extends KTEntity {
    /** user's login name */
    var $sUserName;
    /** user's name (first and last) */
    var $sName;
    /** user's password */
    var $sPassword;
    /** user's maximum allowed file storage quota in bytes */
    var $iQuotaMax = 1234567890;
    /** user's current file storage quota in bytes */
    var $iQuotaCurrent = 0;
    /** user's email address */
    var $sEmail = "";
    /** user's mobile phone number */
    var $sMobile = "";
    /** notify user by mail status */
    var $bEmailNotification = false;
    /** notify user via sms (mobile phone) status */
    var $bSmsNotification = false;
    /** user's ldap identification */
    var $sLdapDn = "";
    /** maxiumum concurrent sessions user may have */
    var $iMaxSessions = 5;
    /** primary key of language preferred by user */
    var $iLanguageID;
    /** internal variable used to determine if the password has changed or not */
    var $bPasswordChanged = false;
    /** authentication source for this user (built-in if null) */
    var $iAuthenticationSourceId = null;
    /** authentication details so that the source knows who this user is */
    var $sAuthenticationDetails = null;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sUserName' => 'username',
        'sName' => 'name',
        'sPassword' => 'password',
        'iQuotaMax' => 'quota_max',
        'iQuotaCurrent' => 'quota_current',
        'sEmail' => 'email',
        'sMobile' => 'mobile',
        'bEmailNotification' => 'email_notification',
        'bSmsNotification' => 'sms_notification',
        'sLdapDn' => 'ldap_dn',
        'iMaxSessions' => 'max_sessions',
        'iLanguageID' => 'language_id',
        'iAuthenticationSourceId' => 'authentication_source_id',
        'sAuthenticationDetails' => 'authentication_details',
    );

    function _table() {
        global $default;
        return $default->users_table;
    }

    function User($sNewUserName = null, $sNewName = null, $sNewPassword = null, $iNewQuotaMax = null, $sNewEmail= null, $sNewMobile= null, $bNewEmailNotification= null, $bNewSmsNotification= null, $sNewLdapDn= null, $iNewMaxSessions= null, $iNewLanguageID= null) {
        //object not created in database yet
        $this->iId = -1;
        $this->sUserName = $sNewUserName;
        $this->sName = $sNewName;
        $this->sPassword = $sNewPassword;
        $this->iQuotaMax = $iNewQuotaMax;
        $this->sEmail = $sNewEmail;
        $this->sMobile = $sNewMobile;
        $this->bEmailNotification = $bNewEmailNotification;
        $this->bSmsNotification = $bNewSmsNotification;
        $this->sLdapDn = $sNewLdapDn;
        $this->iMaxSessions = $iNewMaxSessions;
        $this->iLanguageID = $iNewLanguageID;
        $this->bPasswordChanged = false;
    }

    /** Get the user's login name */
    function getUserName() { return $this->sUserName; }
    /** Set the user's login name */
    function setUserName($sNewValue) { $this->sUserName = $sNewValue; }

    /** Set the user's password */
    function setPassword($sNewValue) { $this->sPassword = $sNewValue; $this->bPasswordChanged = true; }
    /** Get the user's maximum disk quota */
    function getQuotaMax() { return $this->iQuotaMax; }
    /** Set the user's maximum disk quota */
    function setQuotaMax($iNewValue) { $this->iQuotaMax = $iNewValue; }

    /** Set the user's name */
    function setName($sNewValue) { $this->sName = $sNewValue; }
    /** gets the user's name */
    function getName() { return $this->sName; }

    /** Get the user's currrently used quota */
    function getQuotaCurrent() { return $this->iQuotaCurrent; }

    /** Get the user's email  address */
    function getEmail() { return $this->sEmail; }
    /** Set the user's email address */
    function setEmail($sNewValue) { $this->sEmail = $sNewValue; }

    /** Get the user's mobile phone number */
    function getMobile() { return $this->sMobile; }
    /** Set the user's mobile phone number */
    function setMobile($sNewValue) { $this->sMobile = $sNewValue; }

    /** Get the user's email notification status */
    function getEmailNotification() { return $this->bEmailNotification; }
    /** Set the user's email notification status */
    function setEmailNotification($bNewValue) { $this->bEmailNotification = KTUtil::anyToBool($bNewValue); }

    /** Get the user's SMS (mobile phone) notification status */
    function getSmsNotification() { return $this->bSmsNotification; }
    /** Set the user's SMS (mobile phone) notification status */
    function setSmsNotification($bNewValue) { $this->bSmsNotification = $bNewValue; }

    /** Get the user's LDAP distinguished name */
    function getLdapDn() { return $this->sLdapDn; }
    /** Set the user's LDAP distinguished name */
    function setLdapDn($sNewValue) { $this->sLdapDn = $sNewValue; }

    /** Get the user's maximum number of concurrent sessions */
    function getMaxSessions() { return $this->iMaxSessions; }
    /** Set the user's maximum number of concurrent sessions */
    function setMaxSessions($iNewValue) { $this->iMaxSessions = $iNewValue; }

    /** Get the primary key for the language preferred by the user */
    function getLanguageID() { return $this->iLanguageIDID; }
    /** Set the primary key of the language preferred by the user */
    function setLanguageID($iNewValue) { $this->iLanguageIDID = $iNewValue; }

    function getAuthenticationSourceId() { return $this->iAuthenticationSourceId; }
    function setAuthenticationSourceId($iNewValue) { $this->iAuthenticationSourceId = $iNewValue; }
    function getAuthenticationDetails() { return $this->sAuthenticationDetails; }
    function setAuthenticationDetails($sNewValue) { $this->sAuthenticationDetails = $sNewValue; }

	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function deleteFromSystem() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM $default->users_groups_table WHERE user_id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}

    function &get($iId) {
        return KTEntityUtil::get('User', $iId);
    }
	
	/**
	* update the datastore, without overwriting the password.
	*
	* only works for a subset of the db values.
	*/
	function doLimitedUpdate() {
		$sQuery = 'UPDATE ' . $this->_table() . ' SET ';
		$aParams = array();
		
		$blacklist = array(
			"sPassword" => 1,
		);
		
		$aParts = array(); // quick workaround to make the join less hurtful.
		
		foreach ($this->_aFieldToSelect as $attr => $column) {
			if (!array_key_exists($attr, $blacklist)) {
				$val = $this->$attr;
				$aParts[] = $column . ' = ?';
				$aParams[] = $val;  
			} 
		}
		$sQuery .= join(', ', $aParts);
		
		$sQuery .= ' WHERE id = ? ';
		$aParams[] = $this->getId();
		
		$res = DBUtil::runQuery(array($sQuery, $aParams));
		return $res;
	}
	
	
    /**
    * Static function
    * Get a list of users
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of User objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null) {
        return KTEntityUtil::getList(User::_table(), 'User', $sWhereClause);
    }

    /**
     * Static function
     * Return the unitIDs of the specified user
     *
     * @param int the id the user to lookup the unit for
     * @return array the unitIDs, false otherwise
     */
    function getUnitIDs($userID) {
        global $default, $lang_err_database;

        $sql = $default->db;
        /*ok*/$result = $sql->query(array("SELECT DISTINCT gul.unit_id FROM $default->users_groups_table ugl " .
                              "INNER JOIN $default->groups_units_table gul ON ugl.group_id = gul.group_id ".
                              "WHERE ugl.user_id = ?", $userID));
        if ($result) {
            $aUnitIDs = array();
            while ($sql->next_record()) {
                $aUnitIDs[] = $sql->f("unit_id");
            }
            return $aUnitIDs;
        }
        return false;
    }
    
    /**
     * Static function
     * Return the useID for the specified user
     *
     * @param int the id the user to lookup the unit for
     * @return int the unitID, false otherwise and $_SESSION["errorMessage"] set
     */
    function getUnitID($userID) {
        global $default, $lang_err_database;

        $sql = $default->db;
        /*ok*/$result = $sql->query(array("SELECT DISTINCT gul.unit_id FROM $default->users_groups_table ugl " .
                              "INNER JOIN $default->groups_units_table gul ON ugl.group_id = gul.group_id ".
                              "WHERE ugl.user_id = ?", $userID));
        if ($result) {
            if ($sql->next_record()) {
                return $sql->f("unit_id");
            }
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }

    /**
     * static function
     *
     * gets the id of a user using their username
     *
     * @param   string  The username for which we want its ID
     */
    function getUserID($sUsername) {
        global $default;

        $id = lookupID($default->users_table, "username",  $sUsername);

        $this->iId = $id;
    }
    
    /** Static function
    * Gets the user's default top level folder for the current user
    */
    function getUserRootFolderID() {
    	global $default;
    	
    	$unitID = User::getUnitID($_SESSION["userID"]);
    	$iFolderID; 
            
        if ($unitID) {
            // if the user is in a unit, start at the unit's root folder
            
            // lookup the unit name
            $unitName = lookupField($default->units_table, "name", "id", $unitID);             

            // the unit root folder has the same name as the unit
            // FIXME: dodgy i know, but its easy
            $unitRootFolderName = $unitName;
           
            // now lookup the folderID
            $aFolders = Folder::getList(array("name = ? and parent_id = 1", $unitRootFolderName));/*ok*/
            
            if (!$aFolders) {
                // no folder exists with this name, so start at the root
                $iFolderID = lookupID($default->folders_table, "parent_id", 0);
            } else {
            	$iFolderID = $aFolders[0]->getID();
            }
        } else {
        	$iFolderID = lookupID($default->folders_table, "parent_id", 0);
        }
         return $iFolderID;
    }
    

    /**
     * Returns a unit administrator for the current user
     */
	function getUnitAdminUser() {
	    global $default;
	    // find out what unit we're in
	    $iUnitID = User::getUnitID($_SESSION["userID"]);
	    if ($iUnitID) {
	        // then find the group that is unit_admin
	        $sql = $default->db;
	        $sEmail = "";
	        if ($sql->query(array("SELECT group_id FROM $default->groups_units_table GUL " . /*ok*/
	                        "INNER JOIN $default->groups_table GL on GUL.group_id=GL.id " .
	                        "WHERE GL.is_unit_admin=1 " .
	                        "AND unit_id = ?", $iUnitID))) {
	            // get the first record
	            if ($sql->next_record()) {
	                $iGroupID = $sql->f("group_id");
	                // then find the first user in this group that has an email address
	                if ($sql->query(array("SELECT U.id, U.email FROM $default->users_table U " . /*ok*/
	                                "INNER JOIN $default->users_groups_table UGL on UGL.user_id=U.id " .
	                                "WHERE group_id = ?", $iGroupID))) {
	                    while ($sql->next_record()) {
	                        if (strlen($sql->f("email")) > 0) {
	                            return User::get($sql->f("id"));
	                        }
	                    }
	                }
	            }
	        }
	    }
	    return false;
	}    
	
	function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('User', $aOptions); }
	function &getByUserName($sUserName, $aOptions = null) {
        return KTEntityUtil::getBy('User', 'username', $sUserName, $aOptions);
    }
}

/**
* Static function
*
* Creates a User object from an array
*
* @param  Array  Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & userCreateFromArray($aParameters) {
    $oUser = & new User($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
    return $oUser;
}

?>
