<?php

require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

/**
* Class NavBar.inc
*
*  Contains all the functions to create the navbar...as well as possibly handling the 
*  westpayload section.
*
*
* @author Mukhtar Dharsey
* @date 20 January 2003
*/

global $default;

class NavBar 
{
	//variable used to pass highlighting
	var $highlightedSection;
	var $home;
	var $mdocs;
	var $asearch;
	var $admin;
	var $prefs;
	var $help;
	var $logout;
	
	function setHighlightedSection($currentSection)
	{
		return $this->$highlightedSection = $currentSection;
	}
	
		
	// this function creates the navbar for a specific page...
	// Todo take parameter to set page highlighting
	function render()
	{
		global $default;
		
		if($this->$highlightedSection == "General")
		{
			$home = "$default->owl_graphics_url/home/over.gif";
		}
		else
		{
			$home = "$default->owl_graphics_url/home/norm.gif";
		}
		
		if($this->$highlightedSection == "Manage Documents")
		{
			$mdocs = "$default->owl_graphics_url/mdocs/over.gif";
		}
		else
		{
			$mdocs = "$default->owl_graphics_url/mdocs/norm.gif";
		}
		
		if($this->$highlightedSection == "Advanced Search")
		{
			$asearch = "$default->owl_graphics_url/asearch/over.gif";
		}
		else
		{
			$asearch = "$default->owl_graphics_url/asearch/norm.gif";
		}
		
		if($this->$highlightedSection == "Administration")
		{
			$admin = "$default->owl_graphics_url/admin/over.gif";
		}
		else
		{
			$admin = "$default->owl_graphics_url/admin/norm.gif";
		}
		
		if($this->$highlightedSection == "Preferences")
		{
			$prefs = "$default->owl_graphics_url/prefs/over.gif";
		}
		else
		{
			$prefs = "$default->owl_graphics_url/prefs/norm.gif";
		}
		
		if($this->$highlightedSection == "Help")
		{
			$help = "$default->owl_graphics_url/help/over.gif";
		}
		else
		{
			$help = "$default->owl_graphics_url/help/norm.gif";
		}
		
		if($this->$highlightedSection == "Logout")
		{
			$logout = "$default->owl_graphics_url/logout/over.gif";
		}
		else
		{
			$logout = "$default->owl_graphics_url/logout/norm.gif";
		}
		
		
		//$mdocs = "$default->owl_graphics_url/mdocs/norm.gif";
		//$asearch = "$default->owl_graphics_url/asearch/norm.gif";
		//$admin = "$default->owl_graphics_url/admin/norm.gif";
		//$prefs = "$default->owl_graphics_url/prefs/norm.gif";
		//$help = "$default->owl_graphics_url/help/norm.gif";
		//$logout = "$default->owl_graphics_url/logout/norm.gif";
		
		$heading = "$default->owl_graphics_url/heading.gif";
		$hStretched = "$default->owl_graphics_url/hrepeat.gif";
		
		$colourHome = "$default->owl_graphics_url/home/colour.gif";
		$colourmdocs = "$default->owl_graphics_url/mdocs/colour.gif";
		$colourasearch = "$default->owl_graphics_url/asearch/colour.gif";
		$colouradmin = "$default->owl_graphics_url/admin/colour.gif";
		$colourprefs = "$default->owl_graphics_url/prefs/colour.gif";
		$colourhelp = "$default->owl_graphics_url/help/colour.gif";
		$colourlogout = "$default->owl_graphics_url/logout/colour.gif";
		
		
		
		
		// build the top menu of links
      
        // get list of sections
        $aTopMenuLinks = array(generateControllerUrl("dashboard"),
        		       generateControllerUrl($default->siteMap->getDefaultAction("Manage Documents")), 
        		       generateControllerUrl($default->siteMap->getDefaultAction("Advanced Search")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Administration")), 
                               generateControllerUrl($default->siteMap->getDefaultAction("Preferences")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Help")), 
                               generateControllerUrl($default->siteMap->getDefaultAction("Logout")));
         	      
        $aTopMenuImages = array($home,$mdocs,$asearch,$admin,$prefs,$help,$logout);
	        
        $row4MenuImages = array($colourHome,$colourmdocs,$colourasearch,$colouradmin,$colourprefs,$colourhelp,$colourlogout);
        
        $oPatternTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 7,2,$aTopMenuImages);
	$oColourTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 7,2,$row4MenuImages);
	
	// set up rows
	//row one image
	$row1 = "<img src = ". $heading. ">";
	//row 2 will be a search bar
	$row2 ="";
	//row 3 will be the heading subsections
	$row3 =  $oPatternTableLinks->render();
	//array of coloured section images 
	$row4 = $oColourTableLinks->render();

		$toRender = new PatternCustom;
		
		//bar width based on main page sizes...80% of width(600)..30% of height(800)
		$bar= "
			<html> 
				
			<Table border=\"0\" width=\"100%\" height=\"100%\">
			
			<tr><td background = " . $hStretched ." width =\"100%\" height=\"20%\"></td></tr>
			
			<tr><td background = " . $hStretched ." width =\"100%\" height=\"20%\">
			
			<tr><td height= \"25%\">". $row3 . "</td></tr>
			
			<tr><td height=\"25%\">". $row4 . "</td></tr>
			
			</table>
			
			</html>
			";
		
		$toRender->setHtml($bar);
				
		return	$toRender->render();
		
	}




}


?>