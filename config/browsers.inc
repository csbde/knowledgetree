<?php
/**
 * $Id$
 *
 * Sets up browser specific positioning co-ordinates.
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
 */
 
// instantiate phpsniffer
$default->phpSniff = new phpSniff($_SERVER["HTTP_USER_AGENT"]);
$default->browser = $default->phpSniff->property("browser");
$default->version = $default->phpSniff->property("version");
$default->bNN4 = ($default->browser == "ns") && ($default->phpSniff->property("maj_ver") == "4");

if ($default->browser == "ie") {
	// internet explorer
    $default->upArrowLeft = $default->downArrowLeft = "745";
    $default->upArrowTop = "122";
    $default->downArrowTop = "544";
    
    $default->textBoxLeft = "134";
    $default->textBoxTop = "118";
    $default->textBoxWidth = "608";
    $default->textBoxHeight = "430";
    
	$default->pageHeight = "535";
	$default->contactAdminPosition = "500";    
} elseif ( ($default->browser == "mz")) {
	// mozilla
    $default->upArrowLeft = $default->downArrowLeft = "742";
    $default->upArrowTop = "107";
    $default->downArrowTop = "530";
    
    $default->textBoxLeft = "135";
    $default->textBoxTop = "105";
    $default->textBoxWidth = "603";
    $default->textBoxHeight = "432";
    
    $default->pageHeight = "540";
    $default->contactAdminPosition = "490";
} elseif ( ($default->browser == "ns") && ($default->version == "6.2.3") ) {
	// netscape 6.2.3
    $default->upArrowLeft = $default->downArrowLeft = "745";
    $default->upArrowTop = "110";
    $default->downArrowTop = "560";
    
    $default->textBoxLeft = "132";
    $default->textBoxTop = "104";
    $default->textBoxWidth = "610";
    $default->textBoxHeight = "460";
    
    $default->pageHeight = "460";
    $default->contactAdminPosition = "510";    
} elseif ( ($default->browser == "ns") && ($default->version == "7.0") ) {
	// netscape 7.0
    $default->upArrowLeft = $default->downArrowLeft = "745";
    $default->upArrowTop = "107";
    $default->downArrowTop = "538";
    
    $default->textBoxLeft = "132";
    $default->textBoxTop = "103";
    $default->textBoxWidth = "608";
    $default->textBoxHeight = "438";
    
    $default->pageHeight = "540";
    $default->contactAdminPosition = "490";
} elseif ($default->browser == "fb") {
	// mozilla firebird
    $default->upArrowLeft = $default->downArrowLeft = "745";
    $default->upArrowTop = "107";
    $default->downArrowTop = "538";
    
    $default->textBoxLeft = "132";
    $default->textBoxTop = "103";
    $default->textBoxWidth = "608";
    $default->textBoxHeight = "448";
    
    $default->pageHeight = "550";
	$default->contactAdminPosition = "480";
} elseif ($default->bNN4) {
	$default->pageHeight = "500";
} else {
	// default
    $default->upArrowLeft = $default->downArrowLeft = "745";
    $default->upArrowTop = "107";
    $default->downArrowTop = "518";
    
    $default->textBoxLeft = "132";
    $default->textBoxTop = "103";
    $default->textBoxWidth = "608";
    $default->textBoxHeight = "427";	
    
    $default->pageHeight = $default->textBoxTop + $default->textBoxHeight + 10;
    $default->contactAdminPosition = $default->pageHeight;
}
?>