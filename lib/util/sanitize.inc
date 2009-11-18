<?php
/**
 * $Id$
 *
 * This page is meant to provide functions to prevent XSS cracks.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
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

    $string = trim($string);
	if(get_magic_quotes_gpc()) $string = stripslashes($string);

    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;

    if(function_exists("mysql_real_escape_string")) {
        return mysql_real_escape_string($string);
    } else {
        return addslashes($string);
    }
}

function sanitizeForSQLtoHTML($string, $min='', $max='')
{
	$string = str_replace(array("\r","\n"), array('',''), $string);
    return $string;
}

function sanitizeForHTML($string, $min='', $max='')
{
    $string = trim($string);
	if(get_magic_quotes_gpc()) $string = stripslashes($string);

    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;

    if(function_exists("htmlspecialchars")) {
    	return htmlspecialchars($string);
    } else {
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
}

function sanitizeForSYSTEM($string, $min='', $max='')
{
    $string = trim($string);
	if(get_magic_quotes_gpc()) $string = stripslashes($string);

    $len = strlen($string);
    if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max))) return false;

    $pattern = '/( ;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\( )/i';
    $string = preg_replace( $pattern, '', $string);
    return '"'.preg_replace( '/\$/', '\\\$', $string).'"';
}

?>
