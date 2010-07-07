<?php 
class ktVar{
	public static function parseString($string='',$xform=array()){
		if(!is_array($xform))$xform=array();
		
		$from=array_keys($xform);
		$to=array_values($xform);
		
		$delim=create_function('&$item,$key,$prefix','$item="[".$item."]";');
		array_walk($from,$delim);
		
		return str_replace($from,$to,$string);
	}	
}
?>