<?php
class serviceHelper{
	public function __construct(){
		throw new Exception('ServiceHelper is only to be used statically');
	}
	
	function bool2str($bool){
		//TODO: Test this sometime.. a lot shorter - doesn't cater for string though.. but if string then result already correct
		//return ((bool)$bool)?'true':false;
		
		if (is_bool($bool))
		{
			return $bool ? 'true' : 'false';
		}
		if (is_numeric($bool))
		{
			return ($bool+0) ? 'true' : 'false';
		}
		// assume str
		return (strtolower($bool) == 'true') ? 'true' : 'false';
	}
	
	/**
	 * Return human readable sizes
	 *
	 * @param integer $size	The size you want to convert to human readable
	 * @return string
	 */
	function fsize_desc($size){
		$i=0; 
		$iec = array("B", "Kb", "Mb", "Gb", "Tb");
		while (($size/1024)>1) {
			$size=$size/1024;
			$i++;
		}
		return substr($size,0,strpos($size,'.')+3).$iec[$i];
	}
	

	/**
	 * Display byte sizes in human readable representations
	 *
	 * @param integer	$size		Size to represent
	 * @param string	$max		Maximum representational unit (eg B,KB,MB)
	 * @param string	$system		Selecting the system of representation (si,bi&c1 defined)
	 * @param string	$retstring	Format for the return string.
	 * @return String
	 */
	function size_readable($size, $max = null, $system = 'c1', $retstring = '%01.1f %s'){
	    // Pick units
	    $systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
	    $systems['si']['size']   = 1000;
	    $systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
	    $systems['bi']['size']   = 1024;
	    $systems['c1']['prefix'] = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	    $systems['c1']['size']   = 1024;
	    $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];
	
	    // Max unit to display
	    $depth = count($sys['prefix']) - 1;
	    if ($max && false !== $d = array_search($max, $sys['prefix'])) {
	        $depth = $d;
	    }
	
	    // Loop
	    $i = 0;
	    while ($size >= $sys['size'] && $i < $depth) {
	        $size /= $sys['size'];
	        $i++;
	    }
	
	    return sprintf(($sys['prefix'][$i]=='B'?'%01d %s':$retstring), $size, $sys['prefix'][$i]);
	}	
}
?>