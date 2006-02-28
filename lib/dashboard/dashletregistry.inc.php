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

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

class KTDashletRegistry {
    var $nsnames = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTDashboardRegistry')) {
            $GLOBALS['oKTDashboardRegistry'] = new KTDashletRegistry;
        }
        return $GLOBALS['oKTDashboardRegistry'];
    }
    // }}}

    function registerDashlet($name, $nsname, $filename = "", $sPlugin = "") {
        
        $this->nsnames[$nsname] = array($name, $filename, $nsname, $sPlugin);
    }

    // FIXME we might want to do the pruning now, but I'm unsure how to handle the preconditions.
    function getDashlets($oUser) {
        $aDashlets = array();
        $oRegistry =& KTPluginRegistry::getSingleton();        
        // probably not the _best_ way to do things.
        foreach ($this->nsnames as $aPortlet) {
            $name = $aPortlet[0];
            $filename = $aPortlet[1];
            $sPluginName = $aPortlet[3];
            
            require_once($aPortlet[1]);
            $oPlugin =& $oRegistry->getPlugin($sPluginName);
            
            $oDashlet =& new $name;
            $oDashlet->setPlugin($oPlugin);
            if ($oDashlet->is_active($oUser)) {
                $aDashlets[] = $oDashlet;
            }
        }
        
        return $aDashlets;
    }
}

?>
