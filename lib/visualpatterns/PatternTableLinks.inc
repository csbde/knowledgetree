<?php
/*
* 
*
* Given an array of URLs, this class will render them as an
* HTML table with with specified number of rows and columns
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 7 January 2003
* @package lib.visualpatterns
*/

define("TEXT", 1);
define("IMAGE", 2);

class PatternTableLinks {
	
	/** Array of link URLS to display in tables */
	var $aLinks;
	/** Text links display */
	var $aLinksText;
	/** Array of images to display as links */
	var $aLinkImages;
	/** Rows in table */
	var $iRows;
	/** Columns in table */
	var $iColumns;
	/** Link Type:	o 1 = Text
	*				o 2 = Image */	
	var $iLinkType;
	
	/**
	* Class constructor
	*
	* @param $aNewLink		Array of link URLS to display in tables
	* @param $iRows			Number of rows in table
	* @param $iColumns		Number of columns in table
	* @param $iLinkType		Link type (1 = Text or 2 = Button). Default is 1
	* @param $aLinkImages	Array of images URL placed over links
	*/	
	function PatternTableLinks($aNewLinks, $aNewLinksText, $iNewRows, $iNewColumns, $iNewLinkType = TEXT, $aNewLinkImages = null) {
		$this->aLinks = $aNewLinks;
		$this->aLinksText = $aNewLinksText;
		$this->aLinkImages = $aNewLinkImages;
		$this->iRows = $iNewRows;
		$this->iColumns = $iNewColumns;
		$this->iLinkType = $iNewLinkType;
	}	
    
	/**
	* Build the HTML string used to render the object
	*
	* @return String of HTML used to render object
	*/
	function & render() {
		global $default;
		
		$iArraySize = count($this->aLinks);
		$iArrayCount = 0;		 
		$sToRender = "<table border=0 width=\"100%\" cellspacing = \"0\">\n";		
		
		//build the table
		for ($i = 0; $i < $this->iRows; $i++) {			
			$sToRender .= "<tr>\n";
			$sLink = "";
			for ($j = 0; $j < $this->iColumns; $j++) {
				if ($iArrayCount < $iArraySize) {
					if (strlen($this->aLinks[$iArrayCount]) > 0) {
	                    $sLink = "<a href=\"" . $this->aLinks[$iArrayCount] . "\">";
	                    if ($this->iLinkType == TEXT) {
	                        $sLink .= $this->aLinksText[$iArrayCount];
	                    } else if ($this->iLinkType == IMAGE) {
	                        $oImage = new PatternImage($this->aLinkImages[$iArrayCount]);
	                        $sLink .= $oImage->render();
	                    }
	                    $sLink .= "</a>";
						
					}
					$iArrayCount++;
				}
				$sToRender .= "<td cellspacing=\"20\" width=\"100%\">" . (strlen($sLink) > 0 ? $sLink : "&nbsp;") . "</td>";				
			}
			$sToRender .= "</tr>\n";			
		}		
		$sToRender .= "</table>\n";				 
		return $sToRender;		
	}
}

?>