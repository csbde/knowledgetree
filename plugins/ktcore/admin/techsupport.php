<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");

class KTSupportDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Support and System information'));
        return true;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/support');
        $oTemplate->setData(array(
            'context' => $this,
        ));
        return $oTemplate->render();
    }
    
    function do_actualInfo() {
        $download = KTUtil::arrayGet($_REQUEST, 'fDownload', false);
        if ($download != false) {
            header("Content-Disposition: attachment; filename=\"php_info.htm\"");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate");
        }
    
        print phpinfo();
        exit(0);
    }

}

?>
