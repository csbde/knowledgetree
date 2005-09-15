<?php

/**
 * $Id$
 *
 * Small non-domain-specific utility functions
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Neil Blakey-Milner <nbm@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

class KTUtil {
    function extractGPC () {
        foreach (func_get_args() as $var) {
            if (array_key_exists($var, $_REQUEST)) {
                $GLOBALS["$var"] = $_REQUEST["$var"];
            }
        }
    }

    function strToBool ($sString, $null = false, $empty = false) {
        $sString = strtoupper($sString);
        if ($sString == "Y") {
            return true;
        } elseif (($sString == "N")) {
            return false;
        } elseif (($sString == "ON")) {
            return true;
        } elseif (($sString == "OFF")) {
            return false;
        } elseif (($sString == "")) {
            return $empty;
        } else {
            return $null;
        }
    }

    function intToBool ($sString) {
        $iInt = (int)$sString;
        return $iInt !== 0;
    }


    function anyToBool ($sString, $null = false) {
        if (is_bool($sString)) {
            return $sString;
        }

        if (is_numeric($sString)) {
            return KTUtil::intToBool($sString);
        }

        if (is_string($sString)) {
            if (KTUtil::strToBool($sString) === true) {
                return true;
            }
        }

        if (is_null($sString)) {
            return $null;
        }

        return false;
    }

    function randomString($length=16, $sRandom="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"){
        $sString = "";
        $iCurLen = 0;
        $iRandomLen = strlen($sRandom);
        while ($length > $iCurLen) {
            $sString .= substr($sRandom, mt_rand(0, $iRandomLen -1), 1);
            $iCurLen++;
        }
        return $sString;
    }

    function arrayGet($aArray, $sKey, $mDefault = null) {
        if (array_key_exists($sKey, $aArray)) {
            return $aArray[$sKey];
        }
        return $mDefault;
    }

    function requestValue($sKey, $mDefault = null) {
        return KTUtil::arrayGet($_REQUEST, $sKey, $mDefault);
    }

    // {{{ whereToString
    /**
     * Convert an array of parameterised strings to a single
     * parameterised string.
     *
     * Return null in case of an empty array.
     */
    function whereToString($aWhere) {
        $aStrings = array();
        $aParams = array();
        foreach ($aWhere as $oSomething) {
            if (is_string($oSomething)) {
                $aStrings[] = $oSomething;
            } else if (is_array($oSomething)) {
                $aStrings[] = $oSomething[0];
                $aNewParams = array();
                foreach ($oSomething[1] as $oParam) {
                    if (is_array($oParam)) {
                        $aNewParams = array_merge($aNewParams, $oParam);
                    } else {
                        $aNewParams[] = $oParam;
                    }
                }
                $aParams = array_merge($aParams, $aNewParams); 
            } else {
                return PEAR::raiseError("Weird WhereClause passed");
            }
        }
        if (count($aStrings) === 0) {
            return null;
        }
        return array(join(" AND ", $aStrings), $aParams);
    }
    // }}}

    // {{{ safeShellString
    function safeShellString () {
        $aArgs = func_get_args();
        $aSafeArgs = array();
        if (is_array($aArgs[0])) {
            $aArgs = $aArgs[0];
        }
        $aSafeArgs[] = escapeshellarg(array_shift($aArgs));
        if (is_array($aArgs[0])) {
            $aArgs = $aArgs;
        }
        foreach ($aArgs as $sArg) {
            if (empty($sArg)) {
                $aSafeArgs[] = "''";
            } else {
                $aSafeArgs[] = escapeshellarg($sArg);
            }
        }
        return join(" ", $aSafeArgs);
    }
    // }}}

    // {{{
    function copyDirectory($sSrc, $sDst, $bMove = false) {
        if (file_exists($sDst)) {
            return PEAR::raiseError("Destination directory already exists.");
        }
        if (OS_UNIX) {
            if ($bMove && file_exists('/bin/mv')) {
                system(KTUtil::safeShellString(array('/bin/mv', $sSrc, $sDst)));
                return;
            }
            if (!$bMove && file_exists('/bin/cp')) {
                system(KTUtil::safeShellString(array('/bin/cp', '-R', $sSrc, $sDst)));
                return;
            }
        }

        if (substr($sDst, 0, strlen($sSrc)) === $sSrc) {
            return PEAR::raiseError("Destination of move is within source");
        }

        $hSrc = @opendir($sSrc);
        if ($hSrc === false) {
            return PEAR::raiseError("Could not open source directory: $sSrc");
        }

        if (@mkdir($sDst, 0777) === false) {
            return PEAR::raiseError("Could not create destination directory: $sDst");
        }

        while (($sFilename = readdir($hSrc)) !== false) {
            if (in_array($sFilename, array('.', '..'))) {
                continue;
            }
            $sOldFile = sprintf("%s/%s", $sSrc, $sFilename);
            $sNewFile = sprintf("%s/%s", $sDst, $sFilename);

            if (is_dir($sOldFile)) {
                KTUtil::copyDirectory($sOldFile, $sNewFile, $bMove);
                continue;
            }

            if ($bMove) {
                KTUtil::moveFile($sOldFile, $sNewFile);
            } else {
                copy($sOldFile, $sNewFile);
            }
        }
        if ($bMove) {
            rmdir($sSrc);
        }
    }
    // }}}

    // {{{ moveDirectory
    function moveDirectory($sSrc, $sDst) {
        return KTUtil::copyDirectory($sSrc, $sDst, true);
    }
    // }}}
    
    // {{{ moveFile
    function moveFile ($sSrc, $sDst) {
        // Only 4.3.3 and above allow us to use rename across partitions
        // on Unix-like systems.
        if (OS_UNIX) {
            // If /bin/mv exists, just use it.
            if (file_exists('/bin/mv')) {
                system(KTUtil::safeShellString(array('/bin/mv', $sSrc, $sDst)));
                return;
            }

            $aSrcStat = stat($sSrc);
            if ($aSrcStat === false) {
                return PEAR::raiseError("Couldn't stat source file: $sSrc");
            }
            $aDstStat = stat(dirname($sDst));
            if ($aDstStat === false) {
                return PEAR::raiseError("Couldn't stat destination location: $sDst");
            }
            if ($aSrcStat["dev"] === $aDstStat["dev"]) {
                $res = @rename($sSrc, $sDst);
                if ($res === false) {
                    return PEAR::raiseError("Couldn't move file to destination: $sDst");
                }
                return;
            }

            $res = @copy($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError("Could not copy to destination: $sDst");
            }
            $res = @unlink($sSrc);
            if ($res === false) {
                return PEAR::raiseError("Could not remove source: $sSrc");
            }
        } else {
            $res = @rename($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError("Could not move to destination: $sDst");
            }
        }
    }
    // }}}
}

?>
