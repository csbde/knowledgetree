<?php
/**
 * $Id$
 *
 * Contains datetime functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.database 
 */

/**
 * Returns the current date time
 *
 * @return string the current date time (Y-m-d H:i:s)
 */
function getCurrentDateTime() {
    return date("Y-m-d H:i:s", time());
}

/**
 * Returns the specified date time, formatted as Y-m-d H:i:s
 *
 * @param int the date time to format
 * @return string the formatted date time
 */
function formatDateTime($dateTime) {
    return date("Y-m-d H:i:s", $dateTime);
}
?>
