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

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

class KTDashletRegistry {
    var $nsnames = array();

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTDashboardRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'] = new KTDashletRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'];
    }


    function registerDashlet($name, $nsname, $filename = "", $sPlugin = "") {

        $this->nsnames[$nsname] = array($name, $filename, $nsname, $sPlugin);
    }

    private function loadDashletHelpers()
    {
        if (!empty($this->nsnames)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('dashlet');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2])) {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            call_user_func_array(array($this, 'registerDashlet'), $params);
        }
    }
    
    /**
     * Get any dashlets added since the user's last login
     *
     * @param object $oUser
     */
    function getNewDashlets($user, $current) {
        $new = array();
        $inactive = array();

        static $inactiveList = '';
        $ignore = (!empty($inactiveList)) ? $current.','.$inactiveList : $current;

        // Get all dashlets that haven't already been displayed to the user and are active for the user
        $helpers = KTPluginUtil::loadPluginHelpers('dashlet');

        $ignore = explode(',', $ignore);
        
        /*
        $query = "SELECT h.classname, h.pathname, h.plugin FROM plugin_helper h
            INNER JOIN plugins p ON (h.plugin = p.namespace)
            WHERE p.disabled = 0 AND classtype = 'dashlet' ";

        if(!empty($sIgnore)){
            $query .= " AND h.classname NOT IN ($ignore)";
        }

        $res = DBUtil::getResultArray($query);
        */

        // If the query is not empty, get the dashlets and return the new active ones
        // Add the inactive ones the list.
        if (!empty($helpers)) {
            $registry = KTPluginRegistry::getSingleton();
            
            foreach ($helpers as $item){
                $name = $item['classname'];
                
                if (in_array($name, $ignore)) {
                    continue;
                }
                
                $filename = $item['pathname'];
                $pluginName = $item['plugin'];

                require_once($filename);
                $plugin = $registry->getPlugin($pluginName);

                $dashlet = new $name;
                $dashlet->setPlugin($plugin);
                if ($dashlet->is_active($user)) {
                    $new[] = $name;
                }else{
                    $inactive[] = "'$name'";
                }
            }
            // Add new inactive dashlets
            $newInactive = implode(',', $inactive);
            $inactiveList = (!empty($inactiveList)) ? $inactiveList.','.$newInactive : $newInactive;

            return $new;
        }
        return '';
    }

    // FIXME we might want to do the pruning now, but I'm unsure how to handle the preconditions.
    function getDashlets($user) 
    {
        $this->loadDashletHelpers();
           
        $dashlets = array();
        $pluginRegistry = KTPluginRegistry::getSingleton();
        
        // probably not the _best_ way to do things.
        foreach ($this->nsnames as $portlet) {
            $name = $portlet[0];
            $filename = $portlet[1];
            $pluginName = $portlet[3];

            require_once($filename);
            $plugin = $pluginRegistry->getPlugin($pluginName);

            $dashlet = new $name;
            $dashlet->setPlugin($plugin);
            if ($dashlet->is_active($user)) {
                $dashlets[] = $dashlet;
            }
        }

        return $dashlets;
    }
}

?>
