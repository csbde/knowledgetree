<?php
/**
 * $Id$
 *
 * Represents a user as per the users table in the database.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
    var $sAuthenticationDetails2 = null;

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
        'iMaxSessions' => 'max_sessions',
        'iLanguageID' => 'language_id',
        'iAuthenticationSourceId' => 'authentication_source_id',
        'sAuthenticationDetails' => 'authentication_details_s1',
        'sAuthenticationDetails2' => 'authentication_details_s2',
    );

    var $_bUsePearError = true;

    function _table() {
        global $default;
        return $default->users_table;
    }

    function getUserName() { return $this->sUserName; }
    function setUserName($sNewValue) { $this->sUserName = $sNewValue; }
    function setPassword($sNewValue) { $this->sPassword = $sNewValue; $this->bPasswordChanged = true; }
    function getQuotaMax() { return $this->iQuotaMax; }
    function setQuotaMax($iNewValue) { $this->iQuotaMax = $iNewValue; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function getName() { return $this->sName; }
    function getQuotaCurrent() { return $this->iQuotaCurrent; }
    function getEmail() { return $this->sEmail; }
    function setEmail($sNewValue) { $this->sEmail = $sNewValue; }
    function getMobile() { return $this->sMobile; }
    function setMobile($sNewValue) { $this->sMobile = $sNewValue; }
    function getEmailNotification() { return $this->bEmailNotification; }
    function setEmailNotification($bNewValue) { $this->bEmailNotification = KTUtil::anyToBool($bNewValue); }
    function getSmsNotification() { return $this->bSmsNotification; }
    function setSmsNotification($bNewValue) { $this->bSmsNotification = $bNewValue; }
    function getMaxSessions() { return $this->iMaxSessions; }
    function setMaxSessions($iNewValue) { $this->iMaxSessions = $iNewValue; }
    function getLanguageID() { return $this->iLanguageIDID; }
    function setLanguageID($iNewValue) { $this->iLanguageIDID = $iNewValue; }
    function getAuthenticationSourceId() { return $this->iAuthenticationSourceId; }
    function setAuthenticationSourceId($iNewValue) { $this->iAuthenticationSourceId = $iNewValue; }
    function getAuthenticationDetails() { return $this->sAuthenticationDetails; }
    function setAuthenticationDetails($sNewValue) { $this->sAuthenticationDetails = $sNewValue; }
    function getAuthenticationDetails2() { return $this->sAuthenticationDetails2; }
    function setAuthenticationDetails2($sNewValue) { $this->sAuthenticationDetails2 = $sNewValue; }

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
    function getList($sWhereClause = null, $aOptions = null) {
	    if(!is_array($aOptions)) $aOptions = array($aOptions);
		$aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby', 'name');
		
        return KTEntityUtil::getList2('User', $sWhereClause, $aOptions);
    }

    function getEmailUsers() {
        $aUsers = array();
        foreach (User::getList() as $oUser) {
            if ($oUser->getEmail()) {
                $aUsers[] = $oUser;
            }
        }
        return $aUsers;
    }

    /**
     * Static function
     * Return the useID for the specified user
     *
     * @param int the id the user to lookup the unit for
     * @return int the unitID, false otherwise and $_SESSION["errorMessage"] set
     */
    function getUnitId() {
        $ugl = KTUtil::getTableName("users_groups");
        $g = KTUtil::getTableName("groups");
        $aQuery = array(
            "SELECT DISTINCT g.unit_id AS unit_id FROM $ugl AS ugl INNER JOIN $g AS g ON ugl.group_id = g.id WHERE ugl.user_id = ?",
            array($this->iId),
        );
        return DBUtil::getOneResultKey($aQuery, 'unit_id');
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
    function getHomeFolderID() {
    	$iUnitId = $this->getUnitId();

        if (empty($iUnitId)) {
            return false;
        }

        $oUnit =& Unit::get($iUnitId);
        return $oUnit->getFolderId();
    }
	
	function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('User', $aOptions); }
	function &getByUserName($sUserName, $aOptions = null) {
        return KTEntityUtil::getBy('User', 'username', $sUserName, $aOptions);
    }

    function getByAuthenticationSource($oSource, $aOptions = null) {
        $iSourceId = KTUtil::getId($oSource);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'multi' => true,
        ));
        return KTEntityUtil::getByDict('User', array(
            'authentication_source_id' => $iSourceId,
        ), $aOptions);
    }

    function &getByAuthenticationSourceAndDetails($oSource, $sDetails, $aOptions = null) {
        $iSourceId = KTUtil::getId($oSource);

        return KTEntityUtil::getByDict('User', array(
            'authentication_source_id' => $iSourceId,
            'authentication_details_s1' => $sDetails,
        ), $aOptions);
    }
}
