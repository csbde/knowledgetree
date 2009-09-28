<form action="index.php?step_name=installation" method="post">
	<p class="title">Current Installation</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				Installation has been detected. Please click next to continue.
				<br/><br/>
			<?php
		}
	?>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to migrate KnowledgeTree. See the list below to determine which areas you need to address.</span>
		<br/>
	<?php } elseif ($warnings) {
		?>
		<?php
	}?>
	<?php
		if($errors || $warnings) {
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Current_Installation" target="_blank">Click here for help on overcoming installation detection issues</a>
	<?php } ?>
	<!--Content-->
	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			Please verify your current installation settings.
		</p>
		
		<h3>Database Settings</h3>
		
		<h3>System Paths</h3>
		<table class="conf_paths">
		<?php foreach ($paths as $key => $path) { ?>
			<tr>
<!--				<td width="10"><div class='<?php //echo $path['class']; ?>'></div></td>-->
				<td width="22%"><?php echo $path['name']; ?>:</td>
				<td width="50%"<?php //echo $width; ?>"><?php echo $path['path']; ?></td>
	    		<?php //if($path['class'] != 'tick') {	?>
<!--	    			<td class="error" width="27%"> <?php //echo $path['msg']; ?> </td>-->
	    			<td width="10">
					<?php //if (AJAX) { ?>
<!--						<a href="#" class="refresh" onclick="w.refresh('configuration')">Refresh</a>-->
					<?php //} else { ?>
<!--						<a href="javascript:this.location.reload();" class="refresh">Refresh</a>-->
					<?php //} ?>
	    			</td>
	    		<?php //} else { ?>
<!--	    			<td class="error" width="27%"> </td>-->
<!--	    			<td width="10"> </td>-->
	    		<?php //} ?>
			</tr>
		<?php
		    }
		?>
		</table>
		
	</div>
    <input type="submit" name="Edit" value="Edit"  class="button_previous"/>
    <input type="submit" name="Confirm" value="Confirm" class="button_next"/>
</form>
<?php if (AJAX) { ?> <script type="text/javascript" src="resources/form.js"></script> <?php } ?>