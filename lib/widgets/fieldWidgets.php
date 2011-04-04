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

/* Field Widgets
 *
 * Useful (common) widgets to handle creating, editing, extending items, etc.
 *
 */

require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class KTBaseWidget {

    var $sLabel = '';
    var $sDescription = '';
    var $sName = '';
    var $oPage = null;
    var $sId = null;
    var $bRequired = false;
    var $aOptions = null;
    var $aErrors = null;
    var $value = null;
    var $sTemplate = 'kt3/fields/base';

    function KTBaseWidget($label, $description, $name, $value, &$page, $required = false, $id = null, $errors = null, $options = null) {
        $this->sLabel = $label;
        $this->sDescription = $description;
        $this->sName = $name;
        $this->value = $value;
        $this->oPage =& $page;
        $this->bRequired = $required;
        $this->sId = $id;
        $this->aOptions = $options;
        $this->aErrors = $errors;

        if (is_null($this->aOptions)) { $this->aOptions = array(); }
        // default to being a bit bigger.
        $this->aOptions['width'] = KTUtil::arrayGet($this->aOptions, 'width', '45');
    }

    function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $hasErrors = false;
        if (count($this->aErrors) != 0) { $hasErrors = true; }
        //var_dump($this->aErrors);
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate($this->sTemplate);

        $templateData = array(
            'context' => $this,
            'label' => $this->sLabel,
            'description' => $this->sDescription,
            'name' => $this->sName,
            'required' => $this->bRequired,
            'page' => $this->oPage,
            'has_id' => ($this->sId !== null),
            'id' => $this->sId,
            'has_value' => ($this->value !== null),
            'value' => $this->value,
            'has_errors' => $hasErrors,
            'errors' => $this->aErrors,
            'options' => $this->aOptions,
        );

        return $template->render($templateData);
    }

}

/* Ultra simple items, could be extended later (e.g. JS)*/
class KTStringWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/base'; }
class KTPasswordWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/password'; }
class KTIntegerWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/base'; }
class KTTextWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/text'; }
class KTCheckboxWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/checkbox'; }
class KTFileUploadWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/fileupload'; }
class KTStaticTextWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/statictext'; }

/* lookup widget */
// EXPECTS $aOptions['vocab'] => key, item
class KTLookupWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/lookup'; }
// EXPECTS $aOptions['tree'] => inner widget.
class KTTreeWidget extends KTBaseWidget { var $sTemplate = 'kt3/fields/tree'; }

// TODO KTTransferWidget
// TODO KTDateWidget
// TODO KTDateRangeWidget

// TODO Make functions non-static and probably separate into separate selector and text search
//      classes.  Make options and selected options member variables.

// Expects $aOptions['action'] => dispatcher action to load from
//         $aOptions['assigned'] => currently assigned values
//         $aOptions['bind_add'] (opt) => name of js method to call on add
//         $aOptions['bind_remove'] (opt) => name of js method to call on remove
class KTJSONLookupWidget extends KTBaseWidget {

    var $sTemplate = 'kt3/fields/jsonlookup';

    public function setTemplate($template) { $this->sTemplate = $template; }

    public static function getGroupsAndRoles()
    {
        $options = array_merge(self::getGroups(), self::getRoles());
        return $options;
    }

    public static function getGroups()
    {
        $options = array();

        $groups = GroupUtil::listGroups();
        // TODO checking of assigned groups and roles vs available, set active = false for assigned.
        foreach ($groups as $group) {
            $options["group_{$group->getId()}"]['name'] = $group->getName();
            $options["group_{$group->getId()}"]['active'] = 1;
        }

        return $options;
    }

    public static function getRoles()
    {
        $options = array();

        $roles = Role::getList('id > 0');
        foreach ($roles as $role) {
            $options["role_{$role->getId()}"]['name'] = $role->getName();
            $options["role_{$role->getId()}"]['active'] = 1;
        }

        return $options;
    }

    public static function getAssignedGroupsAndRoles(&$options, $members = array())
    {
        $assigned = array_merge(
                            self::getAssignedGroups($options, $members = array()),
                            self::getAssignedRoles($options, $members = array())
                    );
        return $assigned;
    }

    // FIXME Eliminate the need for the return of $options by reference (see comment about member vars.)
    /**
     * @param array $options The options to check for assignment.
     * @param array $members The included members.
     */
    public static function getAssignedGroups(&$options, $members = array())
    {
        $assigned['groups_roles'] = self::getAssignedType('group', $options, $members);
        $assigned = array(implode(',', $assigned['groups_roles']), null);

        return $assigned;
    }

    public static function getAssignedRoles(&$options, $members = array())
    {
        $assigned['groups_roles'] = self::getAssignedType('role', $options, $members);
        $assigned = array(implode(',', $assigned['groups_roles']), null);

        return $assigned;
    }

    /**
     * Compares available options and current membership.
     * Returns a listing of the matched options as well as an indicator of whether an option
     * should be active (selectable) within a list (list can be search results or selector options.)
     *
     * @param string $type The option type.
     * @param array $options The options to check for assignment.
     * @param array $members The included members.
     */
    private static function getAssignedType($type, &$options, $members = array())
    {
        // Process list of existing groups and roles into a format which can be easily parsed in the template.
        // Additionally set disabled (inactive) for any pre-selected select list option.
        $assigned = array();  // FIXME will not work for users, d'uh.
        foreach ($members as $key => $member) {
            // TODO check type before explode?
            $data = explode('_', $key);
            if ($data[0] == $type) {
                $assigned[] = '{id: "' . $key . '", name: "' . $member->getName() . '"}'; // FIXME will not work for users, d'uh.
                $options[$key]['active'] = 0;
            }
        }

        return $assigned;
    }

    public static function getAssignedUsers($members = array())
    {
        // Process list of existing users into a format which can be easily parsed in the template.
        $assigned['users'] = array();
        foreach ($members as $key => $member) {
            $data = explode('_', $key);
            if ($data[0] == 'user') {
                $name = $member->getName();
                if (empty($name)) {
                    $name = $member->getUserName();
                }
                $assigned['users'][] = '{id: "' . $data[1] . '", name: "' . $name . '"}';
            }
        }

        $assigned = array(null, implode(',', $assigned['users']));

        return $assigned;
    }

    /**
     * Build the final widget with the supplied values.
     */
    public static function getJsonWidget($label, $type, $parts, $assigned, $options)
    {
        global $main;

        $baseOptions = array(
                            'assigned' => $assigned,
                            'type' => $type,
                            'parts' => $parts
        );
        $options = array_merge($baseOptions, $options);

        $jsonWidget = new KTJSONLookupWidget(_kt($label['header']),
            _kt($label['text']),
            'members',
            '',
            $main,
            false,
            null,
            null,
            $options
        );

        return $jsonWidget;
    }

}

?>
