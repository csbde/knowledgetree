<?php

/**
 * dbmodify.php
 *
 * Performs all file (upload, update, modify, email) and folder (create, modify) 
 * maintenance and management.
 * 
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 * @todo line 50- refactor 
 */

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");
require("phpmailer/class.phpmailer.php");

/**
 * Lookup the path to the parent of the passed folder.
 *
 * @param $folderID    the id of the child folder
 * @return string      the name of the parent folder
 */
function find_path($folderID) {
    global $default;
    $path = fid_to_name($folderID);
    $sql = new Owl_DB;
    while($folderID != 1) {
        $sql->query("select parent from $default->owl_folders_table where id = '$folderID'");
        while($sql->next_record()) {
            $path = fid_to_name($sql->f("parent"))."/".$path;
            $folderID = $sql->f("parent");
        }
	}
	return $path;
}

/**
 * Recursively deletes a folder from the database
 *
 * @param $folderID    the ID of the folder to delete
 */
function delTree($folderID) {
	global $fCount, $folderList, $default;
	//delete from database
	$sql = new Owl_DB;
	$sql->query("delete from $default->owl_folders_table where id = '$folderID'");
	$sql->query("delete from $default->owl_files_table where parent = '$folderID'");
    for ($c=0; $c < $fCount; $c++) {
        if ($folderList[$c][2]==$folderID) {
            delTree($folderList[$c][0]);
        }
    }
}

// Begin 496814 Column Sorts are not persistant
// + ADDED &order=$order&$sortorder=$sortname to
// all browse.php?  header and HREF LINES
switch ($order) {
    case "name":
        $sortorder = 'sortname';
        break;
    case "major_revision":
        $sortorder = 'sortver';
        break;
    case "filename" :
        $sortorder = 'sortfilename';
        break;
    case "size" :
        $sortorder = 'sortsize';
        break;
    case "creatorid" :
        $sortorder = 'sortposted';
        break;
    case "smodified" :
        $sortorder = 'sortmod';
        break;
    case "checked_out":
        $sortorder = 'sortcheckedout';
        break;
    default:
        break;
}
// END 496814 Column Sorts are not persistant

// BEGIN BUG FIX: #433932 Fileupdate and Quotas
if($action == "file_update") {
    if(check_auth($parent, "folder_modify", $userid) == 1) {
        //if($title == "") exit($lang_specifyname);
        $userfile = uploadCompat("userfile");
        $sql = new Owl_DB;
        $sql->query("select * from $default->owl_users_table where id = '$userid'");
		while($sql->next_record()) { 
            $quota_max = $sql->f("quota_max");
            $quota_current = $sql->f("quota_current");
            //$new_quota = $quota_current + $userfile_size;
		}
		$new_name = ereg_replace("[^-A-Za-z0-9._]", "", ereg_replace(" ", "_", ereg_replace("%20|^-", "_", $userfile["name"])));
		$newpath = $default->owl_FileDir."/".find_path($parent)."/".$new_name;

        /* Begin Daphne Change - backups of files
         * If user requests automatic backups of files 
         * get current details from db and save file state information
         */
        if ($default->owl_version_control == 1) {
            if ($default->owl_use_fs) {
                $sql->query("select * from $default->owl_files_table where filename='$new_name' and parent='$parent'");
            } else {
                // this is guaranteed to get the ID of the most recent revision, just in case we're updating a previous rev.
                $sql->query("select distinct b.* from $default->owl_files_table as a, $default->owl_files_table as b where b.id='$id' AND a.name=b.name AND a.parent=b.parent order by major_revision, minor_revision desc");
       		}
			//$query = "select b.* from $default->owl_files_table as a, $default->owl_files_table as b where a.id='$id' AND a.name=b.name AND a.parent=b.parent order by major_revision, minor_revision desc";
			//printError("QU: $query");

            while($sql->next_record()) {
                // save state information
                $major_revision = $backup_major = $sql->f("major_revision");
                $minor_revision = $backup_minor = $sql->f("minor_revision");
                $backup_filename = $sql->f("filename");
                $backup_name = $sql->f("name");
                $backup_size = $sql->f("size");
                $backup_creatorid = $sql->f("creatorid");
                $backup_modified = $sql->f("modified");
                $backup_smodified = $sql->f("smodified");
                $backup_description = $sql->f("description");
                $backup_description = ereg_replace("'","\\'",$backup_description);
                $backup_metadata = $sql->f("metadata");
                $backup_parent = $sql->f("parent");
                $backup_security = $sql->f("security");
                $backup_groupid = $groupid = $sql->f("groupid");

                $new_quota = $quota_current - $backup_size + $userfile['size'];
                $filename = $sql->f(filename);
                $title = $sql->f(name);
                $description = $sql->f(description);
                
                if ($default->owl_use_fs) {	
                    if ($default->owl_FileDir."/".find_path($parent)."/".$sql->f(filename) != $newpath) {
                        printError("$lang_err_file_update","");
                    }
                }
            }
        }
        // End Daphne Change
        
		//$newpath = $default->owl_fs_root."/".find_path($parent)."/".$new_name;
		//$newpath = $default->owl_FileDir."/".find_path($parent)."/".$new_name;
        //***neller: Read data from database
        //$sql->query("select * from $default->owl_files_table where id='$id'");
        //while($sql->next_record()) {
            //if ($default->owl_fs_root."/".find_path($parent)."/".$sql->f(filename) != $newpath) {
            //if ($default->owl_FileDir."/".find_path($parent)."/".$sql->f(filename) != $newpath) {
                //printError("$lang_err_file_update","");
            //}
            //$new_quota = $quota_current - $sql->f(size) + $userfile_size;
            //$filename = $sql->f(filename);
            //$title = $sql->f(name);
            //$description = $sql->f(description);
        //}
        if (($new_quota > $quota_max) && ($quota_max != "0")) {
            printError("$lang_err_quota".$new_quota."$lang_err_quota_needed".($quota_max - $quota_current)."$lang_err_quota_avail","");
            if(($quota_max - $quota_current) == "0") {
                printError("$lang_err_quota_exceed");
            }
        }
        // End neller

        // BEGIN wes change       
        if ($default->owl_use_fs) {
            /* Begin Daphne Change
             * copy old version to backup folder
             * change version numbers, 
             * update database entries
             * upload new file over the old
             * backup filename will be 'name_majorrev-minorrev' e.g. 'testing_1-2.doc'
             */           
            if ($default->owl_version_control == 1) {
                if(!(file_exists($newpath)==1) || $backup_filename != $new_name){
                    printError("$lang_err_file_update","");
                }
                // Get the file extension.
                $extension = explode(".",$new_name);
                // rename the new, backed up (versioned) filename
                $version_name = $extension[0]."_$major_revision-$minor_revision.$extension[1]";
                // specify path for new file in the /backup/ file of each directory.
                $backuppath = $default->owl_FileDir."/".find_path($parent)."/backup/$version_name";

                if(!is_dir("$default->owl_FileDir/".find_path($parent)."/backup")) {
                    // Danilo change
                    mkdir("$default->owl_FileDir/".find_path($parent)."/backup", 0777);
                    // End Danilo change
                    // is there already a backup directory for current dir?
                    if(is_dir("$default->owl_FileDir/".find_path($parent)."/backup")) {
                        $sql->query("INSERT into $default->owl_folders_table (name, parent, security, groupid, creatorid)  values ('backup', '$parent', '50', '$groupid', '$userid')");
                    } else {
                        printError("$lang_err_backup_folder_create","");
                    }
                }
                copy($newpath,$backuppath); // copy existing file to backup folder
            }
            // End Daphne Change 

            if(!file_exists($newpath) == 1) {
                printError("$lang_err_file_update","");
            }
            copy($userfile['tmp_name'], $newpath);
            unlink($userfile['tmp_name']);
            if(!file_exists($newpath)) {
                if ($default->debug == true) {
                    printError($lang_err_upload,$newpath);
                } else {
                    printError($lang_err_upload,"");
                }
                // Begin Daphne Change
                if ($default->owl_version_control == 1) {
                    if(!file_exists($backuppath)) {
                        die ("$lang_err_backup_file");
                    }
                    // find id of the backup folder you are saving the old file to
                    $sql->query("Select id from $default->owl_folders_table where name='backup' and parent='$parent'");
                    while($sql->next_record()) {
                        $backup_parent = $sql->f("id");
                    }
                }
            }

            if($versionchange == 'major_revision') {
                // if someone requested a major revision, must
                // make the minor revision go back to 0
                //$versionchange = "minor_revision='0', major_revision";
                //$new_version_num = $major_revision + 1;
                $new_major = $major_revision + 1;
                $new_minor = 0;
                $versionchange = "minor_revision='0', major_revision";
                $new_version_num = $major_revision + 1;
            } else {
                // simply increment minor revision number
                $new_version_num = $minor_revision + 1;
				$new_minor = $minor_revision + 1;
                $new_major = $major_revision;
            }
            //	printError("old: $minor_revision", "New: $new_minor");
            // End Daphne Change

            $groupid = owlusergroup($userid);
            $modified = date("M d, Y \a\\t h:i a");
            $smodified = date("Y-m-d g:i:s");

            // Begin Daphne Change
            if ($default->owl_version_control == 1) {
                if ($default->owl_use_fs) {
                    // insert entry for backup file
                    // WORKING WORKING
                    $sql->query("INSERT into $default->owl_files_table (name,filename,size,creatorid,parent,modified, smodified,groupid,description,metadata,security,major_revision,minor_revision) values ('$backup_name','$version_name','$backup_size','$backup_creatorid','$backup_parent','$backup_modified', '$backup_smodified','$backup_groupid', '$backup_description','$backup_metadata','$backup_security','$backup_major','$backup_minor')") or unlink($backuppath);

                    // update entry for existing file. Bozz's code is repeated underneath,
                    // without the versioning attribute included.

                    // BEGIN Bozz Change
                    // Added this check, if the policy is allow Read Write NO DELETE
                    // we have to make sure that the Creator is not changed.
                    // in the case of an updated, that would then allow a user to
                    // delete the file.  Only the original Creator should be allowed
                    // to delete the file.
                    if ( getfilepolicy($id) == 5 || getfilepolicy($id) == 6) {
                        // Daphne addition -- $versionchange = $new_version_num
                        $sql->query("UPDATE $default->owl_files_table set size='".$userfile['size']."',modified='$modified',smodified='$smodified', $versionchange='$new_version_num', description='$newdesc' where id='$id'") or unlink($newpath);
               		} else {
                        // Daphne addition -- $versionchange = $new_version_num
                        $sql->query("UPDATE $default->owl_files_table set size='".$userfile['size']."',creatorid='$userid',modified='$modified',smodified='$smodified', $versionchange='$new_version_num',description='$newdesc'  where id='$id'") or unlink($newpath);
               		}
                } else {
                    // BEGIN wes change
              		// insert entry for current version of file
                    $compressed = '0';
                    $userfile = uploadCompat("userfile");
                    $fsize = filesize($userfile['tmp_name']);
                    $sql->query("INSERT into $default->owl_files_table (name,filename,size,creatorid,parent,modified, smodified,groupid,description,metadata,security,major_revision,minor_revision) values ('$backup_name','".$userfile['name']."','".$userfile['size']."','$backup_creatorid','$parent','$modified', '$smodified','$backup_groupid', '$newdesc', '$backup_metadata','$backup_security','$new_major','$new_minor')");
                    $id = $sql->insert_id();
                    
                    if ($default->owl_compressed_database && file_exists($default->gzip_path)) {
                        system($default->gzip_path . " " . escapeshellarg($userfile['tmp_name']));
                        $fd = fopen($userfile['tmp_name'] . ".gz", 'rb');
                        $userfile['tmp_name'] = $userfile['tmp_name'] . ".gz";
                        $fsize = filesize($userfile['tmp_name']);
                        $compressed = '1';
                    } else {
                        $fd = fopen($userfile['tmp_name'], 'rb');
                    }
                    $filedata = addSlashes(fread($fd, $fsize));
                    fclose($fd);

                    if ($id !== NULL && $filedata) {
                        $sql->query("insert into $default->owl_files_data_table (id, data, compressed) values ('$id', '$filedata','$compressed')");
                    }
                    // END wes change
                }
                // END Bozz Change
            } else {  // versioning not included in the DB update
                if ($default->owl_use_fs) {
                    // BEGIN Bozz Change
                    if ( getfilepolicy($id) == 5 || getfilepolicy($id) == 6) {
                        $sql->query("update $default->owl_files_table set size='".$userfile['size']."',modified='$modified',smodified='$smodified' where id='$id'") or unlink($newpath);
                	} else {
                        $sql->query("update $default->owl_files_table set size='".$userfile['size']."',creatorid='$userid',modified='$modified',smodified='$smodified' where id='$id'") or unlink($newpath);
                	}
                    // END Bozz Change
                }
            }
            // End Daphne Change

            if ($quota_max != "0") {
                $sql->query("update $default->owl_users_table set quota_current = '$new_quota' where id = '$userid'");
            }
		
            //notify_users($groupid,1, find_path($parent),$filename, $title, $newdesc);
            notify_users($groupid,1,$parent,$filename, $title, $newdesc, $type);
            header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
            // END BUG FIX: #433932 Fileupdate and Quotas
        } else {
            include("./lib/header.inc");
            print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>
            	   <TR><TD ALIGN=LEFT>");
            print("$lang_user: ");
            print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
            print uid_to_name($userid);
            print ("</A>");
            print ("<FONT SIZE=-1>");
            print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>
                   </FONT></TD>
                   <TD ALIGN=RIGHT>
                   <A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>
                   </TD></TR></TABLE><BR><BR>");
            print($lang_noupload);
        }
    }
}

if($action == "file_upload") {
    if(check_auth($parent, "folder_modify", $userid) == 1) {
        //if($title == "") exit($lang_specifyname);	
        $groupid = owlusergroup($userid);
        $sql = new Owl_DB;
        $userfile = uploadCompat("userfile");
        if ($type == "url") {
            $modified = date("M d, Y \a\\t h:i a");
            $smodified = date("Y-m-d g:i:s");
            $new_name = $userfile["name"];
            if ($title == "") {
                $title = $userfile["name"];
            }
            $sql->query("insert into $default->owl_files_table (name,filename,size,creatorid,parent,modified,description,metadata,security,groupid,smodified,checked_out, major_revision, minor_revision, url) values ('$title', '".$userfile["name"]."', '".$userfile["size"]."', '$userid', '$parent', '$modified', '$description', '$metadata', '$security', '$groupid','$smodified','$checked_out','$major_revision','1','1')");
		} else {
            $sql->query("select * from $default->owl_users_table where id = '$userid'");
            while($sql->next_record()) { 
                $quota_max = $sql->f("quota_max");
                $quota_current = $sql->f("quota_current");
                $new_quota = $quota_current + $userfile["size"];
            }
            if (($new_quota > $quota_max) && ($quota_max != "0")) {
                die("$lang_err_quota".$new_quota."$lang_err_quota_needed".($quota_max - $quota_current)."$lang_err_quota_avail");
                if(($quota_max - $quota_current) == "0") {
                    die("$lang_err_quota_exceed");
                }
            }
            $new_name = ereg_replace("[^-A-Za-z0-9._]", "", ereg_replace(" ", "_", ereg_replace("%20|^-", "_", $userfile["name"])));

            if ($default->owl_use_fs) {
                $newpath = $default->owl_FileDir."/".find_path($parent)."/".$new_name;
                if(file_exists($newpath) == 1) { 
                    if ($default->debug == true) {
                        printError($lang_fileexists,$newpath);
                    } else {
                        printError($lang_fileexists,"");
                    }
                }

                copy($userfile["tmp_name"], $newpath);
                unlink($userfile["tmp_name"]);
                if(!file_exists($newpath)) { 
                    if ($default->debug == true) {
                        printError($lang_err_upload,$newpath);
                    } else {
                        printError($lang_err_upload,"");
                    }
                } else {
                    // is name already used?
                    //printError("SQL", "select filename from $default->owl_files_table where filename = '$new_name' and parent='$parent'");
                    $sql->query("select filename from $default->owl_files_table where filename = '$new_name' and parent='$parent'");
                    while($sql->next_record()) {
                        if ($sql->f("filename")) {
                          // can't move...
                          printError("<b>File Exists:</b>","There is already a file with the name <i>$new_name</i> in this directory.","");
                          // print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'>$lang_return</A><P>");
                          // needs to be internationalized
                          //exit("<b>File Exists:</b> There is already a file with the name <i>$title</i> in this directory.");
                        }
                    }
                }
                /* BEGIN Bozz Change
                   If your not part of the Administartor Group
                   the Folder will have your group ID assigned to it */
                if ( owlusergroup($userid) != 0 ) {
                    $groupid = owlusergroup($userid);
                }
                // Bozz Change End

                $modified = date("M d, Y \a\\t h:i a");
                $smodified = date("Y-m-d g:i:s");
                if($title == "") {
                    $title = $new_name;
                }
                if($major_revision == "") {
                    $major_revision = 0;
                }
                if($minor_revision == "") {
                    $minor_revision = 1;
                }
                if($checked_out == "") {
                    $checked_out = 0;
                }
                // WORKING WORKING
		
                $compressed = '0';
                $userfile = uploadCompat("userfile");
                $fsize = $userfile['size'];
                if (!$default->owl_use_fs && $default->owl_compressed_database && file_exists($default->gzip_path)) {
                    system($default->gzip_path . " " . escapeshellarg($userfile['tmp_name']));
                    $userfile['tmp_name'] = $userfile['tmp_name'] . ".gz";
                    $fsize = filesize($userfile['tmp_name']);
                    $compressed = '1';
                }
                $result = $sql->query("insert into $default->owl_files_table (name,filename,size,creatorid,parent,modified,description,metadata,security,groupid,smodified,checked_out, major_revision, minor_revision, url) values ('$title', '$new_name', '".$userfile['size']."', '$userid', '$parent', '$modified', '$description', '$metadata', '$security', '$groupid','$smodified','$checked_out','$major_revision','$minor_revision', '0')") or unlink($newpath);

                if (!$result && $default->owl_use_fs) {
                    unlink($newpath);
                }
                // BEGIN wes change
                if (!$default->owl_use_fs) {
                    $id = $sql->insert_id();
                    $fd = fopen($userfile['tmp_name'], 'rb');
                    $filedata = addSlashes(fread($fd, $fsize));
                    fclose($fd);
                    
                    if ($id !== NULL && $filedata) {
                        $sql->query("insert into $default->owl_files_data_table (id, data, compressed) values ('$id', '$filedata', '$compressed')");
                    }
                }

                if ($quota_max != "0") {
                    $sql->query("update $default->owl_users_table set quota_current = '$new_quota' where id = '$userid'");
                }
            }

            notify_users($groupid,0,$parent,$new_name, $title, $description, $type);
            header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	} else {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
		<TR><TD ALIGN=LEFT>
<?php
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>
<?php
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
	    </FONT></TD>
	    <TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
<?php
		print($lang_noupload);
	}
}

if($action == "file_modify") {
    if(check_auth($id, "file_modify", $userid) == 1) {
        $sql = new Owl_DB;
        // Begin Bozz Change
        if ( owlusergroup($userid) != 0 && $groupid == "" ) {
            $groupid = owlusergroup($userid);
        }
		// BEGIN WES change
        if (!$default->owl_use_fs) {
            $name = flid_to_name($id);
            if ($name != $title) {
                // we're changing the name ... need to roll this to other revisions
                // is name already used?
                $sql->query("select name from $default->owl_files_table where name = '$title' and parent='$parent'");
                while($sql->next_record()) {
                    if ($sql->f("name")) {
                        // can't move...
                        //print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'>$lang_return</A><P>");
                        // needs to be internationalized
                        printError("<b>File Exists:</b> There is already a file with the name <i>$title</i> in this directory.","");
                    }
                }
                $sql->query("update $default->owl_files_table set name='$title' where parent='$parent' AND name = '$name'");
            }
        }

		$sql->query("update $default->owl_files_table set name='$title', security='$security', metadata='$metadata', description='$description',groupid='$groupid', creatorid ='$file_owner' where id = '$id'");
        // End Bozz Change
		header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	} else {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
		<TR><TD ALIGN=LEFT>
<?php
        print("$lang_user: ");
	 	if(prefaccess($userid)) {
            print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand&order=$order&$sortorder=$sortname'>");
        }
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>

<?php
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
        </FONT></TD>
		<TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
<?php
		exit($lang_nofilemod);
	}
}

if($action == "file_delete") {
    if(check_auth($id, "file_delete", $userid) == 1) {
        $sql = new Owl_DB;
        if ($type == "url") {
            $sql->query("delete from $default->owl_files_table where id = '$id'");
        } else {
			$sql->query("select * from $default->owl_files_table where id = '$id'");
			while($sql->next_record()) {
                $path = find_path($sql->f("parent"));
                $filename = $sql->f("filename");
                $filesize = $sql->f("size");
                $owner = $sql->f("creatorid");
			}
            
			$sql->query("select * from $default->owl_users_table where id = '$owner'");
			while($sql->next_record()) {
				$quota_current = $sql->f("quota_current");
				$quota_max = $sql->f("quota_max");
			}
            
			$new_quota = $quota_current - $filesize;
			if($quota_max != "0") {
                $sql->query("update $default->owl_users_table set quota_current = '$new_quota' where id = '$owner'");
            }

            if($default->owl_use_fs) {
                unlink($default->owl_FileDir."/".$path."/".$filename);
            } else {
                $sql->query("delete from $default->owl_files_data_table where id = '$id'");
            }

			$sql->query("delete from $default->owl_files_table where id = '$id'");
			sleep(.5);
        }
		header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	} else {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
		<TR><TD ALIGN=LEFT>
<?php
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>
<?php
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
        </FONT></TD>
		<TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
<?php
		exit($lang_nofiledelete);
	}
}

// Begin Daphne Change
// the file policy authorisation has been taken from file_modify
// (it's assumed that if you can't modify the file you can't check it out)
if($action == "file_lock") {
    if(check_auth($id, "file_modify", $userid) == 1) {
        $sql = new Owl_DB;
        // Begin Bozz Change
        if ( owlusergroup($userid) != 0 ) {
            $groupid = owlusergroup($userid);
        }
        // check that file hasn't been reserved while updates have gone through
        $sql->query("select checked_out from $default->owl_files_table where id = '$id'");

        while($sql->next_record()) {
            $file_lock = $sql->f("checked_out");
        }

        if ($file_lock == 0) {
            // reserve the file
            $sql->query("update $default->owl_files_table set checked_out='$userid' where id='$id'");
        } else {
            if ($file_lock == $userid) {
                // check the file back in
                $sql->query("update $default->owl_files_table set checked_out='0' where id='$id'");
            } else {
                // throw error; someone else is trying to lock the file!
                include("./lib/header.inc");
				print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>
                       <TR><TD ALIGN=LEFT>");
                print("$lang_user: ");
                print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
                print uid_to_name($userid);
                print("</A>");
                print("<FONT SIZE=-1>"
                print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
                        </FONT></TD>
                        <TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
                        </TD></TR></TABLE><BR><BR><CENTER>
<?php
                exit("$lang_err_file_lock ".uid_to_name($file_lock).".");
            }
        }
        header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
    } else {
        include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
               <TR><TD ALIGN=LEFT>
<?php   
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>
<?php   
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
        </FONT></TD>
        <TD ALIGN=RIGHT>
<?php   
        print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); 
?>
        </TD></TR></TABLE><BR><BR><CENTER>
<?php
        exit($lang_nofilemod);
    }
}
// End Daphne Change

if($action == "file_email") {
    if(check_auth($parent, "folder_modify", $userid) == 1) {
        $sql = new Owl_DB;
		$path="";
        $filename= flid_to_filename($id);
		if ($default->owl_use_fs) {
            $fID=$parent;
        	do {
                $sql->query("select name,parent from $default->owl_folders_table where id='$fID'");
                while($sql->next_record()) {
                    $tName = $sql->f("name");
                    $fID = $sql->f("parent");
                }
                $path=$tName."/".$path;
            } while ($fID != 0);
		}
        $sql->query("select name, filename, description from $default->owl_files_table where id='$id'");
        $sql->next_record();
        $name=  $sql->f("name");
        $desc=  $sql->f("description");
		$filename = $sql->f("filename");

		$mail = new phpmailer();
		$mail->IsSMTP();                                      // set mailer to use SMTP
		$mail->Host = "$default->owl_email_server";        // specify main and backup server
		$mail->From = "$default->owl_email_from";
		$mail->FromName = "$default->owl_email_fromname";

        $r=preg_split("(\;|\,)",$mailto);
        reset ($r);
        while (list ($occ, $email) = each ($r)) { 
			$mail->AddAddress($email);
        }
		if($replyto == "" ) { 
			$mail->AddReplyTo("$default->owl_email_replyto", "OWL Intranet");
		} else {
			$mail->AddReplyTo("$replyto");
        }
		
		if($ccto != "") {
            $mail->AddCC("$ccto");
        }

		$mail->WordWrap = 50;                                 // set word wrap to 50 characters
		$mail->IsHTML(true);                                  // set email format to HTML
	
		$mail->Subject = "$lang_file: $name  -- $subject";
        if ($type != "url") {
            $mail->Body    = "$mailbody" . "<BR><BR>" . "$lang_description: <BR><BR>$desc";
            $mail->altBody = "$mailbody" . "\n\n" . "$lang_description: \n\n $desc";
			// BEGIN wes change
            if (!$default->owl_use_fs) {
                if (file_exists("$default->owl_FileDir/$path$filename")) {
                    unlink("$default->owl_FileDir/$path$filename");
                }
                $file = fopen("$default->owl_FileDir/$path$filename", 'wb');
                $sql->query("select data,compressed from $default->owl_files_data_table where id='$id'");
                while ($sql->next_record()) {
                    if ($sql->f("compressed")) {
                        $tmpfile = $default->owl_FileDir . "owltmp.$id.gz";
                        $uncomptmpfile = $default->owl_FileDir . "owltmp.$id";
                        if (file_exists($tmpfile)) {
                            unlink($tmpfile);
                        }

                        $fp=fopen($tmpfile,"w");
                        fwrite($fp, $sql->f("data"));
                        fclose($fp);

                        system($default->gzip_path . " -df $tmpfile");

                        $fsize = filesize($uncomptmpfile);
                        $fd = fopen($uncomptmpfile, 'rb');
                        $filedata = fread($fd, $fsize);
                        fclose($fd);

                        fwrite($file, $filedata);
                        unlink($uncomptmpfile);
                    } else {
                        fwrite($file, $sql->f("data"));
                    }
				}
                fclose($file);
            }

			$mail->AddAttachment("$default->owl_FileDir/$path$filename");
        } else {
			$mail->Body    = "$filename" . "<BR><BR>" . "$mailbody" . "<BR><BR>" . "$lang_description: <BR><BR>$desc";
			$mail->altBody = "$filename" . "\n\n" ."$mailbody" . "\n\n" . "$lang_description: \n\n $desc";
        }
		
		if(!$mail->Send()) {
            printError($lang_err_email, $mail->ErrorInfo);
            //printError("Server:$default->owl_email_server<BR>File:$default->owl_FileDir/$path$filename ", $mail->ErrorInfo);
		}
		if (!$default->owl_use_fs) {
            unlink("$default->owl_FileDir/$path$filename");
        }
	}
}

if($action == "folder_create") {
	if(check_auth($parent, "folder_modify", $userid) == 1) {
        $sql = new Owl_DB;
		//we have to be careful with the name just like with the files
        //Comment this one out TRACKER : 603887, this was not done for renaming a folder
        // So lets see if it causes problems while creating folders.
		// Seems it causes a problem, so I put it back.
		$name = ereg_replace("[^-A-Za-z0-9._[:space:]]", "", ereg_replace("%20|^-", " ", $name));
		$sql->query("select * from $default->owl_folders_table where name = '$name' and parent = '$parent'");
		if($sql->num_rows() > 0) { 
            printError("$lang_err_folder_exist","");
        }

        if ( $name == '') {
            printError($lang_err_nameempty,"");
        }

		if($default->owl_use_fs) {
			$path = find_path($parent);
			mkdir($default->owl_FileDir."/".$path."/".$name, 0777);
			if(!is_dir("$default->owl_FileDir/$path/$name")) {
                if ($default->debug == true) {
                    printError($lang_err_folder_create,"$default->owl_FileDir/$path/$name");
                } else {
                    printError($lang_err_folder_create,"");
                }
            }
			$sql->query("insert into $default->owl_folders_table (name,parent,security,groupid,creatorid) values ('$name', '$parent', '$policy', '$groupid', '$userid')");
            header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
        }
    } else {
        include("./lib/header.inc");
        print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
        <TR><TD ALIGN=LEFT>
<?php 
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>
<?php 
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
        </FONT></TD>
        <TD ALIGN=RIGHT>
<?php 
        print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); 
?>
        </TD></TR></TABLE><BR><BR><CENTER>
<?php
        exit($lang_nosubfolder);
    }
}

if($action == "folder_modify") {
    if(check_auth($id, "folder_modify", $userid) == 1) {
        $sql = new Owl_DB;
        $origname = fid_to_name($id);
		$sql->query("select parent from $default->owl_folders_table where id = '$id'");
		while($sql->next_record()) {
            $parent = $sql->f("parent");
        }
		$path = $default->owl_FileDir."/".find_path($parent)."/";
        $source = $path . $origname;
		$name = ereg_replace("[^-A-Za-z0-9._[:space:]]", "", ereg_replace("%20|^-", " ", $name));
        $dest = $path . $name;

		if ($default->owl_use_fs) {
            if (!file_exists($path . $name) == 1 || $source == $dest) {
                if (substr(php_uname(), 0, 7) != "Windows") {
                    if ($source != $dest) {
                        $cmd="mv \"$path$origname\" \"$path$name\" 2>&1";
                        $lines=array();$errco=0;
                        $result = myExec($cmd,$lines,$errco);
                        if ( $errco != 0 ) {
                            printError($lang_err_movecancel, $result);
                        }
                    }
                } else {
                    // IF Windows just do a rename and hope for the best
                    rename ("$path$origname", "$path$name"); 
                }
            } else { 
                printError($lang_err_folderexists,"");
            }
		} else {
            $sql->query("select * from $default->owl_folders_table where parent = '$parent' and name = '$name'");
			if ($sql->num_rows($sql) != 0) {
                printError($lang_err_folderexists,"");
            }
        }
        /* BEGIN Bozz Change
           If your not part of the Administartor Group
               the Folder will have your group ID assigned to it */
        if ( owlusergroup($userid) != 0 ) {
		   	$sql->query("update $default->owl_folders_table set name='$name', security='$policy' where id = '$id'");
        } else {
		   	$sql->query("update $default->owl_folders_table set name='$name', security='$policy', groupid='$groupid'  where id = '$id'");
        }
        // Bozz change End
        
		header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	} else {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
		<TR><TD ALIGN=LEFT>
<?php 
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>
<?php 
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
	    </FONT></TD>
	    <TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
<?php
		exit($lang_nofoldermod);
	}
}

if($action == "folder_delete") {
    if(check_auth($id, "folder_delete", $userid) == 1) {
        $sql = new Owl_DB;
        $sql->query("select id,name,parent from $default->owl_folders_table order by name");
        $fCount = ($sql->nf());
        $i = 0;
        while($sql->next_record()) {
            $folderList[$i][0] = $sql->f("id");
            $folderList[$i][2] = $sql->f("parent");
            $i++;
		}
		if ($default->owl_use_fs) {
			myDelete($default->owl_FileDir."/".find_path($id));
        }

		delTree($id);
		sleep(.5);
		header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	} else {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
		<TR><TD ALIGN=LEFT>
<?php 
        print("$lang_user: ");
        print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
        print uid_to_name($userid);
        print ("</A>");
?>
        <FONT SIZE=-1>

<?php 
        print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");
?>
        </FONT></TD>
		<TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
<?php
		exit($lang_nofolderdelete);
	}
}

if($action == "user") {
    // the following should prevent users from changing others passwords.
    $sql = new Owl_DB;
    $sql->query("select * from $default->owl_sessions_table where uid = '$id' and sessid = '$sess'");
    if($sql->num_rows() <>  1) {
        die ("$lang_err_unauthorized"); 
    }

    if ($newpassword <> '') {
        $sql = new Owl_DB;
        $sql->query("select * from $default->owl_users_table where id = '$id' and password = '" . md5($oldpassword) ."'");
        if($sql->num_rows() ==  0) {
            die("$lang_err_pass_wrong");
        }
        if ( $newpassword == $confpassword) {
            $sql->query("UPDATE $default->owl_users_table SET name='$name',password='" . md5("$newpassword") . "' where id = '$id'");
        } else {
           die ("$lang_err_pass_missmatch");
        }
    }
    $sql->query("UPDATE $default->owl_users_table SET name='$name', email='$email', notify='$notify', attachfile='$attachfile', language='$newlanguage' where id = '$id'");
}

header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");

?>
