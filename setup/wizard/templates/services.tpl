<?php if (AJAX) { ?>
	<form id="configuration_services_database" action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="services">
<?php } else { ?>
	<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="_<?php echo $step_name; ?>">
<?php } ?>

	<p class="title">Checking Service Dependencies</p>

	<p class="description">
	The wizard will review your system to determine whether you can run KnowledgeTree background services. <br/>Once the scan is completed, you&rsquo;ll see whether your system has met the requirements or whether there are areas you need to address.
	</p>
<!--Continue Message-->
<?php if($servicesValidation) { ?>
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				All service dependencies are met. Click next to continue.
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
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Service_Dependencies" target="_blank">Click here for help on overcoming service issues</a>
	<?php } ?>
<?php } ?>
<!--Content-->
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<?php if(!$alreadyInstalled) { ?>
			<?php if($servicesValidation) { ?>
				<?php if($javaExeError) { ?>
					Specify the location of your Java executable
					&nbsp;&nbsp;&nbsp;
					<input name='java' id='port' size='25' value='<?php echo $java['location']; ?>' style="float:none;"/>
					&nbsp;&nbsp;&nbsp;
					<a href="javascript:{w.sendJavaLocation();}" class="specify">Submit</a>
					<br/>
					<?php if($javaExeError != '') { ?><span class="error"><?php echo $javaExeMessage; ?></span><?php } ?>
					<br/>
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
				<?php if($openOfficeExeError) { ?>
					Specify the location of your Open Office executable
					&nbsp;&nbsp;&nbsp;
					<input name='soffice' id='port' size='25' value='<?php if(isset($soffice['location'])) echo $soffice['location']; ?>' style="float:none;"/>
					&nbsp;&nbsp;&nbsp;
					<a href="javascript:{w.sendJavaLocation();}" class="specify">Submit</a>
					<br/>
					<?php if($openOfficeExeError != '') { ?><span class="error"><?php echo $openOfficeExeMessage; ?></span><?php } ?>
				<?php } ?>
			<h3><?php echo "<span class='{$javaCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Java Check</h3>
			<?php if($silent) { ?>
				<?php if($javaExeError) {
					$details = 'Hide Details';
					$display = 'block';
				} else {
					$details = 'Show Details';
					$display = 'none';
				}
				?>
				<div id="option4" class="onclick" onclick="javascript:{w.toggleClass('java_details', 'option4');}"><?php echo $details; ?></div>
				<div class="java_details" style="display:<?php echo $display; ?>">
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
						<td>
							<?php if (AJAX) { ?>
								<a href="#" class="refresh" onclick="w.refresh('services')">Refresh</a>
							<?php } else { ?>
								<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
							<?php } ?>
						</td>
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
							<td>
							<?php if (AJAX) { ?>
								<a href="#" class="refresh" onclick="w.refresh('services')">Refresh</a>
							<?php } else { ?>
								<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
							<?php } ?>
							</td>
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
					<?php if($javaExeError) {
						$details = 'Hide Details';
						$display = 'block';
					} else {
						$details = 'Show Details';
						$display = 'none';
					}
					?>
					<div id="option5" class="onclick" onclick="javascript:{w.toggleClass('java_ext_details', 'option5');}"><?php echo $details; ?></div>
					<div class="java_ext_details" style="display:<?php echo $display; ?>">
				<?php } ?>
				A PHP Java Bridge is required for KnowledgeTree to perform at an optimal level.
				<table>
					<tr>
						<td> <span class='<?php echo $step_vars['extensions']['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
						<td style="width:645px;"> <?php echo $step_vars['extensions']['found']; ?> </td>
						<?php if ($step_vars['extensions']['class'] != 'tick') {
							?>
								<td>
								<?php if (AJAX) { ?>
									<a href="#" class="refresh" onclick="w.refresh('services')">Refresh</a>
								<?php } else { ?>
									<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
								<?php } ?>
								</td>
							<?php
						}
						?>
					</tr>
				</table>
				<?php if($silent) { ?>
					</div>
				<?php } ?>
			<?php } ?>
			<?php } ?>
		<?php } else { ?>
<!--			<p class="description">-->
			<?php if($servicesValidation) { ?>
				All services are already installed.
			<?php } ?>
<!--			</p>-->
		<?php } ?>
		<?php if($servicesValidation) { ?>
		<h3><?php echo "<span class='{$serviceCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services Check</h3>
		<?php if($silent) { ?>
			<?php if($serviceCheck != 'tick') {
				$details = 'Hide Details';
				$display = 'block';
			} else {
				$details = 'Show Details';
				$display = 'none';
			}
			?>
			<div id="option6" class="onclick" onclick="javascript:{w.toggleClass('service_details', 'option6');}"><?php echo $details; ?></div>
			<div class="service_details" style="display:<?php echo $display; ?>">
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
			    			<td>
							<?php if (AJAX) { ?>
								<a href="#" class="refresh" onclick="w.refresh('services')">Refresh</a>
							<?php } else { ?>
								<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
							<?php } ?>
			    			</td>
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
		<?php } else { ?>
			Services have to be manually started after the installation has been run. <br/>
			More information will be provided on completion of the installation.
		<?php } ?>
	</div>
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>