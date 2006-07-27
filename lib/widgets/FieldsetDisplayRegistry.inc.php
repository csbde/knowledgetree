<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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