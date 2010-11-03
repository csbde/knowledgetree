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
    <div id="uploaddialog">
    <form method="post" action="dump.php">
        <table id="[id]" class="uploadTable" border="0" cellspacing="0" cellpadding="0">
        	<!--  tr><td class="ul_heading">Heading</td></tr -->
        	<tr>
        		<td valign="middle" style="height:29px; vertical-align: middle;">
        			<select class="ul_doctype" onChange="kt.app.upload.changeGlobalDoctype(this.options[this.selectedIndex].value);">
        				<?php
        				  $docTypes=getDocTypes();
        				  foreach($docTypes as $docTypeId => $docType):
        				   if(!$docType['disabled']): 
        				?>
        				<option value="<?php echo $docTypeId; ?>" ><?php echo $docType['name']; ?></option>
        				
        				<?php endif; endforeach; ?>
        			</select>
        			<span id="upload_add_file" class="ul_button">Choose Files</span> <span style="display: inline-block; margin: auto 5px">Choose files to upload, then click Upload.</span>
        		</td>
        	</tr>
        	<tr><td class="ul_list"><!-- span class="files"></span --></td></tr>
        	<tr><td class="ul_folder_display">Files will be uploaded to: <span id="uploadpathstring">/tohir - php</span> <a href="javascript:;" id="changepathlink"  onClick="alert('Not Yet Implemented');" >Change</a></td></tr>
        	<tr><td class="ul_actions"><input type="button" value="Cancel" onClick="alert('Not Yet Implemented');" /><input type="button" value="Add Documents" onClick="alert('Not Yet Implemented');" /></td></tr>
        </table>
    </form>
    </div>