<h1>Checking System Configuration</h1>

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
	<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need to address. <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Once you&rsquo;ve fixed these items, return to this wizard and try again.</span><br/>
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

<form action="index.php?step_name=configuration" method="post">
<h3>Server Settings</h3>

<p class="description">
The settings below have been drawn from the system information. The host and port should reflect the host and port that will be used to access KnowledgeTree. The Root Url is only needed if your installation is in a directory off the main web server root.
</p>

<table>
    <tr>
        <td><label for='host'>Host: </label></td>
        <td><input name='host' id='host' size='60' value='<?php echo $server['host']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='port'>Port: </label></td>
        <td><input name='port' id='port' size='5' value='<?php echo $server['port']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='root_url'>Root URL: </label></td>
        <td><input name='root_url' id='root_url' size='60' value='<?php echo $server['root_url']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='file_system_root'>Web Root: </label></td>
        <td><input name='file_system_root' id='file_system_root' size='60' value='<?php echo $server['file_system_root']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='yes'>Do you have SSL Enabled?: </label></td>
        <td>
            <label for='yes'>Yes: </label><input type='radio' name='ssl_enabled' id='yes' value='yes' <?php echo $server['ssl_enabled']['value'] == 'yes' ? 'CHECKED' : ''; ?> />&nbsp;&nbsp;
            <label for='no'>No: </label><input type='radio' name='ssl_enabled' id='no' value='no' <?php echo $server['ssl_enabled']['value'] == 'no' ? 'CHECKED' : ''; ?> />
        </td>
    </tr>
</table>

<h3><?php echo "<span class='{$paths_perms}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Paths and Permissions</h3>
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
    		<td><input name='<?php echo $path['setting']; ?>' id='<?php echo $path['setting']; ?>' size='60' value='<?php echo $path['path']; ?>' /></td>
    		<?php if(isset($path['msg'])) {
    			?>
    			<td class="error"> <?php echo $path['msg']; ?> </td>
    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
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
<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
</div>
</form>