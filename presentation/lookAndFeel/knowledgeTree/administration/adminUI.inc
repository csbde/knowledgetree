<?php

/**
 * Displays heading
 */
function renderHeading($sHeading, $sSectionName = "") {
    global $default;
    
    if ($sSectionName == "") {
        $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    }
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    return $sToRender;
}
?>
