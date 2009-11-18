<?php
/**
 * $Id$
 *    
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once('../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

$sWhere = "status_id=" . DELETED;
$aDocuments =& Document::getList($sWhere);

$pageNum = $_REQUEST['page'];

$items = count($aDocuments);
if(fmod($items, 10) > 0){
	$pages = floor($items/10)+1;
}else{
	$pages = ($items/10);
}

for($i=1; $i<=$pages; $i++){
	$aPages[] = $i;
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



$output = "<table class=\"kt_collection\"> 
				<thead>
			    <tr>
	      			<th style=\"width:2em\"><input type=\"checkbox\" onclick=\"toggleSelectFor(this, 'selected_docs')\" title=\"toggle all\"/></th>
	      			<th>"._kt('Document Name').'</th>
			        <th>'._kt('Last Modification').'</th>
	      			<th>'._kt('Deletion Comment').'</th>
    			</tr>
  				</thead> 
				<tbody>';
				

for($i = $listStart; $i <= $listEnd; $i++){
	$output .= "
				<tr>
	      <td><input type=\"checkbox\" name=\"selected_docs[]\" value=\"".$aDocuments[$i]->getId()."\"/></td>
	      <td>".$aDocuments[$i]->getName()."</td>
	      <td>".$aDocuments[$i]->getLastModifiedDate()."</td>
	      <td>".$aDocuments[$i]->getLastDeletionComment()."</td>
	    </tr>";
	        
}


$output .= '<tfoot>
  	<tr>
  		<td colspan="4">
  			<span style="float: left">'.$items.' '._kt('items, 10 per page').'</span>
  		</td>
  	</tr>
  	<tr>
  		<td colspan="4">
  			<div align="center">';
  			
  				foreach($aPages as $page){
					$output .= '<a href="#" onclick="buildList(this.innerHTML)">'.$page.'</a>&nbsp;';
  				}
  				
$output .= '</div>
		</td>
  	</tr>
  </tfoot>
  </table>
  </tbody></table>';
  
echo $output;
?>
