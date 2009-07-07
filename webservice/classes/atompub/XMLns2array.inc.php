<?php

class XMLns2array{

	private static $namespaces=null;

	/**
	 * Parse an XML document into an array. Supports namespaces. Uses SimpleXML
	 *
	 * @param String/SimpleXMLElement $xml
	 * @return Array
	 */
	public static function parse($xml){
		$xml=(get_class($xml)=='SimpleXMLElement')?$xml:@simplexml_load_string($xml);
		if(get_class($xml)!='SimpleXMLElement') die('The string passed is not a valid XML string or an SimpleXMLElement');
		self::$namespaces=$xml->getNamespaces(true);
		return self::parsetag($xml);
	}

	/**
	 * Parse a single tag recursively calling self to parse children.
	 *
	 * @param SimpleXMLElement $xml					The SimpleXMLElement to convert to an array
	 * @return Array
	 */
	private static function parsetag($xml){
		$array=array();
		$array['@attributes']=self::getAttributes($xml);
		$array['@children']=array();
		if(self::hasChildren($xml)){
			$children=self::getChildren($xml);
			$tmpChildren=array();
			foreach($children as $fullChildName=>$childCollection){
				$childName=split(':',$fullChildName);
				$fullChildName=trim($fullChildName,' :');
				foreach($childCollection as $child){
					$childParsed=self::parsetag($child);
					if(!isset($tmpChildren[$fullChildName]))$tmpChildren[$fullChildName]=array();
					$tmpChildren[$fullChildName][]=$childParsed;
				}
				$array['@children']=array_merge($array['@children'],$tmpChildren);
			}
		}else{
			$array['@value']=(string)$xml;
		}
		return $array;
	}

	/**
	 * Tests whether a node has children
	 *
	 * @param SimpleXMLElement $xml					The node to test
	 * @return Array
	 */
	private static function hasChildren($xml){
		return count(self::getChildren($xml))>0;
	}

	/**
	 * Gets the attributes of a node
	 *
	 * @param SimpleXMLElement $xml					The node to process
	 * @return Array
	 */
	private static function getAttributes($xml){
		$attr=array();
		foreach(self::$namespaces as $namespace=>$uri){
			$nsAttrs=(array)$xml->attributes($uri);
			$nsAttrs=isset($nsAttrs['@attributes'])?$nsAttrs['@attributes']:array();
			foreach($nsAttrs as $nsAttr=>$nsAttrVal){
				$attr[$namespace.':'.$nsAttr]=$nsAttrVal;
			}
		}
		return $attr;
	}

	/**
	 * Returns the children of a node
	 *
	 * @param SimpleXMLElement $xml					The node to process
	 * @return unknown
	 */
	private static function getChildren($xml){
		$children=array();
		foreach(self::$namespaces as $namespace=>$uri){
			$nsChildren=$xml->children($uri);
			foreach($nsChildren as $nsChild){
				$childRealName=$namespace.':'.$nsChild->getName();
				if(!isset($children[$childRealName]))$children[$childRealName]=array();
				if(!is_array($children[$childRealName]))$children[$childRealName]=array();
				$children[$childRealName][]=$nsChild;
			}
		}
		return $children;
	}

}
?>