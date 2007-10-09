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


/*
 * The widget factory is a singleton, which can be used to create
 * and register widgets.
 *
 */

class KTWidgetFactory {
    var $widgets = array();

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTWidgetFactory')) {
			$GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'] = new KTWidgetFactory;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'];
    }

    function registerWidget($sClassname, $sNamespace,  $sFilename = null) {
        $this->widgets[$sNamespace] = array(
            'ns' => $sNamespace,
            'class' => $sClassname,
            'file' => $sFilename,
        );
    }

    function &getWidgetByNamespace($sNamespace) {
        $aInfo = KTUtil::arrayGet($this->widgets, $sNamespace);
        if (empty($aInfo)) {
            return PEAR::raiseError(sprintf(_kt('No such widget: %s'), $sNamespace));
        }
        if (!empty($aInfo['file'])) {
            require_once($aInfo['file']);
        }

        return new $aInfo['class'];
    }

    // this is overridden to either take a namespace or an instantiated
    // class.  Doing it this way allows for a consistent approach to building
    // forms including custom widgets.
    function &get($namespaceOrObject, $aConfig = null) {
        if (is_string($namespaceOrObject)) {
            $oWidget =& $this->getWidgetByNamespace($namespaceOrObject);
        } else {
            $oWidget = $namespaceOrObject;
        }

        if (PEAR::isError($oWidget)) {
            return $oWidget;
        }

        $aConfig = (array) $aConfig; // always an array
        $res = $oWidget->configure($aConfig);
        if (PEAR::isError($res)) {
            return $res;
        }

        return $oWidget;
    }
}

?>
