<form action="index.php?step_name=dependencies" method="post">
	<p class="title">Checking PHP Dependencies</p>
	<p class="description">
	The wizard will review your system to determine whether you have the right PHP components in place to run KnowledgeTree. <br/>
	Once the scan is completed, you&rsquo;ll see whether your system has met the requirements or whether there are areas you need to address. 
	</p>
<!--Continue Message-->
	<?php
		if(!$errors && $warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;</span>
				<span class='description'>Congratulations! Your system is ready to run KnowledgeTree. Click Next to continue.</span>
				<br/><br/>
			<?php
		}
	?>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to run KnowledgeTree. See the list below to determine which areas you need to address. </span>
		<br/>
	<?php } elseif ($warnings) { ?>
			<span class='cross_orange'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<span class='warning_message'>Not all optional dependencies required by KnowledgeTree have been met but you will be able to continue.</span><br/>
	<?php } ?>
	<?php
		if($errors || $warnings) {
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Installer#PHP_Dependencies" target="_blank" class="description_click">Click here for help on overcoming dependency issues</span></a>
	<?php } ?>
<!--Content-->
	<div id="step_content_dependencies" class="step">
		<h3><?php echo "<span class='{$php}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Version Check</h3>
		<?php if($silent) { ?>
			<div id="option1" class="onclick" onclick="javascript:{w.toggleClass('php_details', 'option1');}">Show Details</div>
			<div class="php_details" style="display:none">
		<?php } ?>
		<p class="description">
		Your version of PHP must be between 5.0 and 5.3.2 to run optimally. Versions higher than 5.3.2 are not recommended.
		</p>
		<?php echo "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
		<?php if($version['class'] != 'tick') { ?>
			<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
		<?php } ?>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		<br />
		<h3><?php echo "<span class='{$php_ext}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Extensions</h3>
		<?php
		if($silent) { ?>
			<div id="option2" class="onclick" onclick="javascript:{w.toggleClass('php_ext_details', 'option2');}">Show Details</div>
			<div class="php_ext_details" style="display:none">
		<?php } ?>
		<p class="description">
			The extensions shown in red below are required for KnowledgeTree to run optimally. Items shown in orange are optional, but recommended.
		</p>
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
		    		<td class="ext_refresh"><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
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
		<h3><?php echo "<span class='{$php_con}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Configuration</h3>
		<?php
		if($silent) { ?>
			<div id="option3" class="onclick" onclick="javascript:{w.toggleClass('php_con_details', 'option3');}">Show Details</div>
			<div class="php_con_details" style="display:none">
		<?php } ?>
		<p class="description">
		The configurations shown in red below are required for KnowledgeTree to run optimally. Items shown in orange are optional, but recommended.
		</p>
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
		    		<td class="<?php echo $config['class']; ?>"><?php echo $config['name']; ?></td>
		    <?php
		    	if ($config['class'] == 'orange' || $config['class'] == 'cross') {
		    		?>
		    		<td class="ext_refresh"><a href="javascript:this.location.reload();">Refresh</a></td>
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
    <input type="submit" name="Previous" value="Back" class="button_previous"/>
    <input type="submit" name="Next" value="Next" class="button_next"/>
</form>