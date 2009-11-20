<form id="license_installtype_dependencies" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Installation Type</p>

	<div id="step_content" class="step">
		Select an installation type
		<br/><br/>
		<table>
			<tr>
				<td> <input type="radio" name="installtype" value="Clean Install" checked id="clean" /> </td>
				<td> <label for="clean"><b>Install KnowledgeTree (Default)</b> <br/>Choose this option if you are installing KnowledgeTree for the first time</label> </td>
			</tr>
			<tr>
				<td> <input type="radio" name="installtype" value="Upgrade Installation" id="migrate" /> </td>
				<td> <label for="migrate"><b>Upgrade KnowledgeTree Stack Install</b> <br/>Choose this option if you are upgrading from a previous version of the KnowledgeTree Stack</label></td>
			</tr>
			<?php if ($isCE) { ?>
			<tr>
				<td> <input type="radio" name="installtype" value="Upgrade Only" id="upgrade" /> </td>
				<td> <label for="upgrade"><b>Upgrade KnowledgeTree Source Only Install</b> <br/>Choose this option if you are upgrading from a previous Source Only installation of KnowledgeTree</label></td>
			</tr>
			<?php } ?>
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