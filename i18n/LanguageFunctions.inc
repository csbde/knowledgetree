<?php

function getTranslatedString($sStringToTranslate, $aVariables) {
	//var_dump($aVariables);
	//var_dump($sStringToTranslate);	
	if (count($aVariables) > 0) {
		//variables to replace
		for ($i = 0; $i < count($aVariables); $i++) {
			$sStringToTranslate = str_replace("{{$i}}",$aVariables[$i], $sStringToTranslate);						
		}
	}
	return $sStringToTranslate;
}

?>