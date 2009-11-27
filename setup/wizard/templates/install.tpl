<form id="registration_install_complete" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Finalizing System Installation</p>
	<?php if($ce_check) { ?>
		<div id="step_content_<?php echo $step_name; ?>" class="step">
	<?php } else { ?>
		<div id="step_content" class="step">
	<?php } ?>
		<p class="empty_space" style="font-size:11pt;">	The wizard will now complete the installation and run a final check on your system.	</p>
<!--		<br/>-->
<?php if($ce_check) { ?>
		<p class="empty_space" style="font-size:11pt;" id="left_space">
			We would greatly appreciate it if you would allow us to collect anonymous usage statistics to help us provide a better quality product.
			<br/>
			<br/>
			The information includes a unique identification number, number of users you have created, your operating system type and your IP address. Your privacy is protected by the <a href="http://www.knowledgetree.com/about/legal" target="_blank">KnowledgeTree Privacy and Data Protection Agreements.</a>
		</p>
		<div class="demo"><?php echo $html->image('greenit.jpg', array('style'=>'padding-left: 35px;')); ?></div>
<!--		<br/><br/><br/><br/>-->
<p>		<input type='checkbox' name='call_home' value='enable' checked style="float:left;"/>&nbsp;&nbsp;
		<label for='call_home'>Help to improve KnowledgeTree by providing anonymous usage statistics</label></p>
<?php } else { ?>
		<p class="empty_space" style="font-size:11pt;">
			We would greatly appreciate it if you would allow us to collect anonymous usage statistics to help us provide a better quality product.
			<br/>
			<br/>
			The information includes a unique identification number, number of users you have created, your operating system type and your IP address. Your privacy is protected by the <a href="http://www.knowledgetree.com/about/legal" target="_blank">KnowledgeTree Privacy and Data Protection Agreements.</a>
		</p>
<!--		<div class="demo"><?php //echo $html->image('greenit.jpg', array('style'=>'padding-left: 35px;')); ?></div>-->
		<br/><br/><br/><br/><br/><br/><br/><br/>
			
<p>		
		<input type='checkbox' name='call_home' value='enable' checked style="float:left;"/>&nbsp;&nbsp;
		<label for='call_home'>Help to improve KnowledgeTree by providing anonymous usage statistics</label>
</p>
<?php } ?>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Install" value="Install" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>