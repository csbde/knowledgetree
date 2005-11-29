<?php

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
        $oRegistry = KTPluginRegistry::getSingleton();        
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
