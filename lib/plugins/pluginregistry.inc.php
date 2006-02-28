<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

class KTPluginRegistry {
    var $_aPluginDetails = array();
    var $_aPlugins = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPluginRegistry')) {
            $GLOBALS['oKTPluginRegistry'] = new KTPluginRegistry;
        }
        return $GLOBALS['oKTPluginRegistry'];
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

