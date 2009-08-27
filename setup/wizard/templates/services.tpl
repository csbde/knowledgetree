<form action="index.php?step_name=services" method="post">
<h1>Services Dependencies Check</h1>

<p class="description">
This checkup ensures that your environment is ready to support KnowledgeTree<sup>&#174;</sup>'s background services. 
</p>
<?php
if($errors || $warnings){
    echo '<div>'
       . '<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Service_Dependencies" target="_blank">'
       . 'Click Here for help on overcoming services issues</a></div><br/>';
}
?>
<?php if(!$alreadyInstalled) { ?>
	<?php if($javaExeError != '') { ?>
		Specify the location of your Java executable
		&nbsp;&nbsp;&nbsp;
		<input name='java' id='port' size='25' value='<?php echo $java['location']; ?>'/>
		&nbsp;&nbsp;&nbsp;
		<?php if($javaExeError != true) { ?><span class="error"><?php echo $javaExeError; ?></span><?php } ?>
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
	<?php if($javaExeError != '' || $phpExeError != '') { ?>
		<input type="submit" name="Refresh" value="Submit"/>
	<?php } ?>
	<h3><?php echo "<span class='{$javaCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Java Check</h3>
	<?php if($silent) { ?>
		<div id="options" class="onclick" onclick="javascript:{w.toggleClass('java_details');}">Show Details</div>
		<div class="java_details" style="display:none">
	<?php } ?>
	<p class="description">
	The Java version must be higher than 1.5.
	</p>
	<?php echo "<span class='{$step_vars['java']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['java']['found']}"; ?>
	<br/>
	<?php echo "<span class='{$step_vars['version']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['version']['found']}"; ?>
	<?php if($silent) { ?>
		</div>
	<?php } ?>
	<?php if (!$disableExtension) { ?>
		<h3><?php echo "<span class='{$javaExtCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Java Extensions</h3>
		<?php if($silent) { ?>
			<div id="options" class="onclick" onclick="javascript:{w.toggleClass('java_ext_details');}">Show Details</div>
			<div class="java_ext_details" style="display:none">
		<?php } ?>
		<p class="description">
		A PHP Java Bridge is required for KnowledgeTree<sup>&#174;</sup> to perform at an optimal level.
		</p>
		<?php echo "<span class='{$step_vars['extensions']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['extensions']['found']}"; ?>
		<br />
		<?php if($silent) { ?>
			</div>
		<?php } ?>
	<?php } ?>
<?php } else { ?>
	<p class="description">
	All services are already installed.
	</p>
<?php } ?>
<h3><?php echo "<span class='{$serviceCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services Check</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('service_details');}">Show Details</div>
	<div class="service_details" style="display:none">
<?php } ?>
<p class="description">
Preload Services if posibble.
</p>
<?php
if($step_vars){
	if(isset($step_vars['services'])) {
	    foreach ($step_vars['services'] as $ser){
	    	echo "<span class='{$ser['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$ser['msg']}<br/>";
	    }
	}
}
?>
<?php if($silent) { ?>
	</div>
<?php } ?>
<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
    <input type="submit" name="Refresh" value="Refresh"/>
</div>
</form>