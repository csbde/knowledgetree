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

class KTPageRegistry {
    var $aResources = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTPageRegistry')) {
            $GLOBALS['oKTPageRegistry'] = new KTPageRegistry;
        }
        return $GLOBALS['oKTPageRegistry'];
    }

    function registerPage($sPath, $sClassName, $sFilename = null) {
        $this->aResources[$sPath] = array($sPath, $sClassName, $sFilename);
    }

    function getPage($sPath) {
        $aInfo = KTUtil::arrayGet($this->aResources, $sPath);
        if (empty($aInfo)) {
            return null;
        }
        $sClassName = $aInfo[1];
        $sFilename = $aInfo[2];
        if ($sFilename) {
            require_once($sFilename);
        }
        return new $sClassName;
    }
}

