<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Deactivate Services</p>

	<p class="description">
	All KnowledgeTree services need to be shutdown before the migration can continue.
	</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				All services have been shut down. Click next to continue.
				<br/><br/>
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
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Deactivate_Services" target="_blank">Click here for help on overcoming service issues</a>
	<?php } ?>
<!--Content-->
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<?php if(!$alreadyUninstalled) { ?>

		<?php } else { ?>
			All services are uninstalled.
		<?php } ?>
		<h3><?php echo "<span class='{$serviceCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Shutdown the following services</h3>
		<?php if($silent) { ?>
		<?php if($serviceCheck != 'tick') {
			$details = 'Hide Details';
			$display = 'block';
		} else {
			$details = 'Show Details';
			$display = 'none';
		}
		$display = 'block';
		?>
		<div class="service_details" style="display:<?php echo $display; ?>">
		<?php } ?>
		<table>
		<?php
		if($step_vars) {
			if(isset($step_vars['services'])) {
			    foreach ($step_vars['services'] as $ser){
			    	?>
			    	<tr>
			    		<td> <span class='<?php echo $ser['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
			    		<td style="width:50%;"> <?php echo $ser['name']; ?> </td>
			    		<td style="width:50%;"> <?php echo $ser['msg']; ?> </td>
			    		<?php if ($ser['class'] != 'tick') {
			    			?>
<!--			    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>-->
			    			<?php
			    		} ?>
			    	</tr>
			    	<?php
			    }
			}
		}
		?>
		</table>
		<?php if ($serviceCheck != 'tick') { ?>
			<br/>
			<p class=\"description\">Click <b>Next</b> if the above services are deactivated .</p>
		<?php } ?>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
	</div>
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>