<?php

if (!function_exists('array_fill')) {
    function array_fill($iStart, $iLen, $mValue) {
        $aResult = array();
        $iEnd = $iLen + $iStart;
        for ($iCount = $iStart; $iCount < $iEnd; $iCount++) {
            $aResult[$iCount] = $mValue;
        }
        return $aResult;
    }
}

if(!function_exists('array_combine')) {
    function array_combine($a, $b) {
        $c = array();
        $at = array_values($a);
        $bt = array_values($b);
        foreach ($at as $key=>$aval) {
            $c[$aval] = $bt[$key];
        }
        return $c;
    }
}

 
?>
