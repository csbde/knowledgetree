<?php

include_once("RESTClient.php");

$rest = new RESTclient();

$inputs = array();
$inputs["appid"] = "YahooDemo";
$inputs["street"] = "701 First Street";
$inputs["city"] = "Sunnyvale";
$inputs["state"] = "CA";

$url = "http://www.knowledgetree.pr/webservice/service.php?class=RestService";
$rest->createRequest("$url","GET",'');
$rest->sendRequest();
$output = $rest->getResponse();
echo $output;

?>