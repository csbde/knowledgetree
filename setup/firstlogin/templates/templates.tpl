<form id="step_name_<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Apply Templates</p>
	<p class="description">You can select a base folder layout to apply to your root folder of KnowledgeTree.</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<div class="field ">
      	<p class="descriptiveText">Choose a template, if you would like to generate predefined folders.</p>
		<p class="errorMessage"></p>
			<select name="data[templateId]" id="selectedTemplate" onchange="javascript:fl.showFolderTemplateTree();">
				<option selected="selected" value="0">- No template -</option>
				<?php 
					foreach ($aFolderTemplates as $oFolderTemplate) {
						echo "<option value=\"{$oFolderTemplate->getId()}\">".$oFolderTemplate->getName()."</option>";
?>
						<?php
					}
				?>
	    	</select>
		</div>
		Folder Template Layout : 
		<br>
		<br>
	<div class="tree_navigation">
		<table class="tree_table main" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td>
						<div id="tree_edit"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div id="templatesNode">
							<table class="tree_table aContainer" cellspacing="0" cellpadding="0">
								<tbody>
									<tr>
										<td>
										<?php 
											foreach ($aFolderTemplates as $oFolderTemplate) {
												?>
												<table class="templates" cellspacing="0" cellpadding="0" id="template_<?php echo $oFolderTemplate->getId(); ?>" style="display:none;">
													<tr>
														<td><div id="tconnector_template_<?php echo $oFolderTemplate->getId(); ?>" class="tree_icon connector-open-first open-first">&nbsp;</div></td>
													<td>
														<div class="tree_icon tree_folder closed">&nbsp;</div> 
													</td>
													<td>
														<label> <?php echo $oFolderTemplate->getName(); ?> </label>
													</td>
													</tr>
												</table>
												<table class="tree_table" cellspacing="0" cellpadding="0">
													<tr>
														<td id="templates_<?php echo $oFolderTemplate->getId(); ?>" class="template_nodes"></td>
													</tr>
												</table>
											<?php
											}
										?>
										<td>
									</tr>
								</tbody>
							</table>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	</div>
	<input id="skip" type="submit" name="Skip" value="Skip" class="button_skip"/>
	<input id="next" type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	var fl = new firstlogin('<?php echo WIZARD_ROOTURL; ?>', '<?php echo $ft_dir; ?>');
	jQuery("form").bind("submit", function() {
		fl.sendFirstLoginForm();
		return false;
	});
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>