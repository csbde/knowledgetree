<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
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
        $user = KTUtil::running_user();
        if ($user) {
            $aOptions['cacheDir'] .= $user . '/';
        }
        if (!file_exists($aOptions['cacheDir'])) {
            mkdir($aOptions['cacheDir']);
        }

        // See thirdparty/pear/Cache/Lite.php to customize cache
        $aOptions['lifeTime'] = 60;
        $aOptions['memoryCaching'] = true;
        $aOptions['automaticSerialization'] = true;
        /* Patched line */
        // Disable fileCaching (when cache > 5Mo)
        $aOptions['onlyMemoryCaching'] = true;

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
