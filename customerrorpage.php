<?php
/**
 * $Id: customerrorpage.php 8391 2008-04-23 10:12:34Z jonathan_byrne $
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 */

if (array_key_exists('fatal', $_POST))
{
	$posted = $_POST['fatal'];
	
}

if (array_key_exists('Error_MessageOne', $_POST) && array_key_exists('Error_MessageTwo', $_POST))
{
	$sErrorMessage = $_POST['Error_MessageOne'].''.$_POST['Error_MessageTwo'];
	
}

session_start();


if (array_key_exists('sErrorMessage', $_SESSION))
{
	$phperror = $_SESSION['sErrorMessage'];
}

//Finding root Url
$sHost = $_SERVER['HTTP_HOST'];
$sScriptName = dirname($_SERVER['SCRIPT_NAME']);
$sRoot = $sHost.$sScriptName;
$sLastChar = substr($sScriptName, -1, 1);
$sScriptName = ($sLastChar == '\\' || $sLastChar == '/') ? substr($sScriptName, 0, -1) : $sScriptName;
$bSSLEnabled = false;
if ($_SERVER['HTTPS'] === 'on')
{
	$bSSLEnabled = true;
}
$sRootUrl = ($bSSLEnabled ? 'https://' : 'http://').$sRoot;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
	<head>
		<title>Knowledgetree - Desklet</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $sRootUrl ?>/resources/css/errors.css" />
	
	<script type="text/javascript"> 
		
function Click()
{
	var open = document.getElementById('exp');
	open.style.display = 'block';
	var border = document.getElementById('error-container');
	border.style.height = '220px'; 
}

</script>		
		
	</head>
	<body>
	
		<div id="error-container">
			
			<div id="acc-error">
	
						
				<h1>An Has Error Occurred</h1>
				<p>You have encountered a problem with your document management system.</p>
				<p>Please contact your systems administrator.</p>
				<p>For more information on the error click here: <img src="<?php echo $sRootUrl ?>/resources/graphics/info.gif" style="cursor: pointer;" onclick="Click()" /><div id ="exp" style="display: none; "> <?php if(isset($sErrorMessage)){ echo $sErrorMessage;  }else  if(isset($posted)){ echo $posted; } else if($phperror){ echo $phperror; } ?></div></p>
				
			</div>		
		</div>		
				
	</body>
</html>
