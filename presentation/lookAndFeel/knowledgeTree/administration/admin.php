<?php
require_once("../../../../config/dmsDefaults.php");
require_once("adminUI.inc");

/**
 * $Id$
 *
 * Displays the administration page.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration
 */

if(checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	
	if (isset($sectionName)) {   
	    $sCenter .= "<table width=\"600\">\n";
	    if ($sectionName == "userAdministration") {
	  		
	  		$sCenter .= renderHeading("User Administration");
	    }else if ($sectionName == "Administration"){
	        $sCenter .= "<table width=\"600\">\n";
	        $sCenter .= renderHeading("Administration");	        
	    }
	    $sCenter .=	"</table>\n";
	}
   	//$sCenter .= "<textarea cols=50 rows=50> $sCenter </textarea>";
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($sCenter);
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
