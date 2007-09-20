<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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

    function getPortletsForPage($aBreadcrumbs) {
        $aPortlets = array();
        foreach ($aBreadcrumbs as $aBreadcrumb) {
            $action = KTUtil::arrayGet($aBreadcrumb, 'action');
            if (empty($action)) {
                continue;
            }
            $aThisPortlets = $this->getPortlet($action);
            if (empty($aThisPortlets)) {
                continue;
            }
            foreach ($aThisPortlets as $aPortlet) {
                $aPortlets[] = $aPortlet;
            }
        }

        $aReturn = array();
        $aDone = array();

        foreach ($aPortlets as $aPortlet) {
            if (in_array($aPortlet, $aDone)) {
                continue;
            }
            $aDone[] = $aPortlet;

            $sPortletClass = $aPortlet[0];
            $sPortletFile = $aPortlet[1];
            $sPluginName = $aPortlet[3];
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPluginName);
            if (file_exists($sPortletFile)) {
                require_once($sPortletFile);
            }
            $oPortlet =new $sPortletClass;
            $oPortlet->setPlugin($oPlugin);
            array_push($aReturn, $oPortlet);
        }
        return $aReturn;
    }

    function getPortlet($slot) {
        return $this->actions[$slot];
    }

    function getPortletByNsname($nsname) {
        return $this->nsnames[$nsname];
    }
}

?>
