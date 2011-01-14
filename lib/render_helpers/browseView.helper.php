<?php

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/UserBrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/SharedBrowseView.inc.php');

/**
 * Utility class to switch between user specific browse views
 *
 */
class BrowseViewUtil {

    static function getBrowseView()
    {
    	$oUser = User::get($_SESSION['userID']);
    	$userType = $oUser->getDisabled();
    	switch ($userType) {
    		case 0 :
    			return new UserBrowseView();
    			break;
    		case 4 :
    			return new SharedUserBrowseView();
    			break;
    		default:
    			return new UserBrowseView();
    			break;
    	}
	}

}

?>
