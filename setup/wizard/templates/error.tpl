<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<link rel="shortcut icon" href="../wizard/resources/graphics/favicon.ico" type="image/x-icon">
		<title>KnowledgeTree Installer</title>
		<script type="text/javascript" src="resources/js/jquery-tooltip/lib/jquery.js"></script>
		<script type="text/javascript" src="resources/js/wizard.js" ></script>
		<link rel="stylesheet" type="text/css" href="resources/css/wizard.css" />

	</head>

	<body onload="">
		<div id="outer-outer-wrapper" align="center">
		<div id="outer-wrapper" align="left">
		    <div id="header">
		    <div id="logo"><img src="resources/graphics/dame/installer-header_logo.png"/></div>
		    <div id="install_details">
				<span style="font-size:120%;"> <?php echo $install_version; ?> </span>
				<span style="font-size:80%;"> <?php echo $install_type; ?> </span>
				</div>
		    </div>
		    <div id="wrapper">
		        <div id="container">
		        	<div id="sidebar">
		            	<span id="welcome" class="current">Welcome</span><br><span id="license" class="inactive">License Agreement</span><br><span id="installtype" class="inactive">Install Type</span><br><span id="dependencies" class="inactive">PHP Dependencies</span><br><span id="configuration" class="inactive">System Configuration</span><br><span id="services" class="inactive">Service Dependency</span><br><span id="database" class="inactive">Database Configuration</span><br><span id="registration" class="inactive">Registration</span><br><span id="install" class="inactive">Install</span><br><span id="complete" class="inactive">Complete</span><br></div>
		            <div id="content">
		            	<div id="content_container">
		                	<form action="index.php?step_name=welcome" method="post">
		                		<div id="step_content" class="step">
					                <p class="title">Welcome to the KnowledgeTree Setup Wizard</p>
									<?php if(isset($error)) {
											echo "<span class='error'>".$error."</span>";
											?>
											<?php
										}
									?>
									<?php
										if(isset($errors)) {
											if($errors){
											    echo '<div class="error">';
											    foreach ($errors as $msg){
											        echo $msg . "<br />";
											        ?>
											        	<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
											        <?php
											    }
											    echo '</div>';
											}
										}
									?>
								</div>
							</form>
		                </div>
		            </div>
		        </div>
		        <div class="clearing">&nbsp;</div>
		    </div>
		    <div id="footer">
		    	<img width="105" height="23" align="right" src="resources/graphics/dame/powered-by-kt.png" style="padding: 5px;"/>
		    </div>
		</div>
		</div>
	</body>
</html>
<script>
	var w = new wizard();
</script>