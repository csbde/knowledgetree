<?php

/**
 * $Id$
 *
 * This page is meant to provide functions to prevent XSS cracks.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
