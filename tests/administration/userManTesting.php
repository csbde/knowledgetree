<html>
<body>

<?php

require ("./config/dmsDefaults.php");
//require ("$default->owl_fs_root/lib/db.inc");
require ("$default->owl_fs_root/lib/dms.inc");
require ("$default->owl_fs_root/lib/administration/UserManager.inc");

/*-----------------------------------------------------------------*/
/**
 * $Id: UserManTesting.php
  
 * Tests the user management class
 *
 * Tests: - createUser() -> if username exists and if username does'nt exist
 *	  - listUsers() 
 *	  - removeUser()
 *	  - getUserDetails() -> if user exists and if user does'nt exist
 *	  - getuserID()	    -> if username exists or doe'snt
 *	  - addusertogroup
 *	  - removeuserfromgroup
 
 *	  	
 *
 * @version $Revision$ 
 * @author Mukhtar Dharsey
 * @package tests.administration
 */
/*-----------------------------------------------------------------*/



$user = new UserManager;


/////////////////Test insertions
// Reload page to test if insertions fail due to existance of username
echo "<br><br>*** add Users Test ***<br><br>";
$Details = array();

$Details['username'] = "stone cold";
$Details['name'] = "Kurt Angle";
$Details['password'] = "Idontsuck";
$Details['quota_max'] = 11;
$Details['quota_current'] = 6;
$Details['email'] = "kurt@illmakeutap.com";
$Details['mobile'] = 27825328240;
$Details['email_notification'] = 1;
$Details['sms_notification'] = 1;
$Details['ldap_dn'] = 10202020;
$Details['max_sessions'] = 50;
$Details['language'] = $default->owl_lang;

$user->createUser($Details);

/////////////// Test list users 
echo "<br><br>*** List Users Test ***<br><br>";
$test=$user->listUsers();

for( $i=0;$i < count($test); $i++)

	{       
		printf("UserName: %s<br>", $test[$i]['username']);
	}


/////////////// Test add user to group
echo "<br><br>*** Add Users To a Group Test ***<br><br>";
//test add new user to group
$test = $user->addUserToGroup(2,1);
$test = $user->addUserToGroup(2,2);
$test = $user->addUserToGroup(2,3);

// test user already added
$test = $user->addUserToGroup(3,1);

/////////////// Test get groups user belongs 2
echo "<br><br>*** Show users groups test ***<br><br>";
$test = $user->getGroups(2);

for( $i=1;$i < count($test); $i++)
{      
printf("Group ID: %s<br>", $test[$i]['id']);
printf("Group Name: %s<br>", $test[$i]['name']);
}

/////////////// Test remove user from group
echo "<br><br>*** Remove Users from a Group Test ***<br><br>";
$test = $user->removeUserFromGroup(3,1);

////////////// Test Remove User
echo "<br><br>*** Remove User Test ***<br><br>";
$test = $user->removeUser(1);


///////////////////// Test updateuser
echo "<br><br>*** Update User's Details  Test ***<br><br>";
$Details = array();

$Details['username'] = "BookerT";
$Details['name'] = "Goldust";
$Details['password'] = "suckaTrashing";
$Details['quota_max'] = 10;
$Details['quota_current'] = 2;
$Details['email'] = "GoldustandBookerT@themovies.com";
$Details['mobile'] = 0825328240;
$Details['email_notification'] = 1;
$Details['sms_notification'] = 0;


// test user not exist
$test = $user->UpdateUser(7, $Details);

// test user that does exist
$test = $user->UpdateUser(2, $Details);

	
//////////////// Test Get User Details 
echo "<br><br>*** Get User's Details Test ***<br><br>";
// user does'nt exist
$test = $user->GetUserDetails(10);
	
//user does exist
$test = $user->GetUserDetails(2);	

printf("Name: %s<br>", $test[2]['name']);
printf("UserName: %s<br>", $test[2]['username']);
printf("Password: %s<br>", $test[2]['password']);
printf("Quota_Max: %s<br>", $test[2]['quota_max']);
printf("Quota_current: %s<br>", $test[2]['quota_current']);
printf("Email: %s<br>", $test[2]['email']);
printf("Mobile: %s<br>", $test[2]['mobile']);
printf("Email: %s <br>", $test[2]['email_notification']);
printf("Sms: %s<br>", $test[2]['sms_notification']);


/////////////// Test get User ID
echo "<br><br>*** List Users Test ***<br><br>";

// user exists
$test = $user->getUserID("stone cold");

printf("<br>ID: %s<br>", $test);

// user doe'snt exist
$test = $user->getUserID("Winnie Mandela");

?>

</HTML>
</BODY>