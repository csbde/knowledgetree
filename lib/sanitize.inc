<?php

/**
 * $Id$
 *
 * This page is meant to provide functions to prevent XSS cracks.
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
 * @author Andrew Glen-Young <andrew@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

/**
 * Accepts a web encoded string and outputs a "clean" string.
 */

function sanitize($string) {
	// This should be set if you've read the INSTALL instructions.
	// Better to be safe though.
	if (get_magic_quotes_gpc()) {
        	$string = strip_tags(urldecode(trim($string)));
 	} else {
        	$string = addslashes(strip_tags(urldecode(trim($string))));
	}

	// This might be a little too aggressive
	$pattern = "([^[:alpha:]|^_\.\ \:-])";
	return ereg_replace($pattern, '', $string);
}

?>
