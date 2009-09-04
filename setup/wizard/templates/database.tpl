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
	This step configures the connection to the database server and installs the database. The details for an administrative <br/>
	user on the database server are required in order to be able to configure and install the installation database.
	</div>
	<div id="step_content">
	<table class="dbconf">
<!--	TODO: Different Databases-->
	    <tr><td>Your current database type is: </td>
		<?php if($dtypes) {
		        foreach($dtypes as $k=>$v) {
		    ?><td>
		    	<?php echo ucwords($v);?>
		    	<input type="hidden" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>/>&nbsp;&nbsp;<?php //echo ucwords($v); ?>
		    	</td>
		<?php }
		}
		?>
		</tr>
		    <tr>
		        <td><label for='dname'>Enter a name for the database: </label></td>
		        <td><input type='text' value="<?php echo $dname?>" id='dname' name='dname' size='45'/></td>
		        <td id="error" class="error"><?php if($errors['dname']) echo $errors['dname']; ?></td>
		    </tr>
		    <tr>
		        <td><label for='duname'>Enter Database Administrative username: </label></td>
		        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='45' /></td>
		        <td id="error" class="error"><?php if($errors['duname']) echo $errors['duname']; ?></td>
		    </tr>
		    <tr>
		        <td><label for='dpassword'>Enter the password for the user: </label></td>
		        <td><input type='password' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='45' /></td>
		        <td id="error" class="error"><?php if($errors['dpassword']) echo $errors['dpassword']; ?></td>
		    </tr>
	</table>

	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('adv_options');}">Advanced Options</div>
	<div id="database" class="adv_options" style="display:block;">
	    <div class="description">
	        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings.
	    </div>
	    <div class="db_adv_options">
	    	<div class="adv_option">
	    		<label for='dhost'>Host: </label><br>
				<span class="description">The address of the server where the database is located, if different to the current server.</span>
				<input type="text" value="<?php echo $dhost?>" id="dhost" name="dhost" size='45' class="textinput"/>
	    	</div>
	    	<div class="adv_option">
	    		<label for='dport'>Port: </label><br>
	    		<span class="description">The port on which your database server is listening, if it is a non-standard port please enter the number here.</span>
	    		<input type="text" value="<?php echo $dbbinary?>" id="dbbinary" name="dbbinary" size='45' class="textinput"/>
	    	</div>
	    	<div class="adv_option">
	    		<label for='dport'>Socket: </label><br>
	    		<span class="description">The path to the database binary. If it is not on your system path then please enter it here.</span>	    				<input type="text" value="<?php echo $dport?>" id="dport" name="dport" size='10' class="textinput"/>
	    	</div>
	    </div>
	</div>
	</div>
	    <input type="submit" class="back" name="Previous" value="Previous" />
	    <?php if ($silent) { ?>
	    <input type="submit" class="input" name="Next" value="Next" />
	    <?php } else { ?>
	    <input type="button" class="input" name="Next" value="Next" onclick="javascript:{w.showStep(1, 'n');}"/>
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

	    <input type="button" name="Previous" class="back" value="Previous" onclick="javascript:{w.showStep(2, 'p');}"/>
	    <input type="button" name="Next" class="input" value="Next" onclick="javascript:{w.showStep(2, 'n');}"/>
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
	    <input type="button" name="Previous" value="previous" onclick="javascript:{w.showStep(3, 'p');}" class="back"/>
		<input type="submit" name="Next" value="next" class="input"/>
</div>
</form>