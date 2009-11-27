<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<link rel="shortcut icon" href="../wizard/resources/graphics/favicon.ico" type="image/x-icon">
		<title>KnowledgeTree Upgrader</title>
		<?php echo $html->js('jquery.js'); ?>
		<?php echo $html->js('jquery.form.js'); ?>
		<?php echo $html->js('jquery.blockUI.js'); ?>
		<?php echo $html->js('jquery.hotkeys.js'); ?>
		<?php echo $html->js('wizard.js'); ?>
		<?php echo $html->css('wizard.css'); ?>
		<?php echo $html->css('migrate.css'); ?>
		<?php if(AGENT == "IE6") echo $html->css('ie6.css'); ?>
        <?php if(AGENT == "IE7") echo $html->css('ie7.css'); ?>
        <?php if(AGENT == "IE8") echo $html->css('ie8.css'); ?>
        <?php if(INSTALL_TYPE == "community") echo $html->css('community.css'); ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8">
	</head>
	<body onload="">
		<div id="outer-outer-wrapper" align="center">
		<div id="outer-wrapper" align="left">
		    <div id="header">
		    <div id="logo"><?php echo $html->image('dame/installer-header_logo.png'); ?></div>
		    <div id="upgrade_details">
				<span style="font-size:120%;"> <?php echo $vars['upgrade_version']; ?> </span>
				<span style="font-size:120%;"> <?php echo $vars['upgrade_type']; ?> </span>
				</div>
		    </div>
		    <div id="wrapper">
		        <div id="container">
		        	<div id="sidebar">
		            	<?php echo $vars['left']; ?>
		        	</div>
		            <div id="content">
		            	<div id="content_container">
		                	<?php echo $content; ?>
		                </div>
		            </div>
		        </div>
		        <div class="clearing">&nbsp;</div>
		    </div>
		    <div id="footer">
		    	<?php echo $html->image('dame/powered-by-kt.png', array("height"=>"23px", "width"=>"105px", "style"=>"padding: 5px;")); ?>
		    </div>
		</div>
		</div>
	</body>
</html>
<script>
	var w = new wizard();
</script>