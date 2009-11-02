<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * John
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
