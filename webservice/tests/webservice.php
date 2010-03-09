<?php
$wsdl = "http://".$_SERVER['HTTP_HOST']."/webservice/webservice.php?class=CMISService&wsdl";
echo "<strong>WSDL file:</strong> ".$wsdl."<br>\n";

$options = Array('actor' =>'http://127.0.0.1',
				 'trace' => true);
$client = new SoapClient($wsdl,$options);



echo "<hr> <strong>Result from getrepositories call:</strong><br>";
$res = $client->getRepositories();
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

echo "<hr> <strong>Result from getrepositoryinfo call:</strong><br>";

$res = $client->getRepositoryInfo('1');
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

echo "<hr> <strong>Result from gettypes call:</strong><br>";

$res = $client->getTypes('1');
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

echo "<hr> <strong>Result from gettypedefinition (document) call:</strong><br>";

$res = $client->getTypeDefinition('1', 'Document');
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

echo "<hr> <strong>Result from gettypedefinition (folder) call:</strong><br>";

$res = $client->getTypeDefinition('1', 'Folder');
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

/*

echo "<hr> <strong>Result from getdescendants call:</strong><br>";

$res = $client->getDescendants('1', 'F1', false, false, 3);
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

echo "<hr> <strong>Result from getchildren call:</strong><br>";

$res = $client->getChildren('1', 'F1', false, false);
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());
// *
// */

/*
echo "<hr> <strong>Result from getfolderparent call:</strong><br>";

$res = $client->getFolderParent('1', 'F566', false, false, false);
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());

//echo "<hr><strong>Result from getobjectparents call:</strong><br>";
//
//$res = $client->getObjectParents('1', 'F566', false, false);
//print_r($res);
//echo "<hr><strong>Raw Soap response:</strong><br>";
//echo htmlentities($client->__getLastResponse());

echo "<hr><strong>Result from getobjectparents call:</strong><br>";

$res = $client->getProperties('1', 'F566', false, false);
print_r($res);
echo "<hr><strong>Raw Soap response:</strong><br>";
echo htmlentities($client->__getLastResponse());
*/
// TODO add test of returnToRoot?  would need known existing folder other than DroppedDocuments (F566)

?>