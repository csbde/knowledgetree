<table class="uploadTable" style="height:100px;">
	<tbody>
		<tr id="error" style='display:none;'>
			<td colspan=2> <p class="errorMessage"></p> </td>
		</tr>
		<tr id="user"  style='display:none;'>
			<td> <label for="username">Username : </label> </td>
	    	<td> <input name="username"> </td>
		</tr>
		<tr id="pass"  style='display:none;'>
			<td> <label for="password">Password : </label> </td>
	    	<td> <input name="password"> </td>
		</tr>
		<tr id=reason>
			<td>
		        <label for="data[reason]">Reason</label>
			</td>
			<td>
				<textarea cols="40" rows="5" name="reason"></textarea>
		    </td>
		</tr>
		<tr>
	    	<td align="right" valign="top" class="ul_actions">
				<input type="button" onclick="kt.app.document_actions.submitReason();" value="Submit" class="ul_actions_btns" id="ul_actions_upload_btn">
	    	</td>
			<td align="left" valign="top" class="ul_actions">
				<a href="#" onclick="vActions.closeDisplay('reason'); return false;" class="ul_actions_cancel_link">Cancel</a>
			</td>
		</tr>
	</tbody>
</table>