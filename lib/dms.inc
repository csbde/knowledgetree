<?php

/**
 * $Id$
 *
 * DMS default routines and functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
 
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
