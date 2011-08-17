<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 *
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

class KTPortletRegistry {
    var $actions = array();
    // {{{ getSingleton
    static function &getSingleton () {
    	static $singleton=null;
    	if (is_null($singleton))
    	{
    		$singleton = new KTPortletRegistry();
    	}
    	return $singleton;
    }
    // }}}

    function registerPortlet($action, $name, $nsname, $path = '', $sPlugin = '') {
        if (!is_array($action)) {
            $action = array($action);
        }
        foreach ($action as $slot) {
            $this->portlets[$slot] = KTUtil::arrayGet($this->actions, $slot, array());
            $this->actions[$slot][$nsname] = array($name, $path, $nsname, $sPlugin);
        }
        $this->nsnames[$nsname] = array($name, $path, $nsname, $sPlugin);
    }

    private function loadPortletHelpers()
    {
        if (!empty($this->nsnames)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('portlet');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            $location = unserialize($params[0]);
            if ($location != false) {
               $params[0] = $location;
            }
            if (isset($params[3])) {
                $params[3] = KTPluginUtil::getFullPath($params[3]);
            }
            
            call_user_func_array(array($this, 'registerPortlet'), $params);
        }
    }
    
    function getPortletsForPage($breadcrumbs) 
    {
        $this->loadPortletHelpers();
        
        $portlets = array();
        foreach ($breadcrumbs as $breadcrumb) {
            $action = KTUtil::arrayGet($breadcrumb, 'action');
            if (empty($action)) {
                continue;
            }
            
            $actionPortlets = $this->getPortlet($action);
            if (empty($actionPortlets)) {
                continue;
            }
            foreach ($actionPortlets as $portlet) {
                $portlets[] = $portlet;
            }
        }

        $return = array();
        $done = array();

        foreach ($portlets as $portlet) {
            if (in_array($portlet, $done)) {
                continue;
            }
            $done[] = $portlet;

            $portletClass = $portlet[0];
            $portletFile = $portlet[1];
            $pluginName = $portlet[3];
            $registry = KTPluginRegistry::getSingleton();
            $plugin = $registry->getPlugin($pluginName);
            
            if (file_exists($portletFile)) {
                require_once($portletFile);
            }
            $portletObj =new $portletClass;
            $portletObj->setPlugin($plugin);
            array_push($return, $portletObj);
        }
        return $return;
    }

    function getPortlet($slot) 
    {
        return $this->actions[$slot];
    }

    function getPortletByNsname($nsname) 
    {
        return $this->nsnames[$nsname];
    }
}

?>
