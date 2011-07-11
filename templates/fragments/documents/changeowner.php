<?php
include_once('../../ktapi/ktapi.inc.php');
include_once('../../lib/widgets/fieldWidgets.php');

$KT = new KTAPI(3);

//Pick up the session
$session = KTAPI_UserSession::getCurrentBrowserSession($KT);
$KT->start_system_session($session->user->getUserName());


// Get Document and Owner Info
$document = $KT->get_document_by_id($data['documentId']);


$ownerId = $document->document->getOwnerID();

$owner = KTAPI_User::getById($ownerId);



// Set up and instantiate user search widget.
$members = KTJSONLookupWidget::formatMemberUsers(array($owner));

$label['header'] = 'Change Owner';
//$label['text'] = 'Search for a new owner.';

//$jsonWidget = KTJSONLookupWidget::getUserSearchWidget($label, 'group', 'users', $members);
$jsonWidget = KTJSONLookupWidget::getUserSearchWidget($label, 'group', 'users', '', array('tokenLimit'=>1));


?>
<style type="text/css">
	.usersearchwidgetwrap label {display: none !important;}
	.usersearchwidgetwrap ul.token-input-list-facebook {width: 250px !important}
	.token-input-dropdown-facebook {width: 250px !important}
</style>
<table class="uploadTable" style="height:100px;">
	<tbody>
		<!--
		<tr id="error" style='display:none;'>
			<td> <p class="errorMessage"></p> </td>
		</tr>
		-->
		<tr>
			<td>
				<b>Current Owner</b> : <?php echo $owner->getName(); ?>
			</td>
		</tr>
		<tr>
			<td><b>New Owner</b> : Type to Search
				<div class="usersearchwidgetwrap">
					<?php echo $jsonWidget->render(); ?>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;
			</td>
		</tr>
	</tbody>
</table>
<div class="action-footer">
	<div class="form_actions">
		<input type="hidden" name="changeowner_document" id="changeowner_document" value="<?php echo $data['documentId']; ?>" />
    	<a href="#" onclick="Ext.getCmp('changeowner').close(); return false;" class="ul_actions_cancel_link">Cancel</a>
    	<input name="changeowner_submit" type="button" onclick="kt.app.document_actions.doChangeOwner('<?php echo $data['documentId']; ?>', '<?php echo $ownerId; ?>', jQuery('#user-search-widget').val());" value="Change Owner">
    </div>
</div>	
