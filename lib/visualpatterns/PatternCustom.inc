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
	 * @param string		new HTML string
	 */
	function setHtml($sNewValue) {
		$this->sHtml = $sNewValue;		
	}
	
    /**
     * Appends to the html string
     *
     * @param string        the html to append
     */
    function addHtml($sMoreHtml) {
        $this->sHtml = $this->sHtml . $sMoreHtml;
    }
    
	/**
	 * Render the object
	 *
	 * @return string	    html string
	 */
	function & render() {
		return $this->sHtml;
	}
}
?>
