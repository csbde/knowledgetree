<?php

require_once ("./lib/visualpatterns/PatternMainPage.inc");
require_once ("./lib/visualpatterns/PatternImage.inc");
require_once ("./lib/visualpatterns/PatternTableLinks.inc");
require_once ("./lib/visualpatterns/PatternTableSqlQuery.inc");


//North west image
$img = new PatternImage("file://C:/temp/test/al.jpg");

//build the top menu of links
$aTopMenuLinks = array(0=>"www.google.com",1=>"www.yahoo.com",2=>"www.msn.com");
$aTopMenuText = array(0=>"google",1=>"yahoo",2=>"msn");
$oPatternTableLinks = new PatternTableLinks($aTopMenuLinks, $aTopMenuText, 3, 1);

//build the central grid for paging through results
$aCentralPageColumns = array(0=>"name",1=>"parent",2=>"security");
$aColumnTypes = array(0=>1,1=>2,2=>1);
$oTableSqlQuery = & new PatternTableSqlQuery("Folders", $aCentralPageColumns, $aColumnTypes); 
($HTTP_GET_VARS["fStartIndex"]) ? $oTableSqlQuery->setStartIndex($HTTP_GET_VARS["fStartIndex"]) : $oTableSqlQuery->setStartIndex(0);
$oTableSqlQuery->setLinkType(1);



//get a page 
$tmp = new PatternMainPage();

//put the page together
$tmp->setNorthWestPayload($img);
$tmp->setNorthPayload($oPatternTableLinks);
$tmp->setCentralPayload($oTableSqlQuery);
$tmp->setFormAction("Navigate.inc");
$tmp->render();

?>
