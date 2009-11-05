<?php
/**
 * $Id: KTCorePlugin.php 7954 2008-01-25 05:56:52Z megan_w $
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class BaseConfigDispatcher extends KTAdminDispatcher
{
    protected $category = 'System Configuration';
    protected $name;

	function check() {
        return parent::check();
    }

	function do_main()
	{
	    // Get the configuration settings
	    $settings = $this->getSettings();

		// Check if there are any settings to be saved
		$settings = $this->saveSettings($settings);

		// Organise by group
		$groups = array();
		$groupList = array();
		foreach ($settings as $item){
		    $group_name = $item['group_display'];
		    $groupList[$group_name]['id'] = $item['id'];
		    $groupList[$group_name]['name'] = $group_name;
		    $groupList[$group_name]['description'] = $item['group_description'];
		    $groups[$group_name][] = $item;
		}

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/configsettings');

        //set db config data being sent to template
        $oTemplate->setData(array(
            'context' => $this,
            'groupList' => $groupList,
            'groupSettings' => $groups,
            'section' => $this->name
        ));
        return $oTemplate;
	}

	/**
	 * Get the configuration settings
	 *
	 * @return array
	 */
	function getSettings() {
	    $query = "SELECT g.display_name AS group_display, g.description AS group_description,
            s.id, s.display_name, s.description, s.value, s.default_value, s.type, s.options
            FROM config_groups g
            INNER JOIN config_settings s ON g.name = s.group_name
            WHERE category = '{$this->category}' AND s.can_edit = 1
            ORDER BY g.name, s.item";

		$results = DBUtil::getResultArray($query);

		if(PEAR::isError($results)){
		    $this->addErrorMessage(sprintf(_kt("The configuration settings could not be retrieved: %s") , $results->getMessage()));
		    return array();
		}

		return $results;
	}

	/**
	 * Render the form input for the given setting type.
	 *
	 * @param string $type
	 * @param mixed $value
	 * @param string $options
	 * @return HTML
	 */
	function renderInput($id, $type, $value, $defaultValue = '', $options = null) {

	    if(!empty($options)){
	       $options = unserialize($options);
	    }

	    $input = '';
        if(!empty($defaultValue) && ($type == 'string' || $type == 'numeric_string' || empty($type))){

            $pos = strpos($defaultValue, '${');

            if($pos !== false){
                $pos2 = strpos($defaultValue, '}', $pos);

                $var = substr($defaultValue, $pos + 2, $pos2 - ($pos + 2));

                global $default;
                $var = $default->$var;

                $defaultValue = preg_replace('/\$\{([^}]+)\}/', $var, $defaultValue);
            }

            $defaultValue = "<i>{$defaultValue}</i>";
            $input .= '<span class="descriptiveText">'.sprintf(_kt("The default value is %s") , $defaultValue).'</span><br>';
        }

	    /*
	    The options array can contain a number of settings:
	       - increment => the amount a numeric drop down will increment by
	       - minimum => the minimum value of the numeric dropdown
	       - maximum => the maximum value of the numeric dropdown
	       - label => a word or sentence displayed before the input
	       - append => a word or sentence displayed after the input
	       - options
	           => the values to be used in a dropdown, format: array(array('label' => 'xyz', 'value' => 'Xyz'), array('label' => 'abc', 'value' => 'Abc'));
	           => the values to be used in a radio button, format: array('xyz', 'abc');
	           => the values to be used in a numeric dropdown, format: array(array('label' => '10', 'value' => '10'), array('label' => '2', 'value' => '2'));
	    */

	    switch ($type){
            case 'numeric':
                // If options aren't provided, create them
                if(!isset($options['options'])){

    	            $increment = isset($options['increment']) ? $options['increment'] : 5;
    	            $minVal = isset($options['minimum']) ? $options['minimum'] : 0;
    	            $maxVal = isset($options['maximum']) ? $options['maximum'] : 100;

    	            $optionValues = array();
    	            for($i = $minVal; $i <= $maxVal; $i = $i + $increment){
    	                $optionValues[] = array('label' => $i, 'value' => $i);
    	            }
    	            $options['options'] = $optionValues;
                }

	        case 'dropdown':
	            $optionValues = array();
	            $optionValues = $options['options'];

	            $value = ($value == 'default') ? $defaultValue : $value;

	            // Prepend a label if set
	            $input .= isset($options['label']) ? "<label for='{$id}'>{$options['label']}</label>&nbsp;&nbsp;" : '';

	            // Create dropdown
	            $input .= "<select id='{$id}' name='configArray[{$id}]'>&nbsp;&nbsp;";
	            foreach ($optionValues as $item){
	                $selected = ($item['value'] == $value) ? 'selected' : '';
    	            $input .= "<option value='{$item['value']}' $selected>{$item['label']}</option>";
	            }
	            $input .= '</select>';
	            break;

	        case 'boolean':
	            $options['options'] = array('true', 'false');

	        case 'radio':
	            $optionValues = array();
	            $optionValues = $options['options'];

	            $value = ($value == 'default') ? $defaultValue : $value;

	            foreach ($optionValues as $item){
	                $checked = ($item == $value) ? 'checked ' : '';

    	            $input .= "<input type='radio' id='{$id}_{$item}' name='configArray[{$id}]' value='{$item}' {$checked}>&nbsp;&nbsp;";
    	            $input .= "<label for={$id}>".ucwords($item).'</label>&nbsp;&nbsp;';
	            }
	            break;

	        // Change this later to validate the numbers
	        // For input where the number may be anything like a Port or the number may be a float instead of an integer
	        case 'numeric_string':
	            // Prepend a label if set
	            $input .= isset($options['label']) ? "<label for='{$id}'>{$options['label']}</label>&nbsp;&nbsp;" : '';
	            $input .= "<input name='configArray[{$id}]' value='{$value}' size = '5'>";
	            break;

	        case 'string':
            default:
	            // Prepend a label if set
	            $input .= isset($options['label']) ? "<label for='{$id}'>{$options['label']}</label>&nbsp;&nbsp;" : '';
                $input .= "<input name='configArray[{$id}]' value='{$value}' size = '60'>";
	    }

	    // Append any text
        $input .= isset($options['append']) ? '&nbsp;&nbsp;'.sprintf(_kt('%s') , $options['append']) : '';

	    return $input;
	}

	/**
	 * Save any modified settings, clear the cached settings and return the new settings
	 *
	 * @param array $currentSettings
	 * @return array
	 */
	function saveSettings($currentSettings, $log = false) {
	    $newSettings = isset($_POST['configArray']) ? $_POST['configArray'] : '';
	    if(!empty($newSettings)){
	        $this->addInfoMessage(_kt('The configuration settings have been updated.'));

	        if($log){
	            $comment = array();
	        }

	         // If the value in the post array is different from the current value, then update the DB
	         foreach ($currentSettings AS $setting){
	             $new = $newSettings[$setting['id']];

	             if($setting['value'] != $new){
	                 // Update the value
	                 $res = DBUtil::autoUpdate('config_settings', array('value' => $new), $setting['id']);

	                 if(PEAR::isError($res)){
	                     $this->addErrorMessage(sprintf(_kt("The setting %s could not be updated: %s") , $setting['display_name'],$res->getMessage()));
	                 }
	                 if($log){
	                     $comment[] = sprintf(_kt("%s from %s to %s") , $setting['display_name'],$setting['value'],$new);
	                 }
	             }
	         }

	         if($log){
	             $this->logTransaction($comment);
	         }

	         // Clear the cached settings
	         $oKTConfig = new KTConfig();
        	 $oKTConfig->clearCache();

        	 // Get the new settings from the DB
        	 $currentSettings = $this->getSettings();
	    }
	    return $currentSettings;
	}

	protected function logTransaction($aComment = null)
	{
	    $comment = implode(', ', $aComment);
	    $comment = _kt('Config settings modified: ').$comment;

        // log the transaction
        $date = date('Y-m-d H:i:s');

        require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
        $params = array(
            'userid' => $_SESSION['userID'],
            'datetime' => $date,
            'actionnamespace' => 'ktcore.transactions.modifying_config_settings',
            'comments' => $comment,
            'sessionid' => $_SESSION['sessionID'],
        );
        KTUserHistory::createFromArray($params);
	}
}

class UIConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'User Interface Settings';
        $this->name = _kt('User Interface Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => $this->name
        );
        return parent::check();
    }
}

class ClientSettingsConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'Client Tools Settings';
        $this->name = _kt('Client Tools Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Client Tools Settings'),
        );
        return parent::check();
    }
}

class EmailConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'Email Settings';
        $this->name = _kt('Email Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Email Settings'),
        );
        return parent::check();
    }
}

class GeneralConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'General Settings';
        $this->name = _kt('General Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('General Settings'),
        );
        return parent::check();
    }
}

class i18nConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'Internationalisation Settings';
        $this->name = _kt('Internationalisation Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Internationalisation Settings'),
        );
        return parent::check();
    }
}

class SearchAndIndexingConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'Search and Indexing Settings';
        $this->name = _kt('Search and Indexing Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Search and Indexing Settings'),
        );
        return parent::check();
    }
}

class SecurityConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->category = 'Security Settings';
        $this->name = _kt('Security Settings');

        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Security Settings'),
        );
        return parent::check();
    }

    function saveSettings($currentSettings)
    {
        return parent::saveSettings($currentSettings, true);
    }
}
?>
