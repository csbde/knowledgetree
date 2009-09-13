<form id="configuration_services_database" action="index.php?step_name=services" method="post" id="services">
	<?php //if($javaExeError != '' || $phpExeError != '') { 
		//var_dump($javaExeError);
		//if($errors) $top = "185px"; elseif ($warnings) $top = "185px"; else $top = "168px";
		?>
<!--		<input type="submit" name="Next" value="Submit" id="inner_button" style="top:<?php //echo $top; ?>"/>-->
	<?php //} ?>
	<p class="title">Checking Service Dependencies</p>
	
	<p class="description">
	The wizard will review your system to determine whether you can run KnowledgeTree background services. <br/>Once the scan is completed, you&rsquo;ll see whether your system has met the requirements or whether there are areas you need to address. 
	</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				All service dependencies are met. Please click next to continue.
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
//			var_dump($errors);
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Service_Dependencies" target="_blank">Click here for help on overcoming service issues</a>
	<?php } ?>
<!--Content-->
	<div id="step_content_configuration" style="width:755px;" class="step">
		<?php if(!$alreadyInstalled) { ?>
			<?php //var_dump($javaExeError); ?>
			<?php if($javaExeError) { ?>
				Specify the location of your Java executable
				&nbsp;&nbsp;&nbsp;
				<input name='java' id='port' size='25' value='<?php echo $java['location']; ?>' style="float:none;"/>
				&nbsp;&nbsp;&nbsp;
				<a href="javascript:{document.getElementById('services').submit();}" class="specify">Submit</a>
				<br/>
				<?php if($javaExeError != '') { ?><span class="error"><?php echo $javaExeMessage; ?></span><?php } ?>
			<?php } ?>
			<?php if($phpExeError != '') { ?>
				<br />
				Specify the location of your PHP executable
				<br />
				<?php if($php['location'] == '') { ?>
					<input name='php' id='port' size='25' value='<?php echo $php['location']; ?>'/>
				<?php } else { ?>
					<input type="hidden" name='php' id='port' size='25' value='<?php echo $php['location']; ?>'/>
				<?php } ?>
				&nbsp;&nbsp;&nbsp;
				<?php if($phpExeError != true) { ?><span class="error"><?php echo $phpExeError; ?></span><?php } ?>
			<?php } ?>
			<h3><?php echo "<span class='{$javaCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Java Check</h3>
			<?php if($silent) { ?>
				<div id="option4" class="onclick" onclick="javascript:{w.toggleClass('java_details', 'option4');}">Show Details</div>
				<div class="java_details" style="display:none">
			<?php } ?>
<!--			<p class="description">-->
			The Java version must be higher than 1.5.
<!--			</p>-->
			<table>
				<tr>
					<td> <span class='<?php echo $step_vars['java']['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
					<td style="width:645px;"> <?php echo $step_vars['java']['found']; ?> </td>
					<?php 
					if ($step_vars['java']['class'] != 'tick') {
					?>
						<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
					<?php
					} else { ?>
						<td> </td>
					<?php } ?>
				</tr>
				<tr>
					<td> <span class='<?php echo $step_vars['version']['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
					<td> <?php echo $step_vars['version']['found']; ?> </td>
					<?php if ($step_vars['version']['class'] != 'tick') {
						?>
							<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
						<?php
					}
					?>			
				</tr>
			</table>
			<?php if($silent) { ?>
				</div>
			<?php } ?>
			<?php if (!$disableExtension) { ?>
				<h3><?php echo "<span class='{$javaExtCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Java Extensions</h3>
				<?php if($silent) { ?>
					<div id="option5" class="onclick" onclick="javascript:{w.toggleClass('java_ext_details', 'option5');}">Show Details</div>
					<div class="java_ext_details" style="display:none">
				<?php } ?>
<!--				<p class="description">-->
				A PHP Java Bridge is required for KnowledgeTree to perform at an optimal level.
<!--				</p>-->
				<table>
					<tr>
						<td> <span class='<?php echo $step_vars['extensions']['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
						<td style="width:645px;"> <?php echo $step_vars['extensions']['found']; ?> </td>
						<?php if ($step_vars['extensions']['class'] != 'tick') {
							?>
								<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
							<?php
						}
						?>
					</tr>
				</table>
				<?php if($silent) { ?>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } else { ?>
<!--			<p class="description">-->
			All services are already installed.
<!--			</p>-->
		<?php } ?>
		<h3><?php echo "<span class='{$serviceCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services Check</h3>
		<?php if($silent) { ?>
			<div id="option6" class="onclick" onclick="javascript:{w.toggleClass('service_details', 'option6');}">Show Details</div>
			<div class="service_details" style="display:none">
		<?php } ?>
<!--		<p class="description">-->
		Preload Services if posibble.
<!--		</p>-->
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
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript" src="resources/form.js"></script>