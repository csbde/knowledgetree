<?php
require_once("config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");

require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");

class AdminSplashDispatcher extends KTAdminDispatcher {
    var $category = '';
    var $sSection = 'administration';
    
    function AdminSplashDispatcher() {
        $this->aBreadcrumbs = array(
            array('url' => $_SERVER['PHP_SELF'], 'name' => 'Administration'),
        );
    
        parent::KTAdminDispatcher();
    }

    function do_main() {
        if ($this->category !== '') {
            return $this->do_viewCategory();
        };
    
    
        // are we categorised, or not?
        $oRegistry =& KTAdminNavigationRegistry::getSingleton();
        $categories = $oRegistry->getCategories();		
        
        // we need to investigate sub_url solutions.
        
        $this->oPage->title = _("DMS Administration") . ": ";
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/admin_categories");
        $aTemplateData = array(
              "context" => $this,
              "categories" => $categories,
              "baseurl" => $_SERVER['PHP_SELF'],
        );
        return $oTemplate->render($aTemplateData);				
    }

    function do_viewCategory() {
        // are we categorised, or not?
        
        $category = KTUtil::arrayGet($_REQUEST, "fCategory", $this->category);
        
        $oRegistry =& KTAdminNavigationRegistry::getSingleton();
        $aCategory = $oRegistry->getCategory($category);		
        
        $aItems = $oRegistry->getItemsForCategory($category);
        $this->aBreadcrumbs[] = array("name" => $aCategory["title"], "url" => KTUtil::ktLink('admin.php',$category));

        
        $this->oPage->title = _("DMS Administration") . ": " . $aCategory["title"];
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/admin_items");
        $aTemplateData = array(
              "context" => $this,
              "category" => $aCategory,
              "items" => $aItems, 
              "baseurl" =>  $_SERVER['PHP_SELF'],
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
       
       $aParts = explode('/',$sub_url);
        
       $oRegistry =& KTAdminNavigationRegistry::getSingleton();
       $aCategory = $oRegistry->getCategory($aParts[0]);			   
       
       $oDispatcher->aBreadcrumbs = array();
       $oDispatcher->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => 'Administration');
       $oDispatcher->aBreadcrumbs[] = array("name" => $aCategory['title'], "url" => KTUtil::ktLink('admin.php',$aParts[0]));
    } else {
       // FIXME (minor) redirect to no-suburl?
       $oDispatcher = new AdminSplashDispatcher();
       $oDispatcher->category = $sub_url;
    }
}

$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)

?>
