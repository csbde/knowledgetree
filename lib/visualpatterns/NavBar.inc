<?php
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
/**
 * $Id$
 *
 * Contains all the functions to create the Navbar.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.visualpatterns
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
        $this->iDocTypeID = $iTmpDocTypeID;

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
		if($section == "Discussion Threads" )  {
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
     * this function creates the navbar for a specific page...
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
        $aColourMenuImages = array($colourHome,$colourmdocs,$coloursubscriptions,$colourasearch,$colouradmin,$colourprefs,$colourhelp,$colourlogout);

        //set up the links
        for ($i = 0; $i < count($aTopMenuLinks); $i++) {
            if (strcmp($aTopMenuLinks[$i], "help") == 0) {
                $sTopMenu .= "<td width=\"10\"><a href=\"#\" onClick=\"javascript:helpWindow();\"><image src=\"" . $aTopMenuImages[$i] . "\" border=\"0\" /></a></td>\n";
                $sColourMenu .= "<td width=\"10\"><a href=\"#\" onClick=\"javascript:helpWindow();\"><image src=\"" . $aColourMenuImages[$i] . "\" border=\"0\" /></a></td>\n";
            } else {
                $sTopMenu .= "<td width=\"10\">" . (strlen($aTopMenuLinks[$i]) > 0 ? "<a href=\"" . $aTopMenuLinks[$i] . "\">" : "") . "<img src=\"" . $aTopMenuImages[$i] . "\" border=\"0\" /></a></td>\n";
                $sColourMenu .= "<td width=\"10\">" . (strlen($aTopMenuLinks[$i]) > 0 ? "<a href=\"" . $aTopMenuLinks[$i] . "\">" : "") . "<img src=\"" . $aColourMenuImages[$i] . "\" border=\"0\"/></a></td>\n";
            }
        }

        $toRender = new PatternCustom;

        // build the nav bar
        $bar  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
        $bar .= "\t<tr height=\"30\">\n";
        $bar .= "\t\t<td align=\"right\" valign=\"top\" width=\"100%\"><img src=\"$heading\"/><img src=\"$default->graphicsUrl/logo.jpg\" width=\"30\" height=\"36\"/></td>\n";        
        $bar .= "\t</tr>\n";
        $bar .= "\t<tr height=\"30\">\n";
        $bar .= "\t\t<td colspan=\"2\" bgcolor=\"#1A4383\" width=\"100%\">";
        $bar .= "<img align=\"absmiddle\" src=\"$default->graphicsUrl/arrow.gif\" ></img>\n";

        $bar .= "<INPUT type=\"hidden\" name=\"fForStandardSearch\" value=\"no\" />\n ";
        //disable the search bar if we are browsing search results
        if ((strcmp($this->sSection,"Standard Search") == 0) || (strcmp($this->sSection,"Advanced Search") == 0)) {
            $bar .= "<INPUT type=\"hidden\" name=\"fSearchText\"/>\n ";
        } else {
            $bar .= "<INPUT type=\"text\" name=\"fSearchText\" width=\"30\" height = \"10\" />\n";
            (isset($this->iFolderID) ? $bar .= "<INPUT type=\"hidden\" name=\"fFolderID\" value=\"" . $this->iFolderID . "\"  />\n " : $bar .= "");
            (isset($this->iDocumentID) ? $bar .= "<INPUT type=\"hidden\" name=\"fDocumentID\" value=\"" . $this->iDocumentID . "\"  />\n " : $bar .= "");
            (isset($this->sCategoryName) ? $bar .= "<INPUT type=\"hidden\" name=\"fCategoryName\" value=\"" . $this->sCategoryName . "\"  />\n " : $bar .= "");
            (isset($this->iDocTypeID) ? $bar .= "<INPUT type=\"hidden\" name=\"fDocTypeID\" value=\"" . $this->iDocTypeID . "\"  />\n " : $bar .= "");
            //this next line works in IE, and Netscape 4.79 up, but NOT Netscape 4.79
            //$bar .= "<INPUT type=\"image\" onClick=\"return validateStandardSearch('" . $default->rootUrl . "/control.php')\" src=\"$default->graphicsUrl/search.gif\" align = \"absmiddle\" border=\"0\" />\n";
            //replaced with this line            
            $bar .= "<a href=\"#\" onClick=\"return validateStandardSearch('" . $default->rootUrl . "/control.php')\"><image align=\"absmiddle\" src=\"$default->graphicsUrl/search.gif\" border=\"0\" /></a>";
            
        }
        $bar .= "\t\t</td>\n";
        $bar .= "\t</tr>\n";
        $bar .= "<tr height=30><td ><table cellspacing=0 cellpadding=0 border=0><tr>$sTopMenu</tr>\n";
        $bar .= "<tr>$sColourMenu</tr></table></td></tr>\n";
        $bar .= "</table>\n";

        //set the bar as html to object
        $toRender->setHtml($bar);

        //render the object
        return $toRender->render() . $this->getSearchValidationJavaScript();

    }

    function getSearchValidationJavaScript() {
        $sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
        $sToRender .= "<!--\n";
        $sToRender .= "function validateStandardSearch(newAction) {\n";        
        $sToRender .= "\tif (!(validRequired(document.MainForm.fSearchText, 'Search text'))) {\n";
        $sToRender .= "\t\treturn false;\n\t}\n";
        $sToRender .= "\telse {\n";
        $sToRender .= "\t\tsetActionAndSubmitSearch(newAction);\n";        
        $sToRender .= "\t}\n";
        $sToRender .= "}\n";
        $sToRender .= "//-->\n";
        $sToRender .= "</SCRIPT>\n\n";
        return $sToRender;
    }
}
?>
