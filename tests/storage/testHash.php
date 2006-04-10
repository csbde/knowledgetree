<?php

$aExpectedResults = array(
    1 => '00/1',
    600 => '06/600',
    2300 => '23/2300',
    12300 => '01/23/12300',
);

function blah($iId) {
    $str = (string)$iId;
    if (strlen($str) < 4) {
        $str = sprintf('%s%s', str_repeat('0', 4 - strlen($str)), $str);
    }
    if (strlen($str) % 2 == 1) {
        $str = sprintf('0%s', $str);
    }

    $str = substr($str, 0, -2);
    
    return sprintf("%s/%d", preg_replace('#(\d\d)(\d\d)#', '\1/\2', $str), $iId);
}

foreach ($aExpectedResults as $iInput => $sWanted) {
    $sResult = blah($iInput);
    if ($sResult !== $sWanted) {
        print "Expected result was $sWanted, but got $sResult for $iInput\n";
    }
}


$aResults = array();
for ($c = 0; $c < 1000000; $c++) {
    $sResult = blah($c);
    print $sResult . "\n";
}
