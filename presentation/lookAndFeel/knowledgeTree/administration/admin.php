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

    $sCenter  = "<table width=\"600\">\n";
    $sCenter .= renderHeading("Administration");
    $sCenter .=	"<tr/><tr/><tr><td><b> Welcome to the Administration Section</b></td></tr>\n";
    $sCenter .=	"<tr><td>Please make a selection from the sidemenu.</td></tr>\n";
    $sCenter .=	"</table>";

	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($sCenter);
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
