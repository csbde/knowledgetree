<h1>Database Configuration</h1>

<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
          echo $e;
        }
    }?>
</div>

<form id="dbsettings" action="index.php?step_name=<?php echo $step_name; ?>" method="post" onsubmit="w.onSubmitValidate();return false;">
<!--Hidden Fields-->
<input type="hidden" id='ddrop' name="ddrop" <?php echo ($ddrop) ? 'CHECKED' : ''; ?>/>
<input type="hidden" id="sendAll" name="" value="" />
<!-- STEP 1 of the database configuration - server settings -->
<div id="database" class="step1" style="display:block;">
	<div class="description">
	This step configures the connection to the database server and installs the database. The details for an administrative <br/>
	user on the database server are required in order to be able to configure and install the installation database.
	</div>
	<table class="dbconf">
	    <tr><td>Your current database type is: </td>
		<?php if($dtypes) {
		        foreach($dtypes as $k=>$v) {
		    ?><td><input type="hidden" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>/>&nbsp;&nbsp;<?php echo ucwords($v); ?></td>
		<?php }
		}
		?>
		</tr>
		    <tr>
		        <td><label for='dname'>Enter a name for the database: </label></td>
		        <td><input type='text' value="<?php echo $dname?>" id='dname' name='dname' size='40'/></td>
		    </tr>
		    <tr>
		        <td><label for='duname'>Enter the username for the Root or Administrative User: </label></td>
		        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='40' /></td>
		    </tr>
		    <tr>
		        <td><label for='dpassword'>Enter the password for the user: </label></td>
		        <td><input type='password' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='40' /></td>
		    </tr>
		    
	</table>

	<div id="options" class="onclick" onclick="javascript:{w.showAO();}">Advanced Options</div>

	<div id="database" class="adv_options" style="display:none;">
	    <div class="description">
	        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings.
	    </div>
	    <p>
	    <label for='dhost'>Host: </label><br />
	    <span class="description">The address of the server where the database is located, if different to the current server.</span><br/>
	    <input type="text" value="<?php echo $dhost?>" id="dhost" name="dhost" size='60'/>
	    </p>

	    <p>
	    <label for='dport'>Port: </label><br />
	    <span class="description">The port on which your database server is listening, if it is a non-standard port please enter the number here.</span><br/>
	    <input type="text" value="<?php echo $dport?>" id="dport" name="dport" size='10'/>
	    </p>

	    <p>
	    <label for='dbbinary'>Socket: </label><br />
	    <span class="description">The path to the database binary. If it is not on your system path then please enter it here.</span><br/>
	    <input type="text" value="<?php echo $dbbinary?>" id="dbbinary" name="dbbinary" size='60'/>
	    </p>
	</div>
	<div class="buttons">
	    <input type="submit" name="Previous" value="previous" />
	    <input type="button" name="Next" value="next" onclick="javascript:{w.showStep(1, 'n');}"/>
	</div>
</div>

<!-- STEP 2 of the database configuration - Admin user password settings -->
<div id="database" class="step2" style="display:none;">
<div class="description">
An administrative user is required for creating tables within the database.
</div>
	<table class="dbconf">
	    <tr>
	        <td><label for='dmsname'>Enter the name of the Database Administrative User: </label></td>
	        <td><input type='text' value="<?php echo $dmsname; ?>" id='dmsname' name='dmsname' size='40' /></td>
	        <td id="error_1_2" class="error" style="display:none">Please Enter A Username</td>
		</tr>
	    <tr>
	        <td><label for='dmspassword'>Enter a password: </label></td>
	        <td><input type='password' value="<?php echo $dmspassword; ?>" id='dmspassword' name='dmspassword' size='40' /></td>
	        <td id="error_2_2" class="error" style="display:none">Please Enter A Password</td>
		</tr>
	    <tr>
	        <td><label for='dmspassword2'>Please confirm the password: </label></td>
	        <td><input type='password' value="" id='dmspassword2' name='dmspassword2' size='40' /></td>
	        <td id="error_3_2" class="error" style="display:none">Please Confirm Password</td>
	        <td id="error_4_2" class="error" style="display:none">Passwords Do Not Match</td>
		</tr>
	</table>

	<div class="buttons">
	    <input type="button" name="Previous" value="previous" onclick="javascript:{w.showStep(2, 'p');}"/>
	    <input type="button" name="Next" value="next" onclick="javascript:{w.showStep(2, 'n');}"/>
	</div>
</div>

<!-- STEP 3 of the database configuration - default user password settings -->
<div id="database" class="step3" style="display:none;":>
<div class="description">
An second user is required for normal database interaction, the reading and writing of data.
</div>

	<table class="dbconf">
	    <tr>
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
	        <td><input type='password' value="" id='dmsuserpassword2' name='dmsuserpassword2' size='40' /></td>
	        <td id="error_3_3" class="error" style="display:none">Please Confirm Password</td>
	        <td id="error_4_3" class="error" style="display:none">Passwords Do Not Match</td>
		</tr>
	</table>
		<div class="buttons">
	    <input type="button" name="Previous" value="previous" onclick="javascript:{w.showStep(3, 'p');}"/>
<!--	    <input type="button" name="Next" value="next" onclick="showStep(3, 'n');"/>-->
		<input type="submit" name="Next" value="next" />
	</div>
</div>
</form>