<form id="step_name_<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Apply Templates</p>
	<p class="description">You can select a base folder layout to apply to your root folder of KnowledgeTree.</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<div class="field ">
      	<p class="descriptiveText">Choose a template, if you would like to generate predefined folders.</p>
		<p class="errorMessage"></p>
			<select name="data[templateId]" id="selectedTemplate">
				<option selected="selected" value="0">- No template -</option>
				<?php 
					foreach ($aFolderTemplates as $oFolderTemplate) {
						echo "<option onclick=\"javascript:{fl.showFolderTemplateTree('{$oFolderTemplate->getId()}')}\" value=\"{$oFolderTemplate->getId()}\">".$oFolderTemplate->getName()."</option>";
					}
				?>
	    	</select>
		</div>
<!--		<br>-->
<!--		<br>-->
		Folder Template Layout : 
		<br>
		<br>
		<div class="tree_navigation">
			<table cellspacing="0" class="tree_table">
			<?php 
				foreach ($aFolderTemplates as $oFolderTemplate) {
					?>
					<tr class="templates" style="display:none;" id="template_<?php echo $oFolderTemplate->getId(); ?>">
						<td>
							<div class="tree_icon tree_folder closed">&nbsp;</div> 
						</td>
						<td>
							<label>
								<?php 
									echo $oFolderTemplate->getName(); 
								?>
							</label>
						</td>
					</tr>
					<tr>
						<td id="templates_<?php echo $oFolderTemplate->getId(); ?>" class="templates"></td>
					</tr>
					<?php
				}
			?>
			</table>
		</div>
	</div>
	<input id="skip" type="submit" name="Skip" value="Skip" class="button_skip"/>
	<input id="next" type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("form").bind("submit", function() {
		fl.sendFirstLoginForm();
		return false;
	});
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>