<?php
/**
 * $Id$
 *    
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 */

require_once('../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
 
$aDocuments =& Document::getList("status_id=" . DELETED);

$pageNum = $_REQUEST['page'];

$items = count($aDocuments);
if(fmod($items, 10) > 0){
	$pages = floor($items/10)+1;
}else{
	$pages = ($items/10);
}
if($pageNum == 1){
	$listStart = 0;
	$listEnd = 9;
}elseif($pageNum == $pages){
	$listStart = (10*($pageNum-1));
	$listEnd = count($aDocuments)-1;
}else{
	$listStart = (10*($pageNum-1));
	$listEnd = $listStart+9;
}
for($i = $listStart; $i <= $listEnd; $i++){
	$output .=  "<tr>
	      <td><input type='checkbox' name='selected_docs[]' value='".$aDocuments[$i]->getId()."'/></td>
	      <td>".$aDocuments[$i]->getName()."</td>
	      <td>".$aDocuments[$i]->getLastModifiedDate()."</td>
	      <td>".$aDocuments[$i]->getLastDeletionComment()."</td>
	    </tr>";
}
echo $output;
?>
