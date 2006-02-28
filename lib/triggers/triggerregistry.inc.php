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

class KTTriggerRegistry {
    var $triggers = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTTriggerRegistry')) {
            $GLOBALS['oKTTriggerRegistry'] = new KTTriggerRegistry;
        }
        return $GLOBALS['oKTTriggerRegistry'];
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
        return $ret;
    }
    // }}}
}

?>
