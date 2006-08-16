<?php

/*
 * The valiodator factory is a singleton, which can be used to create
 * and register validators.
 *
 */

class KTValidatorFactory {
    var $validators = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTValidatorFactory')) {
            $GLOBALS['_KT_PLUGIN']['oKTValidatorFactory'] = new KTValidatorFactory;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTValidatorFactory'];    
    }
    
    function registerValidator($sClassname, $sNamespace,  $sFilename = null) {
        $this->validators[$sNamespace] = array(
            'ns' => $sNamespace,
            'class' => $sClassname,
            'file' => $sFilename,
        );
    }
    
    function &getValidatorByNamespace($sNamespace) {
        $aInfo = KTUtil::arrayGet($this->validators, $sNamespace);
        if (empty($aInfo)) {
            return PEAR::raiseError(sprintf(_kt('No such validator: %s'), $sNamespace));
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
            $oValidator =& $this->getValidatorByNamespace($namespaceOrObject);
        } else {
            $oValidator = $namespaceOrObject;
        }
        
        if (PEAR::isError($oValidator)) {
            return $oValidator;
        }
        
        $aConfig = (array) $aConfig; // always an array
        $res = $oValidator->configure($aConfig);
        if (PEAR::isError($res)) {
            return $res;
        }
        
        return $oValidator;
    }
}

?>
