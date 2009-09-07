<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>KnowledgeTree Installer</title>
		<script type="text/javascript" src="resources/jquery-tooltip/lib/jquery.js"></script>
		<script type="text/javascript" src="resources/wizard.js" ></script>
		<link rel="stylesheet" type="text/css" href="resources/wizard.css" />
		
	</head>
	<body onload="w.doFormCheck();">
		<div id="outer-wrapper">
		    <div id="header">
				<span style="font-size:120%;"> <?php echo $vars['install_version']; ?> </span><br>
				<span style="font-size:80%;"><?php echo $vars['install_type']; ?></span>
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
		    	<img width="105" height="23" align="right" src="resources/graphics/dame/powered-by-kt.png" style="padding: 5px;"/>
		    </div>
		</div>
	</body>
</html>
<script>
	var w = new wizard();
</script>