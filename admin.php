<?php
/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
            array('url' => KTUtil::getRequestScriptName($_SERVER), 'name' => 'Administration'),
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
        
        $this->oPage->title = _("DMS Administration") . ": ";
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

        
        $this->oPage->title = _("DMS Administration") . ": " . $aCategory["title"];
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
       $oDispatcher->aBreadcrumbs[] = array('action' => "administration", 'name' => 'Administration');
       $oDispatcher->aBreadcrumbs[] = array("name" => $aCategory['title'], "url" => KTUtil::ktLink('admin.php',$aParts[0]));
       
    } else {
       // FIXME (minor) redirect to no-suburl?
       $oDispatcher = new AdminSplashDispatcher();
       $oDispatcher->category = $sub_url;
    }
}

$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)

?>
