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

    function registerAction($slot, $name, $nsname, $path = "") {
        $this->actions[$slot] = KTUtil::arrayGet($this->actions, $slot, array());
        $this->actions[$slot][$nsname] = array($name, $path, $nsname);
    }

    function getActions($slot) {
        return $this->actions[$slot];
    }
}

?>
