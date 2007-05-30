<?php
/**
 * $Id$
 *
 * Template factory class
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
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
            if (KTUtil::isAbsolutePath($path)) {
                $fulldirectory = $path . "/";
                foreach (array_keys($this->aTemplateRegistry) as $suffix) {
                    $fullpath = $fulldirectory . $templatename . "." .  $suffix;
                    if (file_exists($fullpath)) {
                        $aPossibilities[$loc] = array($suffix, $fullpath);
                    }
                }
            }
            $fulldirectory = KT_DIR . "/" . $path . "/";
            foreach (array_keys($this->aTemplateRegistry) as $suffix) {
                $fullpath = $fulldirectory . $templatename . "." .  $suffix;
                if (file_exists($fullpath)) {
                    $aPossibilities[$loc] = array($suffix, $fullpath);
                }
            }
        }

        if (count($aPossibilities) === 0) {
            return PEAR::raiseError(_kt("No template found"));
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
            return PEAR::raiseError(_kt("Could not find template language"));
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
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTTemplating')) {
            $GLOBALS['_KT_PLUGIN']['oKTTemplating'] = new KTTemplating;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTTemplating'];
    }
    // }}}

    function renderTemplate($sTemplate, $aOptions) {
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate =& $oTemplating->loadTemplate($sTemplate);
	return $oTemplate->render($aOptions);
    }

}

?>
