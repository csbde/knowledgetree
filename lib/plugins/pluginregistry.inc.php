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

require_once( KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTPluginRegistry {
    var $_aPluginDetails = array();
    var $_aPlugins = array();

    static function &getSingleton() {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTPluginRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTPluginRegistry'] = new KTPluginRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTPluginRegistry'];
   	}

   	/**
     * Register the plugin in the database
     *
     * @param unknown_type $sClassName
     * @param unknown_type $sNamespace
     * @param unknown_type $sFilename
     */
    function registerPlugin($sClassName, $sNamespace, $sFilename = null) {
        $sFilename = (!empty($sFilename)) ? KTPlugin::_fixFilename($sFilename) : '';
        $this->_aPluginDetails[$sNamespace] = array($sClassName, $sNamespace, $sFilename);

        /*
        Check whether the system is registering or not. If true, register the plugin in plugin_helper.
        If false, skip.
        This check has been put in place to prevent the plugin being registered on every page load.
        */
        if(isset($_SESSION['plugins_registerplugins']) && $_SESSION['plugins_registerplugins']){
            $object = $sClassName.'|'.$sNamespace.'|'.$sFilename;
            KTPlugin::registerPluginHelper($sNamespace, $sClassName, $sFilename, $object, 'general', 'plugin');
        }
    }

    private function loadPluginHelpers()
    {
        if (!empty($this->_aPluginDetails)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('plugin');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2])) {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            $this->_aPluginDetails[$namespace] = array($params[0], $params[1], $params[2]);
        }
    }
    
    function &getPlugin($namespace) 
    {
        if (array_key_exists($namespace, $this->_aPlugins)) {
            return $this->_aPlugins[$namespace];
        }
        
        $this->loadPluginHelpers();
        
        $details = KTUtil::arrayGet($this->_aPluginDetails, $namespace);
        if (empty($details)) {
                return null;
        }
        
        $filename = (KTUtil::isAbsolutePath($details[2])) ? $details[2] : KT_DIR.'/'.$details[2];
        if (!empty($filename)) {
            require_once($filename);
        }
        
        $className = $details[0];
        $plugin = new $className($filename);
        $this->_aPlugins[$namespace] =& $plugin;
        
        return $plugin;
    }

    function &getPlugins() 
    {
        $this->loadPluginHelpers();
        
        $ret = array();
        foreach (array_keys($this->_aPluginDetails) as $pluginName) {
            $plugin =& $this->getPlugin($pluginName);
            $ret[(int)$plugin->iOrder][] =& $plugin;
        }
        
        ksort($ret);
        $res = array();
        foreach($ret as $order => $pluginList) {
            foreach(array_keys($pluginList) as $pluginId) {
                $res[] =& $ret[$order][$pluginId];
            }
        }
        return $res;
    }
}

