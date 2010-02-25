<form id="<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Apply A Folder Template to KnowledgeTree</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("#duname").focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>