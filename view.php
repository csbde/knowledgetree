<?php

/*
 * view.php
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");

// cv change for security, should deny documents directory
// added image_show that passes the image through

if($action != "image_show") {
	include("./lib/header.inc");
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

function find_path($parent) {
	global $default;
        $path = fid_to_name($parent);
        $sql = new Owl_DB;
        while($parent != 1) {
                $sql->query("select parent from $default->owl_folders_table where id = '$parent'");
                while($sql->next_record()) {
                        $path = fid_to_name($sql->f("parent"))."/".$path;
                        $parent = $sql->f("parent");
                }
        }
        return $path;
}

function fid_to_filename($id) {
	global $default;
        $sql = new Owl_DB;
        $sql->query("select filename from $default->owl_files_table where id = '$id'");
        while($sql->next_record()) return $sql->f("filename");
}

if($action == "image_show") {
	if(check_auth($id, "file_download", $userid) == 1) {
		if ($default->owl_use_fs) {
			$path = $default->owl_FileDir."/".find_path($parent)."/".fid_to_filename($id);
			readfile("$path");
		}
		else {
			$sql = new Owl_DB;
                	$filename =  fid_to_filename($id);
                	if ($filetype = strrchr($filename,".")) {
                  		$filetype = substr($filetype,1);
                  		$sql->query("select * from $default->owl_mime_table where filetype = '$filetype'");
                  		while($sql->next_record()) $mimeType = $sql->f("mimetype");
                	}
                	if ($mimeType) {
                  		header("Content-Type: $mimeType");
                  		$sql->query("select data,compressed from " . $default->owl_files_data_table . " where id='$id'");
                  		while($sql->next_record()) {
                    			if ($sql->f("compressed")) {
                      				$tmpfile = $default->owl_FileDir . "owltmp.$id";
                      				if (file_exists($tmpfile)) unlink($tmpfile);
                      				$fp=fopen($tmpfile,"w");
                      				fwrite($fp, $sql->f("data"));
                      				fclose($fp);
                      				flush(passthru($default->gzip_path . " -dfc $tmpfile"));
                      				unlink($tmpfile);
                    			} else {
                      				print $sql->f("data");
                    			}
				}
	   		}
		}
	} else {
		print($lang_nofileaccess);
	}
	die;
}
print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
<TR><TD ALIGN=LEFT>
<?php print("$lang_user: ");
      if(prefaccess($userid)) {
      print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand'>");
      }
      print uid_to_name($userid);
      print ("</A>");
?>
<FONT SIZE=-1>
<?php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");?>
    </FONT></TD><TD ALIGN=RIGHT>
<?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0>");?>
	</A></TD></TR></TABLE>
<?php
if($action == "file_details") {
	if(check_auth($parent, "folder_view", $userid) == 1) {
                $expand = 1;
                print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
                print("<TR><TD align=left>".gen_navbar($parent)."/".flid_to_name($id)."</TD></TR>");
                print("</TABLE><HR WIDTH=$default->table_expand_width><BR>");
                $sql = new Owl_DB; $sql->query("select * from $default->owl_files_table where id = '$id'");
                while($sql->next_record()) {
                        $security = $sql->f("security");
                        if ($security == "0") $security = $lang_everyoneread;
                        if ($security == "1") $security = $lang_everyonewrite;
                        if ($security == "2") $security = $lang_groupread;
                        if ($security == "3") $security = $lang_groupwrite;
                        if ($security == "4") $security = $lang_onlyyou;
			if ($security == "5") $security = $lang_groupwrite_nod;
			if ($security == "6") $security = $lang_everyonewrite_nod;
			if ($security == "7") $security = $lang_groupwrite_worldread;
			if ($security == "8") $security = $lang_groupwrite_worldread_nod;

                        print("<TABLE WIDTH=66% BORDER=$default->table_border><TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>
                               $lang_title:</TD><TD align=left>".$sql->f("name")."</TD></TR>
				<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_file:</TD><TD align=left>".$sql->f("filename")."&nbsp;(".gen_filesize($sql->f("size")).")</TD></TR>
				<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_ownership:</TD>
				<TD align=left>".fid_to_creator($id)."&nbsp;(".group_to_name(owlfilegroup($id)).")</TD></TR>
				<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_permissions:</TD><TD align=left>$security</TD></TR>
				<TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg>$lang_keywords:</TD><TD align=left>".$sql->f("metadata")."</TD></TR>
                               <TR><TD ALIGN=RIGHT BGCOLOR=$default->table_header_bg VALIGN=TOP>$lang_description:</TD><TD align=left><TEXTAREA NAME=description ROWS=10
				COLS=50>".$sql->f("description")."</TEXTAREA></TABLE><BR>");
                        include("./lib/footer.inc");
		}
	}
}

if($action == "image_preview") {
	if(check_auth($id, "file_download", $userid) == 1) {
		$path = find_path($parent)."/".fid_to_filename($id);
		print("$lang_viewing". gen_navbar($parent) . "/" . fid_to_filename($id) ."<HR WIDTH=50%><BR><BR>");
		print("<IMG SRC='$PHP_SELF?sess=$sess&id=$id&action=image_show&parent=$parent'>");
	} else {
		print($lang_nofileaccess);
	}
}

if($action == "zip_preview") {
	if(check_auth($id, "file_download", $userid) == 1) {
		$name = fid_to_filename($id);

		if ($default->owl_use_fs) {
                  $path = find_path($parent)."/".$name;
                } else {
                  $path = $name;
                  if (file_exists($default->owl_FileDir. "/$path")) unlink($default->owl_FileDir. "/$path");
                  $file = fopen($default->owl_FileDir. "/$path", 'wb');
                  $sql->query("select data,compressed from $default->owl_files_data_table where id='$id'");
                  while($sql->next_record()) {
                	if ($sql->f("compressed")) {

                  		$tmpfile = $default->owl_FileDir . "owltmp.$id.gz";
				$uncomptmpfile = $default->owl_FileDir . "owltmp.$id";
                  		if (file_exists($tmpfile)) unlink($tmpfile);

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
                  	fclose($file);
                  }

                }

		//$path = find_path($parent)."/".$name;
		$expr = "-t";
		if(ereg("gz", $name)) $expr .= "z";
		print("$lang_viewing". gen_navbar($parent) . "/" . fid_to_filename($id) ."<HR WIDTH=50%><BR><BR>");
		print("<TABLE BORDER=$default->table_border CELLPADDING=0 CELLSPACING=0><TR><TD ALIGN=LEFT><PRE>");
		passthru("$default->tar_path $expr < $default->owl_FileDir/$path | sort");
		unlink($default->owl_FileDir. "/$path");
		print("</PRE></TD></TR></TABLE>");
	} else {
		print($lang_nofileaccess);
	}
}

// BEGIN wes change
if($action == "html_show" || $action == "text_show") {
        if(check_auth($id, "file_download", $userid) == 1) {
          if ($default->owl_use_fs) {
                $path = $default->owl_FileDir."/".find_path($parent)."/".fid_to_filename($id);
		print("<BR>$lang_viewing". gen_navbar($parent) . "/" . fid_to_filename($id) ."<HR WIDTH=50%><BR><BR></CENTER>");
                if ($action == "text_show") print("<xmp>");
                readfile("$path");
          } else {
                print("$lang_viewing /".find_path($parent)."/".fid_to_filename($id)."<HR WIDTH=50%><BR><BR></CENTER>");
                if ($action == "text_show") print("<xmp>");

                $sql->query("select data,compressed from " . $default->owl_files_data_table . " where id='$id'");

              while($sql->next_record()) {

                if ($sql->f("compressed")) {

                  $tmpfile = $default->owl_FileDir . "owltmp.$id";
                  if (file_exists($tmpfile)) unlink($tmpfile);

                  $fp=fopen($tmpfile,"w");
                  fwrite($fp, $sql->f("data"));
                  fclose($fp);
                  flush(passthru($default->gzip_path . " -dfc $tmpfile"));
                  unlink($tmpfile);
                } else {
                  print $sql->f("data");
                }
              }
          }
          $path = find_path($parent)."/".fid_to_filename($id);
        } else {
          print($lang_nofileaccess);
        }
}
// end wes change

?>
