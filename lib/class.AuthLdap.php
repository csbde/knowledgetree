<?php
/* class.AuthLdap.php , version 0.1
** Mark Round, December 2002 - http://www.markround.com/unix
** Provides LDAP authentication and user functions.
**
** Not intended as a full-blown LDAP access class - but it does provide
** several useful functions for dealing with users.
** Note - this works out of the box on Sun's iPlanet Directory Server - but
** an ACL has to be defined giving all users the ability to change their
** password (userPassword attribute).
** See the README file for more information and examples.
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
** 
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
** 
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class AuthLdap {

  // 1.1 Public properties -----------------------------------------------------

  var $server;	              // Array of server IP address or hostnames
  var $dn;		      // The base DN (e.g. "dc=foo,dc=com")
  var $people = "People";     // Where the user records are kept
  var $groups = "Groups";     // Where the group definitions are kept
  var $ldapErrorCode; 	      // The last error code returned by the LDAP server
  var $ldapErrorText; 	      // Text of the error message

  // 1.2 Private properties ----------------------------------------------------

  var $connection;		// The internal LDAP connection handle
  var $result;			// Result of any connections etc.

  // 2.1 Connection handling methods -------------------------------------------

  function connect() {
    /* 2.1.1 : Connects to the server. Just creates a connection which is used
    ** in all later access to the LDAP server. If it can't connect and bind
    ** anonymously, it creates an error code of -1. Returns true if connected,
    ** false if failed. Takes an array of possible servers - if one doesn't work,
    ** it tries the next and so on.
    */

    foreach ($this->server as $key => $host) {
        echo "key=$key; host=$host<br>";
      $this->connection = ldap_connect( $host);
      if ( $this->connection) {
          echo "connected<br>";
        // Connected, now try binding....
        if ( $this->result=@ldap_bind( $this->connection)) {
            // Bound OK!
            $this->bound = $host;
            return true;
        }
      } else { echo "not connected<br>";}
    }

    $this->ldapErrorCode = -1;
    $this->ldapErrorText = "Unable to connect to any server";
    return false;

  }



  function close() {
    /* 2.1.2 : Simply closes the connection set up earlier.
    ** Returns true if OK, false if there was an error.
    */
    
    if ( !@ldap_close( $this->connection)) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    } else
      return true;
  }



  function bind() {
    /* 2.1.3 : Anonymously binds to the connection. After this is done,
    ** queries and searches can be done - but read-only.
    */

    if ( !$this->result=@ldap_bind( $this->connection)) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    } else
      return true;
  }



  function authBind( $bindDn,$pass) {
    /* 2.1.4 : Binds as an authenticated user, which usually allows for write 
    ** access. The FULL dn must be passed. For a directory manager, this is
    ** "cn=Directory Manager" under iPlanet. For a user, it will be something
    ** like "uid=jbloggs,ou=People,dc=foo,dc=com".
    */
    if ( !$this->result = @ldap_bind( $this->connection,$bindDn,$pass)) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    } else
      return true;
  }

  // 2.2 Password methods ------------------------------------------------------

  function checkPass( $uname,$pass) {
    /* 2.2.1 : Checks a username and password - does this by logging on to the
    ** server as a user - specified in the DN. There are several reasons why
    ** this login could fail - these are listed below.
    */

    /* Construct the full DN, eg:-
    ** "uid=username, ou=People, dc=orgname,dc=com"
    */
    $checkDn = "uid=" .$uname. ", ou=" .$this->people. ", " .$this->dn;
    // Try and connect...
    $this->result = @ldap_bind( $this->connection,$checkDn,$pass);
    if ( $this->result) {
      // Connected OK - login credentials are fine!
      return true;
    } else {
      /* Login failed. Return false, together with the error code and text from
      ** the LDAP server. The common error codes and reasons are listed below :
      ** (for iPlanet, other servers may differ)
      ** 19 - Account locked out (too many invalid login attempts)
      ** 32 - User does not exist
      ** 49 - Wrong password
      ** 53 - Account inactive (manually locked out by administrator)
      */
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    }
  }



  function changePass( $uname,$oldPass,$newPass) {
    /* 2.2.2 : Allows a password to be changed. Note that on most LDAP servers,
    ** a new ACL must be defined giving users the ability to modify their
    ** password attribute (userPassword). Otherwise this will fail.
    */

    $checkDn = "uid=" .$uname. ", ou=" .$this->people. ", " .$this->dn;
    $this->result = @ldap_bind( $this->connection,$checkDn,$oldPass);

    if ( $this->result) {
      // Connected OK - Now modify the password...
      $info["userPassword"] = $newPass;
      $this->result = @ldap_modify( $this->connection, $checkDn, $info);
      if ( $this->result) {
        // Change went OK
        return true;
      } else {
        // Couldn't change password...
        $this->ldapErrorCode = ldap_errno( $this->connection);
        $this->ldapErrorText = ldap_error( $this->connection);
        return false;
      }
    } else {
      // Login failed - see checkPass method for common error codes
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false; 
    }
  }



  function checkPassAge ( $uname) {
    /* 2.2.3 : Returns days until the password will expire.
    ** We have to explicitly state this is what we want returned from the
    ** LDAP server - by default, it will only send back the "basic"
    ** attributes.
    */
    $results[0] = "passwordexpirationtime";
    $this->result = @ldap_search( $this->connection,$this->dn,"uid=".$uname,$results);
    
    if ( !$info=@ldap_get_entries( $this->connection, $this->result)) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    } else {
      /* Now work out how many days remaining....
      ** Yes, it's very verbose code but I left it like this so it can easily 
      ** be modified for your needs.
      */
      $date  = $info[0]["passwordexpirationtime"][0];
      $year  = substr( $date,0,4);
      $month = substr( $date,4,2);
      $day   = substr( $date,6,2);
      $hour  = substr( $date,8,2);
      $min   = substr( $date,10,2);
      $sec   = substr( $date,12,2);

      $timestamp = mktime( $hour,$min,$sec,$month,$day,$year);
      $today	 = mktime();
      $diff 	 = $timestamp-$today;
      return round( ( ( ( $diff/60)/60)/24));
    }
  }

  // 2.3 Group methods ---------------------------------------------------------

  function checkGroup ( $uname,$group) {
    /* 2.3.1 : Checks to see if a user is in a given group. If so, it returns
    ** true, and returns false if the user isn't in the group, or any other
    ** error occurs (eg:- no such user, no group by that name etc.)
    */
    
    $checkDn = "ou=" .$this->groups. ", " .$this->dn;

    // We need to search for the group in order to get it's entry.
    $this->result = @ldap_search( $this->connection, $checkDn, "cn=" .$group);
    $info = @ldap_get_entries( $this->connection, $this->result);

    // Only one entry should be returned(no groups will have the same name)
    $entry = ldap_first_entry( $this->connection,$this->result);

    if ( !$entry) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;  // Couldn't find the group...
    }
    // Get all the member DNs
    if ( !$values = @ldap_get_values( $this->connection, $entry, "uniqueMember")) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false; // No users in the group
    }
    foreach ( $values as $key => $value) {
      /* Loop through all members - see if the uname is there...
      ** Also check for sub-groups - this allows us to define a group as
      ** having membership of another group.
      ** FIXME:- This is pretty ugly code and unoptimised. It takes ages
      ** to search if you have sub-groups.
      */
      list( $cn,$ou) = explode( ",",$value);
      list( $ou_l,$ou_r) = explode( "=",$ou);

      if ( $this->groups==$ou_r) {
        list( $cn_l,$cn_r) = explode( "=",$cn);
	// OK, So we now check the sub-group...
	if ( $this->checkGroup ( $uname,$cn_r)) {
	  return true;
	}
      }

      if ( preg_match( "/$uname/i",$value)) {
        return true;
      }
    }
  }

  // 2.4 Attribute methods -----------------------------------------------------

  function getAttribute ( $uname,$attribute) {
    /* 2.4.1 : Returns an array containing a set of attribute values.
    ** For most searches, this will just be one row, but sometimes multiple
    ** results are returned (eg:- multiple email addresses)
    */
    
    $checkDn = "ou=" .$this->people. ", " .$this->dn;
    $results[0] = $attribute;

    // We need to search for this user in order to get their entry.
    $this->result = ldap_search( $this->connection, $checkDn, "uid=" .$uname, $results);
    $info = ldap_get_entries( $this->connection, $this->result);

    // Only one entry should ever be returned (no user will have the same uid)
    $entry = ldap_first_entry( $this->connection, $this->result);

    if ( !$entry) {
      $this->ldapErrorCode = -1;
      $this->ldapErrorText = "Couldn't find user";
      return false;  // Couldn't find the user...
    }

    // Get all the member DNs
    if ( !$values = @ldap_get_values( $this->connection, $entry, $attribute)) {
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false; // No matching attributes
    }

    // Return an array containing the attributes.
    return $values;
 }



  function setAttribute( $uname, $attribute, $value) {
    /* 2.4.2 : Allows an attribute value to be set.
    ** This can only usually be done after an authenticated bind as a
    ** directory manager - otherwise, read/write access will not be granted.
    */

    // Construct a full DN...
    $attrib_dn = "uid=" .$uname. ",ou=" .$this->people. "," .$this->dn;
    $info[$attribute] = $value;
    // Change attribute
    $this->result = ldap_modify( $this->connection, $attrib_dn, $info);
    if ( $this->result) {
      // Change went OK
      return true;
    } else {
      // Couldn't change password...
      $this->ldapErrorCode = ldap_errno( $this->connection);
      $this->ldapErrorText = ldap_error( $this->connection);
      return false;
    }
  }

  // 2.5 User methods ----------------------------------------------------------

  function getUsers( $search, $attributeArray) {
    /* 2.5.1 : Returns an array containing a details of users, sorted by
    ** username. The search criteria is a standard LDAP query - * returns all
    ** users.  The $attributeArray variable contains the required user detail field names
    */

    // builds the appropriate dn, based on whether $this->people and/or $this->group is set
    $checkDn = $this->setDn( true);
    
    // Perform the search and get the entry handles
    $this->result = ldap_search( $this->connection, $checkDn, "uid=" .$search);
    $info = ldap_get_entries( $this->connection, $this->result);
    for( $i = 0; $i < $info["count"]; $i++){
      // Get the username, and create an array indexed by it...
      // Modify these as you see fit.
      $uname			              = $info[$i]["uid"][0];
      // add to the array for each attribute in my list
      for ( $i = 0; $i < count( $attributeArray); $i++) {
          $userslist["$uname"]["$attributeArray[$i]"]      = $info[$i][strtolower($attributeArra[$i])][0];
      }
    }

    if ( !@asort( $userslist)) {
      /* Sort into alphabetical order. If this fails, it's because there
      ** were no results returned (array is empty) - so just return false.
      */
      $this->ldapErrorCode = -1;
      $this->ldapErrorText = "No users found matching search criteria ".$search;
      return false;
    }
    return $userslist;
  }

} // End of class


?>
