<?php

/** 
 * 
 * $Id$
 *
 * Used to specify html code that is not catered for by any of the other patterns
 * @package lib.visualpatterns
 */
class PatternCustom {
	
	/** Custom HTML string */
	var $sHtml;
	
	/** 
     * Set the HTML string 
	 *
	 * @param $sNewValue		new HTML string
	 */
	function setHtml($sNewValue) {
		$this->sHtml = $sNewValue;		
	}
	
	/**
	 * Render the object
	 *
	 * @return	html string
	 */
	function & render() {
		return $this->sHtml;
	}
}
?>
