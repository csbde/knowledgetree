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
 */

class KTCache {
    var $aRollbackList = array();

    // {{{ getSingleton
    static function &getSingleton () {
    	static $singleton = null;
    	if (is_null($singleton))
    	{
    		$singleton = new KTCache();
    	}
    	return $singleton;
    }
    // }}}

    // takes an Entity type-name, and an array of the failed attrs.
    function alertFailure($sEntityType, $aFail) {
        $sMessage = sprintf('Failure in cache-comparison on type "%s":  %s', $sEntityType, implode(', ', $aFail));
        global $default;
        $default->log->error($sMessage);
        $_SESSION['KTErrorMessage'][] = $sMessage;
    }

    function KTCache() {
        require_once("Cache/Lite.php");
        require_once(KT_LIB_DIR . '/config/config.inc.php');

        $aOptions = array();
        $oKTConfig = KTConfig::getSingleton();
        $this->bEnabled = $oKTConfig->get('cache/cacheEnabled', false);
        if (empty($this->bEnabled)) {
            return;
        }

        $aOptions['cacheDir'] = $oKTConfig->get('cache/cacheDirectory') . "/";
        $user = KTLegacyLog::running_user();
        if ($user) {
            $aOptions['cacheDir'] .= $user . '/';
        }
        if (!file_exists($aOptions['cacheDir'])) {
            mkdir($aOptions['cacheDir']);
        }
        $aOptions['lifeTime'] = 60;
        $aOptions['memoryCaching'] = true;
        $aOptions['automaticSerialization'] = true;

        $this->cacheDir = $aOptions['cacheDir'];

        $this->oLite = new Cache_Lite($aOptions);
    }

    function get($group, $id) {
        if (empty($this->bEnabled)) {
            return array(false, false);
        }
        $stuff = $this->oLite->get($id, strtolower($group));
        if (is_array($stuff)) {
            return array(true, $stuff[0]);
        }
        return array(false, false);
    }

    function set($group, $id, $val) {
        if (empty($this->bEnabled)) {
            return false;
        }
        $this->aRollbackList[] = array($group, $id);
        return $this->oLite->save(array($val), $id, strtolower($group));
    }

    function remove($group, $id) {
        if (empty($this->bEnabled)) {
            return false;
        }
        return $this->oLite->remove($id, strtolower($group));
    }

    function clear($group) {
        if (empty($this->bEnabled)) {
            return false;
        }
        return $this->oLite->clean(strtolower($group));
    }

    function rollback() {
        // $this->deleteAllCaches();
        foreach ($this->aRollbackList as $aRollbackItem) {
            list($group, $id) = $aRollbackItem;
            $this->remove($group, $id);
        }
    }

    function startTransaction() {
        $this->aRollbackList = array();
    }

    function commit() {
        $this->aRollbackList = array();
    }

    function deleteAllCaches() {
        if (empty($this->bEnabled)) {
            return false;
        }
        $this->oLite->clean();

        return;
        $dir = $this->cacheDir;

        $dh = @opendir($dir);
        if (empty($dh)) {
            return;
        }

        $aFiles = array();
        while (false !== ($sFilename = readdir($dh))) {
            if (substr($sFilename, 0, 6) == "cache_") {
               $aFiles[] = sprintf('%s/%s', $dir, $sFilename);
            }
        }
        foreach ($aFiles as $sFile) {
            @unlink($sFile);
        }

    }
}

?>
