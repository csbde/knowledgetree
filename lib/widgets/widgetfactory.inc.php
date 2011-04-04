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
 */


/*
 * The widget factory is a singleton, which can be used to create
 * and register widgets.
 */

class KTWidgetFactory {

    var $widgets = array();

    static function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTWidgetFactory')) {
                $GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'] = new KTWidgetFactory;
        }

        return $GLOBALS['_KT_PLUGIN']['oKTWidgetFactory'];
    }

    function registerWidget($classname, $namespace,  $filename = null) {
        $this->widgets[$namespace] = array(
            'ns' => $namespace,
            'class' => $classname,
            'file' => $filename,
        );
    }

    function &getWidgetByNamespace($namespace) {
        $info = KTUtil::arrayGet($this->widgets, $namespace);
        if (empty($info)) {
            return PEAR::raiseError(sprintf(_kt('No such widget: %s'), $namespace));
        }

        if (!empty($info['file'])) {
            require_once($info['file']);
        }

        return new $info['class'];
    }

    // this is overridden to either take a namespace or an instantiated
    // class.  Doing it this way allows for a consistent approach to building
    // forms including custom widgets.
    function &get($namespaceOrObject, $config = null) {
        if (is_string($namespaceOrObject)) {
            $widget =& $this->getWidgetByNamespace($namespaceOrObject);
        } else {
            $widget = $namespaceOrObject;
        }

        if (PEAR::isError($widget)) {
            return $widget;
        }

        $config = (array) $config; // always an array
        $res = $widget->configure($config);
        if (PEAR::isError($res)) {
            return $res;
        }

        return $widget;
    }

}

?>
