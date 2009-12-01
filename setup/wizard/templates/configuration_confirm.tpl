<form id="dependencies_configuration_services" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">System Configuration</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='continue_message'>All configuration settings are correctly set. Click next to continue.</span><br/><br/>
			<?php
		}
	?>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need to address.</span>
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
		<table>
		    <tr>
		        <td width="115px">Host: </td>
		        <td width="50px"> <div id="tooltips" title="Location of your web root">&nbsp;</div> </td>
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
		        <td>Web Root: </td>
		        <td> <div id="tooltips" title="Absolute path to KnowledgeTree Source directory">&nbsp;</div> </td>
		        <td><?php echo $server['file_system_root']['value']; ?></td>
		    </tr>
		</table>

		<h3>Paths and Permissions</h3>

		<table class="conf_paths">
		<?php
			if($errors || $warnings) {
				$width = "50%";
				$width = "40%";
			} else {
				$width = "60%";
			}
			$td1 = "10";
			$td2 = "32%";
			$td3 = "27%";
			$td4 = "27%";
			$td5 = "10";
		    foreach ($paths as $key => $path){
		?>
			<tr>
				<td width="<?php echo $td1; ?>"><div class='<?php echo $path['class']; ?>'></div></td>
				<td width="<?php echo $td2; ?>"><?php echo $path['name']; ?>:</td>
				<td width="<?php echo $width; ?>"><?php echo $path['path']; ?></td>
	    		<?php if($path['class'] != 'tick') {	?>
	    			<td class="error" width="<?php echo $td3; ?>"> <?php echo $path['msg']; ?> </td>
	    			<td width="10">
					<?php if (AJAX) { ?>
						<a href="#" class="refresh" onclick="w.refresh('configuration')">Refresh</a>
					<?php } else { ?>
						<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
					<?php } ?>
	    			</td>
	    		<?php } else { ?>
	    			<td class="error" width="<?php echo $td4; ?>"> </td>
	    			<td width="<?php echo $td5; ?>"> </td>
	    		<?php } ?>
			</tr>
		<?php
		    }
		?>
		</table>
	</div>
	<input class="button_previous" type="submit" value="Previous" name="Previous"/>
    <input type="submit" name="Confirm" value="Confirm" class="button_next"/>
    <input type="submit" name="Edit" value="Edit"  class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>