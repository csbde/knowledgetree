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
       	<tr>
       		<td class="ul_metadata">
       			<span class="ul_meta_fieldset">
       				<span class="ul_meta_title">Fieldset Title</span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Tit asdfle</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document ads Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Doc dsaument Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       			</span>
       			<span class="ul_meta_fieldset">
       				<span class="ul_meta_title">Fieldset Title</span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Documed fnt Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Docum dfent Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Document Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       				<span class="ul_meta_row"><span class="ul_meta_label">Docume dasnt Title</span><span class="ul_meta_field"><input type="text" class="ul_meta_fullField"></input></span></span>
       			</span>

			</td>
		</tr>
       	<tr><td class="ul_meta_actionbar">Apply / Save Metadata</td></tr>
       </table>
    </div>