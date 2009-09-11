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
		    <div id="logo"><img src="resources/graphics/dame/installer-header_logo.png"/></div>
		    <div id="install_details">
				<span style="font-size:120%;"> 3.7 </span>
				<span style="font-size:80%;">Commercial Edition</span>
				</div>
		    </div>
		    <div id="wrapper">
		        <div id="container">
		        	<div id="sidebar">
		            	<span class='current'>Welcome</span><br /><span class='inactive'>License Agreement</span><br /><span class='inactive'>PHP Dependencies</span><br /><span class='inactive'>System Configuration</span><br /><span class='inactive'>Service Dependency</span><br /><span class='inactive'>Database Configuration</span><br /><span class='inactive'>Registration</span><br /><span class='inactive'>Install</span><br /><span class='inactive'>Complete</span><br />		        	</div>

		            <div id="content">
		            	<div id="content_container">
		                	<form action="index.php?step_name=welcome" method="post">
		                		<div id="step_content" class="step">
					                <p class="title">Welcome to the KnowledgeTree Setup Wizard</p>
									<?php if(isset($error)) { 
											echo "<span class='error'>".$error."</span>";
											?>
												<a style="width:100px;" href="javascript:this.location.reload();" class="refresh">Refresh</a>
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
	</body>
</html>
<script>
	var w = new wizard();
</script>