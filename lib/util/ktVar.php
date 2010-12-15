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
	
	public static function quickDebug($object=NULL,$title='Debug Output',$exit=true){
		echo "<hr /><h1>{$title}</h1><pre>".print_r($object,true)."</pre><hr />";
		if($exit)exit;
	}
	
	public static function sortArrayMatrixByKeyValue($array, $sortKey,$asc=true){
		$arr=$array;
		if(is_array($array))if(count($array)>0)if(isset($array[0][$sortKey])){
			$tmpArray=$arr=array();
			foreach($array as $key=>$item){
				$tmpArray[strtolower($item[$sortKey])]=$key;
			}
			if($asc){
				ksort($tmpArray);
			}else{
				krsort($tmpArray);
			}
			foreach($tmpArray as $key){
				$arr[]=$array[$key];
			}
		}
		return $arr;
	}
	
	
	public static function arrayRemoveEmpty($arr=NULL,$trim=false){
		$ridx=array();
		foreach($arr as $key=>$val){
			if($trim){
				$val=trim($val);
				$arr[$key]=$val;
			}
			if(!$val)$ridx[]=$key;
		}
		
		foreach($ridx as $key){
			unset($arr[$key]);
		}
		return $arr;
	}
	
	public static function preg_quote_array($arr=array(),$delimiter=null){
		foreach($arr as $key=>$val){
			$arr[$key]=preg_quote($val,$delimiter);
		}
		return $arr;
	}
	
	/**
	 * 
	 * @param $arrayA
	 * @param $arrayB
	 * @return unknown_type
	 * 
	 * O[n]=2n;
	 */
	public static function array_compare($arrayA=array(),$arrayB=array()){
		$arrayAF=array_flip($arrayA);
		$arrayBF=array_flip($arrayB);
		
		$added=NULL;
		$moved=NULL;
		$removed=NULL;
		
		$added=array_flip(array_diff($arrayBF,$arrayAF));
		$removed=array_flip(array_diff($arrayAF,$arrayBF));
		
		
		$delIdx=array();
		
		foreach($arrayA as $key => $val){
			if(isset($arrayBF[$val]))if($arrayBF[$val] != $key){
				if($arrayA[$key] == $arrayB[$key]){
					$duplicate[$key]=$arrayBF[$val];
				}else{
					$moved[$key]=$arrayBF[$val];
				}
				$delIdx[$key]=true;
			}
		}

		foreach($arrayA as $key => $val){
			if(in_array($key,$arrayBF))if($arrayA[$key]!==$arrayB[$key])if(!isset($delIdx[$key])){
				$iChanged[$val]=$arrayB[$key];
				$changed[$key]=$iChanged;
				$delIdx[$key]=true;
			}
		}
		
		
		foreach($delIdx as $key => $val){
			if(isset($added[$key]))unset($added[$key]);
			if(isset($removed[$key]))unset($removed[$key]);
		}
		
		
		$ret=array('added'=>$added,'removed'=>$removed,'moved'=>$moved,'changed'=>$changed,'duplicate'=>$duplicate);		
		return $ret;
	}
	
	public function volatileResource($url=NULL,$localhost=true){
		if($localhost)if($_SERVER['SERVER_ADDR']!='127.0.0.1'){
			return $url;
		}
		$url.=(strpos($url,'?')>0?"&":"?").rand();
		return $url;
	}
	
}

class ktFileMan{
	public static function fixDirectorySeparators($string=NULL){
		$tx=array("\\"=>DIRECTORY_SEPARATOR,'/'=>DIRECTORY_SEPARATOR);
		$nstr=str_replace(array_keys($tx),array_values($tx),$string);
		$nstr=ktVar::arrayRemoveEmpty(explode(DIRECTORY_SEPARATOR,$nstr),true);
		$nstr=implode(DIRECTORY_SEPARATOR,$nstr);
		$nstr=($nstr[0]==DIRECTORY_SEPARATOR || $nstr[1]==':') ? $nstr : DIRECTORY_SEPARATOR.$nstr;
		return $nstr;
	}
	
	public static function getFolderContents($path=NULL,$deep=false,$excludeMatch=array()){
		if(is_array($excludeMatch)){
			foreach($excludeMatch as $key=>$match){
				$excludeMatch[$key]=self::fixDirectorySeparators($match);
			}
			$ignore='/'.implode('|',ktVar::preg_quote_array($excludeMatch)).'/';
		}else{
			$excludeMatch=NULL;
			$ignore=NULL;
		}
		
		
		if(is_array($path)){
			$path=implode(DIRECTORY_SEPARATOR,$path);
		}
		
		$path=realpath(self::fixDirectorySeparators($path));
		$contents=array();
		
		if($handle=opendir($path)){
			while(($file=readdir($handle))!==false){
				if($file!=='.' && $file !== '..'){
					$fullFile=$path.DIRECTORY_SEPARATOR.$file;
					$match=$ignore==NULL?true:(preg_match($ignore,$fullFile)<=0);
					if($match){
						$type=@filetype($fullFile);
						if($type=='file'){
							$contents[]=$fullFile;
						}
						if($type=='dir' && $deep){
							$contents=array_merge($contents,self::getFolderContents($fullFile,$deep,$excludeMatch));
						}
					}
				}
			}
			closedir($handle);
		}
		
		return $contents;
	}
	
	public static function relativizeList($path,$list){
		foreach($list as $key=>$item){
			$list[$key]=str_replace($path,'',$item);
		}
		return $list;
	}
	
	public static function getSubFolders($path=NULL){
		if(is_array($path)){
			$path=implode(DIRECTORY_SEPARATOR,$path);
		}
		
		$path=realpath(self::fixDirectorySeparators($path)); 
		$contents=array();
		
		if($handle=opendir($path)){
			while(($file=readdir($handle))!==false){
				if($file!=='.' && $file !== '..'){
					$fullFile=$path.DIRECTORY_SEPARATOR.$file;
					$type=@filetype($fullFile); 
					if($type=='dir'){
						$contents[]=$file;
					}
				}
			}
			closedir($handle);
		}
		sort($contents);
		return $contents;
	}
	
	public static function hashList($list=array(),$path){
		if(!is_array($list))$list=array($list);
		$ihashes=array();
		foreach($list as $item){
			$fitem=$path.$item;
			if(is_file($fitem)){
				$ihashes[$item]=hash_file('md5',$fitem);
			}
		}
		$ret=array();
		$ret['fileHashes']=$ihashes;
		$ret['fullHash']=md5(implode('',$ihashes));
		return $ret;
	}
}

?>