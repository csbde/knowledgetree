<form id="dbsettings" action="index.php?step_name=<?php echo $step_name; ?>" method="post" onsubmit="w.onSubmitValidate(<?php if ($silent) echo 'true'; else echo 'false'; ?>);return false;">
<p class="title">Confirming Database Configurations</p>
<!-- Check For immediate Errors -->
<span class="error"> <?php if($errors['con']) { echo $errors['con']; } ?> </span>
<!-- Hidden Fields -->
<input type="hidden" id='ddrop' name="ddrop" <?php echo ($ddrop) ? 'CHECKED' : ''; ?>/>
<input type="hidden" id="sendAll" name="" value="" />
<!-- STEP 1 of the database configuration - server settings -->
<div id="database" class="step1" style="display:block;">
	<div class="description">
	This step configures the connection to the database server and migrates the database. The details for an administrative <br/>
	user on the database server are required in order to be able to configure and migrate the migrateation database.
	</div>
	<div id="step_content_database" class="step">
	
	<table class="dbconf">
	<?php
		$input_size = '45';
		$align = 'left';
	?>
<!--	TODO: Different Databases-->
<?php if($state != 'edit') { ?>
	    <tr><td>Your current database type is: </td>
		<?php if($dtypes) {
		        foreach($dtypes as $k=>$v) {
		    ?><td>
		    	&nbsp;
		    	<?php echo ucwords($v)." (Currently the only supported database.)";?>
		    	<input type="hidden" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>/>
		    	</td>
		<?php }
		}
		?>
		</tr>
<?php } ?>
		    <tr>
		        <td><label for='dname'>Enter a name for the database: </label></td>
		        <td><input type='text' value="<?php echo $dname?>" id='dname' name='dname' size='<?php echo $input_size; ?>' style="float:left"/></td>
		        <td id="error" class="error"><?php if($errors['dname']) echo $errors['dname']; ?></td>
		    </tr>
		    <tr>
		        <td><label for='duname'>Enter Database Administrative username: </label></td>
		        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='<?php echo $input_size; ?>' style="float:left"/></td>
		        <td id="error" class="error"><?php if($errors['duname']) echo $errors['duname']; ?></td>
		    </tr>
		    <tr>
		        <td><label for='dpassword'>Enter the password for the user: </label></td>
		        <td><input type='password' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='<?php echo $input_size; ?>' style="float:left"/></td>
		        <td id="error" class="error"><?php if($errors['dpassword']) echo $errors['dpassword']; ?></td>
		    </tr>
	</table>
<br/><br/>
	<div id="option3" class="onclick" onclick="javascript:{w.toggleClass('adv_options', 'option3');}">&nbsp;&nbsp;Advanced Options</div>
	<div id="database" class="adv_options" style="display:none;">
	    <div class="description">
	        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings.
	    </div>
	    <table>
	    	<tr>
	    		<td width="10px"> <label for='dhost'>Host: </label> </td>
	    		<td width="205px"> <div id="tooltips" title="The address of the server where the database is located, if different to the current server">&nbsp;</div> </td>
	    		<td width="10px"> <input type="text" value="<?php echo $dhost?>" id="dhost" name="dhost" size='<?php echo $input_size; ?>' class="textinput"/> </td>
	    	</tr>
	    	<tr>
	    		<td> <label for='dport'>Port: </label> </td>
	    		<td> <div id="tooltips" title="The port on which your database server is listening, if it is a non-standard port please enter the number here">&nbsp;</div> </td>
	    		<td> <input type="text" value="<?php echo $dport?>" id="dport" name="dport" size='10' class="textinput" style="float:left"/> </td>
	    	</tr>
	    	<tr>
	    		<td> <label for='dbbinary'>Socket: </label> </td>
	    		<td> <div id="tooltips" title="The path to the database binary. If it is not on your system path then please enter it here">&nbsp;</div> </td>
	    		<td> <input type="text" value="<?php echo $dbbinary?>" id="dbbinary" name="dbbinary" size='<?php echo $input_size; ?>' class="textinput"/> </td>
	    	</tr>
	    </table>
	</div>
	</div>
	    <input type="submit" name="Previous" value="Previous" class="button_previous" />
	    <?php if ($silent) { ?>
	    <input type="submit" name="Next" value="Next" class="button_next"/>
	    <?php } else { ?>
	    <input type="button" name="Next" value="Next" onclick="javascript:{w.showStep(1, 'n');}" class="button_next"/>
	    <?php } ?>
</div>

<!-- STEP 2 of the database configuration - Admin user password settings -->
<div id="database" class="step2" style="display:none;">
<div class="description">
An administrative user is required for creating tables within the database.
</div>
	<table class="dbconf">
	    <tr style="<?php if ($silent) echo 'display:none' ;?>">
	        <td><label for='dmsname'>Enter the name of the Database Administrative User: </label></td>
	        <td><input type='text' value="<?php echo $dmsname; ?>" id='dmsname' name='dmsname' size='40' /></td>
	        <td id="error_1_2" class="error" style="display:none">Please Enter A Username</td>
		</tr>
	    <tr>
	        <td><label for='dmspassword'>Enter a password for the Administrative User: </label></td>
	        <td><input type='password' value="<?php echo $dmspassword; ?>" id='dmspassword' name='dmspassword' size='40' /></td>
	        <td id="error_2_2" class="error" style="display:none">Please Enter A Password</td>
		</tr>
	    <tr>
	        <td><label for='dmspassword2'>Please confirm the password: </label></td>
	        <td><input type='password' value="<?php echo $dmspassword; ?>" id='dmspassword2' name='dmspassword2' size='40' /></td>
	        <td id="error_3_2" class="error" style="display:none">Please Confirm Password</td>
	        <td id="error_4_2" class="error" style="display:none">Passwords Do Not Match</td>
		</tr>
	</table>

	    <input type="button" name="Previous" value="Previous" onclick="javascript:{w.showStep(2, 'p');}" class="button_previous"/>
	    <input type="button" name="Next" value="Next" onclick="javascript:{w.showStep(2, 'n');}" class="button_next"/>
</div>

<!-- STEP 3 of the database configuration - default user password settings -->
<div id="database" class="step3" style="display:none;">
<div class="description">
An second user is required for normal database interaction, the reading and writing of data.
</div>

	<table class="dbconf">
		<tr style="<?php if ($silent) echo 'display:none' ;?>">
	        <td><label for='dmsusername'>Enter a name for the User: </label></td>
	        <td><input type='text' value="<?php echo $dmsusername; ?>" id='dmsusername' name='dmsusername' size='40' /></td>
	        <td id="error_1_3" class="error" style="display:none">Please Enter A Username</td>
		</tr>
	    <tr>
	        <td><label for='dmsuserpassword'>Enter a password: </label></td>
	        <td><input type='password' value="<?php echo $dmsuserpassword?>" id='dmsuserpassword' name='dmsuserpassword' size='40' /></td>
	        <td id="error_2_3" class="error" style="display:none">Please Enter A Password</td>
		</tr>
	    <tr>
	        <td><label for='dmsuserpassword2'>Please confirm the password: </label></td>
	        <td><input type='password' value="<?php echo $dmsuserpassword?>" id='dmsuserpassword2' name='dmsuserpassword2' size='40' /></td>
	        <td id="error_3_3" class="error" style="display:none">Please Confirm Password</td>
	        <td id="error_4_3" class="error" style="display:none">Passwords Do Not Match</td>
		</tr>
	</table>
	    <input type="button" name="Previous" value="previous" onclick="javascript:{w.showStep(3, 'p');}" class="button_previous"/>
		<input type="submit" name="Next" value="next" class="button_next"/>
</div>
</form>