<?php
/**
 * $Id$
 *
 * Used to specify html code that is not catered for by any of the other patterns.
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
