<?php

class KTFieldsetDisplayRegistry {
    
    var $fieldset_types = array();
    
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTFieldsetDisplayRegistry')) {
            $GLOBALS['oKTFieldsetDisplayRegistry'] = new KTFieldsetDisplayRegistry;
        }
        return $GLOBALS['oKTFieldsetDisplayRegistry'];
    }
    // }}}


    // FIXME include a reg-class, so that lower items can ensure they require_once.
    // pass in:
    //   nsname (e.g. ktcore/subscription)
    //   classname (e.g. KTSimpleFieldset)
    function registerFieldsetDisplay($nsname, $className) {
        $this->fieldset_types[$nsname] = $className;
    }
    
    function getHandler($nsname) {
        if (!array_key_exists($nsname, $this->fieldset_types)) {
            return 'SimpleFieldsetDisplay';
        } else {
            return $this->fieldset_types[$nsname];
        }
    }
}

?>