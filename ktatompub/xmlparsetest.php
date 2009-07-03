<?php

class xml2array{
	public static function parse($xml){
		return self::parsetag(simplexml_load_string($xml));
	}

	private static function parsetag($xml,$ns=NULL,$rootXML=NULL){
		if(!$rootXML)$rootXML=$xml;
		$tagName=$xml->getName();
		if($ns)$tagName=$ns.':'.$tagName;
		$array=array();
		$array[$tagName]['@attributes']=self::getAttributes($xml,$rootXML);
		if(self::hasChildren($xml,$rootXML)){
			$children=self::getChildren($xml,$rootXML);
			echo '<b>'.$tagName.'</b><br /><pre>'.print_r($children,true).'</pre>';
			foreach($children as $fullChildName=>$childCollection){
				//$child=$childCollection;
				$childName=split(':',$fullChildName);
				foreach($childCollection as $child){
					$childParsed=self::parsetag($child,$childName[0],$rootXML);
					//$cIndex=count($array[$tagName][$childName]);
					$array[$tagName][]=$childParsed;
				}

			}
		}else{
			$array[$tagName]['value']=(string)$xml;
		}
		return $array;
	}

	private static function hasChildren($xml,$rootXML){
		return count(self::getChildren($xml,$rootXML))>0;
	}

	private static function getAttributes($xml,$rootXML){
		$attr=array();
		$namespaces=$rootXML->getNamespaces(true);
		foreach($namespaces as $namespace=>$uri){
			$nsAttrs=(array)$xml->attributes($uri);
			$nsAttrs=isset($nsAttrs['@attributes'])?$nsAttrs['@attributes']:array();
			foreach($nsAttrs as $nsAttr=>$nsAttrVal){ //TODO: Support for multiple same name tags
				$attr[$namespace.':'.$nsAttr]=$nsAttrVal;
			}
		}
		return $attr;
	}

	private static function getChildren($xml,$rootXML){
		$children=array();
		$namespaces=$rootXML->getNamespaces(true);
		foreach($namespaces as $namespace=>$uri){
			$nsChildren=$xml->children($uri);
			foreach($nsChildren as $nsChild){ //TODO: Support for multiple same name tags
				$childRealName=$namespace.':'.$nsChild->getName();
				if(!isset($children[$childRealName]))$children[$childRealName]=array();
				if(!is_array($children[$childRealName]))$children[$childRealName]=array();
				$children[$childRealName][]=$nsChild;
			}
		}
		return $children;
	}

}



$xml='<?xml version="1.0" encoding="utf-8"?>
<service xmlns="http://www.w3.org/2007/app" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05" xmlns:alf="http://www.alfresco.org">
  <workspace cmis:repositoryRelationship="self">
    <atom:title>Main Repository</atom:title>

    <cmis:repositoryInfo>
      <cmis:repositoryId>0f91f397-7cd1-479b-9f56-266affe188d8</cmis:repositoryId>
      <cmis:repositoryName>Main Repository</cmis:repositoryName>
      <cmis:repositoryRelationship>self</cmis:repositoryRelationship>
      <cmis:repositoryDescription></cmis:repositoryDescription>
      <cmis:vendorName>Alfresco</cmis:vendorName>
      <cmis:productName>Alfresco Repository (Labs)</cmis:productName>
      <cmis:productVersion>3.0.0 (Stable 1526)</cmis:productVersion>
      <cmis:rootFolderId>http://localhost:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home</cmis:rootFolderId>
      <cmis:capabilities>
        <cmis:capabilityMultifiling>true</cmis:capabilityMultifiling>
        <cmis:capabilityUnfiling>false</cmis:capabilityUnfiling>
        <cmis:capabilityVersionSpecificFiling>false</cmis:capabilityVersionSpecificFiling>
        <cmis:capabilityPWCUpdateable cmis:bla="bleh">true</cmis:capabilityPWCUpdateable>
        <cmis:capabilityPWCSearchable>true</cmis:capabilityPWCSearchable>
        <cmis:capabilityAllVersionsSearchable>false</cmis:capabilityAllVersionsSearchable>
        <cmis:capabilityQuery>both</cmis:capabilityQuery>
        <cmis:capabilityJoin>nojoin</cmis:capabilityJoin>
        <cmis:capabilityFullText>fulltextandstructured</cmis:capabilityFullText>
      </cmis:capabilities>
      <cmis:cmisVersionsSupported>0.5</cmis:cmisVersionsSupported>
      <cmis:repositorySpecificInformation></cmis:repositorySpecificInformation>
    </cmis:repositoryInfo>

    <collection href="http://localhost:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children" cmis:collectionType="root-children">
      <atom:title>root collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/descendants" cmis:collectionType="root-descendants">
      <atom:title>root collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/checkedout" cmis:collectionType="checkedout">
      <atom:title>checkedout collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/unfiled" cmis:collectionType="unfiled">
      <atom:title>unfiled collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/types" cmis:collectionType="types-children">
      <atom:title>type collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/types" cmis:collectionType="types-descendants">
      <atom:title>type collection</atom:title>
    </collection>
    <collection href="http://localhost:8080/alfresco/service/api/query" cmis:collectionType="query">
      <atom:title>query collection</atom:title>
    </collection>

  </workspace>
</service>
';

$sxml=simplexml_load_string($xml);
$struct=json_decode(json_encode($sxml),true);



echo '<pre>'.htmlentities($xml).'</pre>';
//echo '<hr /><pre>'.print_r($struct,true).'</pre>';
//echo '<hr /><pre>'.print_r($sxml,true).'</pre>';
//cho '<hr /><pre>'.print_r(xml2array($xml),true).'</pre>';
echo '<hr /><pre>'.print_r(xml2array::parse($xml),true).'</pre>';


//echo http_get_request_headers();

?>