<form id="dbsettings" action="index.php?step_name=database" method="post">
	<p class="title">Migrate Database</p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step configures the connection to the database server and migrates the database. The details for an administrative <br/>
		user on the database server are required in order to be able to configure and migrate the database.
		</div>
		<div id="step_content_database" class="step">
			<table class="dbconf">
			<?php
				$input_size = '35';
				$align = 'left';
			?>
		<!--	TODO: Different Databases-->
				    <tr>
				        <td><label for='duname'>Enter Database Administrative username: </label></td>
				        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='<?php echo $input_size; ?>' style="float:left"/></td>
				        <td id="error" class="error"><?php if($errors['duname']) echo $errors['duname']; ?></td>
				    </tr>
				    <tr>
				        <td><label for='dpassword'>Enter the password for the Administrator: </label></td>
				        <td><input type='password' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='<?php echo $input_size; ?>' style="float:left"/></td>
				        <td id="error" class="error"><?php if($errors['dpassword']) echo $errors['dpassword']; ?></td>
				    </tr>
			</table>
		</div>
	</div>
    <input type="button" name="Previous" value="previous" class="button_previous"/>
	<input type="submit" name="Next" value="next" class="button_next"/>
</form>