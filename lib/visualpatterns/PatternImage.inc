<?php
/**
 * $Id$
 *
 * Given an image URL, this class will render it inside a single cell table.
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
 * @package lib.visualpatterns
 */
class PatternImage {
	
	/** Image URL */
	var $sImgURL;	
	/** Image width */
	var $iImgWidth;
	/** Image height */
	var $iImgHeigth;
	
	/**
	* Class constructor
	*/
	function PatternImage($sNewImgURL) {		
		$this->sImgURL = $sNewImgURL;		
	}
	
	/**
	* Set the width and height of the image
	
	* @param $iNewWidth		Image width
	* @param $iNewHeigth	Image heigth
	*/
	function setImgSize($iNewWidth, $iNewHeight) {
		$this->iImgWidth = $iNewWidth;
		$this->iImgHeight = $iNewHeight;		
	}
	
	/**
	* Renders the image
	*
	* @return String to be used in HTML
	*/
	function & render() {
		if (isset($this->sImgWidth) && isset($this->sImgWidth)) {
			return "<img src=\"".$this->sImgURL."\" width=\"". $this->iImgWidth."\" height=\"".$this->iImgHeight."\" align=\"center\" border=\"0\" />";
		}
		return "<img src=\"".$this->sImgURL."\" align=\"center\" border=\"0\" />";		
	}
	
	
}

?>