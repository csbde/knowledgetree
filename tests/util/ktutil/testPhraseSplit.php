<?php

require_once('../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

$tests = array(
    // (string, phrases, words)
    array('a"b c"d', array('b c'), array('a','d')), 
);


foreach ($tests as $t) {
    print '<pre>';
    
    $test = $t[0];
    $phrases = $t[1];
    $words = $t[2];
    
    
    $p_expect = implode(', ', $phrases);
    $w_expect = implode(', ',$words);
    
    $res = KTUtil::phraseSplit($test);
    
    $p_got = implode(', ', $res['phrases']);
    $w_got = implode(', ', $res['words']);

    
    if (($w_got == $w_expect) && ($p_got == $p_expect)) {
        print sprintf("Passed: %s\n", $test);
    } else {       
        print "--------\n";
        print sprintf("failed: %s\n", $test); 
        print sprintf("Phrases -  got \"%s\", expected \"%s\"\n", $p_got, $p_expect);
        print sprintf("Words -  got \"%s\", expected \"%s\"\n", $w_got, $w_expect);
        print "--------\n";
    }
}

?>