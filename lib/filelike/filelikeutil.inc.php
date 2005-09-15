<?php
/**
 * $Id$
 *
 * Utilities using file-like objects
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

class KTFileLikeUtil {
    function copy_contents($oFrom, $oTo) {
        if ($oFrom->bSupportChunking && $oTo->bSupportChunking) {
            $res = $oFrom->open("r");
            if (PEAR::isError($res)) {
                return $res;
            }
            $res = $oTo->open("w");
            if (PEAR::isError($res)) {
                return $res;
            }
            while (!$oFrom->eof()) {
                $res = $oFrom->read(8192);
                if (PEAR::isError($res)) {
                    return $res;
                }
                $res = $oTo->write($res);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
            $res = $oFrom->close();
            if (PEAR::isError($res)) {
                return $res;
            }
            $res = $oTo->close();
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            $oTo->put_contents($oFrom->get_contents());
        }
        return;
    }

    function send_contents($oFrom, $bChunking = true) {
        if ($oFrom->bSupportChunking && $bChunking) {
            $res = $oFrom->open("r");
            if (PEAR::isError($res)) {
                return $res;
            }
            while (!$oFrom->eof()) {
                $res = $oFrom->read(8192);
                if (PEAR::isError($res)) {
                    return $res;
                }
                print $res;
            }
            $res = $oFrom->close();
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            print $oFrom->get_contents();
        }
    }
}

?>
