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

class KTPageRegistry {
    var $aResources = array();

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTPageRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTPageRegistry'] = new KTPageRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTPageRegistry'];
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

