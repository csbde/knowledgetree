<?php
/**
 * Contains unit tests for all classes
 *
 * Tests are:
 *	o creation of object
 *	o setting/getting of values
 *	o storing of object
 *  o retrieval of object by primary key
 *  o retrieval of an array of objects  
 *	o updating of object
 * 	o deletion of object
 * @package tests.archiving
 */
 
require_once("../../config/dmsDefaults.php");

echo "<pre>";

$aClasses = array("archiving/ArchiveRestorationRequest" => array("DocumentID", "RequestUserID", "AdminUserID", "DateTime"),
				  "archiving/DocumentArchiving" => array("DocumentID", "ArchivingSettingsID"),
             	  "archiving/TimeUnit" => array("Name"),
				  "archiving/ArchivingType" => array("Name"),
             	  "archiving/TimePeriod" => array("TimeUnitID", "Units"),				  
                  "archiving/ArchivingSettings" => array("ArchivingTypeID", "ExpirationDate", "DocumentTransactionID", "TimePeriodID"));
                  
$aInitialValues = array("1,2,3",
						"1,3",
						"hour",
						"\"blah's\"",
						"1, 10",
 						"2, \"2005-15-15\", 5, 1");
 						
$aSetValues = array(array(4,5,6,"2010-10-10"),
					array(9,8,7),
					array("minute"),
					array("fooblar's"),
					array(2,20),
					array(3, "2001-02-03", -1, 4));

                  
$count = 0;
$aTestClasses = array("ArchivingSettings");

foreach ($aClasses as $classPath => $aMethodList) {
	$aClassPath = explode("/", $classPath);
	$className = $aClassPath[count($aClassPath)-1];
	if (in_array($className, $aTestClasses)) {
			
		require_once("$default->fileSystemRoot/lib/$classPath.inc");
	
		$constructor = "\$oClass = new $className($aInitialValues[$count]);"; 		
		echo "<b>$className- $constructor</b><br>";
		echo "<b>Testing creation</b><br>";
		eval($constructor);
		if (isset($oClass)) {
			echo "Passed creation test<br><br>";
			
			echo "<b>Testing storage</b><br>";
			if ($oClass->create()) {
				echo "Passed storage test<br><br>";
				//$oClass->iId = -1;$oClass->create();
			
				echo "<b>Testing setting and getting of values</b><br>";
				$i=0;
				foreach ($aMethodList as $method) {
					$getter = "get$method";
					$setter = "set$method";
					echo "Current $method: " . $oClass->$getter() . "<br>";		
					echo "Setting $method to: " . $aSetValues[$count][$i] . "<br>";
					$oClass->$setter($aSetValues[$count][$i]);		
					echo "New $method: " . $oClass->$getter() . "<br><br>";
					$i++;		
				}
			
				echo "<b>Testing update</b><br>";
				if ($oClass->update()) {
					echo "Passed update test<br><br>";
					
					echo "<b>Testing retrieval</b><br>";
					$get = "\$oNewClass = $className::get(1);";
					eval($get);
					if ($oNewClass) {
						echo "Passed retrieval test:\n" . arrayToString($oNewClass) . "<br>";
					} else {
						echo "Failed retrieval test.<br>";
					}
									
					echo "<b>Testing array retrieval</b><br>";
					$getList = "\$aNewClass = $className::getList();";
					eval($getList);
					echo "array=\n" . arrayToString($aNewClass) . "<br><br>";
								
					echo "<b>Testing deletion</b><br>";
					if ($oClass->delete()) {
						echo "Passed deletion test<br>";
					} else {
						echo "Failed deletion test";
					}
				} else {
					echo "Failed update test<br>";
					echo "Tests NOT run: (a) retrieval by id (b) array list retrieval (c) deletion<br>";
				}			
			} else {
				echo "Failed storage test<br>";
				echo "Tests NOT run: (a) update (b) retrieval by id (c) array list retrieval (d) deletion<br>";
			}
		} else {
			echo "Failed creation test<br>";
			echo "Tests NOT run: (a)getting and setting (b) storage (c) retrieval by id (d) array list retrieval (e) deletion<br>";
		}
	} else {
		echo "skipping $className";
	}
	$count++;
	echo "<hr>";
}
echo "</pre>";

?>