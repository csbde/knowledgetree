<?php

/*

  File: html.php
  Author: Chris
  Date: 2000/12/14

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/
getprefs();
gethtmlprefs();

// styles sheet
// this is an absolute URL and not a filesystem reference

$default->styles		= "$default->owl_root_url/lib/styles.css";
?>
