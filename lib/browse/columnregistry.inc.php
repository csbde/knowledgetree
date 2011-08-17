<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
		if  (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTColumnRegistry'))  {
			$GLOBALS['_KT_PLUGIN']['oKTColumnRegistry'] = new KTColumnRegistry;
		}
		return  $GLOBALS['_KT_PLUGIN']['oKTColumnRegistry'];
    }
    // }}}

    function registerColumn($name, $namespace, $class, $file) 
    {
        $this->columns[$namespace] = array(
            'name' => $name,
            'namespace' => $namespace,
            'class' => $class,
            'file' => $file
        );
    }

    private function loadColumnHelpers()
    {
        if (!empty($this->columns)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('column');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[3])) {
                $params[3] = KTPluginUtil::getFullPath($params[3]);
            }
            $params[0] = _kt($params[0]);
            call_user_func_array(array($this, 'registerColumn'), $params);
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('view');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            $params[0] = _kt($params[0]);
            call_user_func_array(array($this, 'registerView'), $params);
        }
    }
    
    function getViewName($namespace) 
    { 
        return KTUtil::arrayGet($this->views, $namespace); 
    }
    
    function getViews() 
    { 
        return $this->views; 
    }
    
    function getColumns() 
    { 
        $this->loadColumnHelpers();
        return $this->columns; 
    }
    
    function registerView($name, $namespace)
    { 
        $this->views[$namespace] = $name; 
    }

    function getColumnInfo($namespace)
    {
        return  KTUtil::arrayGet($this->columns, $namespace, null);
    }

    function getColumn($namespace)
    {
        $this->loadColumnHelpers();
        
        $info = $this->getColumnInfo($namespace);
        if (empty($info)) {
            return PEAR::raiseError(sprintf(_kt("No such column: %s"), $namespace));
        }

        require_once($info['file']);

        return new $info['class'];
    }

    function getColumnsForView($viewNamespace)
    {
        $this->loadColumnHelpers();
        
        $viewEntry = KTUtil::arrayGet($this->views, $viewNamespace);
        if (is_null($viewEntry)) {
            return PEAR::raiseError(sprintf(_kt("No such view: %s"), $viewNamespace));
        }

        $viewColumnEntries = KTColumnEntry::getByView($viewNamespace);
        if (PEAR::isError($viewColumnEntries)) {
            return $viewColumnEntries;
        }

        $viewColumns = array();
        foreach ($viewColumnEntries as $entry) {
            $res = $this->getColumn($entry->getColumnNamespace());
            if (PEAR::isError($res))
            {
            	// this was returning before, but the function calling this is just doing
            	// an array merge, so the error propogation is not great.
            	// if this is an unexpected column, lets just skip it for now.
            	continue;
            }
            $options = $entry->getConfigArray();
            $options['column_id'] = $entry->getId();
            $options['required_in_view'] = $entry->getRequired();
            $res->setOptions($options);

            $viewColumns[] = $res;
        }

        return $viewColumns;
    }
}

?>