<?php

/*
 * move.php
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

//
global $order, $sortorder, $sortname;
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


if($action == "file") {
	if(check_auth($id, "file_modify", $userid) == 0) {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
		?>
		<TR><TD ALIGN=LEFT>
                <?php print("$lang_user: ");
		if(prefaccess($userid)) {
                print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand&order=$order&sortname=$sortname'>");
                }
                print uid_to_name($userid);
                print ("</A>");
                ?>
		<FONT SIZE=-1>

		<?php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");?>
		</FONT></TD>    
		<TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'>
		<IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
		<?php
		exit($lang_nofilemod);
	}
} else {
	if(check_auth($id, "folder_modify", $userid) == 0) {
		include("./lib/header.inc");
		print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
		?>
		<TR><TD ALIGN=LEFT>
                <?php print("$lang_user: ");
                if(prefaccess($userid)) {
                print("<A HREF='prefs.php?owluser=$userid&sess=$sess&expand=$expand&order=$order&sortname=$sortname'>");
                }
                print uid_to_name($userid);
                print ("</A>");
		?>
		<FONT SIZE=-1>

		<?php print("<A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A>");?>
		</FONT></TD>    
		<TD ALIGN=RIGHT><?php print("<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A>"); ?>
		</TD></TR></TABLE><BR><BR><CENTER>
		<?php
		exit($lang_nofilemod);
	}
}

function checkForNewFolder() {
	global $HTTP_POST_VARS, $newFolder;
	if (!is_array($HTTP_POST_VARS)) return;
	while (list($key, $value) = each ($HTTP_POST_VARS)) {
		if (substr($key,0,2)=="ID") { 
			$newFolder = intval(substr($key,2)); 
			break; 
		}
	}
}

function showFoldersIn($fid, $folder) {
	global $folderList, $fCount, $fDepth, $excludeID, $action, $id, $default, $userid ;
	for ($c=0 ;$c < ($fDepth-1) ; $c++) print "<img src='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/blank.gif' height=16 width=18 align=top>";
	if ($fDepth) print "<img src='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/link.gif' height=16 width=16 align=top>";
	
	$gray=0;	//	Work out when to gray out folders ...
	if ($fid==$excludeID) $gray=1;	//	current parent for all moves
	if (($action=="folder") && ($fid==$id)) $gray=1;	//	subtree for folder moves
	if (check_auth($fid, "folder_modify", $userid) == 0) $gray = 1; 	//	check for permissions


	if ($gray) {
		print "<img src='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/folder_gray.gif' height=16 width=16 align=top>";
		print " <font color=\"silver\">$folder</font><br>\n";
	} else {
		print "<input type='image' border=0 src='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/folder_closed.gif' height=16 width=16 align=top name=\"ID";
		print "$fid\"> $folder<br>\n";
	}

	if (($action=="folder") && ($fid==$id)) return;	//	Don't show subtree of selected folder as target for folder move
	for ($c=0; $c<$fCount; $c++) {
		if ($folderList[$c][2]==$fid) {
			$fDepth++;
			showFoldersIn( $folderList[$c][0] , $folderList[$c][1] ); 
			$fDepth--;
		}
	}
}

if ($action=="$lang_cancel_button") {
	header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
	exit();
}

checkForNewFolder();
if (isset($newFolder)) {
	$sql = new Owl_DB;

	$source="";
	$fID=$parent;
	do {
		$sql->query("select name,parent from $default->owl_folders_table where id='$fID'");
		while($sql->next_record()) {
			$tName = $sql->f("name");
			$fID = $sql->f("parent");
		}
		$source=$tName."/".$source;
	} while ($fID != 0);

	$dest="";
	$fID=$newFolder;
	do {
		$sql->query("select name,parent from $default->owl_folders_table where id='$fID'");
		while($sql->next_record()) {
			$tName = $sql->f("name");
			$fID = $sql->f("parent");
		}
		$dest=$tName."/".$dest;
	} while ($fID != 0);

	if ($action=="file") {
		$sql = new Owl_DB;
		$sql->query("select filename, parent from $default->owl_files_table where id = '$id'");
		while($sql->next_record()) {
			$fname = $sql->f("filename");
			$parent = $sql->f("parent");
		}
	} else {
		$sql = new Owl_DB;
		$sql->query("select name, parent from $default->owl_folders_table where id='$id'");
		while($sql->next_record()) {
			$fname = $sql->f("name");
			$parent = $sql->f("parent");
		}
	}


	if($default->owl_use_fs) {
		if ($type != "url") {
           		if (!file_exists("$default->owl_FileDir/$dest$fname")) {
    				if (substr(php_uname(), 0, 7) != "Windows") {
					$cmd="mv \"$default->owl_FileDir/$source$fname\" \"$default->owl_FileDir/$dest\" 2>&1";
        				$lines=array();$errco=0;
					$result = myExec($cmd,$lines,$errco);
        				if ( $errco != 0 ) 
                        			printError($lang_err_movecancel, $result);
                		}
                		else {
                        		// IF Windows just do a rename and hope for the best
					rename ("$default->owl_FileDir/$source$fname", "$default->owl_FileDir/$dest/$fname"); 
                     		}
	   		}
	   		else
              			printError($lang_err_fileexists,$result);
        	}
  	}


	if ($action=="file") { 
		$sql->query("update $default->owl_files_table set parent='$newFolder' where id='$id'");
	} else { 
		$sql->query("update $default->owl_folders_table set parent='$newFolder' where id='$id'");
	}
	

	header("Location: browse.php?sess=$sess&parent=$parent&expand=$expand&order=$order&$sortorder=$sortname");
}
	
	
	//	First time through. Generate screen for selecting target directory

include("./lib/header.inc");
print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
?>
<TR><TD ALIGN=LEFT>
<?php print("$lang_user: ");
      if(prefaccess($userid)) {
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

	//	Get information about file or directory we want to move

if ($action=="file") {
	$sql = new Owl_DB;
	$sql->query("select filename, parent from $default->owl_files_table where id='$id'");
} else {
	$sql = new Owl_DB;
	$sql->query("select name, parent from $default->owl_folders_table where id='$id'");
}

while($sql->next_record()) {
	if($action == "file") $fname = $sql->f("filename");
	if($action == "folder") $fname = $sql->f("name");
	$parent = $sql->f("parent");
}

//print "<p>$lang_moving $action $fname.  $lang_select</p>";
print "<p>$lang_moving $fname.  $lang_select</p>";
?>
<div align="center">
<form method="POST">
<input type="hidden" name="parent" value="<?php print $parent; ?>">
<input type="hidden" name="expand" value="<?php print $expand; ?>">
<input type="hidden" name="order" value="<?php print $order; ?>">
<input type="hidden" name="action" value="<?php print $action; ?>">
<input type="hidden" name="fname" value="<?php print $fname; ?>">
<input type="hidden" name="id" value="<?php print $id; ?>">
<table cellspacing=0 border=1 cellpadding=4><tr><td align=left><p>
<?php

	//	Get list of folders sorted by name

$sql->query("select id,name,parent from $default->owl_folders_table order by name");

$i=0;
while($sql->next_record()) {
	$folderList[$i][0] = $sql->f("id");
	$folderList[$i][1] = $sql->f("name");
	$folderList[$i][2] = $sql->f("parent");
	$i++;
}	

$fCount = count($folderList);

$fDepth=0;
$excludeID=$parent;	// current location should not be a offered as a target
showFoldersIn(1, fid_to_name("1"));

?>
</p></td></tr></table>
<br>
<input TYPE="submit" name="action" VALUE="<?php print $lang_cancel_button; ?>"> 

</form>
</div>

<?php
include("./lib/footer.inc");
?>
