<form id="dependencies_configuration_services" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Checking System Configuration</p>

	<p class="description">
	The wizard will review your system to determine whether KnowledgeTree is correctly configured. You&rsquo;ll see whether KnowledgeTree has the correct settings or whether changes are required.
	</p>
	<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				All configuration settings are correctly set. Click next to continue.
				<br/><br/>
			<?php
		}
	?>
	<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need to address. </span>
		<br/>
	<?php } elseif ($warnings) {
		?>
			<span class='cross_orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<span class='warning_message'>Not all optional dependencies required by KnowledgeTree have been met but you will be able to continue.</span>
			<br/>
		<?php
	}?>
	<?php
		if($errors || $warnings) {
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Installer#System_Configuration" target="_blank">Click here for help on overcoming configuration issues</a>
	<?php } ?>
	<!--Content-->
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	<h3>Server Settings</h3>

	<p class="description">
	The settings below have been drawn from the system information. The host and port should reflect the host and port that will be used to access KnowledgeTree. The Root Url is only needed if your installation is in a directory off the main web server root.
	</p>
	<?php $label_size = "180px";?>
	<?php $input_size = 52;?>
	<table class="dbconf">
	    <tr>
	        <td width="<?php echo $label_size; ?>"><label for='host'>Host: </label></td>
	        <td> <div id="tooltips" title="Location of your web root">&nbsp;</div> </td>
	        <td><input name='host' id='host' size='<?php echo $input_size; ?>' value='<?php echo $server['host']['value']; ?>' /></td>
	    </tr>
	    <tr>
	        <td><label for='port'>Port: </label></td>
	        <td> <div id="tooltips" title="The port that the web server listens on.">&nbsp;</div> </td>
	        <td><input name='port' id='port' size='5' value='<?php echo $server['port']['value']; ?>' style="float:left;"/></td>
	    </tr>
	    <tr>
	        <td><label for='root_url'>Root URL: </label></td>
	        <td> <div id="tooltips" title="Relative path to KnowledgeTree Source directory">&nbsp;</div> </td>
	        <td><input name='root_url' id='root_url' size='<?php echo $input_size; ?>' value='<?php echo $server['root_url']['value']; ?>' /></td>
	    </tr>
	    <tr>
	        <td><label for='file_system_root'>Web Root: </label></td>
	        <td> <div id="tooltips" title="Absolute path to KnowledgeTree Source directory">&nbsp;</div> </td>
	        <td><input name='file_system_root' id='file_system_root' size='<?php echo $input_size; ?>' value='<?php echo $server['file_system_root']['value']; ?>' /></td>
	    </tr>
	</table>
	        	<input type='hidden' name='ssl_enabled' id='no' value='no' checked='' />
	<h3><?php echo "<span class='{$paths_perms}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Paths and Permissions</h3>
	<?php if($silent) { ?>
		<div id="option7" class="onclick" onclick="javascript:{w.toggleClass('paths_perms', 'option7');}">Show Details</div>
		<div class="paths_perms" style="display:none">
	<?php } ?>
	<p class="description">
	The following folders must be writable for KnowledgeTree to be able to run. The permissions on the configuration file may be reset to read-only once the installation has completed.
	</p>
	<table>
	<?php
		if($errors || $warnings) {
			$width = "390px";
		} else {
			$width = "520px";
		}
	    foreach ($paths as $key => $path) {
	    	?>
	    	<tr>
	    		<td> <div class='<?php echo $path['class']; ?>'></div> </td>
	    		<td> <label for='<?php echo $path['setting']; ?>'> <?php echo $path['name']; ?>: </label> </td>
	    		<td> <input name='<?php echo $path['setting']; ?>' id='<?php echo $path['setting']; ?>' style="float:left;width:<?php echo $width; ?>;" value='<?php echo $path['path']; ?>' /> </td>
	    		<?php if(isset($path['msg'])) {
	    			?>
	    			<td class="error"> <?php echo $path['msg']; ?> </td>
	    			<?php
	    		} else {
	    			?>
	    			<td class="error"> </td>
	    			<td> </td>
	    			<?php
	    		}
	    		?>
	    	</tr>
	    	<?php
	    }
	?>
	</table>
	<?php if($silent) { ?>
		</div>
	<?php } ?>
		</div>
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>