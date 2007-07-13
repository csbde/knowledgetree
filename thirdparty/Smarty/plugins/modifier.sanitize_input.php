<?php

function smarty_modifier_sanitize_input($string, $esc_type = 'html', $charset='UTF-8')
{
    $string = mb_ereg_replace("'","&#039;", $string);
    $string = mb_ereg_replace('"',"&quot;", $string);
    $string = mb_ereg_replace('<',"&lt;", $string);
    $string = mb_ereg_replace('>',"&gt;", $string);
    return $string;
}


?>
