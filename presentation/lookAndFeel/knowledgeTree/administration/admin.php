<?php
require_once("../../../../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");

// registry.
require_once(KT_DIR . "/plugins/ktcore/KTAdminNavigation.php");
require_once(KT_DIR . "/plugins/ktcore/KTAdminPlugins.php");

class AdminSplashDispatcher extends KTAdminDispatcher {
    var $sub_url = '';
	
	var $aBreadcrumbs = array(
        array('action' => 'administration', 'name' => 'Administration'),
    );
	
    function AdminSplashDispatcher() {
		parent::KTAdminDispatcher();
	}

	function do_main() {
	    // are we categorised, or not?
		$oRegistry =& KTAdminNavigationRegistry::getSingleton();
		$categories = $oRegistry->getCategories();		
		
		$this->oPage->title = "DMS Administration: ";
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/admin_categories");
		$aTemplateData = array(
              "context" => $this,
			  "categories" => $categories,
		);
		return $oTemplate->render($aTemplateData);				
	}

    function do_viewCategory() {
	    // are we categorised, or not?
		
		$category = KTUtil::arrayGet($_REQUEST, "fCategory");
		
		$oRegistry =& KTAdminNavigationRegistry::getSingleton();
		$aCategory = $oRegistry->getCategory($category);		
		
		$aItems = $oRegistry->getItemsForCategory($category);
		$this->aBreadcrumbs[] = array("name" => $aCategory["title"]);

		
		$this->oPage->title = "DMS Administration: " . $aCategory["title"];
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/admin_items");
		$aTemplateData = array(
              "context" => $this,
			  "category" => $aCategory,
			  "items" => $aItems, 
		);
		return $oTemplate->render($aTemplateData);				
	}
}

$sub_url = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
$sub_url = trim($sub_url);
$sub_url= trim($sub_url, "/");

if (empty($sub_url)) {
    $oDispatcher = new AdminSplashDispatcher();
} else {
    $oRegistry =& KTAdminNavigationRegistry::getSingleton();
    if ($oRegistry->isRegistered($sub_url)) {
       $oDispatcher = $oRegistry->getDispatcher($sub_url);
	} else {
	   // FIXME (minor) redirect to no-suburl?
	   $oDispatcher = new AdminSplashDispatcher();
	}
}

$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)

?>
