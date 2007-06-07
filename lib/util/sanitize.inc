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

function sanitizeForSQL($string, $min='', $max='') {
    
    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;
    
    if(get_magic_quotes_gpc()) $string = stripslashes($string);
    
    if(function_exists("mysql_real_escape_string")) {
        return mysql_real_escape_string($string);
    } else {
        return addslashes($string);
    }
}

function sanitizeForHTML($string, $min='', $max='')
{
    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;
    
    $pattern[0] = '/\&/';
    $pattern[1] = '/</';
    $pattern[2] = "/>/";
    $pattern[3] = '/\n/';
    $pattern[4] = '/"/';
    $pattern[5] = "/'/";
    $pattern[6] = "/%/";
    $pattern[7] = '/\( /';
    $pattern[8] = '/\)/';
    $pattern[9] = '/\+/';
    $pattern[10] = '/-/';
    $replacement[0] = '&amp;';
    $replacement[1] = '&lt;';
    $replacement[2] = '&gt;';
    $replacement[3] = '<br>';
    $replacement[4] = '&quot;';
    $replacement[5] = '&#39;';
    $replacement[6] = '&#37;';
    $replacement[7] = '&#40;';
    $replacement[8] = '&#41;';
    $replacement[9] = '&#43;';
    $replacement[10] = '&#45;';
    return preg_replace( $pattern, $replacement, $string);
}

function sanitizeForSYSTEM($string, $min='', $max='')
{
    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;
    
    $pattern = '/( ;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\( )/i'; 
    $string = preg_replace( $pattern, '', $string);
    return '"'.preg_replace( '/\$/', '\\\$', $string).'"';
}

?>
