<?php

/*
 * 
 * Given an image URL, this class will render it inside a single cell table
 *
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 7 January 2003
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
			return "<img src=\"".$this->sImgURL."\" width=\"". $this->iImgWidth."\" height=\"".$this->iImgHeight."\" align=\"center\" />";
		}
		return "<img src=\"".$this->sImgURL."\" width=\"100%\" height=\"100%\" align=\"center\" />";		
	}
	
	
}

?>