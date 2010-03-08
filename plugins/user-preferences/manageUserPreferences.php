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

require_once('UserPreferences.inc.php'); // 

require_once(KT_LIB_DIR . '/templating/templating.inc.php'); // 
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

class ManageUserPreferencesDispatcher extends KTStandardDispatcher {
	//--------------------------------
	// Forms
	//--------------------------------
	
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return html
    */
    public function do_main() {
		return "Coming Soon";
    }
    
    /**
    *
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return html
    */
    // TODO : Write generic, this only for zoho warning
    public function do_saveUserPreference() {
    	$sValue = KTUtil::arrayGet($_GET, 'zohoWarning', '');
    	if($sValue != '') {
    		$aUserPreference = UserPreferences::getUserPreferences($this->oUser->getId(), 'zohoWarning'); // Get user preference
    		if(empty($aUserPreference) || is_null($aUserPreference)) { // Create the prefernce
    			$oUserPreference = new UserPreferences( $this->oUser->getId(), 'zohoWarning', $sValue);
    			DBUtil::startTransaction();
    			$oUserPreference->create();
    			DBUtil::commit();
    		} else {
	    		foreach ($aUserPreference as $oUserPreference) { // Access object
		    		if($oUserPreference->getValue() != $sValue) { // Update preference
		    			$oUserPreference->setValue($sValue);
		    			DBUtil::startTransaction();
		    			$oUserPreference->update();
		    			DBUtil::commit();
		    		}
	    		}
    		}
    	}
    	exit();
    }
    
    public function do_setPreference($sKey, $sValue) {
    	
    }
}

class adminManageUserPreferencesDispatcher extends ManageUserPreferencesDispatcher {
    var $bAdminRequired = true;
    var $sSection = 'administration';

    function adminManageUserPreferencesDispatcher() {
		$this->aBreadcrumbs = array(array('action' => 'administration', 'name' => _kt('Administration')),);

		return parent::KTStandardDispatcher();
    }
}

class manageUserPreferencesNavigationPortlet extends KTPortlet {
    var $bActive = true;
    
    function manageUserPreferencesNavigationPortlet($sTitle) {
        parent::KTPortlet($sTitle);
    }
}

?>
