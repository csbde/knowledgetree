<?php
/**
* Class User
* Represents a user as per the users table in the database
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package lib.user
*/

class User {
	
	/** object primary key */
	var $iId;
	/** user's login name */
	var $sUserName;
	/** user's name (first and last) */
	var $sName;
	/** user's password */
	var $sPassword;
	/** user's maximum allowed file storage quota in bytes */
	var $iQuotaMax;
	/** user's current file storage quota in bytes */
	var $iQuotaCurrent;
	/** user's email address */
	var $sEmail;
	/** user's mobile phone number */
	var $sMobile;
	/** notify user by mail status */
	var $bEmailNotification;
	/** notify user via sms (mobile phone) status */
	var $bSmsNotification;
	/** user's ldap identification */
	var $sLdapDn;
	/** maxiumum concurrent sessions user may have */
	var $iMaxSessions;
	/** primary key of language preferred by user */
	var $iLanguageID;
	/** internal variable used to determine if the password has changed or not */
	var $bPasswordChanged;
	
	function User($sNewUserName, $sNewName, $sNewPassword, $iNewQuotaMax, $sNewEmail, $sNewMobile, $bNewEmailNotification, $bNewSmsNotification, $sNewLdapDn, $iNewMaxSessions, $iNewLanguageID) {
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
	
	/**
	* Get the object's primary key
	*
	* @return int object's primary key
	*
	*/
	function getID() {
		return $this->iId;
	}
	
	/**
	* Get the user's login name
	*
	* @return String user's login name
	*
	*/
	function getUserName() {
		return $this->sUserName;
	}
	
	/**
	* Set the user's login name
	*
	* @param 	String		New user login name
	*
	*/
	function setUserName($sNewValue) {
		$this->sUserName = $sNewValue;
	}
	
	/**
	* Set the user's password
	*
	* @param 	String		New user password
	*
	*/
	function setPassword($sNewValue) {
		$this->sPassword = $sNewValue;
		$this->bPasswordChanged = true;
	}
	
	/**
	* Get the user's maximum disk quota
	*
	* @return int user's maximum disk quota
	*
	*/
	function getQuotaMax() {
		return $this->iQuotaMax;
	}
	
	/**
	* Set the user's maximum disk quota
	*
	* @param 	int		User's maximum disk quota in bytes
	*
	*/
	function setQuotaMax($iNewValue) {
		$this->iQuotaMax = $iNewValue;
	}
	
	function getName() {
		return $this->sName;
	}
	
	/**
	* Get the user's currrently used quota
	*
	* @return int user's currently used quota
	*
	*/
	function getQuotaCurrent() {
		return $this->iQuotaCurrent;
	}
	
	/**
	* Get the user's email  address
	*
	* @return String user's email address
	*
	*/
	function getEmail() {
		return $this->sEmail;
	}
	
	/**
	* Set the user's email address
	*
	* @param 	String		User's email address
	*
	*/
	function setEmail($sNewValue) {
		$this->sEmail = $sNewValue;
	}
	
	/**
	* Get the user's mobile phone number
	*
	* @return String user's mobile phone number 
	*
	*/
	function getMobile() {
		return $this->sMobile;
	}
	
	/**
	* Set the user's mobile phone number
	*
	* @param 	String		User's mobile phone number
	*
	*/
	function setMobile($sNewValue) {
		$this->sMobile = $sNewValue;
	}
	
	/**
	* Get the user's email notification status
	*
	* @return boolean user's email notification status
	*
	*/
	function getEmailNotification() {
		return $this->bEmailNotification;
	}
	
	/**
	* Set the user's email notification status
	*
	* @param 	boolean		User's email notification status (notify by email)
	*
	*/
	function setEmailNotification($bNewValue) {
		$this->bEmailNotification = $bNewValue;
	}
	
	/**
	* Get the user's SMS (mobile phone) notification status
	*
	* @return boolean SMS (mobile phone) notification status
	*
	*/
	function getSmsNotification() {
		return $this->bSmsNotification;
	}
	
	/**
	* Set the user's SMS (mobile phone) notification status
	*
	* @param 	boolean		User's SMS (mobile phone) notification status (notify by mobile phone)
	*
	*/
	function setSmsNotification($bNewValue) {
		$this->bSmsNotification = $bNewValue;
	}
	
	/**
	* Get the user's LDAP distinguished name
	*
	* @return String user's LDAP distinguished name
	*
	*/
	function getLdapDn() {
		return $this->sLdapDn;
	}
	
	/**
	* Set the user's LDAP distinguished name
	*
	* @param 	String		User's LDAP distinguished name
	*
	*/
	function setLdapDn($sNewValue) {
		$this->sLdapDn = $sNewValue;
	}
	
	/**
	* Get the user's maximum number of concurrent sessions
	*
	* @return int user's maximum number of concurrent sessions
	*
	*/
	function getMaxSessions() {
		return $this->iMaxSessions;
	}
	
	/**
	* Set the user's maximum number of concurrent sessions
	*
	* @param 	int		User's maximum number of concurrent sessions
	*
	*/
	function setMaxSessions($iNewValue) {
		$this->iMaxSessions = $iNewValue;
	}
	
	/**
	* Get the primary key for the language preferred by the user
	*
	* @return int primary key of language preferred by user
	*
	*/
	function getLanguageID() {
		return $this->iLanguageIDID;
	}
	
	/**
	* Set the primary key of the language preferred by the user
	*
	* @param 	int		Primary key of language preferred by user
	*
	*/
	function setLanguageID($iNewValue) {
		$this->iLanguageIDID = $iNewValue;
	}

	/**
	* Create the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_database, $lang_err_object_exists;		
		//if the object hasn't been created
		if ($this->iId < 0) 
		{
			//check to see if name exsits
			$sql = $default->db;
			$query = "SELECT username FROM ". $default->owl_users_table ." WHERE username = '" . $this->sUserName . "'";
        		$sql->query($query);
       			$rows = $sql->num_rows($sql);
        
      			 if ($rows > 0) 
       		 	{
            		 // duplicate username
           		 $_SESSION["errorMessage"] = "User::The username " . $this->sUserName . " is already in use!";
           		 return false;
	       		 }
	       
	      		 else
	      		 {
	        		$result = $sql->query("INSERT INTO " . $default->owl_users_table . " (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id) " .
					"VALUES ('" . addslashes($this->sUserName) . "', '" . addslashes($this->sName) . "', '" . addslashes(md5($this->sPassword)) . "', $this->iQuotaMax, 0, '" . addslashes($this->sEmail) . "', '" . addslashes($this->sMobile) . "', " . ($this->bEmailNotification ? 1 : 0) . ", " . ($this->bSmsNotification ? 1 : 0) . ", '" . addslashes($this->sLdapDn) . "', $this->iMaxSessions, $this->iLanguageID)");
				if ($result) 
				{
					$this->iId = $sql->insert_id();
					return  true;
				}
				$_SESSION["errorMessage"] = $lang_err_database;
				return false;
			}
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = $default->owl_users_table";
		return false;
	}
	
	/**
	* Update the values in the database table with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this->iId > 0) {
			$sql = $default->db;
			$result = $sql->query("UPDATE " . $default->owl_users_table . " SET username = '" . addslashes($this->sUserName) . "', name = '" . addslashes($this->sName) . "', " . ($this->bPasswordChanged ? "password = '" . addslashes(md5($this->sPassword)) . "', " : " ") . " quota_max = $this->iQuotaMax, email = '" . addslashes($this->sEmail) . "', mobile = '" . addslashes($this->sMobile) . "', email_notification = " . ($this->bEmailNotification ? 1 : 0) . ", sms_notification = " . ($this->bSmsNotification ? 1 : 0) . ", ldap_dn = '" . addslashes($this->sLdapDn) . "', max_sessions = $this->iMaxSessions, language_id = $this->iLanguageID WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM $default->owl_users_table WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Static function.
	* Given a web_documents primary key it will create a 
	* User object and populate it with the 
	* corresponding database values
	*
	* @return User populated User object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iUserID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_users_table WHERE id = $iUserID");
		if ($result) {
			if ($sql->next_record()) {
				$oUser = & new User(stripslashes($sql->f("username")), stripslashes($sql->f("name")), stripslashes($sql->f("password")), $sql->f("quota_max"), stripslashes($sql->f("email")), stripslashes($sql->f("mobile")), $sql->f("email_notification"), $sql->f("sms_notification"), $sql->f("ldap_dn"), $sql->f("max_sessions"), $sql->f("language_id"));
				$oUser->iId = $iUserID;
				return $oUser;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iUserID . " table = $default->owl_users_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}

	/**
	* Static function
	* Get a list of users
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of User objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aUserArray;
		settype($aUserArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_users_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {
			$iCount = 0;
			while ($sql->next_record()) {
				$oUser = & User::get($sql->f("id"));
				$oUser->iQuotaCurrent = $sql->f("quota_current");
				$aUserArray[$iCount] = $oUser;
				$iCount++;
			}
			return $aUserArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
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
        $result = $sql->query("SELECT DISTINCT gul.unit_id FROM $default->owl_users_groups_table ugl " .
                              "INNER JOIN $default->owl_groups_units_table gul ON ugl.group_id = gul.group_id ".
                              "WHERE ugl.user_id=$userID");
		if ($result) {
			if ($sql->next_record()) {
				return $sql->f("unit_id");
			}
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
    }
    /*
     * static function
     *
     * gets the id of a user using their username
     *
     * @param   string  The username for which we want its ID
     *
     *   
     */
     
	function getUserID($username)
	 {
	       global $default;
	
	        $id = lookupID($default->owl_users_table, "username",  $username);
	        
	        $this->iId = $id;
	}
}

/**
* Static function
*
* Creates a User object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & userCreateFromArray($aParameters) {
	$oUser = & new User($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oUser;
}

?>
