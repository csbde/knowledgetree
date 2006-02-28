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

require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

class KTFieldsetDisplayRegistry {
    
    var $fieldset_types = array();
    
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTFieldsetDisplayRegistry')) {
            $GLOBALS['oKTFieldsetDisplayRegistry'] = new KTFieldsetDisplayRegistry;
        }
        return $GLOBALS['oKTFieldsetDisplayRegistry'];
    }
    // }}}


    // FIXME include a reg-class, so that lower items can ensure they require_once.
    // pass in:
    //   nsname (e.g. ktcore/subscription)
    //   classname (e.g. KTSimpleFieldset)
    function registerFieldsetDisplay($nsname, $className) {
        $this->fieldset_types[$nsname] = $className;
    }
    
    function getHandler($nsname) {
        if (!array_key_exists($nsname, $this->fieldset_types)) {
            // unfortunately, we need to do a bit more spelunking here.  
            // if its conditional, we use a different item.  ns is unsufficient.
            // 
            // FIXME this is slightly wasteful from a performance POV, though DB caching should make it OK.
            $oFieldset =& KTFieldset::getByNamespace ($nsname);
            if ($oFieldset->getIsConditional() && KTMetadataUtil::validateCompleteness($oFieldset)) {
                return 'ConditionalFieldsetDisplay';
            } else {
                return 'SimpleFieldsetDisplay';
            }
        } else {
            return $this->fieldset_types[$nsname];
        }
    }
}

?>