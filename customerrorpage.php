<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *  
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

session_start();

// Get the error message
$error = isset($_POST['fatal']) ? $_POST['fatal'] : '';
$error = isset($_SESSION['sErrorMessage']) ? $_SESSION['sErrorMessage'] : $error;
unset($_SESSION['sErrorMessage']);
//Finding root Url
$sHost = $_SERVER['HTTP_HOST'];
$sScriptName = dirname($_SERVER['SCRIPT_NAME']);
$sRoot = $sHost.$sScriptName;
$sLastChar = substr($sScriptName, -1, 1);
$sScriptName = ($sLastChar == '\\' || $sLastChar == '/') ? substr($sScriptName, 0, -1) : $sScriptName;
$bSSLEnabled = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? true : false;
$sRootUrl = ($bSSLEnabled ? 'https://' : 'http://').$sRoot;

$error = strip_tags($error);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
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

				<h1>An Error Has Occurred</h1>
				<p>You have encountered a problem with your document management system.</p>
				<p>Please contact your systems administrator.</p>
				<p>For more information on the error click here: <img src="<?php echo $sRootUrl ?>/resources/graphics/info.gif" style="cursor: pointer;" onclick="Click()" />
				    <div id ="exp" style="display: none; "> <?php echo $error; ?></div>
			    </p>

			</div>
		</div>
	</body>
</html>
