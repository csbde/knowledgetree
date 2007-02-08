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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

class KTDashletRegistry {
    var $nsnames = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTDashboardRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'] = new KTDashletRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'];
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
