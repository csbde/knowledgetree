<?php

/*

  File: footer.inc
  Author: Chris
  Date: 2000/12/14

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

if ($expand == "1") {
        echo "\t<HR ALIGN=CENTER WIDTH=$default->table_expand_width>";
} else {
        echo "\t<HR ALIGN=CENTER WIDTH=$default->table_collapse_width>";
}

?>

<?php
// BUG Number: 457588
// This is to display the version inforamation
// BEGIN
print("<BR>$lang_engine<BR>");
print("Version: $default->version");
// END
?>
<?php
if (owlusergroup($userid) == 0) {
	print("<BR><A HREF='$default->owl_root_url/admin/index.php?sess=$sess'>$lang_admin</A><BR>");
}
print "<FORM ACTION='$default->owl_root_url/search.php' METHOD=POST><INPUT TYPE=TEXT NAME=query>
<INPUT TYPE=HIDDEN NAME=sess VALUE=$sess><INPUT TYPE=HIDDEN NAME=parent VALUE=$parent>
<INPUT TYPE=HIDDEN NAME=expand VALUE=$expand><INPUT TYPE=SUBMIT VALUE=$lang_search></FORM>";
?>
</BODY>
</HTML>

