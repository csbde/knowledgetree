<?php

/**
 * $Id$
 *
 * Contains datetime functions
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
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
