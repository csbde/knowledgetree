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
class NavBar {
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
	var $sSection;

    /**  variables used for searching */
    var $sBrowseType;
    var $iFolderID;
    var $iDocumentID;
    var $sCategoryName;
    var $iDocTypeID;

    function NavBar($sTmpBrowseType, $iTmpFolderID, $iTmpDocumentID, $sTmpCategoryName, $iTmpDocTypeID) {
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
    * @param The section that needs to be highlighted
    */
    function getNWColourbar() {
        return $this->nwColourBar;
    }



    /**
    * sets a highlighted section varaibles
    *
    * @param The section that needs to be highlighted
    */
    function setHighlightedSection($section) {
        global $default;
		$this->sSection = $section;

        // check for highlighting..and highlight as required
        if($section == "General") {
            $this->home = "$default->graphicsUrl/home/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/home/def.gif";
        }

        if(($section == "Manage Documents") || ($section == "Manage Categories") || ($section == "Manage Document Types")) {
            $this->mdocs = "$default->graphicsUrl/mdocs/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/mdocs/def.gif";
        }

        if($section == "Subscriptions") {
            $this->subscriptions = "$default->graphicsUrl/subscriptions/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/subscriptions/def.gif";
        }
		
        if(($section == "Standard Search") || ($section == "Advanced Search")) {			
            $this->asearch = "$default->graphicsUrl/asearch/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/asearch/def.gif";
        }

	
        if(substr($section, -14) == "Administration") {
        		
            $this->admin = "$default->graphicsUrl/admin/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/admin/def.gif";
            
        }

        if($section == "Preferences") {
            $this->prefs = "$default->graphicsUrl/prefs/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/prefs/def.gif";
        }

        if($section == "Help") {
            $this->help = "$default->graphicsUrl/help/over.gif";
            $this->nwColourBar = "$default->graphicsUrl/help/def.gif";
        }


        if($section == "Logout") {
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
    function render() {
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
                               //generateControllerUrl($default->siteMap->getDefaultAction("Help")),
                               "help",
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
        $sTopMenu = "<table width=\"100%\" height=\"100%\" cellspacing = \"0\">\n<tr>\n";
        for ($i = 0; $i < count($aTopMenuLinks); $i++) {
            if (strcmp($aTopMenuLinks[$i], "help") == 0) {
                $sTopMenu .= "<td cellspacing = \"20\"><input type=\"image\" src=\"" . $aTopMenuImages[$i] . "\" onClick=\"return helpWindow();\" border=\"0\" /></a></td>\n";
            } else {
                $sTopMenu .= "<td cellspacing = \"20\">" . (strlen($aTopMenuLinks[$i]) > 0 ? "<a href=\"" . $aTopMenuLinks[$i] . "\">" : "") . "<img src=\"" . $aTopMenuImages[$i] . "\" border=\"0\" /></a></td>\n";
            }
        }
        $sTopMenu .= "</tr>\n</table>\n";
        $oColourTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 8,2,$row4MenuImages);

        // set up rows
        //row one image
        $row1 = "<img src = ". $heading. ">";
        //row 2 will be a search bar
        $row2 ="";
        //row 3 will be the heading subsections
        //$row3 =  $oPatternTableLinks->render();
        $row3 =  $sTopMenu;
        //array of coloured section images
        $row4 = $oColourTableLinks->render();

        $toRender = new PatternCustom;		

        //bar is the actaul html creation of the nav bar
        $bar= "
              <Table cellpadding = \"1\" cellspacing = \"1\" border=\"1\" width=\"100%\" height=\"100%\">\n " .
              "<tr height=\"20%\"><td background = " . $hStretched ." width =\"100%\">". $row1 ."</td></tr>\n " .
              "<tr  height=\"30%\"> <td VALIGN = \"absmiddle\" background = " . $hStretched ." width =\"100%\" NOWRAP >\n " .
              "<img align = \"absmiddle\" src = \"$default->graphicsUrl/arrow.gif\" ></img>\n " .
              "<img align = \"absmiddle\" src = \"$default->graphicsUrl/arrow.gif\" ></img>\n " .
			  "<INPUT type=\"hidden\" name=\"fForStandardSearch\" value=\"no\" />\n ";
			  //disable the search bar if we are browsing search results
			  if ((strcmp($this->sSection,"Standard Search") == 0) || (strcmp($this->sSection,"Advanced Search") == 0)) {
				  $bar .= "<INPUT type=\"hidden\" name=\"fSearchText\" width=\"30\" height = \"10\" align = \"absmiddle\" />\n ";
			  } else {
				  $bar .= "<INPUT type=\"text\" name=\"fStandardSearchString\" width=\"30\" height = \"10\" align = \"absmiddle\" />\n " .
						"<INPUT type=\"hidden\" name=\"fBrowseType\" value=\"" . $this->sBrowseType . "\"  />\n " .
						"<INPUT type=\"hidden\" name=\"fFolderID\"  value=\"" . $this->iFolderID . "\"  />\n " .
						"<INPUT type=\"hidden\" name=\"fDocumentID\" value=\"" . $this->iDocumentID . "\"  />\n " .
						"<INPUT type=\"hidden\" name=\"fCategoryName\" value=\"" . $this->sCategoryName . "\"  />\n " .
						"<INPUT type=\"hidden\" name=\"fDocTypeID\"  value=\"" . $this->iDocTypeID . "\"  />\n " .
						"<INPUT type=\"image\" onClick=\"return validateStandardSearch('" . $default->rootUrl . "/control.php')\" src=\"$default->graphicsUrl/search.gif\" align = \"absmiddle\" border=\"0\" />\n ";
			  }			  
              $bar .= "</td>\n " .
				  "</tr>\n" .
				  "<tr height=\"25%\" ><td width =\"10%\">". $row3 . "</td></tr>\n" .
				  "<tr height=\"25%\"><td width =\"100%\">". $row4 . "</td></tr>\n" .
				  "</table>\n";

        //set the bar as html to object
        $toRender->setHtml($bar);

        //render the object
        return $toRender->render() . $this->getSearchValidationJavaScript();

    }
	
	function getSearchValidationJavaScript() {
		$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
		$sToRender .= "<!--\n";		
		$sToRender .= "function validateStandardSearch(newAction) {\n";		
		$sToRender .= "\tif (!(validRequired(document.MainForm.fStandardSearchString, 'Search text'))) {\n";
		$sToRender .= "\t\treturn false;\n\t}\n";
		$sToRender .= "\telse {\n";
		$sToRender .= "\t\tsetActionAndSubmitSearch(newAction);\n";
		$sToRender .= "\t}\n";
		$sToRender .= "}\n";
		$sToRender .= "-->\n";
		$sToRender .= "</SCRIPT>\n\n";
		return $sToRender;
	
	}
}
        //render the object
?>