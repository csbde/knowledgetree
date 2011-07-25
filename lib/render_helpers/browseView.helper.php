<?php

// TODO confirm whether this must load for all, but seems likely...
require_once(KT_LIB_DIR . '/datetime/datetimeutil.inc.php');

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/UserBrowseView.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/SharedUserBrowseView.inc.php');

require_once(KT_LIB_DIR . '/render_helpers/BulkActionBrowse.inc.php');
require_once(KT_LIB_DIR . '/render_helpers/SharedUserBrowseView.inc.php');

/**
 * Utility class to switch between user specific browse views
 *
 */
class BrowseViewUtil {

    public static function getBrowseView($bulkActionInProgress = '')
    {
    	$oUser = User::get($_SESSION['userID']);
    	$userType = $oUser->getDisabled();
    	if($bulkActionInProgress == '') {
			return self::getBrowse($userType);
    	}
	    else {
	    	return self::getBulkActionBrowse($bulkActionInProgress);
	    }
	}

	private static function getBrowse($userType)
	{
    	switch ($userType) {
    		case 0 :
   				return new BulkActionBrowse();
    			break;
    		case 4 :
    			return new SharedUserBrowseView();
    			break;
    		default:
    			return new UserBrowseView();
    			break;
    	}
	}

	private static function getBulkActionBrowse($bulkActionInProgress)
	{
    	switch ($userType) {
    		case 0 :
    			return new UserBrowseView();
    			break;
    		case 4 :
    			return new SharedBulkActionBrowse();
    			break;
    		default:
    			return new UserBrowseView();
    			break;
    	}
	}
}

?>
