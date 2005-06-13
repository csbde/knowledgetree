<?php
/**
 * $Id$
 *
 * List document link types.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("../../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");    

require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");

require_once("listLinkTypesUI.inc");

if (checkSession()) {	
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getPage());
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction($_SERVER['PHP_SELF']);	
    $main->render();	
}
?>
