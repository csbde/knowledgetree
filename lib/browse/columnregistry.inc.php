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

require_once(KT_LIB_DIR . '/browse/columnentry.inc.php');

class KTColumnRegistry {
    var $columns = array();
    var $views = array();         // should be in here
    // {{{ getSingleton
    static function &getSingleton () {
		if  (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'],  'oKTColumnRegistry'))  {
			$GLOBALS['_KT_PLUGIN']['oKTColumnRegistry']  =&  new  KTColumnRegistry;
		}
		return  $GLOBALS['_KT_PLUGIN']['oKTColumnRegistry'];
    }
    // }}}

    function registerColumn($sName, $sNamespace, $sClass, $sFile) {
        $this->columns[$sNamespace] = array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'class' => $sClass,
            'file' => $sFile
        );
    }

    function getViewName($sNamespace) { return KTUtil::arrayGet($this->views, $sNamespace); }
    function getViews() { return $this->views; }
    function getColumns() { return $this->columns; }
    function registerView($sName, $sNamespace) { $this->views[$sNamespace] = $sName; }

    function getColumnInfo($sNamespace) {
        return  KTUtil::arrayGet($this->columns, $sNamespace, null);
    }

    function getColumn($sNamespace) {
        $aInfo = $this->getColumnInfo($sNamespace);
        if (empty($aInfo)) {
            return PEAR::raiseError(sprintf(_kt("No such column: %s"), $sNamespace));
        }

        require_once($aInfo['file']);

        return new $aInfo['class'];
    }

    function getColumnsForView($sViewNamespace) {
        $view_entry = KTUtil::arrayGet($this->views, $sViewNamespace);
        if (is_null($view_entry)) {
            return PEAR::raiseError(sprintf(_kt("No such view: %s"), $sViewNamespace));
        }

        $view_column_entries = KTColumnEntry::getByView($sViewNamespace);
        if (PEAR::isError($view_column_entries)) {
            return $view_column_entries;
        }

        $view_columns = array();
        foreach ($view_column_entries as $oEntry) {
            $res = $this->getColumn($oEntry->getColumnNamespace());
            if (PEAR::isError($res))
            {
            	// this was returning before, but the function calling this is just doing
            	// an array merge, so the error propogation is not great.
            	// if this is an unexpected column, lets just skip it for now.
            	continue;
            }
            $aOptions = $oEntry->getConfigArray();
            $aOptions['column_id'] = $oEntry->getId();
            $aOptions['required_in_view'] = $oEntry->getRequired();
            $res->setOptions($aOptions);

            $view_columns[] = $res;
        }

        return $view_columns;
    }
}

?>
