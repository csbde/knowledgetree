<?php
/**
 * $Id$
 *
 * Template factory class
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . "/templating/smartytemplate.inc.php");

class KTTemplating {
    /** Templating language registry */
    var $aTemplateRegistry;

    /** Location registry */
    var $aLocationRegistry;
    
    // {{{ KTTemplating
    function KTTemplating() {
        $this->aTemplateRegistry = array(
            "smarty" => "KTSmartyTemplate",
        );

        $this->aLocationRegistry = array(
            "core" => "templates",
        );
    }
    // }}}

    // {{{ _chooseTemplate
    function _chooseTemplate($templatename, $aPossibilities) {
        $aLocs = array_keys($aPossibilities);
        return $aPossibilities[$aLocs[count($aLocs) - 1]];
    }
    // }}}

    // {{{ _findTemplate
    function _findTemplate($templatename) {
        $aPossibilities = array();

        foreach ($this->aLocationRegistry as $loc => $path) {
            $fulldirectory = KT_DIR . "/" . $path . "/";
            foreach (array_keys($this->aTemplateRegistry) as $suffix) {
                $fullpath = $fulldirectory . $templatename . "." .  $suffix;
                if (file_exists($fullpath)) {
                    $aPossibilities[$loc] = array($suffix, $fullpath);
                }
            }
        }

        if (count($aPossibilities) === 0) {
            return PEAR::raiseError("No template found");
        }

        return $this->_chooseTemplate($templatename, $aPossibilities);
    }
    // }}}
    
    // {{{ loadTemplate
    /**
     * Create an object that conforms to the template interface, using
     * the correct template system for the given template.
     *
     * KTI: Theoretically, this will do path searching in multiple
     * locations, allowing the user and possibly third-parties to
     * replace templates.
     */
    function &loadTemplate($templatename) {
        $res = $this->_findTemplate($templatename);
        if (PEAR::isError($res)) {
            return $res;
        }
        list($sLanguage, $sTemplatePath) = $res;
        $sClass = $this->aTemplateRegistry[$sLanguage];
        if (!class_exists($sClass)) {
            return PEAR::raiseError("Could not find template language");
        }
        
        $oTemplate =& new $sClass($sTemplatePath);
        return $oTemplate;
    }
    // }}}

    // {{{ addLocation
    function addLocation ($descr, $loc) {
        $this->aLocationRegistry[$descr] = $loc;
    }
    // }}}

    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTTemplating')) {
            $GLOBALS['oKTTemplating'] = new KTTemplating;
        }
        return $GLOBALS['oKTTemplating'];
    }
    // }}}
}

?>
