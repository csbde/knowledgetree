<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mb_wordwrap modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mb_wordwrap<br>
 * Purpose:  wrap a multibyte string of text at a given length
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_mb_wordwrap($string,$length=80,$break="\n",$cut=false)
{
    
	$newString = "";
	$index = 0;
	while(mb_strlen($newString) < mb_strlen($string)){
		$newString .= mb_strcut($string, $index, $length, "UTF8") . $break;
		$index += $length;
	}
	return $newString;
	
}
?>