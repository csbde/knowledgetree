<?php

/**
 * $Id$
 *
 * DMS default routines and functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */
 
/**
 * Initialises the web application by making current
 * request parameters global and loading the default language
 */ 
// make request parameters global
if (substr(phpversion(),0,5) >= "4.1.0") {
    // if supported by the installed version of PHP
    import_request_variables('pgc');
} else {
    // do it manually
    if (!EMPTY($_POST)) {
        extract($_POST);
    } else {
        extract($HTTP_POST_VARS);
    }
    
    if (!EMPTY($_GET)) {
        extract($_GET);
    } else {
        extract($HTTP_GET_VARS);
    }
        
    if (!EMPTY($_FILE)) {
        extract($_FILE);
    } else {
        extract($HTTP_POST_FILES);
    }
}
 
// set default language
if(isset($default->owl_lang)) {

    $langdir = "$default->owl_fs_root/locale/$default->owl_lang";

    if(is_dir("$langdir") != 1) {
        die("$lang_err_lang_1 $langdir $lang_err_lang_2");
    } else {

        $sql = new Owl_DB;
        $sql->query("select * from $default->owl_sessions_table where id = '" . session_id() . "'");       
        $sql->next_record();
        $numrows = $sql->num_rows($sql);
        $userID = $sql->f("user_id");
        if($numrows == 1) {
            $sql->query("select * from $default->owl_users_table where id = $userID");
            $sql->next_record();
            $language = $sql->f("language");
            // BEGIN wes fix
            if(!$language) {
                $language = $default->owl_lang;
            }
            // END wes fix
            require("$default->owl_fs_root/locale/$language/language.inc");
            $default->owl_lang = $language;
        } else {
            require("$default->owl_fs_root/locale/$default->owl_lang/language.inc");
        }
    }
} else {
    die("$lang_err_lang_notfound");
}
?>
