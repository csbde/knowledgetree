<?php 

/**
 * $Id$
 *
 * Handles database value escaping.
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
 * @author Michael Joseph, Jam Warehouse (Pty) Ltd, South Africa
 */

/**
 * Apply stripslashes recursively.
 * [From php.net/mysql-real-escape-string]
 */
function stripslashes_deep($value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
}
/**
 * Quote a variable to make it safe for rdbms processing.
 * This includes some SQL injection combatting side-effects.
 * [From php.net/mysql-real-escape-string] 
 */
//TODO: add tablename/fieldname escaping function from forum.pear:db
//      move to pear::db in the medium term, instead of implementing XSS,sql injection prevention code 
function quote($value) {
    global $default;
	// Stripslashes if we need to
	if (get_magic_quotes_gpc()) {
		$value = stripslashes_deep($value);
	}

    if (is_numeric($value)) {
        return $value;
    } elseif (is_bool($value)) {
        return $value ? 1 : 0;
    } elseif (is_null($value)) {
        return 'NULL';
    } else {
		// only use the mysql api function if we're using mysql
        // ??: is the api function still available to php if its not compiled in
        // ??: it is in by default isn't it?
		if ($default->dbType == "mysql") {
			$value = "'" . mysql_real_escape_string($value) . "'";
		} else {
            $value = "'" . addslashes($value) . "'";
        }
		return $value;
	}
}
?>