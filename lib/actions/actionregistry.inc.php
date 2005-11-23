<?php

class KTActionRegistry {
    var $actions = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTActionRegistry')) {
            $GLOBALS['oKTActionRegistry'] = new KTActionRegistry;
        }
        return $GLOBALS['oKTActionRegistry'];
    }
    // }}}

    function registerAction($slot, $name, $nsname, $path = "", $sPlugin = null) {
        $this->actions[$slot] = KTUtil::arrayGet($this->actions, $slot, array());
        $this->actions[$slot][$nsname] = array($name, $path, $nsname, $sPlugin);
        $this->nsnames[$nsname] = array($name, $path, $nsname, $sPlugin);
    }

    function getActions($slot) {
        return $this->actions[$slot];
    }

    function getActionByNsname($nsname) {
        return $this->nsnames[$nsname];
    }
}

?>
