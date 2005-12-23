<?php

/* quick help / tutorial / introduction for KT users / administrators. */

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . '/dashboard/DashletDisables.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dashboard/Notification.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

/* 
* The registration hooks. 
* 
* Since this is too small to _actually_ need a full plugin object, we go:
*
*/

class KTUserAssistance extends KTPlugin {
    var $sNamespace = 'ktcore.userassistance';
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTUserAssistance', 'ktcore.userassistance', __FILE__);
$oPlugin =& $oRegistry->getPlugin('ktcore.userassistance');

// ultra simple skeleton for the user tutorial
// FIXME do we want to store the namespace inside the dashlet?
class KTUserTutorialDashlet extends KTBaseDashlet {
	function is_active($oUser) {
	    $namespace = 'ktcore.dashlet.usertutorial';
		$disables = KTDashletDisable::getForUserAndDashlet($oUser->getId(), $namespace);
		
		if (!empty($disables)) { 
		    return false;
		} else {
		    return true;
		}
	}
	
    function render() {
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/usertutorial");
		$aTemplateData = array(
		);
		return $oTemplate->render($aTemplateData);
    }
}

$oPlugin->registerDashlet('KTUserTutorialDashlet', 'ktcore.dashlet.usertutorial', __FILE__);

// ultra simple skeleton for the admin tutorial
class KTAdminTutorialDashlet extends KTBaseDashlet {
	function is_active($oUser) {
	 
	    $namespace = 'ktcore.dashlet.admintutorial';

		if (!Permission::userIsSystemAdministrator($oUser->getId())) {
		    return false; // quickest disable.
		}
		
		$disables = KTDashletDisable::getForUserAndDashlet($oUser->getId(), $namespace);		
		if (!empty($disables)) { 
		    return false;
		} else {
		    return true;
		}
	}
	
    function render() {
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/dashlets/admintutorial");
		$aTemplateData = array(
		);
		return $oTemplate->render($aTemplateData);
    }
}
$oPlugin->registerDashlet('KTAdminTutorialDashlet', 'ktcore.dashlet.admintutorial', __FILE__);

class KTUserAssistBasePage extends KTStandardDispatcher {
    var $sSection = 'help';
    var $aBreadcrumbs = array(
	    array('action' => 'dashboard', 'name' => 'Dashboard'),
		array('name' => 'User Assistance')
	);
    var $pagefile = 'base';
	var $title = 'User Assistance';
	function do_main() {
	    $this->oPage->setBreadcrumbDetails($this->title);
	    $contents = @file_get_contents(dirname(__FILE__) . '/docs/' . $this->pagefile);
		if ($contents === false) { 
		    $contents = '<div class="ktError"><p>Unable to find requested documentation.</p></div>';
		}
		$this->oPage->setTitle($this->title);
		$this->oPage->setShowPortlets(false);
		return $contents;
	}
	
	// hide the dashlet from the user (e.g. don't show it again) and redirect back to the dashboard.
}

class KTUserAssistB1WhatIs extends KTUserAssistBasePage { var $pagefile = 'kt3b1-what-is-a-beta'; var $title = 'What is a Beta?'; }
$oPlugin->registerPage('kt3b1-what-is-a-beta', 'KTUserAssistB1WhatIs', __FILE__);

class KTUserAssistBugReportingGUide extends KTUserAssistBasePage { var $pagefile = 'kt-bug-reporting-guide'; var $title = 'Help! Something went wrong'; }
$oPlugin->registerPage('kt-bug-reporting-guide', 'KTUserAssistBugReportingGUide', __FILE__);

class KTUserAssistAdminQuickguide extends KTUserAssistBasePage { var $pagefile = 'admin-quickguide'; var $title = 'Quickstart Guide for Administrators'; }
$oPlugin->registerPage('admin-quickguide', 'KTUserAssistAdminQuickguide', __FILE__);

class KTUserAssistAdminGuideWhatsNew extends KTUserAssistBasePage { var $pagefile = 'admin-guide-whats-new-in-kt3'; var $title = 'What\'s new in KT3 for Administrators'; }
$oPlugin->registerPage('admin-guide-whats-new-in-kt3', 'KTUserAssistAdminGuideWhatsNew', __FILE__);

?>
