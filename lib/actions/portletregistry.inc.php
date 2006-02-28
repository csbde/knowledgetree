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

class KTPortletRegistry {
    var $actions = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPortletRegistry')) {
            $GLOBALS['oKTPortletRegistry'] =& new KTPortletRegistry;
        }
        return $GLOBALS['oKTPortletRegistry'];
    }
    // }}}

    function registerPortlet($action, $name, $nsname, $path = "", $sPlugin = "") {
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
            $oPortlet =&  new $sPortletClass;
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
