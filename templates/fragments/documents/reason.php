<input type="hidden" value="reason" id="type">
<input type="hidden" value="" id="reasondocid">
<input type="hidden" value="" id="reasonaction">
<table class="uploadTable" style="height:100px;">
	<tbody>
		<tr id="error" style='display:none;'>
			<td colspan=2> <p class="errorMessage"></p> </td>
		</tr>
		<tr>
			<td>
		        <span for="reason" name="reason_label"></span>
			</td>
		</tr>
		<tr id="user"  style='display:none;'>
			<td> <label for="username">Email Address</label> </td>
	    	<td align="right"> <input name="sign_username"> </td>
		</tr>
		<tr id="pass"  style='display:none;'>
			<td> <label for="password">Password</label> </td>
	    	<td align="right"> <input name="sign_password"> </td>
		</tr>
		<tr id=reason>
			<td>
				<br/>
				<span for="reason" name="reason_field"></span>
				<textarea cols="50" rows="5" name="reason"></textarea>
		    </td>
		</tr>
		<tr>
	    	<td align="right" valign="top" class="ul_actions">
				<a href="#" onclick="vActions.closeDisplay('reason'); return false;" class="ul_actions_cancel_link">Cancel</a>
				<input name="reason_submit" type="button" onclick="kt.app.document_actions.submit_reason();" value="Submit" class="ul_actions_btns" id="ul_actions_upload_btn">
	    	</td>
		</tr>
	</tbody>
</table>