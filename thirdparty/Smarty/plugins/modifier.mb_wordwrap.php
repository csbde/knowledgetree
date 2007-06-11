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
    if ($length == 0)
    return '';

    if (mb_strlen($string) > $length) {

        //if (!$cut) $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1));

        $newString = "";
        $index = 0;
        $breakslen = 0;
        while(mb_strlen($newString)-$breakslen < mb_strlen($string)){
            $newString .= mb_strcut($string, $index, $length, "UTF-8") . $break;
            $index += $length;
            $breakslen += mb_strlen($break);
        }
        return $newString;
    } else return $string;
}
?>