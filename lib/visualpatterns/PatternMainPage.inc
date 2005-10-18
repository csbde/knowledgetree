<?php
/**
 * $Id$
 *
 * Main page of the document management system.  Consists of a 2 x 2 HTML table.
 * Custom defined payloads can be placed in each of the table cells, giving a reasonable
 * amount of layout/design flexibility
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
class PatternMainPage {
	
	/** property holder for $oNorthWestPayload */
	var $oNorthWestPayload;
    /** bg colour **/
    var $sNorthWestPayloadBackGroundColour;
    
	/** property holder for $oWestPayload */
	var $oWestPayload;
    /** bg colour **/
    var $sWestPayloadBackGroundColour;
    
	/** property holder for $oEastPayload */
	var $oCentralPayload;
    /** bg colour **/
    var $sCentralPayloadBackGroundColour;
    
	/** property holder for $oNorthPayload */
	var $oNorthPayload;
    /** bg colour **/
    var $sNorthPayloadBackGroundColour;
    
	/** form action*/
	var $sFormAction;
	/** form enctype */
	var $sFormEncType;
	/** form submit method */
	var $sSubmitMethod = "POST";
	/** whether the form has fields that require javascript checking */
	var $bHasRequiredFields = false;
	/** default page error message */
	var $sErrorMessage = array();
	
	var $sHelpContext = "browse";
	/** javascript to execute on body load **/	
	var $onLoadJavaScript;
	/** more javascript */
	var $additionalJavaScript;
	/** whether or not to use dhtml scrolling **/
	var $bScroll;
	var $aBreadcrumbs = array();

    var $bFormDisabled = false;
	
	
	function PatternMainPage() {
        global $default;
		$this->sFormAction = "$default->rootUrl/control.php";
		$this->bScroll = $default->contentPaneScrolling;
	}
		
	function setNorthWestPayload($oNewVal) {		
		$this->oNorthWestPayload = & $oNewVal;		
	}
	
    function setNorthWestPayloadBackGroundColour($sColour) {
        $this->sNorthWestPayloadBackGroundColour = $sColour;
    }
    
	function setNorthPayload($oNewVal) {		
		$this->oNorthPayload = & $oNewVal;		
	}
	
    function setNorthPayloadBackGroundColour($sColour) {
        $this->sNorthPayloadBackGroundColour = $sColour;
    }
    
	function setCentralPayload($oNewVal) {
		$this->oCentralPayload = & $oNewVal;		
	}
	
    function setCentralPayloadBackGroundColour($sColour) {
        $this->sCentralPayloadBackGroundColour = $sColour;
    }
    
	function setWestPayload($oNewVal) {
		$this->oWestPayload = & $oNewVal;
	}
    
    function setWestPayloadBackGroundColour($sColour) {
        $this->sWestPayloadBackGroundColour = $sColour;
    }
	
	function getFormAction() {
		return $this->sFormAction;
	}
	
	function setFormAction($sNewVal) {
		$this->sFormAction = & $sNewVal;
	}	
	
	function setErrorMessage($sNewValue) {
		$this->sErrorMessage[] = $sNewValue;
	}
	
	function setFormEncType($sNewValue) {
		$this->sFormEncType = $sNewValue;
	}
	
	function setHasRequiredFields($bNewValue) {
		$this->bHasRequiredFields = $bNewValue;
	}
	
	function setHelpContext($sNewValue) {
		$this->sHelpContext = $sNewValue;
	}
	
	function setOnLoadJavaScript($sJavaScript) {
		$this->onLoadJavaScript = $sJavaScript;
	}
	function getOnLoadJavaScript() {
		return $this->onLoadJavaScript;
	}	

	function setDHTMLScrolling($sNewValue) {
		$this->bScroll = $sNewValue;
	}
	
	function setSubmitMethod($sNewValue) {
		$this->sSubmitMethod = $sNewValue;
	}

    function setBreadcrumbs($aNewValue) {
		$this->aBreadcrumbs = $aNewValue;
    }
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
        require_once(KT_LIB_DIR . "/templating/templating.inc.php");

        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/standard_page");

        $aTemplateData = array(
            "sessiontimeout" => $default->sessionTimeout+3,
            "javascript" => PatternMainPage::getJavaScript(),
            "treeicon" => "$default->graphicsUrl/tree.ico",
            "jsfiles" => array(
                "$default->uiUrl/js/datePicker.js",
                "$default->uiUrl/js/misc.js",
                "$default->uiUrl/js/divSwitch.js",
                "$default->uiUrl/js/OptionTransfer.js",
            ),
            "stylesheet" => "$default->uiUrl/stylesheet.php",
            "onloadjavascript" => $this->onLoadJavaScript,
            "formdisabled" => $this->bFormDisabled,
            "formaction" => $this->sFormAction,
            "formenctype" => $this->sFormEncType,
            "formsubmitmethod" => $this->sSubmitMethod,
            "hasrequiredfields" => $this->bHasRequiredFields,
            "pageheight" => $default->pageHeight,
            "nwbgcolour" => $this->sNorthWestPayloadBackGroundColour,
            "nwpayload" => $this->oNorthWestPayload,
            "nbgcolour" => $this->sNorthPayloadBackGroundColour,
            "npayload" => $this->oNorthPayload,
            "wbgcolour" => $this->sWestPayloadBackGroundColour,
            "wpayload" => $this->oWestPayload,
            "cbgcolour" => $this->sCentralPayloadBackGroundColour,
            "cpayload" => $this->oCentralPayload,
            "errormessages" => $this->sErrorMessage,
            "kterrormessages" => $_SESSION['KTErrorMessage'],
            "breadcrumbs" => $this->aBreadcrumbs,
        );

        $aTopMenuWords = array(
            _("Dashboard"),
            _("Browse Collections"),
            _("Advanced Search"),
            _("Admin"),
            _("Preferences"),
            _("Logout"),
        );

        $aTopMenuLinks = array(generateControllerUrl("dashboard"),
                               generateControllerUrl($default->siteMap->getDefaultAction("Manage Documents")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Advanced Search")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Administration")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Preferences")),
                               generateControllerUrl($default->siteMap->getDefaultAction("Logout")));

        $aTemplateData["navbar"] = array_combine($aTopMenuWords, $aTopMenuLinks);

        $_SESSION['KTErrorMessage'] = array();

        $sToRender = $oTemplate->render($aTemplateData);
		echo $sToRender;
	}
	
	/**
	 * Returns links to javascript that has 3 functions:
	 *	o validateString - validates a string
	 *	o validateNumber - validates a number
	 *	o setActionAndSubmit - sets the MainForm action to newAction and submits
     * And includes the dhtml text scrolling routine
	 */
	function getJavaScript() {
        global $default;
        $sUrl = $default->uiUrl;
		$sJavaScript .= $this->getHelpJavaScript();
		$sJavaScript .= $this->getAdditionalJavaScript();
		return $sJavaScript;       
	}
	
	function getHelpJavaScript() {
		global $default;
		return "\n\n<SCRIPT LANGUAGE=\"javascript\">\n " .
		"<!--\n" .		
		"function helpWindow() {\n" .			
			"window.open ('" . generateControllerUrl("help", "fAction=" . urlencode($default->siteMap->getActionFromPage((substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"]))))) . "&fSection=" . urlencode($default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"]))))) . "', 'newwindow', config='height=400,width=600,left=400,top=300, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no, directories=no, status=no');\n" .
			"return false;\n" .
		"}\n" .
		"//-->\n" .
		"</SCRIPT>\n\n";
	}
	
	function setAdditionalJavaScript($sJavaScript) {
		$this->additionalJavaScript = $sJavaScript;
	} 
	function getAdditionalJavaScript() {
		return $this->additionalJavaScript;
	}	
}
?>
