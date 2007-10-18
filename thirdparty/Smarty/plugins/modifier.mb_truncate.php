<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mb_truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mb_truncate<br>
 * Purpose:  Truncate a multibyte string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string.
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @return string
 */
function smarty_modifier_mb_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false)
{
    if ($length == 0)
        return '';

    if (mb_strlen($string, 'UTF-8') > $length) {
        mb_internal_encoding("UTF-8");
        
        if (!$break_words)
            $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1));
      
        return html_entity_decode(mb_substr($string, 0, $length, 'UTF-8').$etc, ENT_NOQUOTES, 'UTF-8');
        
    } else
        return $string;
}
?>