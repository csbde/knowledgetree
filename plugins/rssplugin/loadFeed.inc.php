<?php
/*
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice. 
 * Contributor( s): ______________________________________
 *
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
 $reposonse .= "</table></div><br>";
	 
 echo  $reposonse;
?>
