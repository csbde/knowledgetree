<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/administration/news/newsUI.inc");

/**
 * $Id$
 *  
 * This page previews a news item
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.news
 */

if (checkSession()) {	
		
    // instantiate my content pattern
    $oContent = new PatternCustom();
    
    if (isset($fNewsID)) {
    	// we have an id, so we're can proceed
    	$oContent->setHtml(renderPreviewNewsPage(DashboardNews::get($fNewsID)));
    } else {
    	// no news id, nothing to preview
    	$oContent->setHtml(renderListNewsPage());
    }        

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->render();	
} 
?>