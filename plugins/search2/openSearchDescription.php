<?php

require_once('../../config/dmsDefaults.php');

//header('Content-Type: text/xml');

$url = KTUtil::kt_url();

$search_url = $url . "/search2.php?action=process&amp;txtQuery=GeneralText+contains+%22{searchTerms}%22";

$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

$xml = "<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\">\r\n";
$xml .= "<ShortName>KnowledgeTree " ._kt('Quick Search') . "</ShortName>\r\n";
$xml .= "<Description>" . _kt('Search metadata and content on KnowledgeTree') . "</Description>\r\n";
$xml .= "<Image width=\"16\" height=\"16\" type=\"image/x-icon\">$url/resources/favicon.ico</Image>\r\n";
$xml .= "<Url type=\"text/html\" template=\"$search_url\"/>\r\n";
$xml .= "<OutputEncoding>UTF-8</OutputEncoding>\n";
$xml .= "<InputEncoding>UTF-8</InputEncoding>\n";
$xml .= "</OpenSearchDescription>";

print $xml;

?>