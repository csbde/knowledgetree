<?php


/* owl.lib.php
 *
 *  contains the major owl classes and functions
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
 */
 
/**
 * class Owl_DB extends DB_Sql 
 *
 * This class is used for DB connections
 *
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
 */

class Owl_DB extends DB_Sql {
	
	/** Class name */
    var $classname = "Owl_DB";

    // BEGIN wes changes -- moved these settings to config/owl.php
    // Server where the database resides
	
	/** Host name.  Retrieved from config/owl.php */
    var $Host = ""; 
    /** Database name */
    var $Database = "";
    /** Database user */
    var $User = "";
    /** Database user password */
    var $Password = "";		
	/** Query to execute */
	var $sQuery;
	/** Name of table last query was executed on*/
	var $sLastTableName;
	/** Where clause last used in query execution */
	var $sLastWhereClause;
	/** Order by clause last used in query execution */
	var $sLastOrderByClause;        
	
	/** Default Constructor */
	function Owl_DB() {
	  global $default;
	  $this->Host = $default->owl_db_host;
	  $this->Database = $default->owl_db_name;
	  $this->User = $default->owl_db_user;
	  $this->Password = $default->owl_db_pass;
	}
	// END wes changes
	
	/**
	*	Create a query from the provided paramaters.  The ID column
	*	is seleted by default
	*	
	*	@param $sTableName		Table to query
	*	@param $aColumns		Columns in table
	*	@param $sWhereClause	Where clause (optional)
	*	@param $sOrderByClause	Order by clause (optional)
	*/
	function createSQLQuery($sTableName, $aColumns, $sWhereClause = null, $sOrderByClause = null) {
		$this->sLastTableName = $sTableName;
		$this->sLastWhereCluase = $sWhereClause;
		$this->sLastOrderByClause = $sOrderByClause;
		
		$this->sQuery = "SELECT ID, ";
		
		for( $i = 0; $i < count($aColumns) - 1; $i++ ) {			
			$this->sQuery = $this->sQuery . $aColumns[$i] . ",";
		}
		
		$this->sQuery .= $aColumns[count($aColumns) - 1] . " ";
		$this->sQuery .= "FROM " . $sTableName . " ";
		
		if (isset($sWhereClause)) {
			$this->sQuery .= "WHERE " . $sWhereClause . " ";
		}	
		
		if (isset($sOrderByClause)) {
			$this->sQuery .= "ORDER BY " . $sOrderByClause . " ";
		}
		
	}
	
	/**
		Create a query from the provided paramaters, specifying a limit and an offset.
		The ID column is selected by default
		
		@param $sTableName		Table to query
		@param $aColumns		Columns in table
		@param $iOffset			Offset
		@param $iLimit			Limit
		@param $sWhereClause	Where clause (optional)
		@param $sOrderByClause	Order by clause (optional)
	*/
	
	function createSQLQueryWithOffset($sTableName, $aColumns, $iOffset, $iLimit, $sWhereClause = null, $sOrderByClause = null) {
		$this->sLastTableName = $sTableName;
		$this->sLastWhereCluase = $sWhereClause;
		$this->sLastOrderByClause = $sOrderByClause;
		
		$this->sQuery = "SELECT ID, ";
		
		for( $i = 0; $i < count($aColumns) - 1; $i++ ) {			
			$this->sQuery = $this->sQuery . $aColumns[$i] . ",";			
		}
		
		$this->sQuery .= $aColumns[count($aColumns) - 1] . " ";
		$this->sQuery .= "FROM " . $sTableName . " ";
		
		
		
		if (isset($sWhereClause)) {
			$this->sQuery .= "WHERE " . $sWhereClause . " ";
		}	
		
		if (isset($sOrderByClause)) {
			$this->sQuery .= "ORDER BY " . $sOrderByClause . " ";
		}
		
		$this->sQuery .= "LIMIT " . $iOffset . ", " . $iLimit;		
	}
	
	/**
	*	Get the result count for the previously executed query.  Meant
	*	to be used in conjuction with createSSQLQueryWithOffset so that
	*	the total number of results can be calculated
	*	
	*	@return	int row count
	*/
	function & getLastQueryResultCount() {
		if (isset($this->sLastTableName) {
			$sCountResultQuery = "SELECT COUNT(*) AS ResultCount FROM " . $this->sLastTableName;
			
			if (isset($this->sLastWhereClause)) {
				sCountResultQuery . " WHERE " . $this->sLastWhereClause;
			}
			$sql = & $this->query($sCountResultQuery);
			$sql->next_record();
			return $sql->f("ResultCount");
		} else {
			return 0;
		}
	}
	
	/**
	*	Execute the query and return the results
	*	
	*	@returns 	Results of query
	*/
	function & getQueryResults() {
		$result = null;
		if (isset($this->sQuery)) {
			$result = $this->query($this->sQuery);			
		}
		return $result;
	}
	
   /**
   * Display any database errors encountered
   */
   function haltmsg($msg) {
	   printf("</td></table><b>Database error:</b> %s<br>\n", $msg);
       printf("<b>SQL Error</b>: %s (%s)<br>\n",$this->Errno, $this->Error);
   }
}

/**
 * class Owl_Session
 *
 * This class is used for opening and closing sessions
 *
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
*/
class Owl_Session {
	var $sessid;
	var $sessuid;
	var $sessdata;

//------------------------------------------------------------        
/**
 * Function Open_Session($sessid=0, $sessuid=0)
 *
 * Opens a session
 *
 * @param $sessid
 *	The Session id
 * @param ssessuid
 *	The user session id
 * @Return $this
 * 	Return the session
 * 
*/
//------------------------------------------------------------
// Usable
	function Open_Session($sessid=0, $sessuid=0) {
                global $default;
		$this->sessid = $sessid;
		$this->sessuid = $sessuid;

		// if there is no user loged in, then create a session for them
		if($sessid == "0")
		 { 		
			$current = time();
			$random = $this->sessuid . $current;
			$this->sessid = md5($random);
			$sql = ;
 		
 			if(getenv("HTTP_CLIENT_IP")) 
 			{
                                $ip = getenv("HTTP_CLIENT_IP");
                        }
                         elseif(getenv("HTTP_X_FORWARDED_FOR")) 
                        {
                                $forwardedip = getenv("HTTP_X_FORWARDED_FOR");
                                list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
                        } 
                        else 
                        {
                                $ip = getenv("REMOTE_ADDR");
                        }
			//$result = $sql->query("insert into active_sessions  values ('$this->sessid', '$this->sessuid', '$current', '$ip')");
			$result = $sql->query("insert into $default->owl_sessions_table  values ('$this->sessid', '$this->sessuid', '$current', '$ip')");
			
			if(!'result') 
			{
				die("$lang_err_sess_write");
			}
		}

		// else we have a session id, try to validate it...
		$sql = ;
		$sql->query("select * from $default->owl_sessions_table where sessid = '$this->sessid'");

		// any matching session ids?
		$numrows = $sql->num_rows($sql);
		if(!$numrows) die("$lang_err_sess_notvalid");

		// return if we are a.o.k.
		while($sql->next_record()) {
			$this->sessdata["sessid"] = $sql->f("sessid");
		}
		return $this;
	}
}


//------------------------------------------------------------        
/**
 * Function notify_users($groupid, $flag, $parent, $filename, $title, $desc, $type)
 *
 * Used to notify users 
 *
 *   @param $groupid
 *	The Id of the group
 *   @param $flag
 *	The relvant flag
 *   @param $filename
 *	The relevant filename
 *   @param $title
 *	The relevant title
 *   @param $desc
 *	The description
 *   @param $type
 *	the Relevant type
 */
//-------------------------------------------------------------	
// Semi-Usable Some Interface based code
function notify_users($groupid, $flag, $parent, $filename, $title, $desc, $type) 
{
                global $default;
                global $lang_notif_subject_new, $lang_notif_subject_upd, $lang_notif_msg;
                global $lang_title, $lang_description;
                $sql = ; 
// BEGIN BUG 548994
                // get the fileid
                $path = find_path($parent);
		$sql->query("select id from $default->owl_files_table where filename='$filename' AND parent='$parent'");
		$sql->next_record();
		$fileid = $sql->f("id");
// END BUG 548994 More Below
                $sql->query("select distinct id, email,language,attachfile from $default->owl_users_table as u, $default->owl_users_grpmem_table as m where notify = 1 and (u.groupid = $groupid or m.groupid = $groupid)");
                
                // loop through records
                while($sql->next_record()) 
                {
// BEGIN BUG 548994
			// check authentication rights
			if ( check_auth($fileid, "file_download", $sql->f(id)) == 1 )
			 {
// END BUG 548994 More Below
                  		$newpath = ereg_replace(" ","%20",$path);
                  		$newfilename = ereg_replace(" ","%20",$filename);
				$DefUserLang = $sql->f("language");
 				require("$default->owl_fs_root/locale/$DefUserLang/language.inc");

				$r=preg_split("(\;|\,)",$sql->f("email"));
				reset ($r);
				while (list ($occ, $email) = each ($r)) 
				{
           				$mail = new phpmailer();
                                	// Create a temporary session id, the user
					// will need to get to this file before
					// the default session timeout
					$session = new Owl_Session;
                			$uid = $session->Open_Session(0,$sql->f("id"));
                                        $tempsess = $uid->sessdata["sessid"];
					
					// if flag set to 0
                  			if ( $flag == 0 ) {
                				$mail->IsSMTP();                                      // set mailer to use SMTP
                				$mail->Host = "$default->owl_email_server";        // specify main and backup server
                				$mail->From = "$default->owl_email_from";
                				$mail->FromName = "$default->owl_email_fromname";
                        			$mail->AddAddress($email);
                        			$mail->AddReplyTo("$default->owl_email_replyto", "OWL Intranet");
                				$mail->WordWrap = 50;                                 // set word wrap to 50 characters
                				$mail->IsHTML(true);                                  // set email format to HTML
                				$mail->Subject = "$lang_notif_subject_new";
					
						// as long as its not a url
						if ($type != "url") 
						{
							// if attachfile is true
                                                        if ($sql->f("attachfile") == 1)
                                                         {
                                                                $mail->Body    = "$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>$lang_description: $desc";
                        					$mail->altBody = "$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
								
								// use file system
								if (!$default->owl_use_fs)
								 {
								 	//check if file exits
									if (file_exists("$default->owl_FileDir/$filename"))
									{
                                        					unlink("$default->owl_FileDir/$filename");
                                					}
                                					$file = fopen("$default->owl_FileDir$filename", 'wb');
									$getfile = ;	
                                					$getfile->query("select data,compressed from $default->owl_files_data_table where id='$fileid'");
                                					while ($getfile->next_record()) 
                                					{
                                						//check if compressed ..and uncompress it
                                        					if ($getfile->f("compressed")) {
					
                                                					$tmpfile = $default->owl_FileDir . "owltmp.$fileid.gz";
                                                					$uncomptmpfile = $default->owl_FileDir . "owltmp.$fileid";
                                                					if (file_exists($tmpfile)) unlink($tmpfile);
					
                                                					$fp=fopen($tmpfile,"w");
                                                					fwrite($fp, $getfile->f("data"));
                                                					fclose($fp);

                                                					system($default->gzip_path . " -df $tmpfile");

                                                					$fsize = filesize($uncomptmpfile);
                                                					$fd = fopen($uncomptmpfile, 'rb');
                                                					$filedata = fread($fd, $fsize);
                                                					fclose($fd);

                                                					fwrite($file, $filedata);
                                                					unlink($uncomptmpfile);
                                        					} 
                                        					else
                                        					{	// otherwise just write the file
                                                					fwrite($file, $getfile->f("data"));
                                        					}
                                					}
                                					fclose($file);
                                					// add a mail attachment
                        						$mail->AddAttachment("$default->owl_FileDir$newfilename");
								} else 
								{
                        						$mail->AddAttachment("$default->owl_FileDir/$newpath/$newfilename");
								}
                                                        }
                                                        else 
                                                        {	// set up mail body 
                                                                $mail->Body    = "$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>URL:  $default->owl_notify_link" . "browse.php?sess=$tempsess&parent=$parent&expand=1&fileid=$fileid" . "<BR><BR>$lang_description: $desc";
                        					$mail->altBody = "$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
                                                        }
                				}
                				else 
                				{
                        				$mail->Body    = "URL: $newfilename <BR><BR>$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>$lang_description: $desc";
                        				$mail->altBody = "URL: $newfilename \n\n$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
                				}

                                 	}
                  			else 
                  				// set up mailer
                  			{
 						$mail = new phpmailer();
                                                $mail->IsSMTP();                                      // set mailer to use SMTP
                                                $mail->Host = "$default->owl_email_server";        // specify main and backup server
                                                $mail->From = "$default->owl_email_from";
                                                $mail->FromName = "$default->owl_email_fromname";
                                                $mail->AddAddress($email);
                                                $mail->AddReplyTo("$default->owl_email_replyto", "OWL Intranet");
                                                $mail->WordWrap = 50;                                 // set word wrap to 50 characters
                                                $mail->IsHTML(true);                                  // set email format to HTML
						$mail->Subject = "$lang_notif_subject_upd";
                                        
                                        	// if type not a url
                                                if ($type != "url") 
                                                {
                                                	// if attachfile is true..go through process of attaching file..simarly to previous
                                                        if ($sql->f("attachfile") == 1)
                                                         {
                                                                $mail->Body    = "$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>$lang_description: $desc";
                                                                $mail->altBody = "$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
								if (!$default->owl_use_fs) 
								{
									// check existence of file
                                                                        if (file_exists("$default->owl_FileDir/$filename")) 
                                                                        {
                                                                                unlink("$default->owl_FileDir/$filename");
                                                                        }
                                                                        
                                                                        $file = fopen("$default->owl_FileDir$filename", 'wb');
                                                                        $getfile = ;
                                                                        $getfile->query("select data,compressed from $default->owl_files_data_table where id='$fileid'");
                                                                        
                                                                        // get file check if compressed, if so uncompress 
                                                                        // otherwise write away
                                                                        while ($getfile->next_record()) 
                                                                        {
                                                                                if ($getfile->f("compressed")) {

                                                                                        $tmpfile = $default->owl_FileDir . "owltmp.$fileid.gz";
                                                                                        $uncomptmpfile = $default->owl_FileDir . "owltmp.$fileid";
                                                                                        if (file_exists($tmpfile)) unlink($tmpfile);

                                                                                        $fp=fopen($tmpfile,"w");
                                                                                        fwrite($fp, $getfile->f("data"));
                                                                                        fclose($fp);

                                                                                        system($default->gzip_path . " -df $tmpfile");

                                                                                        $fsize = filesize($uncomptmpfile);
                                                                                        $fd = fopen($uncomptmpfile, 'rb');
                                                                                        $filedata = fread($fd, $fsize);
                                                                                        fclose($fd);

                                                                                        fwrite($file, $filedata);
                                                                                        unlink($uncomptmpfile);
                                                                                } 
                                                                                else 
                                                                                {
                                                                                        fwrite($file, $getfile->f("data"));
                                                                                }
                                                                        }
                                                                        fclose($file);
                                                                        $mail->AddAttachment("$default->owl_FileDir$newfilename");
                                                                }
                                                                 else 
                                                                {
                                                                        $mail->AddAttachment("$default->owl_FileDir/$newpath/$newfilename");
                                                                }

                                                        }
                                                        else 
                                                        {
                                                                $mail->Body    = "$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>URL:  $default->owl_notify_link" . "browse.php?sess=$tempsess&parent=$parent&expand=1&fileid=$fileid" . "<BR><BR>$lang_description: $desc";
                                                                $mail->altBody = "$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
                                                        } 
                                                }
                                                else 
                                                {
                                                        $mail->Body    = "URL: $newfilename <BR><BR>$lang_notif_msg<BR><BR>" . "$lang_title: $title" . "<BR><BR>$lang_description: $desc";
                                                        $mail->altBody = "URL: $newfilename \n\n$lang_notif_msg\n\n" . "$lang_title: $title" . "\n\n $lang_description: $desc";
                                                }
                                     	}
                                     	// send the email
                  			$mail->Send();
					if (!$default->owl_use_fs && $sql->f("attachfile") == 1) 
					{
                        			unlink("$default->owl_FileDir$newfilename");
					}
					
                               }
                            }
// BEGIN BUG 548994
                	}
// END BUG 548994
}

//------------------------------------------------------------        
/**
 * Function verify_login($username, $password) 
 *
 * Used to verify a users login name and password
 *
 *   @param $username
 *	The username to verfiy
 *   @param $password
 *	The password to verify
 */
//-------------------------------------------------------------
// Usable 
function verify_login($username, $password) 
{
	global $default;
	$sql = ; 
	$query = "select * from $default->owl_users_table where username = '$username' and password = '" . md5($password) . "'";
	$sql->query("select * from $default->owl_users_table where username = '$username' and password = '" . md5($password) . "'");
	$numrows = $sql->num_rows($sql);
        // Bozz Begin added Password Encryption above, but for now 
        // I will allow admin to use non crypted password untile he 
        // upgrades all users 
	if ($numrows == "1") 
	{
               	while($sql->next_record()) {
                       if ( $sql->f("disabled") == 1 )  
                        	$verified["bit"]        = 2;
                       else
                        	$verified["bit"]        = 1;
                        $verified["user"]       = $sql->f("username");
                        $verified["uid"]        = $sql->f("id");
                        $verified["group"]      = $sql->f("groupid");
                        $maxsessions            = $sql->f("maxsessions") + 1;
                }
        }
        // Remove this else in a future version
        else {
        	// username admin check password 
           if ($username == "admin") 
           {
	        $sql->query("select * from $default->owl_users_table where username = '$username' and password = '$password'");
                $numrows = $sql->num_rows($sql);
                if ($numrows == "1") 
                {
                   while($sql->next_record()) 
                   {
                        $verified["bit"]        = 1;
                        $verified["user"]       = $sql->f("username");
                        $verified["uid"]        = $sql->f("id");
                        $verified["group"]      = $sql->f("groupid");
                        $maxsessions            = $sql->f("maxsessions") + 1;
                   }
                }
           }
        }

        // remove stale sessions from the database for the user
        // that is signing on.
        //
        $time = time() -  $default->owl_timeout;
	$sql = ; $sql->query("delete from $default->owl_sessions_table where uid = '".$verified["uid"]."' and lastused <= $time ");
        // Check if Maxsessions has been reached
        //

	$sql = ; 
        $sql->query("select * from $default->owl_sessions_table where uid = '".$verified["uid"]."'");

	if ($sql->num_rows($sql) >= $maxsessions && $verified["bit"] != 0) {
          if ( $verified["group"] == 0)
	  	$verified["bit"] = 1;
          else
	  	$verified["bit"] = 3;
	}
	return $verified;
}

//------------------------------------------------------------        
/**
 * Function verify_session($username, $password) 
 *
 * Used to verify a users session
 *
 *   @param $username
 *	The username to check
 *   @param $password
 *	The password to check
 */
//-------------------------------------------------------------
// Usable 

function verify_session($sess) {
        getprefs();
	global $default, $lang_sesstimeout, $lang_sessinuse, $lang_clicklogin;
        $sess = ltrim($sess);
	$verified["bit"] = 0;
	
	$sql = ; 
        $sql->query("select * from $default->owl_sessions_table where sessid = '$sess'");
	$numrows = $sql->num_rows($sql);
	$time = time();
	
	if ($numrows == "1")
	{
		while($sql->next_record()) 
		{
			if(getenv("HTTP_CLIENT_IP"))
			{
				$ip = getenv("HTTP_CLIENT_IP");
			} 
			elseif(getenv("HTTP_X_FORWARDED_FOR")) 
			{
				$forwardedip = getenv("HTTP_X_FORWARDED_FOR");
				list($ip,$ip2,$ip3,$ip4)= split (",", $forwardedip);
			}
			 else 
			{
				$ip = getenv("REMOTE_ADDR");
			}
			if ($ip == $sql->f("ip")) 
			{
				// if timeout not exceeded
				if(($time - $sql->f("lastused")) <= $default->owl_timeout) 
				{
					$verified["bit"] = 1;
					$verified["userid"] = $sql->f("uid");
					$sql->query("select * from $default->owl_users_table where id = '".$verified["userid"]."'");
					while($sql->next_record()) $verified["groupid"] = $sql->f("groupid");
				} 
				else 
				{
                                        // Bozz Bug Fix begin
                                        if (file_exists("./lib/header.inc")) 
                                        {
					    include("./lib/header.inc");
                                        } else {
                                           include("../lib/header.inc");
                                        }
                                        // Bozz Buf Fix End
					print("<BR><BR><CENTER>".$lang_sesstimeout);
                                        if ($parent == "" || $fileid == "")
						print("<A HREF='$default->owl_root_url/index.php'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
                                        else
						print("<A HREF='$default->owl_root_url/index.php?parent=$parent&fileid=$fileid'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
					exit();
				}
			} else {
                                // Bozz Bug Fix begin
                                if (file_exists("./lib/header.inc")) {
				    include("./lib/header.inc");
                                } else {
                                    include("../lib/header.inc");
                                }
                                // Bozz Bug Fix End
				print("<BR><BR><CENTER>".$lang_sessinuse);
				exit;
			}
		}
	}
	return $verified;
}

//------------------------------------------------------------        
/**
 * Function fid_to_name($parent) 
 *
 * used to get the folder name
 *
 *   @param $parent
 *	The parent id
 *   @Return $sql->f("name");
 *	Return the name of the folder	
*/
//-------------------------------------------------------------
// Usable 
function fid_to_name($parent) 
{
	global $default;
	$sql = ; $sql->query("select name from $default->owl_folders_table where id = $parent");
	while($sql->next_record()) 
	{
		return $sql->f("name");
	}
}

//------------------------------------------------------------        
/**
 * Function flid_to_name($id) 
 *
 *  Gets the filename corresponding to the file id
 *
 *   @param $id
 *	The file  id
 *   @Return $sql->f("name");
 *	Return the name of the file	
*/
//-------------------------------------------------------------
// Usable 
function flid_to_name($id) 
{
	global $default;
	$sql = ; $sql->query("select name from $default->owl_files_table where id = $id");
	while($sql->next_record()) 
	{
		return $sql->f("name");
	}
}

//------------------------------------------------------------        
/**
 * Function fid_to_filename($id) 
 *
 * gets filename based on id
 *
 *   @param $id
 *	file id
 *   @Return $sql->f("name");
 *	Return the name of the file
*/
//-------------------------------------------------------------
// Usable 
function flid_to_filename($id) {
	global $default;
	$sql = ; $sql->query("select filename from $default->owl_files_table where id = $id");
	while($sql->next_record()) 
	{
		return $sql->f("filename");
	}
}
//------------------------------------------------------------        
/**
 * Function owlusergroup($userid) 
 *
 * Gets the group id that the user blongs to
 *
 *   @param $userid
 *	The user id
 *   @Return $groupid
 *	Return the groupId
*/
//-------------------------------------------------------------
// Usable 
function owlusergroup($userid) 
{
	global $default;
	$sql = ; $sql->query("select groupid from $default->owl_users_table where id = '$userid'");
	while($sql->next_record()) 
	{
		$groupid = $sql->f("groupid");
		return $groupid;
	}
}
//------------------------------------------------------------        
/**
 * Function owlfilecreator($fileid) 
 *
 * used to find the file creator
 *
 *   @param $fileid
 *	The parent id
 *   @return $filecreator
 *	Return the creatorid of the file
*/
//-------------------------------------------------------------
// Usable 
function owlfilecreator($fileid) {
	global $default;
	$sql = ; $sql->query("select creatorid from ".$default->owl_files_table." where id = '$fileid'");
	while($sql->next_record()) 
	{
		$filecreator = $sql->f("creatorid");
		return $filecreator;
	}
}
//------------------------------------------------------------        
/**
 * Function owlfoldercreator($fileid) {
 *
 * Used to get the folder creator
 *
 *   @param $fileid
 *	The file id
 *   @Return $foldercreator
 *	Return the creatorid of the folder	
*/
//-------------------------------------------------------------
// Usable 
function owlfoldercreator($folderid) 
{
	global $default;
	$sql = ; $sql->query("select creatorid from ".$default->owl_folders_table." where id = '$folderid'");
	while($sql->next_record()) 
	{
		$foldercreator = $sql->f("creatorid");
		return $foldercreator;
	}
}
//-------------------------------------------------------------
/**
 * Function owlfilegroup($fileid)
 *
 * Used to get the file group id
 *
 *   @param $fileid
 *	The file id
 *   @Return $filegroup;
 *	Returns the group id of the file group
*/
//-------------------------------------------------------------
// Usable 
function owlfilegroup($fileid)
 {
	global $default;
	$sql = ; $sql->query("select groupid from $default->owl_files_table where id = '$fileid'");
	while($sql->next_record())
	{
		 $filegroup = $sql->f("groupid");
		 return $filegroup;
	}
	
}
//-------------------------------------------------------------
/**
 * Function owlfoldergroup($folderid)
 *
 * Used to get the folder group id
 *
 *   @param $folderid
 *	The folder id
 *   @Return $foldergroup;
 *	Returns the group id of the folder group
*/
//-------------------------------------------------------------
// Usable  
function owlfoldergroup($folderid) {
	global $default;
	$sql = ; $sql->query("select groupid from $default->owl_folders_table where id = '$folderid'");
	while($sql->next_record()) 
	{
		$foldergroup = $sql->f("groupid");
		return $foldergroup;
	}
	
}
//-------------------------------------------------------------
/**
 * Function owlfolderparent($folderid)
 *
 * Used to get the folder parent
 *
 *   @param $folderid
 *	The folder id
 *   @Return $folderparent
 *	Returns the folderparent of from the folder
*/
//-------------------------------------------------------------
// Usable 
function owlfolderparent($folderid)
 {
	global $default;
	$sql = ; $sql->query("select parent from $default->owl_folders_table where id = '$folderid'");
	while($sql->next_record()) 
	{
		$folderparent = $sql->f("parent");
		return $folderparent;
	}
	
}
//-------------------------------------------------------------
/**
 * Function owlfileparent($fileid)
 *
 * Used to get the file parent
 *
 *   @param $fileid
 *	The file id
 *   @Return $fileparent
 *	Returns the file parent of from the files
*/
//-------------------------------------------------------------
// Usable 
function owlfileparent($fileid) 
{
	global $default;
	$sql = ; $sql->query("select parent from $default->owl_files_table where id = '$fileid'");
	while($sql->next_record()) 
	{
		$fileparent = $sql->f("parent");
	
	return $fileparent;
	}
}
//------------------------------------------------------------        
/**
 * Function fid_to_creator($id) 
 *
 * Used to get the creator of the files
 *
 *   @param $id
 *	The id
 *   @Return $name;
 *	Return the name of the creator
*/
//-------------------------------------------------------------
// Usable 
function fid_to_creator($id) {

	global $default;
	$sql = ; 
	$sql->query("select creatorid from ".$default->owl_files_table." where id = '$id'");
	$sql2 = ; 
	while($sql->next_record()) 
	{
		$creatorid = $sql->f("creatorid");
		$sql2->query("select name from $default->owl_users_table where id = '".$creatorid."'");
		$sql2->next_record();
		$name = $sql2->f("name");
	}
	return $name;
}
//------------------------------------------------------------        
/**
 * Function group_to_name($id) 
 *
 * select name from the group
 *
 *   @param $id
 *	The id
 *   @Return $sql->f("name");
 *	Return the name of the group
*/
//-------------------------------------------------------------
// Usable 
function group_to_name($id) 
{
	global $default;
	$sql = ; 
	$sql->query("select name from $default->owl_groups_table where id = '$id'");
	while($sql->next_record()) 
	{
		return $sql->f("name");
	}
}
//------------------------------------------------------------        
/**
 * Function uid_to_name($id) 
 *
 *  name from the users
 *
 *   @param $id
 *	The id
 *   @Return $name
 *	Return the name of the user
*/
//-------------------------------------------------------------
// Usable 
function uid_to_name($id) 
{
	global $default;
	$sql = ; 
	$sql->query("select name from $default->owl_users_table where id = '$id'");
	while($sql->next_record()) 
	{
		$name = $sql->f("name");
		if ($name == "") 
		{
			$name = "Owl";
		}
		return $name;
	}
}
//------------------------------------------------------------        
/**
 * Function prefaccess($id) 
 *
 * get the noprefaccess from the users to compare if access granted
 *
 *   @param $id
 *	The id
 *   @Return prefaccess;
 *	Return the name of the folder	
*/
//-------------------------------------------------------------
// Usable 

function prefaccess($id) {
	global $default;
	$prefaccess = 1;
	$sql = ; $sql->query("select noprefaccess from $default->owl_users_table where id = '$id'");
	while($sql->next_record()) 
	{
		$prefaccess = !($sql->f("noprefaccess"));
		return $prefaccess;
	}
}
//------------------------------------------------------------        
/**
 * Function gen_navbar($parent) 
 *
 * Used to generate a nav bar
 *
 *   @param $parent
 *	The parent id
 *   @Return $Navbar
 *	Return the navbar that has been generated
*/
//-------------------------------------------------------------
// NOT Usable -> Interface based
function gen_navbar($parent) 
{
	global $default;
	global $sess, $expand, $sort, $sortorder, $order;
	$name = fid_to_name($parent);
	$navbar = "<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sort'>$name</A>";
	$new = $parent;
	while ($new != "1")
	 {
		$sql = ; $sql->query("select parent from $default->owl_folders_table where id = '$new'");
		while($sql->next_record()) $newparentid = $sql->f("parent");
		$name = fid_to_name($newparentid);
		$navbar = "<A HREF='browse.php?sess=$sess&parent=$newparentid&expand=$expand&order=$order&$sortorder=$sort'>$name</A>/" . $navbar;
		$new = $newparentid;
	}
	return $navbar;
}
//------------------------------------------------------------        
/**
 * Function get_dirpath($parent) 
 *
 * Get the directory path from the db
 *
 *   @param $parent
 *	The parent id
 *   @Return $Navbar;
 *	Return the navbar with directory path
*/
//-------------------------------------------------------------
// NOT Usable  if used in ocnjunction with navbar
//only get dir path from db
function get_dirpath($parent) {
        global $default;
        global $sess, $expand;
        $name = fid_to_name($parent);
        $navbar = "$name";
        $new = $parent;
        while ($new != "1") {
                $sql = ; $sql->query("select parent from $default->owl_folders_table where id = '$new'");
                while($sql->next_record()) $newparentid = $sql->f("parent");
                $name = fid_to_name($newparentid);
                $navbar = "$name/" . $navbar;
                $new = $newparentid;
        }
        return $navbar;
}

//------------------------------------------------------------        
/**
 * Function gen_filesze($filesize) 
 *
 * generates the file size
 *
 *   @param $filesize
 *	The size of the file
 *   @Return $file_size;
 *	Return the rounded off file size
*/
//-------------------------------------------------------------
// Usable 
function gen_filesize($file_size) 
{
	if(ereg("[^0-9]", $file_size))
	{
		 return $file_size;
	}

	if ($file_size >= 1073741824) 
	{
		$file_size = round($file_size / 1073741824 * 100) / 100 . "g";
        } 
        elseif ($file_size >= 1048576) 
        {
                $file_size = round($file_size / 1048576 * 100) / 100 . "m";
        } 
        elseif ($file_size >= 1024) 
        {
                $file_size = round($file_size / 1024 * 100) / 100 . "k";
        } 
        else 
        {
                $file_size = $file_size . "b"; 
        }
	return $file_size;
}
//------------------------------------------------------------        
/**
 * Function unloadCompat($varname) 
 *
 * used to upload 
 *
 *   @param $varname
 *	The parent id
 *   @Return $sql->f("name");
 *	Return the name of the folder	
*/
//-------------------------------------------------------------
// Usable 
function uploadCompat($varname) {

   if ($_FILES[$varname]) return $_FILES[$varname];
   if ($HTTP_POST_FILES[$varname]) return $HTTP_POST_FILES[$varname];
   $tmp = "$varname_name"; global $$tmp; $retfile['name'] = $$tmp;
   $tmp = "$varname_type"; global $$tmp; $retfile['type'] = $$tmp;
   $tmp = "$varname_size"; global $$tmp; $retfile['size'] = $$tmp;
   $tmp = "$varname_error"; global $$tmp; $retfile['error'] = $$tmp;
   $tmp = "$varname_tmp_name"; global $$tmp; $retfile['tmp_name'] = $$tmp;
   return $retfile;
}

//------------------------------------------------------------        
/**
 * Function checkrequirements() 
 *
 * Used to check requirments
 *
 *   @Return 1
 *	Returns 1
*/
//-------------------------------------------------------------
// Usable 
function checkrequirements()
{
    global $default, $lang_err_bad_version_1, $lang_err_bad_version_2, $lang_err_bad_version_3;

    if (substr(phpversion(),0,5) < $default->phpversion) 
    {
        print("<CENTER><H3>$lang_err_bad_version_1<BR>");
        print("$default->phpversion<BR>");
        print("$lang_err_bad_version_2<BR>");
        print phpversion();
        print("<BR>$lang_err_bad_version_3</H3></CENTER>");
        return 1; 
    }
    else 
    {
        return 0;
    }
}
//------------------------------------------------------------        
/**
 * Function myExec($cmd, &$lines, &$errco) 
 *
 * 
 *
 *   @param $cmd
 *	The command
 *   @param $lines
 *
 *   @param $errco
 *	The error code
 *   @Return "";
 *	Return empty string
 *   @Return $lines[count($lines)-1]
 *	Returns numba of lines
*/
//-------------------------------------------------------------
// Usable 
function myExec($_cmd, &$lines, &$errco) 
{
	$cmd = "$_cmd ; echo $?";
	exec($cmd, $lines);
	// Get rid of the last errco line...
	$errco = (integer) array_pop($lines);
	if (count($lines) == 0) 
	{
		return "";
	} 
	else 
	{
		return $lines[count($lines) - 1];
	}
}
//------------------------------------------------------------        
/**
 * Function my_delete($file)
 *
 * used to delete a file if it exists
 *
 *   @param $file
 *	The file to be deleted
*/
//-------------------------------------------------------------
// Usable 
function myDelete($file) {
	if (file_exists($file)) 
	{
		chmod($file,0777);
		if (is_dir($file))
		 {
			$handle = opendir($file);
			while($filename = readdir($handle)) 
			{
				if ($filename != "." && $filename != "..") 
				{
					myDelete($file."/".$filename);
				}
			}
			closedir($handle);
			rmdir($file);
		} 
		else 
		{
			unlink($file);
		}
	}
}
//------------------------------------------------------------        
/**
 * Function printError($message, $submessage) 
 *
 * Prints out error messages
 *
 *   @param $message
 *	The message 
 *   @param $submessage
 *	The submessage
*/
//-------------------------------------------------------------
// Not Usable -> INTERFACE Based
function printError($message, $submessage) {
	global $default;
        global $sess, $parent, $expand, $order, $sortorder ,$sortname, $userid;
        global $language;
 
	require("$default->owl_fs_root/locale/$default->owl_lang/language.inc");
	include("./lib/header.inc");

	if(check_auth($parent, "folder_view", $userid) != "1") {
 		$sql = ;
                $sql->query("select * from $default->owl_folders_table where id = '$parent'");
                $sql->next_record();
                $parent = $sql->f("parent");
	}

	echo("<TABLE WIDTH=$default->table_expand_width BGCOLOR=\"#d0d0d0\" CELLSPACING=0 CELLPADDING=0 BORDER=0 HEIGHT=30>");
	echo("<TR><TD ALIGN=LEFT>");
	print("$lang_user: ");
	print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand&order=$order&sortname=$sortname'>");
	print uid_to_name($userid);
	print ("</A><FONT SIZE=-1>");
	print("<A HREF='index.php?login=logout&sess=$sess'> $lang_logout</A>");
	print("</FONT></TD>");
	print("<TD ALIGN=RIGHT><A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>");
	print("</TD></TR></TABLE><BR><BR><CENTER>");
	print $message;
        print("<BR>");
	print $submessage;
	include("./lib/footer.inc");
        exit();
}
//------------------------------------------------------------        
/**
 * Function getprefs() 
 *
 * gets all the preferences
 *
*/
//-------------------------------------------------------------
// Usable 
function getprefs ( )
{
	global $default;

	$sql = ;
	//$sql->query("select * from $default->owl_prefs_table");
	$sql->query("select * from prefs");
	$sql->next_record();

	$default->owl_email_from     	= $sql->f("email_from");
	$default->owl_email_fromname   	= $sql->f("email_fromname");
	$default->owl_email_replyto    	= $sql->f("email_replyto");
	$default->owl_email_server     	= $sql->f("email_server");
	$default->owl_LookAtHD     	= $sql->f("lookathd");
	$default->owl_def_file_security	= $sql->f("def_file_security");
	$default->owl_def_file_group_owner= $sql->f("def_file_group_owner");
	$default->owl_def_file_owner    = $sql->f("def_file_owner");
	$default->owl_def_file_title    = $sql->f("def_file_title");
	$default->owl_def_file_meta     = $sql->f("def_file_meta");
	$default->owl_def_fold_security	= $sql->f("def_fold_security");
	$default->owl_def_fold_group_owner= $sql->f("def_fold_group_owner");
	$default->owl_def_fold_owner   	= $sql->f("def_fold_owner");
	$default->max_filesize     	= $sql->f("max_filesize");
	$default->owl_timeout     	= $sql->f("timeout");
	$default->expand       		= $sql->f("expand");
	$default->owl_version_control  	= $sql->f("version_control");
	$default->restrict_view       	= $sql->f("restrict_view");
	$default->dbdump_path 		= $sql->f("dbdump_path");
	$default->gzip_path 		= $sql->f("gzip_path");
	$default->tar_path 		= $sql->f("tar_path");


};

//------------------------------------------------------------        
/**
 * Function gethtmlprefs() 
 *
 * get html preferences
 *
*/
//-------------------------------------------------------------
// Usable 

function gethtmlprefs ( )
{
	global $default;

	$sql = ;
	$sql->query("select * from $default->owl_html_table");
	$sql->next_record();

	$default->table_border          = $sql->f("table_border");
	$default->table_header_bg       = $sql->f("table_header_bg");
	$default->table_cell_bg         = $sql->f("table_cell_bg");
	$default->table_cell_bg_alt     = $sql->f("table_cell_bg_alt");
	$default->table_expand_width    = $sql->f("table_expand_width");
	$default->table_collapse_width  = $sql->f("table_collapse_width");
	$default->main_header_bgcolor	= $sql->f("main_header_bgcolor");
	$default->body_bgcolor          = $sql->f("body_bgcolor");
	$default->body_textcolor        = $sql->f("body_textcolor");
	$default->body_link             = $sql->f("body_link");
	$default->body_vlink            = $sql->f("body_vlink");

};
//------------------------------------------------------------        
/**
 * Function printfileperm($currentval, $namevariable, $printmessage, $type) 
 *
 * Print file permissions
 *
 *   @param $currentval
 *	The current value
 *   @param $namevariable
 *	The name of the file
 *   @param $pringmessage
 *	The message to be printed
 *   @param $type
 *	The type of file
*/
//-------------------------------------------------------------
// SEMI-Usable Interface based
function printfileperm($currentval, $namevariable, $printmessage, $type) {
	global $default;
	global $lang_everyoneread, $lang_everyonewrite, $lang_everyonewrite_nod, $lang_groupread, $lang_groupwrite, $lang_groupwrite_nod, $lang_groupwrite_worldread, $lang_groupwrite_worldread_nod, $lang_onlyyou;
	global $lang_everyoneread_ad, $lang_everyonewrite_ad, $lang_everyonewrite_ad_nod, $lang_groupread_ad, $lang_groupwrite_ad, $lang_groupwrite_ad_nod, $lang_groupwrite_worldread_ad, $lang_groupwrite_worldread_ad_nod, $lang_onlyyou_ad;


                   $file_perm[0][0] = 0;
                   $file_perm[1][0] = 1;
                   $file_perm[2][0] = 2;
                   $file_perm[3][0] = 3;
                   $file_perm[4][0] = 4;
                   $file_perm[5][0] = 5;
                   $file_perm[6][0] = 6;
                   $file_perm[7][0] = 7;
                   $file_perm[8][0] = 8;
	
	// show admin permissions
	if ($type == "admin")
	 {
        	$file_perm[0][1] = "$lang_everyoneread_ad";
       		$file_perm[1][1] = "$lang_everyonewrite_ad";
       		$file_perm[2][1] = "$lang_groupread_ad";
       		$file_perm[3][1] = "$lang_groupwrite_ad";
       		$file_perm[4][1] = "$lang_onlyyou_ad";
       		$file_perm[5][1] = "$lang_groupwrite_ad_nod";
       		$file_perm[6][1] = "$lang_everyonewrite_ad_nod";
       		$file_perm[7][1] = "$lang_groupwrite_worldread_ad";
       		$file_perm[8][1] = "$lang_groupwrite_worldread_ad_nod";
      	} 
	else {// otherwise show other permissions
        	$file_perm[0][1] = "$lang_everyoneread";
       		$file_perm[1][1] = "$lang_everyonewrite";
       		$file_perm[2][1] = "$lang_groupread";
       		$file_perm[3][1] = "$lang_groupwrite";
       		$file_perm[4][1] = "$lang_onlyyou";
       		$file_perm[5][1] = "$lang_groupwrite_nod";
       		$file_perm[6][1] = "$lang_everyonewrite_nod";
       		$file_perm[7][1] = "$lang_groupwrite_worldread";
       		$file_perm[8][1] = "$lang_groupwrite_worldread_nod";
	}
            
          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$printmessage</TD><TD align=left><SELECT NAME=$namevariable>");
                foreach($file_perm as $fp) {
                        print("<OPTION VALUE=$fp[0] ");
                        if($fp[0] == $currentval)
                                print("SELECTED");
                        print(">$fp[1]");
                }
                           print("</SELECT></TD></TR>");


};
//------------------------------------------------------------        
/**
 * Function printFileIcons ($fid, $filename, $checked_out, $url, $allicons, $ext)
 *
 *prints the file icons
 *
 *   @param $fid
 *	The folder id
 *   @param $filename
 *	The name of the file
 *   @param $check_out
 *	checkout status
 *   @param $url
 *	The relevant url
 *   @param $allicons
 *	
 *   @param $ext
 *	The extension of the file
 *   @Return $sql->f("name");
 *	Return the name of the folder	
*/
//-------------------------------------------------------------
// NOT  Usable INTERFACE based
function printFileIcons ($fid, $filename, $checked_out, $url, $allicons, $ext)
{
	global $default;
	global $sess, $parent, $expand, $order, $sortorder ,$sortname, $userid;
	global $lang_log_file, $lang_reallydelete, $lang_del_file_alt, $lang_mod_file_alt;
	global $lang_move_file_alt,$lang_upd_file_alt,$lang_get_file_alt,$lang_lock_file,$lang_email_alt,$lang_view_file_alt;

	if ($allicons == 1)
	{
		if ($url != "1")
       			print("<a href='log.php?sess=$sess&id=".$fid."&filename=".$filename."&parent=$parent&expand=$expand&order=$order&sortname=$sortname'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/log.gif' BORDER=0 ALT='$lang_log_file' TITLE='$lang_log_file'></a>");
		}

     	if (($checked_out == 0) || ($checked_out == $userid)) {
       		// *****************************************************************************
    		// Don't Show the delete icon if the user doesn't have delete access to the file
    		// *****************************************************************************

    		if (check_auth($fid, "file_delete", $userid) == 1)
        		if ($url == "1")
            			print("\t<A HREF='dbmodify.php?sess=$sess&action=file_delete&type=url&id=".$fid."&parent=$parent&expand=$expand&order=$order&sortname=$sortname'\tonClick='return confirm(\"$lang_reallydelete ".$filename."?\");'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/trash.gif' ALT='$lang_del_file_alt' TITLE='$lang_del_file_alt'\tBORDER=0></A>");
    			else
            			print("\t<A HREF='dbmodify.php?sess=$sess&action=file_delete&id=".$fid."&parent=$parent&expand=$expand&order=$order&sortname=$sortname'\tonClick='return confirm(\"$lang_reallydelete ".$filename."?\");'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/trash.gif' ALT='$lang_del_file_alt' TITLE='$lang_del_file_alt'\tBORDER=0></A>");

		// *****************************************************************************
    		// Don't Show the modify icon if the user doesn't have modify access to the file
    		// *****************************************************************************

    		if(check_auth($fid, "file_modify", $userid) == 1)
            		print("<A HREF='modify.php?sess=$sess&action=file_modify&id=".$fid."&parent=$parent&expand=$expand&order=$order&sortname=$sortname'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/edit.gif' BORDER=0 ALT='$lang_mod_file_alt' TITLE='$lang_mod_file_alt'></A>");

    		// *****************************************************************************
    		// Don't Show the move modify icon if the user doesn't have move access to the file
    		// *****************************************************************************

    		if(check_auth($fid, "file_modify", $userid) == 1)
            		if ($url == "1")
                    		print("<A HREF='move.php?sess=$sess&id=".$fid."&parent=$parent&expand=$expand&action=file&type=url&order=$order&sortname=$sortname'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/move.gif' BORDER=0 ALT='$lang_move_file_alt' TITLE='$lang_move_file_alt'></A>");
            		else
                    		print("<A HREF='move.php?sess=$sess&id=".$fid."&parent=$parent&expand=$expand&action=file&order=$order&sortname=$sortname'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/move.gif' BORDER=0 ALT='$lang_move_file_alt' TITLE='$lang_move_file_alt'></A>");
		// *****************************************************************************
    		// Don't Show the file update icon if the user doesn't have update access to the file
    		// *****************************************************************************

    		if(check_auth($fid, "file_modify", $userid) == 1)
            		if ($url != "1")
                    		print("<A HREF='$default->owl_root_url/modify.php?sess=$sess&expand=$expand&action=file_update&order=$order&sortname=$sortname&id=".$fid."&parent=".$parent."'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/update.gif' BORDER=0 ALT='$lang_upd_file_alt' TITLE='$lang_upd_file_alt'></A>");

    		// *****************************************************************************
    		// Don't Show the file dowload icon if the user doesn't have download access to the file
    		// *****************************************************************************

    		if(check_auth($fid, "file_download", $userid) == 1)
            		if ($url != "1")
                    		print("<A HREF='$default->owl_root_url/download.php?sess=$sess&id=".$fid."&parent=".$parent."&binary=1'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/bin.gif' BORDER=0 ALT='$lang_get_file_alt' TITLE='$lang_get_file_alt'></A>");

		if ($allicons == 1)
		{
    			// *****************************************************************************
    			// Don't Show the lock icon if the user doesn't have access to the file
    			// *****************************************************************************
    			if(check_auth($fid, "file_modify", $userid) == 1)
            			if ($url != "1")
                    			print("<A HREF='dbmodify.php?sess=$sess&action=file_lock&id=".$fid."&parent=$parent&expand=$expand&order=$order&sortname=$sortname'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/lock.gif' BORDER=0 ALT='$lang_lock_file' TITLE='$lang_lock_file'></a>");
			}

    		// *****************************************************************************
    		// Don't Show the email icon if the user doesn't have access to email the file
    		// *****************************************************************************

    		if(check_auth($fid, "file_modify", $userid) == 1)
            		if ($url == "1")
                    		print("<A HREF='$default->owl_root_url/modify.php?sess=$sess&expand=$expand&action=file_email&type=url&order=$order&sortname=$sortname&id=".$fid."&parent=".$parent."'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/email.gif' BORDER=0 ALT='$lang_email_alt' TITLE='$lang_email_alt'></A>");
            		else
                    		print("<A HREF='$default->owl_root_url/modify.php?sess=$sess&expand=$expand&action=file_email&order=$order&sortname=$sortname&id=".$fid."&parent=".$parent."'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/email.gif' BORDER=0 ALT='$lang_email_alt' TITLE='$lang_email_alt'></A>");
	
		// *****************************************************************************
		// Don't Show the view icon if the user doesn't have download access to the file
		// *****************************************************************************
		
		 if(check_auth($fid, "file_download", $userid) == 1)
    			if ($url != "1") {
            			$imgfiles = array("jpg","gif");
            			if ($ext != "" && preg_grep("/$ext/", $imgfiles)) {
                    			print("<A HREF='view.php?sess=$sess&id=".$fid."&parent=$parent&action=image_preview&expand=$expand&order=$order&sortname=$sortname'>&nbsp;<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/mag.jpg' BORDER=0 ALT='$lang_view_file_alt' TITLE='$lang_view_file_alt'></A>");
            			}
            			$htmlfiles = array("html","htm",xml);
            			if ($ext != "" && preg_grep("/$ext/", $htmlfiles)) {
					print("<A HREF='view.php?sess=$sess&id=".$fid."&parent=$parent&action=html_show&expand=$expand&order=$order&sortname=$sortname'>&nbsp;<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/mag.jpg' BORDER=0 ALT='$lang_view_file_alt' TITLE='$lang_view_file_alt'></A>");
            			}
            			$txtfiles = array("txt","text","README", "readme", "sh", "c", "cpp", "php", "php3", "pl", "perl", "sql", "py");
            			if ($ext != "" && preg_grep("/$ext/", $txtfiles)) {
					print("<A HREF='view.php?sess=$sess&id=".$fid."&parent=$parent&action=text_show&expand=$expand&order=$order&sortname=$sortname'>&nbsp;<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/mag.jpg' BORDER=0 ALT='$lang_view_file_alt' TITLE='$lang_view_file_alt'></A>");
            			}
            			if (substr(php_uname(), 0, 7) != "Windows") {
                    			$zipfiles = array("tar.gz", "tgz", "tar", "gz");
                    			if ($ext != "" && preg_grep("/$ext/", $zipfiles))
                            			print("<A HREF='view.php?sess=$sess&id=".$fid."&parent=$parent&action=zip_preview&expand=$expand&order=$order&sortname=$sortname'>&nbsp;<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/mag.jpg' BORDER=0 ALT='$lang_view_file_alt' TITLE='$lang_view_file_alt'></A>");
            			}
    			} 
	}
};

//------------------------------------------------------------        
/**
 * Function printgroupperm($currentval, $namevariable, $printmessage, $type) 
 *
 * Prints group permissions
 *
 *   @param $currentval
 *	The current value
 *   @param $namevariable
 *	The name of the group
 *   @param $printmessage
 *	The message to be printed
 *   @param $type
 *	The type of group
*/
//-------------------------------------------------------------
// NOT Usable INTERFACE based
function printgroupperm($currentval, $namevariable, $printmessage, $type) {
	global $default;
	global $lang_geveryoneread, $lang_geveryonewrite, $lang_geveryonewrite_nod, $lang_ggroupread, $lang_ggroupwrite, $lang_ggroupwrite_nod, $lang_ggroupwrite_worldread, $lang_ggroupwrite_worldread_nod, $lang_gonlyyou;
        global $lang_geveryoneread_ad, $lang_geveryonewrite_ad, $lang_geveryonewrite_ad_nod, $lang_ggroupread_ad, $lang_ggroupwrite_ad, $lang_ggroupwrite_ad_nod, $lang_ggroupwrite_worldread_ad, $lang_ggroupwrite_worldread_ad_nod, $lang_gonlyyou_ad;


                   $group_perm[0][0] = 50;
                   $group_perm[1][0] = 51;
                   $group_perm[2][0] = 52;
                   $group_perm[3][0] = 53;
                   $group_perm[4][0] = 54;
                   $group_perm[5][0] = 55;
                   $group_perm[6][0] = 56;
                   $group_perm[7][0] = 57;
                   $group_perm[8][0] = 58;
	if ($type == "admin")
	 {
                   $group_perm[0][1] = "$lang_geveryoneread_ad";
                   $group_perm[1][1] = "$lang_geveryonewrite_ad";
                   $group_perm[2][1] = "$lang_ggroupread_ad";
                   $group_perm[3][1] = "$lang_ggroupwrite_ad";
                   $group_perm[4][1] = "$lang_gonlyyou_ad";
                   $group_perm[5][1] = "$lang_ggroupwrite_ad_nod";
                   $group_perm[6][1] = "$lang_geveryonewrite_ad_nod";
                   $group_perm[7][1] = "$lang_ggroupwrite_worldread_ad";
                   $group_perm[8][1] = "$lang_ggroupwrite_worldread_ad_nod";

	}
	else
	 {
                   $group_perm[0][1] = "$lang_geveryoneread";
                   $group_perm[1][1] = "$lang_geveryonewrite";
                   $group_perm[2][1] = "$lang_ggroupread";
                   $group_perm[3][1] = "$lang_ggroupwrite";
                   $group_perm[4][1] = "$lang_gonlyyou";
                   $group_perm[5][1] = "$lang_ggroupwrite_nod";
                   $group_perm[6][1] = "$lang_geveryonewrite_nod";
                   $group_perm[7][1] = "$lang_ggroupwrite_worldread";
                   $group_perm[8][1] = "$lang_ggroupwrite_worldread_nod";
	 }

          print("<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$printmessage</TD><TD align=left><SELECT NAME=$namevariable>");
                foreach($group_perm as $fp) 
                {
                        print("<OPTION VALUE=$fp[0] ");
                        if($fp[0] == $currentval)
                                print("SELECTED");
                        print(">$fp[1]");
                }
                           print("</SELECT></TD></TR>");

};

// ----------------------
// page start
// ----------------------

/**
 * Initialises the web application by making current
 * request parameters global, performing session checking
 * and loading the default language
 */ 
// make request parameters global
if (substr(phpversion(),0,5) >= "4.1.0") {
    // if supported by the installed version of PHP
    import_request_variables('pgc');
} else {
    // do it manually
    if (!EMPTY($_POST)) {
        extract($_POST);
    } else {
        extract($HTTP_POST_VARS);
    }
    
    if (!EMPTY($_GET)) {
        extract($_GET);
    } else {
        extract($HTTP_GET_VARS);
    }
        
    if (!EMPTY($_FILE)) {
        extract($_FILE);
    } else {
        extract($HTTP_POST_FILES);
    }
}

// initialise session var
if(!isset($sess)) {
    $sess = 0;
}
// initialise loginname
if(!isset($loginname)) {
    $loginname = 0;
}
// initialise login var
if(!isset($login)) {
    $login = 0;
}

// set default language
if(isset($default->owl_lang)) {

    $langdir = "$default->owl_fs_root/locale/$default->owl_lang";

    if(is_dir("$langdir") != 1)	{
        die("$lang_err_lang_1 $langdir $lang_err_lang_2");
    } else {

        $sql = ;
        $sql->query("select * from $default->owl_sessions_table where sessid = '$sess'");       
        $sql->next_record();
        $numrows = $sql->num_rows($sql);
        $getuid = $sql->f("uid");
        if($numrows == 1) {
            $sql->query("select * from $default->owl_users_table where id = $getuid");
            $sql->next_record();
            $language = $sql->f("language");
            // BEGIN wes fix
            if(!$language) {
              $language = $default->owl_lang;
            }
            // END wes fix
            require("$default->owl_fs_root/locale/$language/language.inc");
            $default->owl_lang = $language;
        } else {
            require("$default->owl_fs_root/locale/$default->owl_lang/language.inc");
        }
    }
} else {
    die("$lang_err_lang_notfound");
}

if ($sess) {
    gethtmlprefs();
    $ok = verify_session($sess);
    $temporary_ok =  $ok["bit"];
	$userid = $ok["userid"];
	$usergroupid = $ok["groupid"];

    if ($ok["bit"] != "1") {
        // Bozz Bug Fix begin
        if (file_exists("./lib/header.inc")) {
             include("./lib/header.inc");
        } else {
             include("../lib/header.inc");
        }
        // Bozz Bug Fix end
        print("<BR><BR><CENTER>".$lang_invalidsess);
        if ($parent == "" || $fileid == "") {
            print("<A HREF='$default->owl_root_url/index.php'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
        } else {
			print("<A HREF='$default->owl_root_url/index.php?parent=$parent&fileid=$fileid'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_login.gif' BORDER=0 ></A>");
        }
		exit;
    } else {
		$lastused = time();
		$sql = ;
		$sql->query("update $default->owl_sessions_table set lastused = '$lastused' where uid = '$userid'");
	}
}

if (!$sess && !$loginname && !$login) {
	if(!isset($fileid)) {
		header("Location: " . $default->owl_root_url . "/index.php?login=1");
    } else {
		header("Location: " . $default->owl_root_url . "/index.php?login=1&fileid=$fileid&parent=$parent");
    }
}
?>
