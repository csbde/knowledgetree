<?php
/**
 * $Id$
 *
 * i18n helper functions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
/**
 * Returns a list of available gettext locales.
 */
function getInstalledLocales() {
	global $default;
    // get a list of directories in ($default->fileSystemRoot . "/i18n")
    $aLocales = array();
    $aLocales[] = "en";
    $aLocales[] = "en_GB";
    $aLocales[] = "en_ZA";
	if ($handle = opendir($default->fileSystemRoot . "/i18n")) {
	   while (false !== ($file = readdir($handle))) {
	       if ($file != "." && $file != ".." && 
	           $file != "CVS" && is_dir("$default->fileSystemRoot/i18n/$file")) {
	           $aLocales[] = $file;
	       }
	   }
	   closedir($handle);
	}
	return $aLocales;
}
?>
