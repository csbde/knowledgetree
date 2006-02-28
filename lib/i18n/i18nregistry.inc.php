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

require_once(KT_LIB_DIR . '/i18n/i18n.inc.php');

class KTi18nRegistry {
    var $_ai18nDetails = array();
    var $_ai18ns = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTi18nRegistry')) {
            $GLOBALS['oKTi18nRegistry'] = new KTi18nRegistry;
        }
        return $GLOBALS['oKTi18nRegistry'];
    }

    function registeri18n($sDomain, $sDirectory = "") {
        if (empty($sDirectory)) {
            $sDirectory = KT_DIR . '/i18n';
        }
        if (in_array("gettext", get_loaded_extensions())) {
            $this->_ai18nDetails[$sDomain] = array($sDomain, $sDirectory);
            bindtextdomain($sDomain, $sDirectory);
            bind_textdomain_codeset($sDomain, 'UTF-8');
        }
    }

    function &geti18n($sDomain) {
        $oi18n =& KTUtil::arrayGet($this->_ai18ns, $sDomain);
        if (!empty($oi18n)) {
            return $oi18n;
        }
        $aDetails = KTUtil::arrayGet($this->_ai18nDetails, $sDomain);
        if (empty($aDetails)) {
            return new KTi18nGeneric;
        }
        $oi18n =& new KTi18n($sDomain, $sDirectory);
        $this->ai18ns[$sDomain] =& $oi18n;
        return $oi18n;
    }
}

