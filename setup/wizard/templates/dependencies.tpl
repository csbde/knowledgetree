<!--<form id="license_dependencies_configuration" action="index.php?step_name=<?php //echo $step_name; ?>" method="post">-->
<form id="installtype_dependencies_configuration" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Checking PHP Dependencies</p>
	<p class="description">
	The wizard will review your system to determine whether you have the right PHP components in place to run KnowledgeTree. <!--<br/> -->
	Once the scan is completed, you&rsquo;ll see whether your system has met the requirements or whether there are areas you need to address.
	</p>
<!--Continue Message-->
	<?php
		if(!$errors && $warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;</span>
				<span class='description'>Congratulations! Your system is ready to run KnowledgeTree. Click <b>Next</b> to continue.</span>
				<br/>
<!--				<br/>-->
			<?php
		}
	?>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need to address. </span>
	<?php } elseif ($warnings) { ?>
			<span class='cross_orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<span class='warning_message'>Not all optional dependencies required by KnowledgeTree have been met but you will be able to continue.</span>
	<?php } ?>
	<?php
		if($errors || $warnings) {
			?>
		    	<a href="http://wiki.knowledgetree.com/Web_Based_Installer#PHP_Dependencies" target="_blank" class="description_click">Click here for help on overcoming dependency issues</span></a>
	<?php } ?>
<!--Content-->
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	<?php
		$iconFlag = "<span class='{$php}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:30%; float:left'>".$iconFlag."PHP Version Check</h3>";
	?>
<!--		PHP Version Check</h3>-->
		<?php if($silent) { ?>
			<?php if($versionSection) {
				$details = 'Hide Details';
				$display = 'block';
			} else {
				$details = 'Show Details';
				$display = 'none';
			}
			?>
			<?php
				$phpVersionCheck = $checkHeading;
				$phpVersionCheck .= "<div id='option1' class='onclick' onclick='javascript:{w.toggleClass(\"php_details\", \"option1\");}'>";
				$phpVersionCheck .= $details."</div>";
				$phpVersionCheck .= "<div class='php_details dependency_details' style='display:".$display.";'>";
			?>
		<?php } ?>
		<?php
			$space = "<p class='space'>&nbsp;</p>";
			$phpVersionCheck .= "<p class='description dependencies'>Your version of PHP must be between 5.0 and 5.2 to run optimally. Versions that are 5.3 or higher  are not recommended.</p>";
		?>
		<?php $phpVersionCheck .= "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
		<?php //echo "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
		<?php if($version['class'] != 'tick') { ?>
			<?php if (AJAX) { ?>
			<?php $phpVersionCheck .= "<a href='#' class='refresh' onclick='w.refresh('dependencies')'>Refresh</a>"; ?>
			<?php } else { ?>
			<?php $phpVersionCheck .=  "<a href='javascript:this.location.reload();' class='refresh'>Refresh</a>"; ?>
			<?php } ?>
		<?php } ?>
		<?php if($silent) { ?>
			<?php $phpVersionCheck .= "</div>"; ?>
		<?php } ?>
		<?php echo $phpVersionCheck; ?>
		<br />
	<?php
		$iconFlag = "<span class='{$php_ext}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:30%; float:left'>".$iconFlag."PHP Extensions</h3>";
	?>
		<?php
		if($silent) { ?>
			<?php if($extensionSection) {
				$details = 'Hide Details';
				$display = 'block';
			} else {
				$details = 'Show Details';
				$display = 'none';
			}
			?>
			<?php
				$phpExtensionCheck = $checkHeading;
				$phpExtensionCheck .= "<div id='option2' class='onclick' onclick='javascript:{w.toggleClass(\"php_ext_details\", \"option2\");}'>";
				$phpExtensionCheck .= $details."</div>";
				$phpExtensionCheck .= "<div class='php_ext_details dependency_details' style='display:".$display.";'>";
			?>
		<?php } ?>
		<?php
			$phpExtensionCheck .= "<p class='description dependencies'>The extensions shown in red below are required for KnowledgeTree to run optimally. Items shown in orange are optional, but recommended.</p>";
		?>
		<br/>
		<?php echo $phpExtensionCheck; ?>
		<table class="description">
		<?php
		    foreach($extensions as $ext) {
		   	?>
				<tr>
			<?php
		        switch($ext['available']){
		            case 'yes':
		                $class = 'tick';
		                break;
		            case 'optional':
		                $class = 'cross_orange';
		                break;
		            case 'no':
		            default:
		                $class = 'cross';
		        }
			?>
		        <td class="ext_indicator"><div class='<?php echo $class; ?>'></div></td>
		        <td class="ext_name"><?php echo $ext['name']; ?></td>
		        <?php echo ($ext['available'] != 'yes') ? "<td class='ext_description'>{$ext['details']}</td>" : '<td></td>'; ?>
		        <?php echo isset($errors[$ext['extension']]) ? "<td class='ext_error'><span class='error'>{$errors[$ext['extension']]}</span></td>" : '<td></td>'; ?>
		    <?php
		    	if ($class == 'orange' || $class == 'cross') {
		    		?>
		    		<td class="ext_refresh">
					<?php if (AJAX) { ?>
						<a href="#" class="refresh" onclick="w.refresh('dependencies')">Refresh</a>
					<?php } else { ?>
						<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
					<?php } ?>
		    		</td>
		    		<?php
		    	}
		    ?>
		        <?php
		    }
		?>
		</table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		<br />
		<?php
			$iconFlag = "<span class='{$php_con}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
			$checkHeading = "<h3 style='width:30%; float:left'>".$iconFlag."PHP Configurations</h3>";
		?>
		<?php
		if($silent) { ?>
			<?php if($configurationSection) {
				$details = 'Hide Details';
				$display = 'block';
			} else {
				$details = 'Show Details';
				$display = 'none';
			}
			?>
			<?php
				$phpDirectivesCheck = $checkHeading;
				$phpDirectivesCheck .= "<div id='option3' class='onclick' onclick='javascript:{w.toggleClass(\"php_con_details\", \"option3\");}'>";
				$phpDirectivesCheck .= $details."</div>";
				$phpDirectivesCheck .= "<div class='php_con_details dependency_details' style='display:".$display.";'>";
			?>
		<?php } ?>
		<?php
			$phpDirectivesCheck .= "<p class='description dependencies'>The configurations shown in red below are required for KnowledgeTree to run optimally. Items shown in orange are optional, but recommended.</p>";
		?>
		<br/>
		<?php echo $phpDirectivesCheck; ?>
		<table class="description">
		<tr>
		    <th>Setting</th>
		    <th>Recommended value</th>
		    <th>Current value</th>
		</tr>
		<?php
		    foreach($configurations as $config) {
		    	?>
		    	<tr>
		    		<td class="dir_name"><?php echo $config['name']; ?></td>
		    		<td class="dir_description"><?php echo $config['recommended']; ?></td>
		    		<td class="<?php echo $config['class']; ?>"><?php echo $config['setting']; ?></td>
		    <?php
		    	if ($config['class'] == 'orange' || $config['class'] == 'cross') {
		    		?>
		    		<td class="ext_refresh">
					<?php if (AJAX) { ?>
						<a href="#" class="refresh" onclick="w.refresh('dependencies')">Refresh</a>
					<?php } else { ?>
						<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
					<?php } ?>
		    		</td>
		    		<?php
		    	}
		    ?>
		        </tr>
				<?php
		    }
		?>
		</table>
		<br/>
		B = Bytes, K = Kilobytes, M = Megabytes, G = Gigabytes
		<?php if($silent) { ?>
			</div>
		<?php } ?>
	</div>
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>
