<?php

// TODO confirm whether this must load for all, but seems likely...
require_once(KT_LIB_DIR . '/datetime/datetimeutil.inc.php');

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/UserBrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/SharedUserBrowseView.inc.php');

/**
 * Utility class to switch between user specific browse views
 *
 */
class BrowseViewUtil {

    public static function getBrowseView()
    {
    	$oUser = User::get($_SESSION['userID']);
    	$userType = $oUser->getDisabled();

		return self::getBrowse($userType);
	}

	private static function getBrowse($userType)
	{
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
