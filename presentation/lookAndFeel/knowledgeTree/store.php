<?php
/**
 * $Id$
 *
 * Page used by all editable patterns to actually perform the db insert/updates
 *
 * Expected form variables
 *	o fReturnURL
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
		$sql = $default->db;
		$sql->query($aQueries[$i]);
	}
	redirect(urldecode($fReturnURL));
}
?>