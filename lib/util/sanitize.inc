<?php
/**
 * $Id$
 *
 * This page is meant to provide functions to prevent XSS cracks.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
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
	//$pattern = "([^[:alpha:]|^_\.\ \:-])";
	// Allow numeric characters
	$pattern = "([^[:alnum:]|^_\.\ \:-])";
	return ereg_replace($pattern, '', $string);
}

?>
