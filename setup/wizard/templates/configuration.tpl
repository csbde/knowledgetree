<form action="index.php?step_name=configuration" method="post">
<p class="title">Checking System Configuration</p>

<p class="description">
The wizard will review your system to determine whether KnowledgeTree is correctly configured. You&rsquo;ll see whether KnowledgeTree has the correct settings or whether changes are required.
</p>

<div class="continue_message">
<?php
	if(!$errors && !$warnings) {
		?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;All configuration settings are correctly set. Please click next to continue.
		<?php
	}
?>
</div>
<div class="error_message">
<?php if($errors) { ?>
	<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;to address. Once you&rsquo;ve fixed these items, return to this wizard and try again.</span><br/>
<?php } elseif ($warnings) {
	?>
	<span class='cross_orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KnowledgeTree Optional Dependencies not met, but you will be able to continue.</span><br/>
	<?php
}?>
<?php
	if($errors || $warnings) {
		?>
	    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Installer#System_Configuration" target="_blank">Click here for help on overcoming configuration issues</a>
<?php } ?>
</div>

<div id="step_content_configuration">
<h3>Server Settings</h3>

<p class="description">
The settings below have been drawn from the system information. The host and port should reflect the host and port that will be used to access KnowledgeTree. The Root Url is only needed if your installation is in a directory off the main web server root.
</p>

<table class="dbconf">
    <tr>
        <td width="150px"><label for='host'>Host: </label></td>
        <td> <div id="tooltips" title="Location of your web root">&nbsp;</div> </td>
        <td><input name='host' id='host' size='35' value='<?php echo $server['host']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='port'>Port: </label></td>
        <td> <div id="tooltips" title="The port that the web server listens on.">&nbsp;</div> </td>
        <td><input name='port' id='port' size='5' value='<?php echo $server['port']['value']; ?>' style="float:left;"/></td>
    </tr>
    <tr>
        <td><label for='root_url'>Root URL: </label></td>
        <td> <div id="tooltips" title="Relative path to KnowledgeTree Source directory">&nbsp;</div> </td>
        <td><input name='root_url' id='root_url' size='35' value='<?php echo $server['root_url']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='file_system_root'>Web Root: </label></td>
        <td> <div id="tooltips" title="Absolute path to KnowledgeTree Source directory">&nbsp;</div> </td>
        <td><input name='file_system_root' id='file_system_root' size='35' value='<?php echo $server['file_system_root']['value']; ?>' /></td>
    </tr>
    <tr>
        <td> <label for='yes'>Do you have SSL Enabled?: </label> </td>
        <td> <div id="tooltips" title="Whether or not you have SSL installed">&nbsp;</div> </td>
        <td> 
        	<label for='yes'>Yes: </label> 
        	<input class="radio" type='radio' name='ssl_enabled' id='yes' value='yes' <?php echo $server['ssl_enabled']['value'] == 'yes' ? 'CHECKED' : ''; ?> />
        	<label for='no'>No: </label>
        	<input class="radio" type='radio' name='ssl_enabled' id='no' value='no' <?php echo $server['ssl_enabled']['value'] == 'no' ? 'CHECKED' : ''; ?> />
		</td>
    </tr>
</table>

<h3><?php echo "<span class='{$paths_perms}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Directory Paths and Permissions</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('paths_perms');}">Show Details</div>
	<div class="paths_perms" style="display:none">
<?php } ?>
<p class="description">
The following folders must be writable for KnowledgeTree to be able to run. The permissions on the configuration file may be reset to read-only once the installation has completed.
</p>
<table>
<?php
    foreach ($paths as $key => $path){
    	?>
    	<tr>
    		<td> <div class='<?php echo $path['class']; ?>'></div> </td>
    		<td> <label for='<?php echo $path['setting']; ?>'> <?php echo $path['name']; ?>: </label> </td>
    		<td><input name='<?php echo $path['setting']; ?>' id='<?php echo $path['setting']; ?>' style="float:left;width:290px;" value='<?php echo $path['path']; ?>' /></td>
    		<?php if(isset($path['msg'])) {
    			?>
    			<td class="error"> <?php echo $path['msg']; ?> </td>
    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
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
    <input type="submit" name="Previous" value="Previous" class="back"/>
    <input type="submit" name="Next" value="Next" class="input"/>
</form>