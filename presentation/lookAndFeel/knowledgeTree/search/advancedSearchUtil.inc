<?php
/**
 * $Id$
 *
 * Business logic used to perform advanced search.  Advanced search allows
 * users to search by meta data types.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package search
 */

/**
 * Interrogates the posted variables for the chosen metadata tags
 */
function getChosenMetaDataTags($aVariables) {
	$aKeys = array_keys($aVariables);
	$aTagIDs = array();
	for ($i = 0; $i < count($aKeys); $i++) {	
		$sRowStart = $aKeys[$i];		
		$pos = strcmp("adv_search_start", $sRowStart);
		if ($pos == 0) {
			$i++;
			$sRowStart = $aKeys[$i];
			while ((strcmp("adv_search_end", $sRowStart) != 0)  && ($i < count($aKeys))) {				
				$aTagIDs[count($aTagIDs)] = $aVariables[$aKeys[$i]];
				$i++;
				$sRowStart = $aKeys[$i];
			}
			
		}
	}
	if (count($aTagIDs) > 1) {
		return implode(",",$aTagIDs);
	}
	return $aTagIDs[0];
}

/*
* Generate a string that can be used in a SQL query
* from the list of documents the user is allowed to see
*/
function getSQLSearchString($sSearchString) {
	$aWords = explode(" ", $sSearchString);
	$sSQLSearchString;
	for ($i = 0; $i < count($aWords) - 1; $i++) {
		$sSQLSearchString .= "(DFL.value LIKE '%" . $aWords[$i] . "%') OR ";
	}
	$sSQLSearchString .= "(DFL.value LIKE '%" . $aWords[count($aWords) -1] . "%')";
	return $sSQLSearchString;
}
?>