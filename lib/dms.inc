<?php
/**
 * $Id$
 *
 * DMS default routines and functions
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
?>
