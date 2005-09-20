<?php
/**
 * $Id$
 *
 * Business logic to modify type specific meta data for a document.
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
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fDocumentID', 'fFirstEdit', 'fForStore');

if (!checkSession()) {	
    die();
}

require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMetaData.inc");					
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("documentUI.inc");
require_once("modifySpecificMetaDataUI.inc");

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

$oDocument = Document::get($fDocumentID);
if (!Permission::userHasDocumentWritePermission($oDocument)) {
    die();
}
    
if (empty($fForStore)) {
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getPage($fDocumentID, $oDocument->getDocumentTypeID(), $fFirstEdit));
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
    $main->setHasRequiredFields(true);		
    $main->render();
    exit(0);
}

$matches = array();
$aFields = array();
foreach ($_REQUEST as $k => $v) {
    if (preg_match('/^emd(\d+)$/', $k, $matches)) {
        $aFields[] = array(DocumentField::get($matches[1]), $v);
    }
}

DBUtil::startTransaction();
$res = KTDocumentUtil::saveMetadata($oDocument, $aFields);
if (PEAR::isError($res)) {
    DBUtil::rollback();
    $_SESSION['KTErrorMessages'][] = $res->getMessage();
    controllerRedirect('modifyDocumentTypeMetaData', "fDocumentID=$fDocumentID");
    exit(0);
}
DBUtil::commit();

if (isset($fFirstEdit)) {
    controllerRedirect('viewDocument', "fDocumentID=$fDocumentID");
} else {
    controllerRedirect('viewDocument', "fDocumentID=$fDocumentID&fShowSection=typeSpecificMetaData");
}

?>
