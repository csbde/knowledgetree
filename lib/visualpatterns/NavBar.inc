<?php

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

/**
*  NavBar.inc
*
*  Create the top section of the navbar..
 
* @author Mukhtar Dharsey
* @date 22 January 2003
*/

global $default;

/**
* Contains all the functions to create the navbar...
*
* @author Mukhtar Dharsey
* @date 22 January 2003
*/
class NavBar 
{
	//variable used to pass highlighting
	var $home;
	var $mdocs;
	var $subscriptions;
	var $asearch;
	var $admin;
	var $prefs;
	var $help;
	var $logout;
	var $nwColourbar;
	
	/**  variables used for searching */
	var $sBrowseType;
	var $iFolderID;
	var $iDocumentID;
	var $sCategoryName;
	var $iDocTypeID;
	
	function NavBar($sTmpBrowseType, $iTmpFolderID, $iTmpDocumentID, $sTmpCategoryName, $iTmpDocTypeID) 
	{	
		global $default;
		
		$this->sBrowseType = $iTmpBrowseType;
		$this->iFolderID = $iTmpFolderID;
		$this->iDocumentID = $iTmpDocumentID;
		$this->sCategoryName = $sTmpCategoryName;
		$this->iDoctypeID = $iTmpDocTypeID;
		
		
		$this->mdocs = "$default->graphicsUrl/mdocs/norm.gif";
		$this->home = "$default->graphicsUrl/home/norm.gif";
		$this->subscriptions = "$default->graphicsUrl/subscriptions/norm.gif";
		$this->asearch = "$default->graphicsUrl/asearch/norm.gif";
		$this->admin = "$default->graphicsUrl/admin/norm.gif";
		$this->prefs = "$default->graphicsUrl/prefs/norm.gif";
		$this->help = "$default->graphicsUrl/help/norm.gif";
		$this->logout = "$default->graphicsUrl/logout/norm.gif";
		
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
			$this->home = "$default->graphicsUrl/home/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/home/def.gif";
		}
		
		if($section == "Manage Documents")
		{
			$this->mdocs = "$default->graphicsUrl/mdocs/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/mdocs/def.gif";
		}
		
		if($section == "Subscriptions")
		{
			$this->subscriptions = "$default->graphicsUrl/subscriptions/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/subscriptions/def.gif";
		}
		
		if($section == "Advanced Search")
		{
			$this->asearch = "$default->graphicsUrl/asearch/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/asearch/def.gif";
		}
		
		
		if(substr($section, -14) == "Administration")
		{
			$this->admin = "$default->graphicsUrl/admin/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/admin/def.gif";
		}
				
		if($section == "Preferences")
		{
			$this->prefs = "$default->graphicsUrl/prefs/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/prefs/def.gif";
		}
		
		if($section == "Help")
		{
			$this->help = "$default->graphicsUrl/help/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/help/def.gif";
		}
		
		
		if($section == "Logout")
		{
			$this->logout = "$default->graphicsUrl/logout/over.gif";
			$this->nwColourBar = "$default->graphicsUrl/logout/def.gif";
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
		$heading = "$default->graphicsUrl/heading.gif";
		$hStretched = "$default->graphicsUrl/hrepeat.gif";
		
		// set colour pics for row 4 of navbar
		$colourHome = "$default->graphicsUrl/home/colour.gif";
		$colourmdocs = "$default->graphicsUrl/mdocs/colour.gif";
		$coloursubscriptions = "$default->graphicsUrl/subscriptions/colour.gif";
		$colourasearch = "$default->graphicsUrl/asearch/colour.gif";
		$colouradmin = "$default->graphicsUrl/admin/colour.gif";
		$colourprefs = "$default->graphicsUrl/prefs/colour.gif";
		$colourhelp = "$default->graphicsUrl/help/colour.gif";
		$colourlogout = "$default->graphicsUrl/logout/colour.gif";
		
		
      // get list of sections

        $aTopMenuLinks = array(generateControllerUrl("dashboard"),
                               generateControllerUrl($default->siteMap->getDefaultAction("Manage Documents")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Subscriptions")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Advanced Search")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Administration")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Preferences")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Help")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Logout")));


        // create array of images for navbar sections
        $aTopMenuImages = array($this->home,
                                $this->mdocs,
                                $this->subscriptions,
                                $this->asearch,
                                $this->admin,
                                $this->prefs,
                                $this->help,
                                $this->logout);


        // create array of menu colour images
        $row4MenuImages = array($colourHome,$colourmdocs,$coloursubscriptions,$colourasearch,$colouradmin,$colourprefs,$colourhelp,$colourlogout);

        //set up the links
        $oPatternTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 8,2,$aTopMenuImages);
        $oColourTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 8,2,$row4MenuImages);

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
              <Table cellpadding = \"1\" cellspacing = \"1\" border=\"1\" width=\"100%\" height=\"100%\">\n " .
              "<tr height=\"20%\"><td background = " . $hStretched ." width =\"100%\">". $row1 ."</td></tr>\n " .
              "<tr  height=\"30%\"> <td VALIGN = \"absmiddle\" background = " . $hStretched ." width =\"100%\" NOWRAP >\n " .
              "<img align = \"absmiddle\" src = \"$default->graphicsUrl/arrow.gif\" ></img>\n " .
              "<INPUT type=\"Text\" name=\"fSearchText\" width=\"30\" height = \"10\" align = \"absmiddle\" />\n " .
			  "<INPUT type=\"hidden\" name=\"fBrowseType\" value=\"" . $this->sBrowseType . "\"  />\n " .
			  "<INPUT type=\"hidden\" name=\"fFolderID\"  value=\"" . $this->iFolderID . "\"  />\n " .
			  "<INPUT type=\"hidden\" name=\"fDocumentID\" value=\"" . $this->iDocumentID . "\"  />\n " .
			  "<INPUT type=\"hidden\" name=\"fCategoryName\" value=\"" . $this->sCategoryName . "\"  />\n " .
			  "<INPUT type=\"hidden\" name=\"fDocTypeID\"  value=\"" . $this->iDocTypeID . "\"  />\n " .
              "<INPUT type=\"button\" onClick=\"setActionAndSubmit('$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/searchDocumentBL.php')\" value=\"Search\" src=\"$default->graphicsUrl/search.gif\" align = \"absmiddle\" border=\"0\" />\n " .
              "</td>\n " .
              "</tr>\n" .
              "<tr height=\"25%\" ><td width =\"10%\">". $row3 . "</td></tr>\n" .
              "<tr height=\"25%\"><td width =\"100%\">". $row4 . "</td></tr>\n" .
              "</table>\n";

        //set the bar as html to object
        $toRender->setHtml($bar);

        //render the object
        return $toRender->render();
    }
}
?>