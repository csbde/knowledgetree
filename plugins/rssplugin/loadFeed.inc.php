<?php
/**
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
 */
 require_once('../../config/dmsDefaults.php');
 require_once(KT_DIR. '/plugins/rssplugin/rss2array.inc.php');
 require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');
 
 $feed = $_GET["feed"];
 $user = $_GET["user"];
 
 // Check if the feed matches a url
 if(!preg_match("/^http:\/\/([^\/]+)(.*)$/", $feed, $matches)){
 	// If not, it is an internal feed
 	$aRSSArray = KTrss::getInternalFeed($user);
 }else{
 	// If it is a url, it is an external feed
 	$aRSSArray = rss2array($feed);
 }
 
 // Prepare response data to be passed back to page
 $reposonse = "<h3>".$aRSSArray[channel][title]."</h3>" .
 		"<div class='outerContainer' id='outerContainer'>" .
 		"<table width='90%'>";
		for($i=0;$i<count($aRSSArray[items]);$i++){
			 $reposonse .= "<tr>
				<td colspan='2'><strong><a href='".$aRSSArray[items][$i][link]."' target='_blank'>".$aRSSArray[items][$i][title]."</a><strong></td>
			</tr>
			<tr>
				<td>".$aRSSArray[items][$i][description]."</td>
			</tr>
			<tr><td colspan='2'><br></td></tr>";
		}
 $reposonse .= "</table></div>";
	 
 echo  $reposonse;
?>
