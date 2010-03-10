<?php
/**
 * Electronic Signatures
 *
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

$start = strpos(dirname(__FILE__), 'plugins');
$filePath = substr(dirname(__FILE__), 0, $start);

require_once($filePath.'config/dmsDefaults.php');
require_once('UserPreferences.inc.php');

/**
 * Class handles the document type alerts
 *
 * @author KnowledgeTree Team
 * @package Alerts Plugin
 * @version 1.0
 */
class KTUserPreferences
{
	private $oUserPreference;
	
    /**
     * Constructor function for the class
     *
     * @author KnowledgeTree Team
     * @access public
     * @return KTDocTypeAlerts
     */
    public function __construct()
    {
    	$this->oUserPreference = new UserPreferences();
    }

    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @return 
    */
    function saveUserPreferences() {
    	$iUserId = KTUtil::arrayGet($_GET, 'user_id', $_SESSION['userID']);
    	$sKey = KTUtil::arrayGet($_GET, 'key', null);
    	$sValue = KTUtil::arrayGet($_GET, 'value', null);
    	if(is_null($iUserId) || is_null($sKey) || is_null($sValue)) {
    		exit("Missing Required options : user_id = $iUserId key = $sKey value = $sValue");
    	}
    	$this->oUserPreference->saveUserPreferences($iUserId, $sKey, $sValue);
    }
    
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @return 
    */
    function getUserPreferences() {
    	
    }
    
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @return 
    */
    function deleteUserPreferences() {
    	
    }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @return boolean
    */
    function isLoggedIn() {
    	$session = new Session();
    	$sessionStatus = $session->verify();
    	if ($sessionStatus !== true) {
    		return false;
    	}
    	return true;
    }
}

$oKTUserPreferences = new KTUserPreferences();

if (!$oKTUserPreferences->isLoggedIn()) {
    echo _kt('Session has expired. Refresh page and login.');
    exit;
}

if(isset($_GET['action'])) {
	$action = $_GET['action'];
	$oKTUserPreferences->$action();
}

exit;
?>