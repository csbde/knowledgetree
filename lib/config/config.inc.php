<?php

require_once("Config.php");

require_once(KT_LIB_DIR . '/util/ktutil.inc');

class KTConfig {
    var $conf = array();
    var $aSectionFile;
    var $aFileRoot;
    var $flat = array();
    var $flatns = array();

    function loadFile($filename, $bDefault = false) {
        $c = new Config;
        $root =& $c->parseConfig($filename, "IniCommented");
        $this->aFileRoot[$filename] =& $root;
        $conf =& $root->toArray();
        foreach ($conf["root"] as $seck => $secv) {
            $aSectionFile[$seck] = $filename;
            if (is_array($secv)) {
                foreach ($secv as $k => $v) {
                    $this->setns($seck, $k, $v);
                }
            } else {
                $this->setns(null, $seck, $secv);
            }
        }
        $this->conf = array_merge($this->conf, $conf["root"]);
    }

    function setns($seck, $k, $v, $bDefault = false) {
        if ($v === "default") {
            return;
        } elseif ($v === "true") {
            $v = true;
        } elseif ($v === "false") {
            $v = false;
        }
        $this->flat[$k] = $v;
        if (!is_null($seck)) {
            $this->flatns["$seck/$k"] = $v;
        }
        return;
    }

    function setdefaultns($seck, $k, $v) {
        return $this->setns($seck, $k, $v, true);
    }

    var $expanded = array();
    var $expanding = array();
    function expand($val) {
        if (strpos($val, '$') === false) {
            return $val;
        }
        $v = $val;
        while(($m = preg_match('/\$\{([^}]+)\}/', $v, $matches))) {
            array_push($this->expanding, $matches[1]);
            $r = $this->get($matches[1]);
            if (PEAR::isError($r)) {
                return $r;
            }
            $v = str_replace($matches[0], $r, $v);
            $this->expanded[$matches[1]] = $r;
        }
        return $v;
    }

    function get($var, $oDefault = null) {
        if (array_key_exists($var, $this->flatns)) {
            return $this->expand($this->flatns[$var]);
        }
        if (array_key_exists($var, $this->flat)) {
            return $this->expand($this->flat[$var]);
        }
        return $oDefault;
    }

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'KTConfig')) {
            $GLOBALS['KTConfig'] =& new KTConfig;
        }
        return $GLOBALS['KTConfig'];
    }
}


?>
