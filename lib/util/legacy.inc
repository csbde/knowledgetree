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
 
?>
