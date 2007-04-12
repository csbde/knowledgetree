<?php
/*
 * Created on 11 Apr 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
