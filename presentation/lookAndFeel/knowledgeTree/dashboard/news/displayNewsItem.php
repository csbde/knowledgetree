<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->uiDirectory/dashboardUI.inc");

/**
 * $Id$
 *  
 * Displays a news item.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.news
 */

if (checkSession()) {
	if (isset($fNewsID)) {
		$oNews = DashboardNews::get($fNewsID);
		if ($oNews) {
			echo renderNewsItemPage($oNews);
		} else {
			// do something intelligent like closing the popup automatically
			// or more prosaically, printing an error message
		}
	}
}
?>