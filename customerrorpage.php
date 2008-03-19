<?php

if (array_key_exists('fatal', $_POST))
{
	$posted = $_POST['fatal'];
	
}

if (array_key_exists('Error_MessageOne', $_POST) && array_key_exists('Error_MessageTwo', $_POST))
{
	$sErrorMessage = $_POST['Error_MessageOne'].''.$_POST['Error_MessageTwo'];
	
}

session_start();
require_once("config/dmsDefaults.php");

if (array_key_exists('sErrorMessage', $_SESSION))
{
$phperror = $_SESSION['sErrorMessage'];
}

global $default;

$sUrl = $default->rootUrl;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
	<head>
		<title>Knowledgetree - Desklet</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $sUrl ?>/resources/css/errors.css" />
	
	<script type="text/javascript"> 
		
function Click()
{
var open = document.getElementById('exp');
open.style.display = 'block';
var border = document.getElementById('error-container');
border.style.height = '250px'; 
}

</script>		
		
	</head>
	<body>
	
		<div id="error-container">
			
			<div id="acc-error">
	
						
				<h1>Error!! - You have encountered a problem starting your document management system.</h1>
				<p><h2>Please contact your systems administrator</h2></p>
				<p>For more details, click here <img src="<?php echo $sUrl ?>/resources/graphics/info.gif" style="cursor: pointer;" onclick="Click()" /><div id ="exp" style="display: none; "> <?php if(isset($sErrorMessage)){ echo $sErrorMessage;  }else  if(isset($posted)){ echo $posted; } else if($phperror){ echo $phperror; } ?></div></p>
				
			</div>		
		</div>		
				
	</body>
</html>