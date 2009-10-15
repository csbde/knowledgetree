<form id="registration_install_complete" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Installation Type</p>

	<div id="step_content" class="step">
		<br/>
		The wizard will require you choose an installation type.
		<br/><br/>
		<table>
			<tr>
				<td> <input type="radio" name="installtype" value="Clean Install" checked id="clean" /> </td>
				<td> <label for="clean"><b>Clean Install</b> - New Installation of KnowledgeTree</label> </td>
			</tr>
			<tr>
				<td> <input type="radio" name="installtype" value="Upgrade Installation" id="migrate" /> </td>
				<td> <label for="migrate"><b>Upgrade Installation</b> - Upgrade an existing Installation of KnowledgeTree</label> </td>
			</tr>
			<tr>
				<td> <input type="radio" name="installtype" value="Upgrade Only" id="upgrade" /> </td>
				<td> <label for="upgrade"><b>Source Only Upgrade</b> - Manually Update Source and Upgrade Database only</label> </td>
			</tr>
		</table>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next" />
</form>
<script type="text/javascript">
	function clear() {
		
	}
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>