<?php

/**
* Class CustomHTML
*
* Used to specify html code that is not catered for by any of the other patterns
*/

class CustomHTML {
	
	/** Custom HTML string */
	$sHtml;
	
	/** Set the HTML string 
	*
	* @param $sNewValue		new HTML string
	*/
	function setHtml($sNewValue) {
		$this->sHtml = $newValue;		
	}
	
	/**
	* Render the object
	*
	* @return	html string
	*/
	function & render() {
		return $this->sHtml;
	}

?>
