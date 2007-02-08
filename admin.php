<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

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
            array('url' => KTUtil::getRequestScriptName($_SERVER), 'name' => _kt('Administration')),
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
		$KTConfig =& KTConfig::getSingleton();
        $condensed_admin = $KTConfig->get("condensedAdminUI");
        
        $aAllItems = array();
        // we need to investigate sub_url solutions.
        if ($condensed_admin) {
            foreach ($categories as $aCategory) {
                $aItems = $oRegistry->getItemsForCategory($aCategory['name']);
                $aAllItems[$aCategory['name']] = $aItems;
            }
        }
        
        $this->oPage->title = _kt("DMS Administration") . ": ";
        $oTemplating =& KTTemplating::getSingleton();
        
        if ($condensed_admin) {
            $oTemplate = $oTemplating->loadTemplate("kt3/admin_fulllist");
        } else {
            $oTemplate = $oTemplating->loadTemplate("kt3/admin_categories");
        }
        
        $aTemplateData = array(
              "context" => $this,
              "categories" => $categories,
              "all_items" => $aAllItems,
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

        
        $this->oPage->title = _kt("DMS Administration") . ": " . $aCategory["title"];
        $oTemplating =& KTTemplating::getSingleton();
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
       $oDispatcher->aBreadcrumbs[] = array('action' => "administration", 'name' => _kt('Administration'));
       $oDispatcher->aBreadcrumbs[] = array("name" => $aCategory['title'], "url" => KTUtil::ktLink('admin.php',$aParts[0]));
       
    } else {
       // FIXME (minor) redirect to no-suburl?
       $oDispatcher = new AdminSplashDispatcher();
       $oDispatcher->category = $sub_url;
    }
}

$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)

?>
