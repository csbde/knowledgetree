<?php
/**
 * browse.php -- Browse page
 * 
 * Browse a list of files/folders
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
*/

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/readhd.php");
require("./lib/security.lib.php");
include("./lib/header.inc");

if(!isset($parent)) $parent = "1";
if(!isset($expand)) $expand = $default->expand;
if(!isset($order)) $order = "name";
if(!isset($sortname)) $sortname = "ASC";
// Daphne change
if(!isset($sortver)) $sortver = "ASC, minor_revision ASC";
if(!isset($sortcheckedout)) $sortcheckedout = "ASC";
// end Daphne change
if(!isset($sortfilename)) $sortfilename = "DESC";
if(!isset($sortsize)) $sortsize = "DESC";
if(!isset($sortposted)) $sortposted = "DESC";
if(!isset($sortmod)) $sortmod = "DESC";
if(!isset($sort)) $sort = "asc";

// Begin 496814 Column Sorts are not persistant
switch ($order) {
     case "name":
           $sortorder = 'sortname';
           $sort=$sortname;
           break;
     case "major_revision":
           $sortorder = 'sortver';
           $sort=$sortver;
           break;
     case "filename" :
           $sortorder = 'sortfilename';
           $sort=$sortfilename;
           break;
     case "size" : 
           $sortorder = 'sortsize';
           $sort=$sortsize;
           break;
     case "creatorid" :
           $sortorder = 'sortposted';
           $sort=$sortposted;
           break;
     case "smodified" :
           $sortorder = 'sortmod';
           $sort=$sortmod;
           break;
     case "checked_out":
           $sortorder = 'sortcheckedout';
           $sort = $sortcheckedout;
           break;
     default:
          $sort="ASC";
          break;
}

// END 496814 Column Sorts are not persistant


//if the user does not have permission to view the folder
if(check_auth($parent, "folder_view", $userid) != "1") {
 	printError($lang_nofolderaccess,"");
	exit;
}

print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
<TR><TD ALIGN=LEFT> 
<?php print("$lang_user: "); 
// If the user is the anonymouse users
// do not allow him to change his prefs
      if(prefaccess($userid)) {
      	print("<A HREF='prefs.php?owluser=$userid&sess=$sess&parent=$parent&expand=$expand&order=$order&sortname=$sort'>");
      }
      print uid_to_name($userid);
      print ("</A>");
?> 
<FONT SIZE=-1>
<?php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");?>
    </FONT></TD>
    <TD ALIGN=RIGHT> <?php

print("<A HREF='modify.php?sess=$sess&action=folder_create&parent=$parent&expand=$expand&order=$order&sortname=$sort'>
		<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_add_folder.gif' BORDER=0></A>&nbsp;");
print("<A HREF='modify.php?sess=$sess&action=file_upload&type=url&parent=$parent&expand=$expand&order=$order&sortname=$sort'>
		<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_add_url.gif' BORDER=0></A>&nbsp;");
print("<A HREF='modify.php?sess=$sess&action=file_upload&parent=$parent&expand=$expand&order=$order&sortname=$sort'>
		<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_add_file.gif' BORDER=0></A>&nbsp;&nbsp;");
if($expand==1) {
	print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=0&order=$order'><IMG SRC=
        \t\t'$default->owl_root_url/locale/$default->owl_lang/graphics/btn_collapse_view.gif'
        \t\tBORDER=0></A>");
} else {
	print("\t\t\t<A HREF='browse.php?sess=$sess&parent=$parent&expand=1&order=$order'><IMG SRC=
	\t\t'$default->owl_root_url/locale/$default->owl_lang/graphics/btn_expand_view.gif'
	\t\tBORDER=0></A>&nbsp;&nbsp;\n");
}

print("</TD></TR></TABLE>");

print("<CENTER>");
if ($expand == 1) {
	print("\t\t<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>\n");
} else {
	print("\t\t<TABLE WIDTH=$default->table_collapse_width BORDER=$default->table_border>\n");
}

print("\t\t\t<TR><TD></TD></TR>");
print("\t\t\t<TR><TD align=left>" . gen_navbar($parent) . "</TD></TR></TABLE>");

if ($expand == 1) {
	print("\t\t<HR WIDTH=$default->table_expand_width>\n");
} else {
	print("\t\t<HR WIDTH=$default->table_collapse_width>\n");
}

/**
* Creates links that can be sorted
*
* @param $column	current column
* @param $sortname
* @param $sortvalue	ASC or DESC
* @param $order		column to order by
* @param $sess
* @param $expand
* @param $parent
* @param $lang_title
* @param $url
*/

function show_link($column,$sortname,$sortvalue,$order,$sess,$expand,$parent,$lang_title,$url) {

	if ($sortvalue == "ASC") {
	     print("\t\t\t\t<TD align=left><A HREF='browse.php?sess=$sess&expand=$expand&parent=$parent&order=$column&$sortname=DESC' STYLE='toplink'>$lang_title");
	     if ($order == $column)
	     {
	       print("<img border='0' src='$url/graphics/asc.gif' width='16' height='16'></A></TD>");
	     }
	     else
	     {
	       print("</A></TD>");
	     }
	
	}
	else {
	     print("\t\t\t\t<TD align=left><A HREF='browse.php?sess=$sess&expand=$expand&parent=$parent&order=$column&$sortname=ASC' STYLE='toplink'>$lang_title");
	     if ($order == $column)
	     {
	       print("<img border='0' src='$url/graphics/desc.gif' width='16' height='16'></A></TD>");
	     }
	     else {
	       print("</A></TD>");
	     }
	}
}


if ($expand == 1) {
	print("\t\t\t<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border><TR BGCOLOR='$default->table_header_bg'>");
        show_link("name","sortname",$sortname,$order,$sess,$expand,$parent,$lang_title,$default->owl_root_url);
                // Daphne change - column for files checked out
                if ($default->owl_version_control == 1) {
                show_link("major_revision","sortver",$sortver,$order,$sess,$expand,$parent,$lang_ver,$default->owl_root_url);
                }
                // end Daphne change
        show_link("filename","sortfilename",$sortfilename,$order,$sess,$expand,$parent,$lang_file,$default->owl_root_url);
        show_link("size","sortsize",$sortsize,$order,$sess,$expand,$parent,$lang_size,$default->owl_root_url);
        show_link("creatorid","sortposted", $sortposted,$order,$sess,$expand,$parent,$lang_postedby,$default->owl_root_url);
        show_link("smodified","sortmod", $sortmod,$order,$sess,$expand,$parent,$lang_modified,$default->owl_root_url);
        print("<TD align=left>$lang_actions</TD>");
                // Daphne change - column for files checked out
                if ($default->owl_version_control == 1) {
                show_link("checked_out","sortcheckedout", $sortcheckedout, $order,$sess,$expand,$parent,$lang_held,$default->owl_root_url);
                }
                // end Daphne change
} else {
	print("\t\t\t<TABLE WIDTH=$default->table_collapse_width BORDER=$default->table_border><TR BGCOLOR='$default->table_header_bg'>");
        show_link("name","sortname",$sortname,$order,$sess,$expand,$parent,$lang_title,$default->owl_root_url);
                // Begin Daphne Change
                if ($default->owl_version_control == 1) {
                show_link("major_revision","sortver",$sortver,$order,$sess,$expand,$parent,$lang_ver,$default->owl_root_url);
                }
                // end Daphne change
        show_link("filename","sortfilename",$sortfilename,$order,$sess,$expand,$parent,$lang_file,$default->owl_root_url);
        show_link("size","sortsize",$sortsize,$order,$sess,$expand,$parent,$lang_size,$default->owl_root_url);
                // Daphne change - column for files checked out
                if ($default->owl_version_control == 1) {
                show_link("checked_out","sortcheckedout", $sortcheckedout, $order,$sess,$expand,$parent,$lang_held,$default->owl_root_url);
                }
                // end Daphne change
}

//Looping out Folders

$DBFolderCount = 0;
$CountLines = 0;

$sql = new Owl_DB;
if ($order == "creatorid") {
	$sql->query("SELECT * from $default->owl_folders_table where parent = '$parent' order by $order");
} else {
	$sql->query("SELECT * from $default->owl_folders_table where parent = '$parent' order by name ");
}

//**********************
//* BEGIN Print Folders
//**********************

while($sql->next_record()) {
	//if the current user has a restricted view
	if($default->restrict_view == 1) {
		//if the current user does not have permission to view the folder
		if(!check_auth($sql->f("id"), "folder_view", $userid)) 
			continue;
	}
        $CountLines++;
        $PrintLines = $CountLines % 2;
        if ($PrintLines == 0)
                print("\t\t\t\t<TR BGCOLOR='$default->table_cell_bg_alt'>");
        else
                print("\t\t\t\t<TR BGCOLOR='$default->table_cell_bg'>");

	print("<TD ALIGN=LEFT><A HREF='browse.php?sess=$sess&parent=" . $sql->f("id") . "&expand=$expand&order=$order&$sortorder=$sort'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/folder_closed.gif' BORDER=0>" . $sql->f("name") . "</A></TD>");

        $DBFolderCount++;  //count number of filez in db 2 use with array
        $DBFolders[$DBFolderCount] = $sql->f("name");  //create list if files in

	if($expand == 1) {
		print("\t\t\t\t<TD>&nbsp</TD><TD>&nbsp</TD><TD>&nbsp</TD><TD>&nbsp</TD>");
                // begin Daphne change
                // extra colunm width for "version" column which folders don't need
                if ($default->owl_version_control == 1) {
                        print("<TD>&nbsp</TD>");
                }
                // end Daphne change
                print("<TD ALIGN=LEFT>");

		if(check_auth($sql->f("id"), "folder_delete", $userid) == 1)
			print("\t<A HREF='dbmodify.php?sess=$sess&action=folder_delete&id=".$sql->f("id")."&parent=$parent&expand=$expand&order=$order&sortname=$sort'\tonClick='return confirm(\"$lang_reallydelete ".htmlspecialchars($sql->f("name"),ENT_QUOTES)."?\");'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/trash.gif' ALT='$lang_del_folder_alt' TITLE='$lang_del_folder_alt'\tBORDER=0 ></A>&nbsp;");
		if(check_auth($sql->f("id"), "folder_modify", $userid) == 1) {
			print("<A HREF='modify.php?sess=$sess&action=folder_modify&id=".$sql->f("id")."&parent=$parent&expand=$expand&order=$order&sortname=$sort'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/edit.gif' BORDER=0 ALT='$lang_mod_folder_alt' TITLE='$lang_mod_folder_alt'>");
			print("<A HREF='move.php?sess=$sess&id=".$sql->f("id")."&parent=$parent&expand=$expand&action=folder&order=$order&sortname=$sort'><IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/move.gif' BORDER=0 ALT='$lang_move_folder_alt' TITLE='$lang_move_folder_alt'></A>");
		 	print("<A HREF='download.php?sess=$sess&id=".$sql->f("id")."&parent=".$sql->f("parent")."&action=folder&binary=1'>
                        <IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/zip.jpg' BORDER=0 ALT='$lang_get_file_alt' TITLE='$lang_get_file_alt'></A>&nbsp;");

                }
                
		print("</TD>");
                if ($default->owl_version_control == 1)
			print ("<TD>&nbsp</TD>");
                print("</TR>");
	} else {
		//print("</TD>");
		print("\t\t\t\t<TD></TD><TD></TD><TD></TD>");
                // begin Daphne change
                // extra column width for "held" column which folders don't need
                if ($default->owl_version_control == 1) {
        		if ($PrintLines == 0)
                        	print ("<TD BGCOLOR='$default->table_cell_bg_alt'></TD>");
        		else
                        	print ("<TD BGCOLOR='$default->table_cell_bg'></TD>");

                }
                // end Daphne change
                print("</TR>");

	}
}

if ($default->owl_LookAtHD != "false") 
{
   $DBFolders[$DBFolderCount+1] = "[END]";  //end DBfolder array
   $RefreshPage = CompareDBnHD('folder', $default->owl_FileDir . "/" . get_dirpath($parent), $DBFolders, $parent, $default->owl_folders_table); 
}

//**********************
// BEGIN Print Files
//**********************
$sql = new Owl_DB;
//$sql->query("SELECT * from $default->owl_files_table where parent = '$parent' order by $order $sort");
if ($default->owl_version_control == 1)
{
  //$sql->query("drop table tmp");
  $sql->query("create temporary table tmp (name char(80) not null, parent int(4) not null, val double(4,2) not null)");
  // POSTGRES? $sql->query("create temporary table tmp (name varchar(80) not null, parent int4 not null, val float not null);");
  //$sql->query("lock tables files read");
  $sql->query("insert into tmp select name, parent, max(major_revision+(minor_revision/10)) from files group by name,parent");
  $sql->query("select files.* from files,tmp where files.name=tmp.name and major_revision+(minor_revision/10)=tmp.val AND tmp.parent=files.parent AND tmp.parent = '$parent' order by $order $sort");
}
 else
{

  $sql->query("select * from $default->owl_files_table where parent = '$parent' order by $order $sort" );
}

//Looping out files from DB!

$DBFileCount = 0;

while($sql->next_record()) {
	if($default->restrict_view == 1) {
		if(!check_auth($sql->f("id"), "file_download", $userid)) 
			continue;
	}
        $CountLines++;
        $PrintLines = $CountLines % 2;
        if ($PrintLines == 0)
		print("\t\t\t\t<TR BGCOLOR='$default->table_cell_bg_alt'>");
	else
		print("\t\t\t\t<TR BGCOLOR='$default->table_cell_bg'>");
        print("<TD ALIGN=LEFT><A HREF='view.php?sess=$sess&action=file_details&id=".$sql->f("id")."&parent=$parent&expand=$expand&order=$order&sortname=$sort'>");
	$iconfiles = array("html","htm","gif","jpg","bmp","zip","tar","doc","mdb","xls","ppt","pdf","gz","mp3","tgz");
	$choped = split("\.", $sql->f("filename"));
	$pos = count($choped);
// BEGIN BUG FIX: #433548 Problem with uppercase fileextensions
	$ext = strtolower($choped[$pos-1]);
// END BUG FIX: #433548 Problem with uppercase fileextensions
        if ($sql->f("url") == "1")
		print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/url.gif' BORDER=0>&nbsp;");
        else {
		if (preg_grep("/$ext/",$iconfiles))
			print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/$ext.jpg' BORDER=0>&nbsp;");
                else
			print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/file.gif' BORDER=0>&nbsp;");
	}
        if($fileid == $sql->f("id"))
		print("<B>".$sql->f("name")."</B></A></TD>");
        else
		print($sql->f("name")."</A></TD>");
        // Begin Daphne Change
        // print version numbers if version control used
        if ($default->owl_version_control == 1){
        	if($fileid == $sql->f("id"))
                	print("<TD ALIGN=LEFT><B>".$sql->f("major_revision").".".$sql->f("minor_revision")."<B></td>");
                else
                	print("<TD ALIGN=LEFT>".$sql->f("major_revision").".".$sql->f("minor_revision")."</td>");
        }
        // end Daphne Change
         if ($sql->f("url") == "1")
                if($fileid == $sql->f("id")) 
             		print("<TD ALIGN=LEFT><A HREF='".$sql->f("filename")."' TARGET=new><B>".$sql->f("name")." </B></A></TD><TD ALIGN=RIGHT><B>".gen_filesize($sql->f("size"))."</B></TD>");
                else
             		print("<TD ALIGN=LEFT><A HREF='".$sql->f("filename")."' TARGET=new>".$sql->f("name")." </A></TD><TD ALIGN=RIGHT>".gen_filesize($sql->f("size"))."</TD>");
        else
                if($fileid == $sql->f("id")) 
             		print("<TD ALIGN=LEFT><A HREF='download.php?sess=$sess&id=".$sql->f("id")."&parent=".$sql->f("parent")."'><B>".$sql->f("filename")."</B></A></TD><TD ALIGN=RIGHT><B>".gen_filesize($sql->f("size"))."</B></TD>");
                else
             		print("<TD ALIGN=LEFT><A HREF='download.php?sess=$sess&id=".$sql->f("id")."&parent=".$sql->f("parent")."'>".$sql->f("filename")."</A></TD><TD ALIGN=RIGHT>".gen_filesize($sql->f("size"))."</TD>");


        $DBFileCount++;  //count number of filez in db 2 use with array
        $DBFiles[$DBFileCount] = $sql->f("filename");  //create list if files in
//print("<H1> HERE WE ARE ID - $tmp</H1>");
//exit();
	if($expand ==1) {
                if($fileid == $sql->f("id"))
			print("\t\t\t\t<TD ALIGN=LEFT><B>".fid_to_creator($sql->f("id"))."</B></TD><TD ALIGN=left><B>".$sql->f("modified")."</B></TD>");
                else
			print("\t\t\t\t<TD ALIGN=LEFT>".fid_to_creator($sql->f("id"))."</TD><TD ALIGN=left>".$sql->f("modified")."</TD>");
		print("\t\t\t\t<TD ALIGN=LEFT>");

		printFileIcons($sql->f("id"),$sql->f("filename"),$sql->f("checked_out"),$sql->f("url"),$default->owl_version_control,$ext);
                
        }

        // begin Daphne change
        // printing who has a document checked out
        if ($default->owl_version_control == 1) {
                if (($holder = uid_to_name($sql->f("checked_out"))) == "Owl") {
                print("\t<TD ALIGN=center>-</TD></TR>");
                }
                else {
                print("\t<TD align=left>$holder</TD></TR>");
                }
        }
        // end Daphne Change

}

if ($default->owl_version_control == 1) {
  //$sql->query("unlock tables");
  $sql->query("drop table tmp");
}

$DBFiles[$DBFileCount+1] = "[END]";  //end DBfile array

print("</TABLE>");

// ***********************************
// If the refresh from hard drive 
// feature is enabled
// ***********************************
if ($default->owl_LookAtHD != "false") 
{
   if($RefreshPage == true) {
     CompareDBnHD('file', $default->owl_FileDir . "/" . get_dirpath($parent), $DBFiles, $parent, $default->owl_files_table);
   }else{
     $RefreshPage = CompareDBnHD('file', $default->owl_FileDir . "/" . get_dirpath($parent), $DBFiles, $parent, $default->owl_files_table);
   }

   if($RefreshPage == true) {
?>
   <script language="javascript">
     window.location.reload(true);
   </script>
   <?php
   }
}

include("./lib/footer.inc");
?>
