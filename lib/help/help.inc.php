<?php

class KTHelp {
    function getHelpStringForSection($sSection) {
        global $default;
        $sQuery = "SELECT hlp.help_info AS helpinfo FROM
            $default->help_table AS hlp WHERE hlp.fSection = ?";
        $aParams = array($sSection);
        $sHelpFile = DBUtil::getOneResultKey(array($sQuery, $aParams), 'helpinfo');
        if (PEAR::isError($sHelpFile)) {
            return $sHelpFile;
        }
        $sQuery = "SELECT hlprp.description AS contents FROM
            $default->help_replacement_table AS hlprp WHERE
            hlprp.name = ?";
        $aParams = array($sHelpFile);
        $sHelpContents = DBUtil::getOneResultKey(array($sQuery,
                    $aParams), 'contents');
        if (PEAR::isError($sHelpContents)) {
            return $sHelpContents;
        }
        if (!(is_null($sHelpContents) || trim($sHelpContents) === "")) {
            return $sHelpContents;
        }
        return file_get_contents("$default->uiDirectory/help/" .  $sHelpFile);
    }
}

?>
