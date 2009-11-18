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
 *
 */

class KTTriggerRegistry {
    var $triggers = array();
    // {{{ getSingleton
    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTTriggerRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTTriggerRegistry'] = new KTTriggerRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTTriggerRegistry'];
    }
    // }}}

    // {{{ registerTrigger
    function registerTrigger($action, $slot, $name, $nsname, $path = "") {
        if (!array_key_exists($action, $this->triggers)) {
            $this->triggers[$action] = array();
        }
        if (!array_key_exists($slot, $this->triggers[$action])) {
            $this->triggers[$action][$slot] = array();
        }
        $this->triggers[$action][$slot][$nsname] = array($name, $path, $nsname);
    }
    // }}}

    // {{{ getTriggers
    function getTriggers($action, $slot) {
        $ret = array();
        if (array_key_exists($action, $this->triggers)) {
            if (array_key_exists($slot, $this->triggers[$action])) {
                $ret = $this->triggers[$action][$slot];
            }
        }
        if (empty($ret)) {
            return array();
        }

        foreach($ret as $trigger)
        {
        	if (!class_exists($trigger[0]))
        	{
        	    $sPath = (KTUtil::isAbsolutePath($trigger[1])) ? $trigger[1] : KT_DIR.'/'.$trigger[1];
        		require_once($sPath);
        		if (!class_exists($trigger[0]))
        		{
        			global $default;
        			$default->log->error(sprintf(_kt('Cannot locate trigger class \'%s\' for action \'%s\' slot \'%s\'.'), $trigger[0], $action, $slot));
        		}
        	}
        }

        return $ret;
    }
    // }}}
}

?>
