<?php

/**
 * log.php
 *
 * Used for Revision history and logs when the changes occurred
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package test
 */


require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");
include("./lib/header.inc");

// store file name and extension separately
$filesearch = explode('.',$filename);

// Begin 496814 Column Sorts are not persistant
// + ADDED &order=$order&$sortorder=$sortname to
// all browse.php?  header and HREF LINES

// responsible for determining the order of information
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
print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
<TR><TD ALIGN=LEFT>
<?php print("$lang_user: ");

      if(prefaccess($userid)) 
      {
	print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand&order=$order&sortname=$sortname'>");
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

print("<CENTER>");
	// generates a navigation bar and provides details for the docs
	print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border>");
	print("<TR><TD align=left>$lang_viewlog ".gen_navbar($parent)."/".flid_to_name($id)."</TD></TR>");
	print("</TABLE><HR WIDTH=$default->table_expand_width><BR>$filename");

	print ("<TABLE width=$default->table_expand_width border=$default->table_border cellpadding=3 cellspacing=0>
	 	<TR><TD BGCOLOR='$default->table_header_bg' width=5%>$lang_ver</td>
		<TD BGCOLOR='$default->table_header_bg' width=10%>$lang_user</TD>
		<TD BGCOLOR='$default->table_header_bg' width=60%>$lang_log_file</TD>
		<TD BGCOLOR='$default->table_header_bg' width=25%>$lang_modified</TD></TR>");

	$sql = new Owl_DB; 


// SPECIFIC SQL LOG QUERY -  NOT USED (problematic)
// This SQL log query is designed for repository assuming there is only 1
// digit in major revision, and noone decides to have a "_x-" in their
// filename.
//
// Has to be changed if the naming structure changes.
// Also a problem that it didn't catch the "current" 
// file because of the "_x-" matching (grr)
//
//$sql->query("select * from $default->owl_files_table where filename LIKE '$filesearch[0]\__-%$filesearch[1]' order by major_revision desc, minor_revision desc");

// GENERIC SQL LOG QUERY - currently used.
// prone to errors when people name a set of docs 
// Blah.doc 
// Blah_errors.doc 
// Blah_standards.doc
// etc. and search for a log on Blah.doc (it brings up all 3 docs)

//$sql->query("select * from $default->owl_files_table where filename LIKE '$filesearch[0]%$filesearch[1]' order by major_revision desc, minor_revision desc");
//$SQL = "select * from $default->owl_files_table where filename LIKE '$filesearch[0]%$filesearch[1]' order by major_revision desc, minor_revision desc";
//printError("PARENT: $parent",$SQL);

if ($default->owl_use_fs) 
{
  $sql->query("Select id from $default->owl_folders_table where name='backup' and parent='$parent'");
                        while($sql->next_record()) {
                                $backup_parent = $sql->f("id");
                        }
  $sql->query("select * from $default->owl_files_table where filename LIKE '$filesearch[0]%$filesearch[1]' AND (parent = $backup_parent OR parent = $parent) order by major_revision desc, minor_revision desc");

}
else
{
// name based query -- assuming that the given name for the file doesn't change...

  $name = flid_to_name($id);
  $sql->query("select * from $default->owl_files_table where name='$name' AND parent='$parent' order by major_revision desc, minor_revision desc");
}

//global $sess;
// prints out all the relevant information on the specific document
	while($sql->next_record()) 
	{
		$choped = split("\.", $sql->f("filename"));
        	$pos = count($choped);
        	$ext = strtolower($choped[$pos-1]);

		print("<TR><TD valign=top>".$sql->f("major_revision").".".$sql->f("minor_revision")."</TD>
               <TD valign=top>".uid_to_name($sql->f("creatorid"))."</TD>
               <TD valign=top align=left><font size=2 style='font-weight:bold'>");
		printFileIcons($sql->f("id"),$sql->f("filename"),$sql->f("checked_out"),$sql->f("url"),$default->owl_version_control,$ext);
		print("&nbsp&nbsp[ ".$sql->f("filename")." ]</font><br>
               <pre>".$sql->f("description")."</></TD>
               <TD valign=top>".$sql->f("modified")."</TD></TR>");
	}

	       //print("<TR><TD valign=top>".$sql->f("major_revision").".".$sql->f("minor_revision")."</TD>
	       //<TD valign=top>".uid_to_name($sql->f("creatorid"))."</TD>
	       //<TD valign=top><font style='font-weight:bold'>[ ".$sql->f("filename")." ]</font><br>
	       //<pre>".$sql->f("description")."</></TD>
	       //<TD valign=top>".$sql->f("modified")."</TD></TR>"); 
		//}
	print("</TABLE>");
	include("./lib/footer.inc");

?>
