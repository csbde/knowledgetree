<form action="index.php?step_name=services" method="post" id="services">
	<p class="title">Deactivate Services</p>
	
	<p class="description">
	Services need to be deactivated before the migration takes place.
	</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				All services have been shut down. Please click next to continue.
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
	<div id="step_content_configuration" style="width:755px;" class="step">
		<?php if(!$alreadyUninstalled) { ?>
			
		<?php } else { ?>
			All services are uninstalled.
		<?php } ?>
		<h3><?php echo "<span class='{$serviceCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services Check</h3>
		<?php if($silent) { ?>
		<?php if($serviceCheck != 'tick') {
			$details = 'Hide Details';
			$display = 'block';
		} else {
			$details = 'Hide Details';
			$display = 'block';
//			$details = 'Show Details';
//			$display = 'none';
		}
		?>
			<div id="option6" class="onclick" onclick="javascript:{w.toggleClass('service_details', 'option6');}"><?php echo $details; ?></div>
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
			    		<td style="width:645px;"> <?php echo $ser['msg']; ?> </td>
			    		<?php if ($ser['class'] != 'tick') {
			    			?>
			    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
			    			<?php
			    		} ?>
			    	</tr>
			    	<?php
			    }
			}
		}
		?>
		</table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
	</div>
    <input type="submit" name="Previous" value="Back" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>