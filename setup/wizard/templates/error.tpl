<head>
	<title>KnowledgeTree Installer</title>
	<script type="text/javascript" src="resources/wizard.js"></script>
	<link rel="stylesheet" type="text/css" href="resources/wizard.css">
	</head><body onload="w.doFormCheck();">
		<div id="outer-wrapper">
		    <div id="header">
		        <div class="logo"></div>
		    </div>
		    <div id="wrapper">
		        <div id="container">
		            <div id="content">
		            	<form action="index.php?step_name=welcome" method="post">
			                <p class="title">Welcome to the KnowledgeTree Setup Wizard</p>
							<div style="width: 800px;">
							<?php if(isset($error)) echo '';echo "<div class='error'>".$error."</div>"; ?>
							<?php 
								if(isset($errors)) {
									if($errors){
									    echo '<div class="error">';
									    foreach ($errors as $msg){
									        echo $msg . "<br />\n";
									    }
									    echo '</div>';
									}
								}
							?>
							</div>
						</form>
					</div>
				</div>
		        <div id="sidebar">
		            <div class="menu">
		            	<span class="active">Welcome</span><br>
		            	<span class="inactive">License Agreement</span><br>
		            	<span class="inactive">PHP Dependencies</span><br>
		            	<span class="inactive">System Configuration</span><br>
		            	<span class="inactive">Service Dependencies</span><br>
		            	<span class="inactive">Database Configuration</span><br>
		            	<span class="inactive">Registration</span><br>
		            	<span class="inactive">Install</span><br>
		            	<span class="inactive">Complete</span><br>
		            </div>
				</div>
		        <div class="clearing">&nbsp;</div>
		    </div>
		    <div id="footer">
		        <div class="powered-by"></div>
		    </div>
		</div>
	<script>
	var w = new wizard();
</script></body>