<?php
// instantiate phpsniffer
$default->phpSniff = new phpSniff($_SERVER["HTTP_USER_AGENT"]);
$default->browser = $default->phpSniff->property("browser");
$default->version = $default->phpSniff->property("version");
$default->log->info("browser=" . $default->browser . "; version=" . $default->version);
$default->bNN4 = ($default->browser == "ns") && ($default->phpSniff->property("maj_ver") == "4");
$default->scroll = array();
if ( ($default->browser == "moz") && ($default->version <= 4.79)) {
    $default->scroll["upArrowLeft"] = "780";
    $default->scroll["upArrowTop"] = "190";
    $default->scroll["downArrowLeft"] = "780";
    $default->scroll["downArrowTop"] = "585";
    
    $default->scroll["textBoxLeft"] = "145";
    $default->scroll["textBoxTop"] = "190";
    $default->scroll["textBoxWidth"] = "620";
    $default->scroll["textBoxHeight"] = "395";
} elseif ( ($default->browser == "mz")) {
	// mozilla
    $default->scroll["upArrowLeft"] = "742";
    $default->scroll["upArrowTop"] = "107";
    $default->scroll["downArrowLeft"] = "742";
    $default->scroll["downArrowTop"] = "500";
    
    $default->scroll["textBoxLeft"] = "135";
    $default->scroll["textBoxTop"] = "105";
    $default->scroll["textBoxWidth"] = "600";
    $default->scroll["textBoxHeight"] = "398";
} elseif ($default->browser == "ie") {
    // MSIE
    $default->scroll["upArrowLeft"] = "745";
    $default->scroll["upArrowTop"] = "122";
    $default->scroll["downArrowLeft"] = "745";
    $default->scroll["downArrowTop"] = "524";
    
    $default->scroll["textBoxLeft"] = "134";
    $default->scroll["textBoxTop"] = "118";
    $default->scroll["textBoxWidth"] = "608";
    $default->scroll["textBoxHeight"] = "420";
} elseif ( ($default->browser == "ns") && ($default->version == "6.2.3") ) {
    $default->scroll["upArrowLeft"] = "745";
    $default->scroll["upArrowTop"] = "110";
    $default->scroll["downArrowLeft"] = "745";
    $default->scroll["downArrowTop"] = "510";
    
    $default->scroll["textBoxLeft"] = "132";
    $default->scroll["textBoxTop"] = "104";
    $default->scroll["textBoxWidth"] = "610";
    $default->scroll["textBoxHeight"] = "385";
} elseif ( ($default->browser == "ns") && ($default->version == "7.0") ) {
    $default->scroll["upArrowLeft"] = "742";
    $default->scroll["upArrowTop"] = "107";
    $default->scroll["downArrowLeft"] = "742";
    $default->scroll["downArrowTop"] = "508";
    
    $default->scroll["textBoxLeft"] = "132";
    $default->scroll["textBoxTop"] = "103";
    $default->scroll["textBoxWidth"] = "608";
    $default->scroll["textBoxHeight"] = "418";
} elseif ($default->browser == "fb") {
    $default->scroll["upArrowLeft"] = "742";
    $default->scroll["upArrowTop"] = "107";
    $default->scroll["downArrowLeft"] = "742";
    $default->scroll["downArrowTop"] = "518";
    
    $default->scroll["textBoxLeft"] = "132";
    $default->scroll["textBoxTop"] = "103";
    $default->scroll["textBoxWidth"] = "608";
    $default->scroll["textBoxHeight"] = "427";
} else {
    $default->scroll["upArrowLeft"] = "742";
    $default->scroll["upArrowTop"] = "107";
    $default->scroll["downArrowLeft"] = "742";
    $default->scroll["downArrowTop"] = "518";
    
    $default->scroll["textBoxLeft"] = "132";
    $default->scroll["textBoxTop"] = "103";
    $default->scroll["textBoxWidth"] = "608";
    $default->scroll["textBoxHeight"] = "427";	
}
$default->scroll["height"] = $default->scroll["textBoxTop"] + $default->scroll["textBoxHeight"];
?>