<form id="<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Migrate Database</p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step configures the connection to the database server and migrates the database. 
<!--		The details for an administrative <br/>-->
<!--		user on the database server are required in order to be able to configure and migrate the database.-->
		</div>
		<div id="step_content_<?php echo $step_name; ?>" class="step">
			<br/>
			<span class="error">!!NB!! You are advised to backup your database before proceeding. !!NB!!</span>
			<br/><br/>
			<?php if(isset($errors)) { ?>
				<?php
					foreach ($errors as $error) {
					?>
						<span class="error">
						<?php
							if(isset($error['error'])) {
								echo $error['error'];
								?>
									<br/><br/>
								<?php
							}
						?>
						</span>
						<?php
							if(isset($error['msg'])) {
								echo $error['msg'];
								?>
									<br/><br/>
								<?php
							}
						?>
							
						<?php
							if(isset($error['cmd'])) {
								echo $error['cmd'];
							}
					}
				}
				?>
			<br/>
		</div>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
<!--	<input type="submit" name="Backup" value="Backup" class="button_next"/>-->
</form>
<script type="text/javascript">
	$("#duname").focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>