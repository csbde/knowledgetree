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

// {{{ KTUtil
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
        } elseif (($sString == "")) {
            return $empty;
        } else {
            return $null;
        }
    }


    function anyToBool ($sString, $null = false) {
        if (is_bool($sString)) {
            return $sString;
        }

        if (is_string($sString)) {
            if (strToBool($sString) === true) {
                return true;
            }
        }

        if (is_int($sString)) {
            return intToBool($sString);
        }

        if (is_null($sString)) {
            return $null;
        }

        return false;
    }

    
}
// }}}

?>
