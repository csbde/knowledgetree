<?php
/**
* Presentation information when updating group properties is successful
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

require_once("../../../../config/dmsDefaults.php");
require_once("editUserPrefsUI.inc");

global $default;

if(checkSession()) {

    // include the page template (with navbar)
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getEditPageSuccess());
    $main->setCentralPayload($oPatternCustom);
    $main->render();

}
?>