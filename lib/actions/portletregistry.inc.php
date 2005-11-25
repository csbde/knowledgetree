<?php

class KTPortletRegistry {
    var $actions = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPortletRegistry')) {
            $GLOBALS['oKTPortletRegistry'] = new KTPortletRegistry;
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
            $sPluginName = $aPortlet[3];
            $oRegistry = KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPluginName);
            $oPortlet =&  new $sPortletClass;
            $oPortlet->setPlugin($oPlugin);
            array_push($aReturn, &$oPortlet);
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
