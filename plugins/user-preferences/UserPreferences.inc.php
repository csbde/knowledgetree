<?php
/*
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
$dir = realpath($dir).DIRECTORY_SEPARATOR;
require_once(KT_LIB_DIR . "/ktentity.inc");

class UserPreferences extends KTEntity {
    public $iId; // primary key of current object
    public $iUserId; // id of user
    public $sKey; // description of user preference
    public $sValue; // value of user preference

    public $_aFieldToSelect = array(
        "iId" => "id",
        "iUserId" => "user_id",
        "sKey" => "prefkey",
        "sValue" => "prefvalue",
    );

    public $_bUsePearError = true;

    function UserPreferences($iUserId, $sKey, $sValue) {
		$this->iId = -1;
		$this->iUserId = $iUserId;
		$this->sKey = $sKey;
		$this->sValue = $sValue;
    }
    
    /**
    * Retrieve UserPreferences objects database table name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    function _table () { return KTUtil::getTableName('user_preferences'); }
    
    // ---------------
    // Getters/setters
    // ---------------
    /**
    * Retrieve a list of UserPreferences objects
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $sWhereClause - string
    * @param $aOptions - array
    * @return UserPreferences objects - array
    */
    public function getList($sWhereClause = null, $aOptions = null) {
        if (is_null($aOptions)) { $aOptions = array(); }
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','name');
        
        return KTEntityUtil::getList2('UserPreferences', $sWhereClause, $aOptions);
    }
    
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param 
    * @param 
    * @return
    */
    public function getPreferences($iUserId, $sKey, $aOptions = null) {
    	$sWhereClause = "WHERE user_id = '$iUserId' AND prefkey = '$sKey'";
    	
    	return KTEntityUtil::getList2('UserPreferences', $sWhereClause, $aOptions);
    }
    
    /**
    * Retrieve a UserPreferences object
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $iId - int - Id of template
    * @return UserPreferences object
    */
    public function get($iId) { return KTEntityUtil::get('UserPreferences', $iId); }
    
    /**
    * Retrieve UserPreferences user id
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    public function getUserId() { return $this->iUserId; }
    
    /**
    * Set the user id
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $iUserId - string - the user id
    * @return none
    */
    public function setUserId($iUserId) { $this->iUserId = $iUserId; }

    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param 
    * @return none
    */
    public function setKey($sKey) { $this->sKey = $sKey; }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    public function getKey() { return $this->sKey; }
    
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param 
    * @return none
    */
    public function setValue($sValue) { $this->sValue = $sValue; }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    public function getValue() { return $this->sValue; }
    
    // Utility
    
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param 
    * @param 
    * @param 
    * @return 
    */
    public function exists($iUserId, $sKey, $sValue) {
        $sQuery = "SELECT id, name FROM " . KTUtil::getTableName('user_preferences') . " WHERE user_id = ? AND prefkey = ? AND prefvalue = ?";/*ok*/
        $aParams = array($iUserId, $sKey, $sValue);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		if (count($res) != 0) {
		    foreach ($res as $user_pref){
		    	$userid = isset($user_pref['user_id']) ? $user_pref['user_id'] : '';
    		    $key = isset($user_pref['prefkey']) ? $user_pref['prefkey'] : '';
    		    if($sKey == $key && $iUserId == $userid) {
    		        return true;
    		    }
		    }
			return false;
		}
		return false;
    }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $aOptions - array
    * @return 
    */
    public function getAllUserPreferences($userId, $aOptions = null) {
        if (is_null($aOptions)) { $aOptions = array(); }
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','name');
        $sWhereClause = "WHERE user_id = '$userId'";
        return KTEntityUtil::getList2('UserPreferences', $sWhereClause, $aOptions);
    }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $aOptions - array
    * @return 
    */
    public function getUserPreferenceValue($iUserId, $sKey) {
    	$aPref = UserPreferences::getPreferences($iUserId, $sKey);
    	if(PEAR::isError($aPref)) {
    		return false;
    	}
    	if(count($aPref) > 1) {
    		return false;
    	}
    	
    	foreach ($aPref as $oPref) {
    		return $oPref->getValue();
    	}
    }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $aOptions - array
    * @return 
    */
    public function saveUserPreferences($iUserId, $sKey, $sValue) {
    	$oUser = User::get($iUserId); // Get the user
    	if (PEAR::isError($oUser)) {
    		return false;
    	}
		$aUserPreference = UserPreferences::getPreferences($iUserId, 'zohoWarning'); // Get user preference
		if(empty($aUserPreference) || is_null($aUserPreference)) { // Create the preference
			$oUserPreference = new UserPreferences($iUserId, 'zohoWarning', $sValue);
			$oUserPreference->create();
		} else {
    		foreach ($aUserPreference as $oUserPreference) { // Access object
	    		if($oUserPreference->getValue() != $sValue) { // Check if value needs to be updated
	    			$oUserPreference->setValue($sValue); // Set the new value
	    			$oUserPreference->update(); // Update preference
	    		}
    		}
		}
    }
}
?>
