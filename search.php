<?php 
/**
 * Search.php
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * This class is just random php used as a example.
 *
 * @version 1.1.1.1 2002/12/04
 * @author Michael
 * @package Owl
 */

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");


//-------------------------------------------------------------
/**
 *   Function find_path($parent) 
 *
 *   Retrieves the parent folder from the DB
 *
 *   @param $parent
 *	The parent folder id of the parent folder that needs to be retrieved 
 *   @Return $path
 *	Returns the path of the parent folder
*/
//-------------------------------------------------------------
// Usable
function find_path($parent) 
{
        global $default;
        $path = fid_to_name($parent);
        $sql = new Owl_DB;
        while($parent != 1) 
        {// retrieve the parent from the folders table that corresponds to the parent id param
                $sql->query("select parent from $default->owl_folders_table where id = '$parent'");
                while($sql->next_record()) 
                {
                        $path = fid_to_name($sql->f("parent"))."/".$path;
                        $parent = $sql->f("parent");
                }
        }
        return $path;
}

// This Layout section will not be needed as it is going to change

// BEGIN patch Scott Tigr
// patch for layout
include("./lib/header.inc");

        print("<TABLE WIDTH=$default->table_expand_width BGCOLOR=$default->main_header_bgcolor CELLSPACING=0 CELLPADDING=0 BORDER=$default->table_border HEIGHT=30>");
        print("<TR><TD WIDTH=200 VALIGN=TOP>");
        print("<TR>");
        print("<TD ALIGN=LEFT WIDTH=33%>");
        print uid_to_name($userid);
        print(" : <A HREF='index.php?login=logout&sess=$sess'>$lang_logout</A></TD>");
        print("<TD ALIGN=CENTER WIDTH=33%>&nbsp</TD>");
        print("<TD ALIGN=RIGHT WIDTH=33%> <A HREF='../browse.php?sess=$sess'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A> </TD>");
        print("</TR>");
        print("</TABLE>");

print "<center>";
print "<br>";
if ($expand == 1) 
{ // long view
        print("\t\t<TABLE WIDTH=\"".$default->table_expand_width."\" border=\"0\">\n");
} 
else 
{
        print("\t\t<TABLE WIDTH=$default->table_collapse_width>\n");
}

       print("\t\t\t<tr><td align=\"left\"><b style=\"color:#0000aa;\">".$lang_search.":</b> ");

       print(gen_navbar($parent) . "</td></tr></table>");

// END patch Scott Tigr

$groupid = owlusergroup($userid);

// first we have to find out what we can search
// we need a list of all folder that can be searched
// so we need to see which folders the user can read
$sql = new Owl_DB;
$sql->query("SELECT id,creatorid,groupid,security FROM $default->owl_folders_table");

//
// get all the folders that the user can read
while($sql->next_record()) 
{
	$id = $sql->f("id");
	if(check_auth($id, "folder_view", $userid) == 1) $folders[$id] = $id;
}

//
// get all the files in those folders that the user can read
foreach($folders as $item) 
{
	$sql->query("SELECT * FROM $default->owl_files_table where parent = '$item'");
	while($sql->next_record()) 
	{
		$id = $sql->f("id");
		
		if(check_auth($id, "file_download", $userid) == 1) 
		{
			$files[$id][id] = $id;
			$files[$id][n] = $sql->f("name");
			$files[$id][m] = explode(" ", $sql->f("metadata"));
			$files[$id][d] = explode(" ", $sql->f("description"));
			$files[$id][f] = $sql->f("filename");
			$files[$id][c] = $sql->f("checked_out");
			$files[$id][u] = $sql->f("url");
			$files[$id][p] = $sql->f("parent");
			$files[$id][score] = 0;
		}
	}
}

//
// right now we have the array $files with all possible files that the user has read access to

// BEGIN bufix Scott Tigr
// error_handler if query empty

if (strlen(trim($query))>0) {

// END bugfix Scott Tigr

//
// break up our query string
$query = explode(" ", $query);

//
// the is the meat of the matching
if(sizeof($files) > 0) {
foreach($query as $keyword)
 {
	foreach(array_keys($files) as $key) 
	{
		// BEGIN enhancement Sunil Savkar
		// if the $parent string contains a keyword to be searched, then the score is
		// adjusted.  This takes into account the hierarchy.
		
		// if keyword is found in the path
		if(eregi("$keyword", find_path($files[$key][p])))
		{ 
			$files[$key][score] = $files[$key][score] + 4;
		}
		
		//if keyword is found in the files array
		if(eregi("$keyword", $files[$key][n]))
		{
			 $files[$key][score] = $files[$key][score] + 4;
		}
		
		if(eregi("$keyword", $files[$key][f]))
		{
			$files[$key][score] = $files[$key][score] + 3;
		}
		// if keyword is found in metadata
		foreach($files[$key][m] as $metaitem) 
		{
			// add 2 to the score if we find it in metadata (key search items)
			if(eregi("$keyword", $metaitem)) 
			{
				$files[$key][score] = $files[$key][score] + 2;
			}
		}
		
		// if keyword is found in description
		foreach($files[$key][d] as $descitem) 
		{
			// only add 1 for regular description matches
			if(eregi("$keyword", $descitem)) 
			{
				$files[$key][score] = $files[$key][score] + 1;
			}
		}
	}
}
}
//
// gotta find order to the scores...any better ideas?
print "$lang_search_results_for \"".implode(" ", $query)."\"<BR><BR><HR WIDTH=200 ALIGN=CENTER><BR>";
$max = 30;
$hit = 1;
$CountLines = 0;
$iconfiles = array("html","htm","gif","jpg","bmp","zip","tar","doc","mdb","xls","ppt","pdf","gz","mp3","tgz");

//if array exists print out the results based on their score of relavence
// This section will have to change as the interface is changing
if(sizeof($files) > 0) 
{
	while($max > 0) 
	{
		foreach(array_keys($files) as $key)
		 {
			if($files[$key][score] == $max)
			 {
				$name = find_path($files[$key][p])."/".$files[$key][n];			
				$filename = $files[$key][f];
				$choped = split("\.", $filename);
				$pos = count($choped);
				$ext = strtolower($choped[$pos-1]);
				print("<TABLE WIDTH=$default->table_expand_width BORDER=$default->table_border CELLSPACING=1 CELLPADDING=1>");

        			$CountLines++;
        			$PrintLines = $CountLines % 2;
        			if ($PrintLines == 0)
        			{
                			print("<TR BGCOLOR='$default->table_cell_bg_alt'>");
                		}
        			else
                			print("<TR BGCOLOR='$default->table_cell_bg'>");
				print "<TD ALIGN=CENTER width=5%>";
				//for ($i=$max; $i>0; $i--) {

				//} 
				
				// display results based on relevance (different graphics) and score
				$t_score = $max;
				for ($c=$max; $c>=1; $c--) 
				{	
					if ( $t_score >= 10) 
					{					
						if ( 0 == ($c % 10)) 
						{
							print "<IMG SRC='$default->owl_root_url/graphics/star10.gif' BORDER=0>";
							$t_score = $t_score - 10;	
						}
					} 
					else 
					{
						if ( (0 == ($t_score % 2)) && $t_score > 0 ) 
						{
							print "<IMG SRC='$default->owl_root_url/graphics/star.gif' BORDER=0>";
						}
							$t_score = $t_score - 1;
					}

				}

				//print "<BR>($lang_score $max)";
				print "</TD>";
				print("<TD ALIGN=LEFT WIDTH=40%>");
				print "$hit.  <A HREF='download.php?sess=$sess&id=".$files[$key][id]."&parent=".$files[$key][p]."'>".$name."</A></TD>";
				print("<TD ALIGN=LEFT WIDTH=15%>");

        			if ($files[$key][u] == "1")
                			print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/url.gif' BORDER=0>&nbsp;");
        						else {
                			if (preg_grep("/$ext/",$iconfiles))
                        			print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/$ext.jpg' BORDER=0>&nbsp;");
                			else
                        			print("<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/icons/file.gif' BORDER=0>&nbsp;");
        			}

				print("&nbsp $filename</TD>");
				print("<TD ALIGN=LEFT width=10%>");
                		printFileIcons($files[$key][id],$name,$files[$key][c],$files[$key][u],$default->owl_version_control,$ext);
				print("</TD></TR></TABLE>");
				//print "<TABLE><TR><TD WIDTH=10></TD><TD Align=left>".implode(" ", $files[$key][d])."</TD></TR></TABLE><BR>";
				$hit++;
			}
		}
		$max--;
	}
}
print "<HR WIDTH=\"".$default->table_expand_width."\" ALIGN=\"center\"><BR>";
print "<A HREF='browse.php?sess=$sess&parent=$parent&expand=$expand'><IMG SRC='$default->owl_root_url/locale/$language/graphics/btn_browse.gif' BORDER=0></A><P></P>";

// BEGIN bugfix  Scott Tigr
// error_handler if query empty
} // end of check strlen(query)
else { // if query was empty



print("<p>" . $lang_query_empty . "</p>");

}

include("./lib/footer.inc");

// END bugfix Scott Tigr
?> 
