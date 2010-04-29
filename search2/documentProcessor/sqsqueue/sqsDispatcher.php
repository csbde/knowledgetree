<?php
/**
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
/**
 * Load KT default configurations
 */


class sqsDispatcher 
{
    /**
     * Constructor
     *
     * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return none
     */
    public function __construct()
    {
    }
    
    /**
    * Check if a user is logged in. (Testing)
    *
    * @author KnowledgeTree Team
    * @access public
    * @return boolean
    */
    public function isLoggedIn() {
    	$session = new Session();
    	$sessionStatus = $session->verify();
    	if ($sessionStatus !== true) {
    		return false;
    	}
    	return true;
    }    
}

// (Testing)
if(isset($_GET['sqsmethod']) && isset($_GET['sqsaction'])) {
	$sDispatcher = $_GET['sqsaction'] . "Dispatcher";
	$sFilename = $sDispatcher . ".php";
	require_once($sFilename);
	$oDispatcher = new $sDispatcher();
	require_once(dirname(__FILE__) . '/../../../config/dmsDefaults.php');
	if (!$oDispatcher->isLoggedIn()) {
    	echo _kt('Session has expired. Refresh page and login.');
    	exit();
	}
	if(!isset($_GET['sqsmethod'])) {
    	echo _kt('No sqsmethod specified.');
    	exit();
	}
	$method = $_GET['sqsmethod'];
	unset($_GET['sqsmethod']);
	unset($_GET['sqsaction']);
	call_user_func_array(array($oDispatcher, $method), $_GET);
	exit();
}
?>