<html>
<body>

<?php

/*-----------------------------------------------------------------*/
/**
 * $Id: UserManTesting.php
  
 * Tests the group management class
 *
 * Tests: - createUser() -> if username exists and if username does'nt exist
 *	  - listUsers() 
 *	  - removeUser()
 *	  - getUserDetails() -> if group exists and if group does'nt exist
 *	  - getuserID()	    -> if username exists or doe'snt
 *	  - addusertogroup
 *	  - removeuserfromgroup
 
 *	  	
 *
 * @version $Revision$ 
 * @author Mukhtar Dharsey
 * @package testing
 */
/*-----------------------------------------------------------------*/


require ("./config/dmsDefaults.php");
//require ("$default->owl_fs_root/lib/db.inc");
require ("$default->owl_fs_root/lib/dms.inc");
require ("$default->owl_fs_root/lib/administration/GroupManager.inc");

$group = new groupManager;


/////////////////Test insertions
// Reload page to test if insertions fail due to existance of username
echo "<br><br>*** add new Group Test ***<br><br>";


$group->createGroup("SpellCheckers");
$group->createGroup("Publishers");
$group->createGroup("Reapers");
$group->createGroup("Politicians");

/////////////// Test list groups
echo "<br><br>*** List Groups Test ***<br><br>";
$test=$group->listGroups();

for( $i=0;$i < count($test); $i++)

	{       
		printf("GroupNames: %s<br>", $test[$i]['name']);
	}


/////////////// Test add group to unit
echo "<br><br>*** Add Group to unit Test ***<br><br>";
//test add new group to group note that 1 group can only belong to 1 unit
$test = $group->addGroupToUnit(2,1);
$test = $group->addGroupToUnit(3,2);
$test = $group->addGroupToUnit(2,3);


// test group already added
// test group already added
$test = $group->addGroupToUnit(2,1);


/////////////// Test get unit 
echo "<br><br>*** Show unit group belongs to test ***<br><br>";
$test = $group->getUnit(2);

printf("unit ID: %s<br>", $test[1]['id']);
printf("unit Name: %s<br>", $test[1]['name']);


/////////////// Test get groups group belongs 2
echo "<br><br>*** Show org unit belongs to ***<br><br>";
$test = $group->getOrg(1);

printf("org ID: %s<br>", $test[1]['id']);
printf("org Name: %s<br>", $test[1]['name']);

/////////////// Test remove group from unit
echo "<br><br>*** Remove group from unit Test ***<br><br>";
$test = $group->removeGroupFromUnit(2,2);

////////////// Test Remove Group
echo "<br><br>*** Remove group Test ***<br><br>";
$test = $group->removeGroup(3);


///////////////////// Test updateGroup
echo "<br><br>*** Update Group Details/ Rename group  Test ***<br><br>";
// test group not exist
$test = $group->updateGroup(4,"baboona");

// test group that does exist
$test = $group->UpdateGroup(12, "charl's glass");

	
//////////////// Test Get Group Name
echo "<br><br>*** Get Group Name Test ***<br><br>";
// group does'nt exist
$test = $group->GetGroupName(15);
	
//group does exist
$test = $group->GetGroupName(4);	

printf("Name: %s<br>", $test);



/////////////// Test getGroupID
echo "<br><br>*** get GroupID Test ***<br><br>";

// group exists
$test = $group->getGroupID('System Administrators');

printf("<br>ID: %s<br>", $test);

// group doe'snt exist
$test = $group->getGroupID("Winnie Mandela");

?>

</HTML>
</BODY>