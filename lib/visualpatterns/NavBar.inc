<?php

require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

/**
*  NavBar.inc
*
*  Create the top section of the navbar..
*
*
* @author Mukhtar Dharsey
* @date 22 January 2003
*/

global $default;

/**
* 
*
*  Contains all the functions to create the navbar...
*
*
* @author Mukhtar Dharsey
* @date 22 January 2003
*/
class NavBar 
{
	//variable used to pass highlighting
	var $home;
	var $mdocs;
	var $asearch;
	var $admin;
	var $prefs;
	var $help;
	var $logout;
	var $nwColourbar;
	
	function NavBar()
	{
		global $default;
		$this->mdocs = "$default->owl_graphics_url/mdocs/norm.gif";
		$this->home = "$default->owl_graphics_url/home/norm.gif";
		$this->asearch = "$default->owl_graphics_url/asearch/norm.gif";
		$this->admin = "$default->owl_graphics_url/admin/norm.gif";
		$this->prefs = "$default->owl_graphics_url/prefs/norm.gif";
		$this->help = "$default->owl_graphics_url/help/norm.gif";
		$this->logout = "$default->owl_graphics_url/logout/norm.gif";
		
	}
	
	
	/**
	* gets the url for the colour bar
	*
	* @param	The section that needs to be highlighted
	*/
	function getNWColourbar()
	{
		return $this->nwColourBar;
	}
	
	
	
	/**
	* sets a highlighted section varaibles
	*
	* @param	The section that needs to be highlighted
	*/
	function setHighlightedSection($section)
	{
		global $default;
		
		// check for highlighting..and highlight as required
		if($section == "General")
		{
			$this->home = "$default->owl_graphics_url/home/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/home/def.gif";
		}
		
		if($section == "Manage Documents")
		{
			$this->mdocs = "$default->owl_graphics_url/mdocs/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/mdocs/def.gif";
		}
		
		if($section == "Advanced Search")
		{
			$this->asearch = "$default->owl_graphics_url/asearch/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/asearch/def.gif";
		}
		
		
		if($section == "Administration")
		{
			$this->admin = "$default->owl_graphics_url/admin/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/admin/def.gif";
		}
				
		if($section == "Preferences")
		{
			$this->prefs = "$default->owl_graphics_url/prefs/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/prefs/def.gif";
		}
		
		if($section == "Help")
		{
			$this->help = "$default->owl_graphics_url/help/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/help/def.gif";
		}
		
		
		if($section == "Logout")
		{
			$this->logout = "$default->owl_graphics_url/logout/over.gif";
			$this->nwColourBar = "$default->owl_graphics_url/logout/def.gif";
		}
	}
	
	
	
	/**
	* 
	* this function creates the navbar for a specific page...
	* 
	* 
	*/		
	function render()
	{
		global $default;
		
				//set up headings for row 1 and 2
		$heading = "$default->owl_graphics_url/heading.gif";
		$hStretched = "$default->owl_graphics_url/hrepeat.gif";
		
		// set colour pics for row 4 of navbar
		$colourHome = "$default->owl_graphics_url/home/colour.gif";
		$colourmdocs = "$default->owl_graphics_url/mdocs/colour.gif";
		$colourasearch = "$default->owl_graphics_url/asearch/colour.gif";
		$colouradmin = "$default->owl_graphics_url/admin/colour.gif";
		$colourprefs = "$default->owl_graphics_url/prefs/colour.gif";
		$colourhelp = "$default->owl_graphics_url/help/colour.gif";
		$colourlogout = "$default->owl_graphics_url/logout/colour.gif";
		
		
      // get list of sections
        $aTopMenuLinks = array(generateControllerUrl("dashboard"),
        		       generateControllerUrl($default->siteMap->getDefaultAction("Manage Documents")), 
        		       generateControllerUrl($default->siteMap->getDefaultAction("Advanced Search")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Administration")), 
                               generateControllerUrl($default->siteMap->getDefaultAction("Preferences")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Help")), 
                               generateControllerUrl($default->siteMap->getDefaultAction("Logout")));


	// create array of images for navbar sections         	      
        $aTopMenuImages = array($this->home,
        			$this->mdocs,
        			$this->asearch,
        			$this->admin,
        			$this->prefs,
        			$this->help,
        			$this->logout);

	  
	// create array of menu colour images
        $row4MenuImages = array($colourHome,$colourmdocs,$colourasearch,$colouradmin,$colourprefs,$colourhelp,$colourlogout);
        
        //set up the links
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
		
		//bar is the actaul html creation of the nav bar
	$bar= "				
		<Table cellpadding = \"1\" cellspacing = \"1\" border=\"1\" width=\"100%\" height=\"100%\">
			
		<tr height=\"20%\"><td background = " . $hStretched ." width =\"100%\">". $row1 ."</td></tr>
			
		<tr  height=\"30%\">	<td VALIGN = \"absmiddle\" background = " . $hStretched ." width =\"100%\" NOWRAP >
					<img align = \"absmiddle\" src = \"$default->owl_graphics_url/arrow.gif\" ></img> 
					<INPUT type=\"Text\" name=\"searchField\" width=\"30\" height = \"10\" align = \"absmiddle\" >
					<INPUT type=\"image\" name=\"search\" value=\"Search\" src = \"$default->owl_graphics_url/search.gif\" align = \"absmiddle\" >
					</td>
				   
		</tr>
			
		<tr height=\"25%\" ><td width =\"10%\">". $row3 . "</td></tr>
			
		<tr height=\"25%\"><td width =\"100%\">". $row4 . "</td></tr>
			
		</table>
		
		";
		
		
	//set the bar as html to object
	$toRender->setHtml($bar);
	
	//render the object			
	return	$toRender->render();
		
	}




}


?>