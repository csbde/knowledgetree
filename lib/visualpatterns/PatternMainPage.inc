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
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
		$sToRender  = 	"<html>\n";
		$sToRender .= 	"<head>\n";
		$sToRender .=   "<title>The KnowledgeTree</title>";
       	$sToRender .= 	"<meta http-equiv=\"refresh\" content=\"" . ($default->sessionTimeout+3) . ($bNN4 ? ";" : "") . "\">\n" .
                            "<link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">\n" .
                            "<link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">\n".                            
							PatternMainPage::getJavaScript() . "\n" .							
							"</head>\n" .
							"<body marginleft=\"0\" marginheight=\"0\" " . (isset($this->onLoadJavaScript) ? "onload=\"$this->onLoadJavaScript\"" : "") . ">\n";							
        // hack to circumvent the crapness of ns 4.79
        if (!$default->bNN4 && $this->bScroll) {
            $sToRender .=       "<div id=\"divUp\">\n" .
                                "\t<a href=\"#\" onmouseover=\"scroll(-7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->graphicsUrl . "/down.gif\" width=\"18\" height=\"9\" alt=\"up\" border=\"0\"></a>\n" .
                                "</div>\n" .
                                "<div id=\"divDown\">\n" .
                                "\t<a href=\"#\" onmouseover=\"scroll(7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->graphicsUrl . "/up.gif\" width=\"18\" height=\"9\" alt=\"down\" border=\"0\"></a>\n" .
                                "</div>\n";
        }

		$sToRender .= 		    "<form name=\"MainForm\" " . (isset($this->sFormEncType) ? "enctype=\"$this->sFormEncType\" " : " ") . " action=\"".$this->sFormAction."\" method=\"$this->sSubmitMethod\" " . (($this->bHasRequiredFields) ? "onsubmit=\"return validateForm(this)\" " : " ") . ">\n" .		
								"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"700\" height=\"$default->pageHeight\">\n " .
									"<tr>\n".
										"<td height=\"0\" valign=\"top\" " . (isset($this->sNorthWestPayloadBackGroundColour) ? " bgcolor=\"" . $this->sNorthWestPayloadBackGroundColour . "\"" : "") . ">\n";
										if (isset($this->oNorthWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oNorthWestPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorthwest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td valign=\"top\" align=\"left\" " . (isset($this->sNorthPayloadBackGroundColour) ? " bgcolor=\"" . $this->sNorthPayloadBackGroundColour . "\"" : "") . ">\n";										
										if (isset($this->oNorthPayload)) {
											$sToRender = $sToRender . "\n" . $this->oNorthPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorth payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
									</tr>\n
									<tr>\n										
										<td height=\"100%\" valign=\"top\"" . (isset($this->sWestPayloadBackGroundColour) ? " bgcolor=\"" . $this->sWestPayloadBackGroundColour . "\"" : "") . ">";
										if (isset($this->oWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oWestPayload->render() . "\n";											
										} else {
											$sToRender = $sToRender . "\nWest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td valign=\"top\"" . (isset($this->sCentralPayloadBackGroundColour) ? " bgcolor=\"" . $this->sCentralPayloadBackGroundColour . "\"" : "") . ">\n";
                                        
										if (isset($this->oCentralPayload)) {
                                            // hack to circumvent the crapness of ns 4.79
                                            if (!$default->bNN4 && $this->bScroll) {
                                                $sToRender .= "\t<div id=\"divScrollTextCont\">\n
                                                               \t\t<div id=\"divText\">\n";												
                                            }                                          
											foreach ($this->sErrorMessage as $sErrorMessage) {
												$sToRender .= "<table border=\"0\"><tr><td><p class=\"errorText\">$sErrorMessage</p></td></tr></table>\n ";
											}
                                            if ($_SESSION['errorMessage']) {
                                                $sErrorMessage = $_SESSION['errorMessage'];
												$sToRender .= "<table border=\"0\"><tr><td><p class=\"errorText\">$sErrorMessage</p></td></tr></table>\n ";
                                                unset($_SESSION['errorMessage']);
                                            }
											$sToRender = $sToRender . "\n" . $this->oCentralPayload->render() . "\n";
										} else {											
											$sToRender = $sToRender . "\nCentral payload not set\n";
										}
                                        // hack to circumvent the crapness of ns 4.79
                                        if (!$default->bNN4 && $this->bScroll) {
                                            $sToRender = $sToRender . "</div></div>\n";
                                        }
                                        $sToRender = $sToRender . "</td>\n" .
									"</tr>\n" .								
								"</table>\n" .
								"</form>\n" .
							"</body>\n" .
						"</html>\n";
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
        // hack to circumvent the crapness of ns 4.79
 
        if (!$default->bNN4 && $this->bScroll) {
            $sJavaScript = "<script type=\"text/javascript\" src=\"$sUrl/js/scroll.js\"></script>\n";
        }
        $sJavaScript .= "<script type=\"text/javascript\" src=\"$sUrl/js/datePicker.js\"></script>\n";            
		$sJavaScript .= "<script type=\"text/javascript\" src=\"$sUrl/js/misc.js\"></script>\n";
		$sJavaScript .= "<script type=\"text/javascript\" src=\"$sUrl/js/divSwitch.js\"></script>\n";            	    
		$sJavaScript .= "<script type=\"text/javascript\" src=\"$sUrl/js/OptionTransfer.js\"></script>\n";
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
