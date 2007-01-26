<?php
/*
 * Created on 10 Jan 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
