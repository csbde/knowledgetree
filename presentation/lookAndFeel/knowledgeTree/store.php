<?php
/**
 * $Id$
 *
 * Page used by all editable patterns to actually perform the db insert/updates
 *
 * Expected form variables
 *	o fReturnURL
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING. 
 * Copyright (c) 2003 Jam Warehouse - http://www.jamwarehouse.com
  
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("store.inc");


if (count($_POST) > 0) {
	$aKeys = array_keys($_POST);
	$aQueries = constructQuery($aKeys);
	
	//execute the queries
	for ($i=0; $i<count($aQueries); $i++) {
		$default->log->info("query=" . $aQueries[$i]);
		$sql = $default->db;
		$sql->query($aQueries[$i]);
	}
	redirect(urldecode($fReturnURL));
}
?>