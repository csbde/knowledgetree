<?php

class KTCache {
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTCache')) {
            $GLOBALS['oKTCache'] = new KTCache;
        }
        return $GLOBALS['oKTCache'];
    }
    // }}}

    // takes an Entity type-name, and an array of the failed attrs.
    function alertFailure($sEntityType, $aFail) {
        //var_dump($aFail); 
        $sMessage = sprintf('Failure in cache-comparison on type "%s":  %s', $sEntityType, implode(', ', $aFail));
        global $default;
        $default->log->error($sMessage);
        $_SESSION['KTErrorMessage'][] = $sMessage;
    }

    function KTCache() {
        require_once("Cache/Lite.php");
        require_once(KT_LIB_DIR . '/config/config.inc.php');

        $aOptions = array();
        $oKTConfig = KTConfig::getSingleton();
        $this->bEnabled = $oKTConfig->get('cache/cacheEnabled', false);
        if (empty($this->bEnabled)) {
            return;
        }

        $aOptions['cacheDir'] = $oKTConfig->get('cache/cacheDirectory') . "/";
        $user = KTLegacyLog::running_user();
        if ($user) {
            $aOptions['cacheDir'] .= $user . '/';
        }
        if (!file_exists($aOptions['cacheDir'])) {
            mkdir($aOptions['cacheDir']);
        }
        $aOptions['lifeTime'] = 60;
        $aOptions['memoryCaching'] = true;
        $aOptions['automaticSerialization'] = true;
        $this->oLite =& new Cache_Lite($aOptions);
    }

    function get($group, $id) {
        if (empty($this->bEnabled)) {
            return array(false, false);
        }
        $stuff = $this->oLite->get($id, strtolower($group));
        if (is_array($stuff)) {
            return array(true, $stuff[0]);
        }
        return array(false, false);
    }

    function set($group, $id, $val) {
        if (empty($this->bEnabled)) {
            return false;
        }
        return $this->oLite->save(array($val), $id, strtolower($group));
    }

    function remove($group, $id) {
        if (empty($this->bEnabled)) {
            return false;
        }
        return $this->oLite->remove($id, strtolower($group));
    }

    function clear($group) {
        if (empty($this->bEnabled)) {
            return false;
        }
        return $this->oLite->clean(strtolower($group));
    }
}

?>
