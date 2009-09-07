<form action="index.php?step_name=configuration" method="post">
	<p class="title">System Configuration</p>
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
		<table>
		    <tr>
		        <td width="150px">Host: </td>
		        <td> <div id="tooltips" title="Location of your web root">&nbsp;</div> </td>
		        <td><?php echo $server['host']['value']; ?></td>
		    </tr>
		    <tr>
		        <td>Port: </td>
		        <td> <div id="tooltips" title="The port that the web server listens on.">&nbsp;</div> </td>
		        <td><?php echo $server['port']['value']; ?></td>
		    </tr>
		    <tr>
		        <td>Root Url: </td>
		        <td> <div id="tooltips" title="Relative path to KnowledgeTree Source directory">&nbsp;</div> </td>
		        <td><?php echo $server['root_url']['value']; ?></td>
		    </tr>
		    <tr>
		        <td>File System Root: </td>
		        <td> <div id="tooltips" title="Absolute path to KnowledgeTree Source directory">&nbsp;</div> </td>
		        <td><?php echo $server['file_system_root']['value']; ?></td>
		    </tr>
		    <tr>
		        <td>SSL Enabled: </td>
		        <td> <div id="tooltips" title="Whether or not you have SSL installed">&nbsp;</div> </td>
		        <td><?php echo $server['ssl_enabled']['value']; ?></td>
		    </tr>
		</table>
		
		<h3>Paths and Permissions</h3>
		
		<table class="conf_paths">
		<?php
		    foreach ($paths as $key => $path){
		?>
			<tr>
				<td width="10"><div class='<?php echo $path['class']; ?>'></div></td>
				<td width="22%"><?php echo $path['name']; ?>:</td>
				<td width="50%"><?php echo $path['path']; ?></td>
	    		<?php if(isset($path['msg'])) {	?>
	    			<td class="error" width="27%"> <?php echo $path['msg']; ?> </td>
	    			<td width="10"><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
	    		<?php } else { ?>
	    			<td class="error" width="27%"> </td>
	    			<td width="10"> </td>
	    		<?php } ?>
			</tr>
		<?php
		    }
		?>
		</table>
		<br/>
	</div>
    <input type="submit"  name="Edit" value="Edit" class="back"/>
    <input type="submit" name="Confirm" value="Confirm" class="input"/>
</form>