<?php

/**
 * $Id$
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
 */

class KTPluginRegistry {
    var $_aPluginDetails = array();
    var $_aPlugins = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTPluginRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTPluginRegistry'] = new KTPluginRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTPluginRegistry'];
    }

    function registerPlugin($sClassName, $sNamespace, $sFilename = null) {
        $this->_aPluginDetails[$sNamespace] = array($sClassName, $sNamespace, $sFilename);
    }

    function &getPlugin($sNamespace) {
        if (array_key_exists($sNamespace, $this->_aPlugins)) {
            return $this->_aPlugins[$sNamespace];
        }
        $aDetails = KTUtil::arrayGet($this->_aPluginDetails, $sNamespace);
        if (empty($aDetails)) {
            return null;
        }
        $sFilename = $aDetails[2];
        if (!empty($sFilename)) {
            require_once($sFilename);
        }
        $sClassName = $aDetails[0];
        $oPlugin =& new $sClassName($sFilename);
        $this->_aPlugins[$sNamespace] =& $oPlugin;
        return $oPlugin;
    }

    function &getPlugins() {
        $aRet = array();
        foreach (array_keys($this->_aPluginDetails) as $sPluginName) {
            $aRet[] =& $this->getPlugin($sPluginName);
        }
        return $aRet;
    }
}

