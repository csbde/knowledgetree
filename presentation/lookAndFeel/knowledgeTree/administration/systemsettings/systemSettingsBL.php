<?php
/**
* BL information for adding a Unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("systemSettingsUI.inc");
	require_once("$default->owl_fs_root/lib/security/permission.inc");
	require_once("$default->owl_fs_root/lib/System.inc");
	require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
	require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
	require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->owl_fs_root/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();	
	
	if(isset($fForStore)){
		$oSys = new System;
		
		$aNames = array("ldapServer",
				"ldapRootDn",
				"emailServer",
				"emailFrom",
				"emailFromName",
				"filesystemRoot",
				"documentRoot",
				"languageDirectory",
				"uiDirectory",
				"rootUrl",
				"graphicsUrl",
				"uiUrl",
				"useFs",
				"defaultLanguage",
				"sessionTimeout");
		
		$aValues = array($fldapServer,
				 $fldapRootDn,
				 $femailServer,
				 $femailFrom,
				 $femailFromName,
				 $ffilesystemRoot,
				 $fdocumentRoot,
				 $flanguageDirectory,
				 $fuiDirectory,
				 $frootUrl,
				 $fgraphicsUrl,
				 $fuiUrl,
				 $fuseFs,
				 $fdefaultLanguage,
				 $fsessionTimeout);
		
			
		for($i = 0; $i < count($aNames);$i++){
			
			$oSys->set($aNames[$i], $aValues[$i]);
			//echo "<br>Name: " . $aNames[$i];
			//echo "<br>Value: " . $aValues[$i];
			
		}
		
		
		$oPatternCustom->setHtml(getPage());	
		
	}else{
		
		$oPatternCustom->setHtml(getPage());
		
		$main->setFormAction($_SERVER["PHP_SELF"]. "?fForStore=1");
	}
		
	
	
	
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>
