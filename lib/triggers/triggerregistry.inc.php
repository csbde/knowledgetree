<?php

class KTTriggerRegistry {
    var $triggers = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTTriggerRegistry')) {
            $GLOBALS['oKTTriggerRegistry'] = new KTTriggerRegistry;
        }
        return $GLOBALS['oKTTriggerRegistry'];
    }
    // }}}

    // {{{ registerTrigger
    function registerTrigger($action, $slot, $name, $nsname, $path = "") {
        if (!array_key_exists($action, $this->triggers)) {
            $this->triggers[$action] = array();
        }
        if (!array_key_exists($slot, $this->triggers[$action])) {
            $this->triggers[$action][$slot] = array();
        }
        $this->triggers[$action][$slot][$nsname] = array($name, $path, $nsname);
    }
    // }}}

    // {{{ getTriggers
    function getTriggers($action, $slot) {
        $ret = array();
        if (array_key_exists($action, $this->triggers)) {
            if (array_key_exists($slot, $this->triggers[$action])) {
                $ret = $this->triggers[$action][$slot];
            }
        }
        if (empty($ret)) {
            return array();
        }
        return $ret;
    }
    // }}}
}

?>
