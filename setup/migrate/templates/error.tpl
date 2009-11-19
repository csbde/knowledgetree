<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<link rel="shortcut icon" href="../wizard/resources/graphics/favicon.ico" type="image/x-icon">
		<title>KnowledgeTree Installer</title>
		<script type="text/javascript" src="resources/jquery.js"></script>
		<script type="text/javascript" src="resources/wizard.js" ></script>
		<link rel="stylesheet" type="text/css" href="resources/wizard.css" />
	</head>

	<body onload="">
		<div id="outer-wrapper">
		    <div id="header">
		    <div id="logo"><img src="resources/graphics/dame/installer-header_logo.png"/></div>
		    <div id="install_details">
				<span style="font-size:120%;"> <?php echo $migrate_version; ?> </span>
				<span style="font-size:80%;"> <?php echo $migrate_type; ?> </span>
				</div>
		    </div>
		    <div id="wrapper">
		        <div id="container">
		        	<div id="sidebar">
		            	<span class="current">Welcome</span><br><span class="inactive">Current Installation</span><br><span class="inactive">Deactivate Services</span><br><span class="inactive">Database Migration</span><br><span class="inactive">Complete</span><br></div>

		            <div id="content">
		            	<div id="content_container">
		                	<form action="index.php?step_name=isntallation" method="post">
		                		<div id="step_content" class="step">
					                <p class="title">Welcome to the KnowledgeTree Migration Wizard</p>
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
<!--											        	<a href="javascript:this.location.reload();" class="refresh">Refresh</a>-->
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
	</body>
</html>
<script>
	var w = new wizard();
</script>