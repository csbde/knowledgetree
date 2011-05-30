<?php
include_once('../../ktapi/ktapi.inc.php');

global $default;
$username = _kt('Username');
if($default->useEmailLogin) {
	$username = _kt('Email Address');
}
?>
<table class="uploadTable" style="height:100px;">
	<tbody>
		<tr id="error" style='display:none;'>
			<td> <p class="errorMessage"></p> </td>
		</tr>
		<tr>
			<td>
				<p id="esign-info" style='display: none'>
					<b>This action requires authentication</b><br />
                    Please provide your user credentials as confirmation of this action.
				</p>
				
				<p id="reason-info">
					Please provide a reason for performing this action.
				</p>
			</td>
		</tr>
		<tr id="user" style='display:none;'>
			<td> 
				<label for="username"><?php echo $username; ?></label>  <br />
				<input name="sign-username" size="30"> 
			</td>
		</tr>
		<tr id="pass" style='display:none;'>
			<td> 
				<label for="password">Password</label> <br />
				<input type="password" name="sign-password" size="30"> 
			</td>
		</tr>
		<tr id="reason">
			<td>
				<label for="reason" name="reason-label" id="reason-label">Note</label> <br />
				<textarea cols="50" rows="5" name="reason" id="reason-field"></textarea>
		    </td>
		</tr>
		<tr>
	    	<td align="right" valign="top" class="ul_actions">
	    		<input type="hidden" value="reason" id="type">
				<input type="hidden" value="" id="reason-doc-id">
				<input type="hidden" value="" id="reason-action">

				<a href="#" onclick="vActions.closeDisplay('reason'); return false;" class="ul_actions_cancel_link">Cancel</a>
				<span class="reason-spinner none" style="visibility:hidden;">&nbsp;</span>
				<input name="reason_submit" type="button" onclick="kt.api.esignatures.saveESignatures();" value="Save" id="reason-btn">
	    	</td>
		</tr>
	</tbody>
</table>