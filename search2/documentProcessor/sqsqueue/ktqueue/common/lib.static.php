<?php

class LibException extends Exception{}

if(!defined('LIB_STDOUT'))define('LIB_STDOUT',true);

class lib{
	/**
     * uuid()
     * 
     * Generates a random uuid.
     * **Depends on 
     * 
     * @return uuid
     */
	public static function uuid(){
        // The field names refer to RFC 4122 section 4.1.2
        return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
        mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
        mt_rand(0, 65535), // 16 bits for "time_mid"
        mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
        bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
        // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
        // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
        // 8 bits for "clk_seq_low"
        mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
        );
    }
    
    /**
     * Parse a string containing references to associative array keys and return the string with values inserted
     * @param $template			The template string. eg: 'My name is [name], I am [sex], and I am [age] years old.'
     * @param $values			The array containing the values to substitute. eg. Array('name'=>'peter','sex'=>'male', 'age'=>25)
     * @param $preDelim			OPTIONAL. This allows you to override the method for delimiting the values in the template.
     * @param $postDelim		OPTIONAL. This allows you to override the method for delimiting the values in the template.
     * @return string			eg: 'My name is peter, I am male, and I am 25 years old.'
     */
    public static function parseString($template=NULL,$values=null,$preDelim='[',$postDelim=']'){
    	if(!is_array($values))throw new LibException("\$values is not an array in lib::parseString()");
    	$xfrom=array_keys($values);
    	$xto=array();
    	foreach($xfrom as $key=>$value){
    		$xfrom[$key]=$preDelim.$value.$postDelim;
    		$xto[$key]=$values[$value];
    	}
    	$parsedString=str_replace($xfrom,$xto,$template);
    	return $parsedString;
    }
    
	/**
	 * Serialize the object tokenizing chr(0)
	 * @param $obj		The object to be serialized
	 * @return string
	 */
	public static function sSerialize($obj=null){
		return str_replace("\0", '~snl~',serialize($obj));
	}
	
	/**
	 * deSerialize String de-tokenizing chr(0)
	 * @param $string
	 * @return object
	 */
	public static function sUnserialize($string=null){
		$obj=@unserialize(str_replace('~snl~',"\0",$string));
		return $obj;		
	}
	
	public static function printVar($var=NULL,$title=NULL){
		$id=uniqid('printVar_');
		if($title)echo "<H4 style=\"cursor:pointer\" onClick=\"var e=document.getElementById('{$id}');e.style.display=(e.style.display=='none'?'block':'none');\">{$title}</H3>";
		echo '<hr /><pre id="'.$id.'"'.($title?'style="display:none"':'').'>'.print_r($var,true).'</pre>';
	}

	/**
	 * Send messages to standard out. Encapsulated in this function in order to easily turn this on or off
	 * @param $msg
	 * @return void
	 */
	public static function out($msg=null,$do=true){
    	if(LIB_STDOUT && $do)fwrite(STDOUT, $msg."\n");
    }
    
	/**
	 * Recursively processes an array and returns the same array with values urlencoded
	 * @param String/Array $var
	 * @return String/Array
	 */
    public static function aUrlEncode($var=NULL){
    	if(is_array($var)){
    		$ret=array();
    		foreach ($var as $idx=>$val){
    			$ret[$idx]=self::aUrlEncode($val);
    		}
    		return $ret;
    	}else{
    		return urlencode($var);
    	}
    }
    
    
    /**
     * Converts an associative array into an indexed array of key value pairs
     * @param $array
     * @return array
     */
    public static function array2kvp(array $array,$valueQuotes=false,$keyQuotes=false){
    	$ret=array();
    	$keyQuote=$keyQuotes?'"':'';
    	$valueQuote=$valueQuotes?'"':'';
    	if(is_array($array)){
    		foreach($array as $key=>$value){
    			$ret[]="{$keyQuote}{$key}{$keyQuote}={$valueQuote}{$value}{$valueQuote}";
    		}
    	}
    	return $ret;
    }
    
}

?>