<?php

/**
* Presentation information for changing system info
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getPage() {
    global $default;
    $oSystem = new System;

    global $default;

    $aRowNames = array ("LDAP Server: ",
                        "LDAP Root DN: ",
                        "Email Server:",
                        "Administrator Email Address: ",
                        "Administrator Name: ",
                        "Default Email Address: ",
                        "Default Email Name: ",
                        "Document Root: ",
                        "Language Directory: ",
                        "User Interface Directory: ",
                        "Root URL: ",
                        "Graphics URL: ",
                        "User Interface URL: ",
                        "Default Language: ",
                        "Session Timeout: ");

    $aNames = array("fldapServer",
                    "fldapRootDn",
                    "femailServer",
                    "femailAdmin",
                    "femailAdminName",
                    "femailFrom",
                    "femailFromName",
                    "fdocumentRoot",
                    "flanguageDirectory",
                    "fuiDirectory",
                    "frootUrl",
                    "fgraphicsUrl",
                    "fuiUrl",
                    "fdefaultLanguage",
                    "fsessionTimeout");

    $aValue = $aNames;
    // todo

    // create a for loop that'll print out the names and input field with the value in
    // use the system table to get the value into the text field, by passing it the name
    // then loop a database entry to add everything...
    // and then when submit...run a set thingy..

    $sToRender .= "<table width=\"600\">" . renderHeading("System Settings") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><b>Current System Settings: </b></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    for($i = 0; $i < count($aNames); $i++) {
        if ($oSystem->initialised()) {
            $sToRender .= "<td>". ($i+1) .".</td><td>" .$aRowNames[$i] ." </td><td><input size = \"40\" type=\"text\" name=\"". $aNames[$i] ."\" value =\"". $oSystem->get(substr($aNames[$i],1)) ."\"></td>\n";
        } else {
            $sSetting = substr($aNames[$i],1);
            $sToRender .= "<td>". ($i+1) .".</td><td>" .$aRowNames[$i] ." </td><td><input size = \"40\" type=\"text\" name=\"". $aNames[$i] ."\" value =\"". $default->$sSetting ."\"></td>\n";
        }
        $sToRender .= "</tr>\n";

    }
    //$oSystem->generateRequiredFieldValidation($aNames,$aRowNames);
    // do some validation
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";

    for ($i = 0; $i < count($aNames); $i++) {
        $sToRender .= "\tif (!(validRequired(document.MainForm." .$aNames[$i] . ", '" . $aRowNames[$i] . "'))) {\n";
        $sToRender .= "\t\treturn false;\n\t}\n";

    }
    $sToRender .= "return true;\n}\n";
    $sToRender .= "//-->\n</script>\n\n";

    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Submit\" border=\"0\"/></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// if edition is successful print otu success page
function getPageSuccess() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("System Settings") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
    $sToRender .= "<tr><td>The Settings were successfully modified</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=systemAdministration\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}
?>