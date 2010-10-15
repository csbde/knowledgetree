<?php
include_once('../../ktapi/ktapi.inc.php');

$KT = new KTAPI();
$KT->get(3);// Set it to Use Web Version 3

//Pick up the session
$session=KTAPI_UserSession::getCurrentBrowserSession($KT);
$KT->start_system_session($session->user->getUserName());

function getDocTypes(){
	$types=DocumentType::getList();
	$ret=array();
	foreach($types as $type){
		$ret[$type->aFieldArr['id']]=$type->aFieldArr;
	}
	return $ret;
}


?> 

    <div>
       <table class="metadataTable" border="0" cellspacing="0" cellpadding="0">
       	<tr><td class="ul_meta_selectDocType">Select Document Type<span class="ul_meta_docTypeOptions">
	        <select class="ul_doctype" onChange="kt.app.upload.getMetaItem(this).changeDocType(this.options[this.selectedIndex].value);">
	        	<?php
	        	  $docTypes=getDocTypes();
	        	  foreach($docTypes as $docTypeId => $docType):
	        	   if(!$docType['disabled']): 
	        	?>
	        	<option value="<?php echo $docTypeId; ?>" ><?php echo $docType['name']; ?></option>
	        	
	        	<?php endif; endforeach; ?>
	        </select>       		
       	</span></td></tr>
       	<tr><td class="ul_metadata"></td></tr>
       	<tr><td class="ul_meta_actionbar">
       		<input type="checkbox" id="ul_meta_actionbar_apply_to_all">
       		<label for="ul_meta_actionbar_apply_to_all">Apply to All</label>
       		<input type="button" value="OK" onclick="kt.app.upload.getMetaItem(this).applyMetadata();" />
       		<input type="button" value="Cancel" onclick="kt.app.upload.getMetaItem(this).clearAndCloseMetadataWindow();" />
       	</td></tr>
       </table>
    </div>