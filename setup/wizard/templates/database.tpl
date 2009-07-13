<h1>Database Configuration</h1>

<form action="index.php?step_name=<?php echo $step_name; ?>" method="post">
<div id="database" class="step1" style="display:block;">
	Select your database type:
	<div class="dtype">
		<?php if($dtypes) {
		        foreach($dtypes as $k=>$v) {
		    ?>
		        <input type="radio" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>/>&nbsp;&nbsp;<?php echo ucwords($v); ?>
		        <br/>
		<?php }
		}
		?>
	</div>

	<table>
	    <tr>
	        <td><label for='dname'>Enter a name for the database: </label></td>
	        <td><input type='text' value="<?php echo $dname?>" id='dname' name='dname' size='40' /></td>
	    </tr>
	    <tr>
	        <td><label for='duname'>Enter the username for the Root or Administrative User: </label></td>
	        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='40' /></td>
	    </tr>
	    <tr>
	        <td><label for='dpassword'>Enter the password for the user: </label></td>
	        <td><input type='password' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='40' /></td>
	    </tr>
	    <tr>
	        <td><label for='ddrop'>Drop database if it exists:  </label></td>
	        <td><input type="checkbox" id='ddrop' name="ddrop" <?php echo ($ddrop) ? 'CHECKED' : ''; ?>/></td>
	</table>
	<div id="options" onclick="showAO();" style="cursor:pointer;">Advanced Options</div>
	<div id="database" class="adv_options" style="display:none;">
	    <div class="description">
	        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings or check with your hosting provider.
	    </div>
		<table>
			<tr>
				<td><label for='dname'>Host: </label></td>
				<td><input type="text" value="<?php echo $dhost?>" id="dhost" name="dhost" size='40'/><br/></td>
				
			</tr>
			<tr><td><div class="description">If your database is located on a different server, change this.</div></td></tr>
			<tr>
				<td><label for='dname'>Port: </label></td>
				<td><input type="text" value="<?php echo $dport?>" id="dport" name="dport" size="40"/></td>
				<tr><td><div class="description">If your database server is listening to a non-standard port, enter its number.</div></tr></td>
			</tr>
			<tr>
				<td><label for='dname'>Binary: </label></td>
				<td><input type="text" value="<?php echo $dbbinary?>" id="dbbinary" name="dbbinary" size="40"/></td>
				<tr><td><div class="description">If your database binary is not in the system path, change this.</div></tr></td>
			</tr>
			<tr>
				<td><label for='dname'>Table Prefix: </label></td>
				<td><input type="text" value="<?php echo $tprefix?>" id="tprefix" name="tprefix" size="40"/></td>
				<tr><td><div class="description">If more than one application will be sharing this database, enter a table prefix such as kt_ for your KnowledgeTree site here.</div></tr></td>
			</tr>
		</table>
	</div>
	<div class="buttons">
	    <input type="submit" name="Previous" value="previous" />
	    <input type="button" name="Next" value="next" onclick="showStep(1, 'n');"/>
	</div>
</div>
<div id="database" class="step2" style="display:none;">
	<table>
	    <tr>
	        <td><label for='dmsname'>Enter a name for the DMS Admin User: </label></td>
	        <td><input type='text' value="<?php echo $dmsname; ?>" id='dmsname' name='dmsname' size='40' /></td>
		</tr>
	    <tr>
	        <td><label for='dmspassword'>Enter the password for the DMS Admin User: </label></td>
	        <td><input type='password' value="<?php echo $dmspassword; ?>" id='dmspassword' name='dmspassword' size='40' /></td>
		</tr>
	    <tr>
	        <td><label for='dmspassword2'>Re Enter the password for the DMS Admin User: </label></td>
	        <td><input type='password' value="" id='dmspassword2' name='dmspassword2' size='40' /></td>
		</tr>
	</table>
	
	<div class="buttons">
	    <input type="button" name="Previous" value="previous" onclick="showStep(2, 'p');"/>
	    <input type="button" name="Next" value="next" onclick="showStep(2, 'n');"/>
	</div>
</div>

<div id="database" class="step3" style="display:none;":>
	<table>
	    <tr>
	        <td><label for='dmsusername'>Enter a name for the DMS Default User: </label></td>
	        <td><input type='text' value="<?php echo $dmsusername; ?>" id='dmsusername' name='dmsusername' size='40' /></td>
		</tr>
	    <tr>
	        <td><label for='dmsuserpassword'>Enter the password for the DMS Default User: </label></td>
	        <td><input type='password' value="<?php echo $dmsuserpassword?>" id='dmsuserpassword' name='dmsuserpassword' size='40' /></td>
		</tr>
	    <tr>
	        <td><label for='dmsuserpassword2'>Re Enter the password for the DMS Default User: </label></td>
	        <td><input type='password' value="" id='dmsuserpassword2' name='dmsuserpassword2' size='40' /></td>
		</tr>
	</table>
		<div class="buttons">
	    <input type="button" name="Previous" value="previous" onclick="showStep(3, 'p');"/>
	    <input type="submit" name="Next" value="next"/>
	</div>
</div>

<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
          echo $e;
        }
    }?>
</div>
</form>
<script type="text/javascript">
// Toggle Advance Database options
    function showAO() {
        var el = document.getElementsByClassName("adv_options");
        if(el[0].style.display == 'none')
            el[0].style.display = 'block';
        else
            el[0].style.display = 'none';
    }
// Handle steps within database page
    function showStep(p, d) {
    	var el = document.getElementsByClassName("step"+p);
    	el[0].style.display = 'none';
    	if(d == "n") {
    		var j = p+1;
    	} else if(d == "p") {
    		var j = p-1;
    	}
    	var el = document.getElementsByClassName("step"+j);
    	el[0].style.display = 'block';
    }
</script>