<?php

/*
 * The widget factory is a singleton, which can be used to create
 * and register widgets.
 *
 */

class KTWidgetFactory {
    var $widgets = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTWidgetFactory')) {
            $GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'] = new KTWidgetFactory;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'];    
    }
    
    function registerWidget($sClassname, $sNamespace,  $sFilename = null) {
        $this->widgets[$sNamespace] = array(
            'ns' => $sNamespace,
            'class' => $sClassname,
            'file' => $sFilename,
        );
    }
    
    function &getWidgetByNamespace($sNamespace) {
        $aInfo = KTUtil::arrayGet($this->widgets, $sNamespace);
        if (empty($aInfo)) {
            return PEAR::raiseError(sprintf(_kt('No such widget: %s'), $sNamespace));
        }
        if (!empty($aInfo['file'])) {
            require_once($aInfo['file']);
        }
        
        return new $aInfo['class'];
    }    
    
    // this is overridden to either take a namespace or an instantiated
    // class.  Doing it this way allows for a consistent approach to building
    // forms including custom widgets. 
    function &get($namespaceOrObject, $aConfig = null) {
        if (is_string($namespaceOrObject)) {
            $oWidget =& $this->getWidgetByNamespace($namespaceOrObject);
        } else {
            $oWidget = $namespaceOrObject;
        }
        
        if (PEAR::isError($oWidget)) {
            return $oWidget;
        }
        
        $aConfig = (array) $aConfig; // always an array
        $res = $oWidget->configure($aConfig);
        if (PEAR::isError($res)) {
            return $res;
        }
        
        return $oWidget;
    }
}

?>
