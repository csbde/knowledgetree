<?php
define("TEXT", 1);
define("IMAGE", 2);
/**
 * $Id$
 *
 * Given an array of URLs, this class will render them as an
 * HTML table with with specified number of rows and columns
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

        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/standard_links");
        $aTemplateData = array(
            "array_size" => count($this->aLinks),
            "links" => array_combine($this->aLinksText, $this->aLinks),
        );
        $sToRender = $oTemplate->render($aTemplateData);

		return $sToRender;		
	}
}

?>
