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

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

class KTDashletRegistry {
    var $nsnames = array();

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTDashboardRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'] = new KTDashletRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTDashboardRegistry'];
    }


    function registerDashlet($name, $nsname, $filename = "", $sPlugin = "") {

        $this->nsnames[$nsname] = array($name, $filename, $nsname, $sPlugin);
    }

    /**
     * Get any dashlets added since the user's last login
     *
     * @param object $oUser
     */
    function getNewDashlets($oUser, $sCurrent) {
        $new = array();
        $inactive = array();

        static $sInactive = '';
        $sIgnore = (!empty($sInactive)) ? $sCurrent.','.$sInactive : $sCurrent;

        // Get all dashlets that haven't already been displayed to the user and are active for the user
        $query = "SELECT h.classname, h.pathname, h.plugin FROM plugin_helper h
            INNER JOIN plugins p ON (h.plugin = p.namespace)
            WHERE p.disabled = 0 AND classtype = 'dashlet' ";

        if(!empty($sIgnore)){
            $query .= " AND h.classname NOT IN ($sIgnore)";
        }

        $res = DBUtil::getResultArray($query);

        // If the query is not empty, get the dashlets and return the new active ones
        // Add the inactive ones the list.
        if(!PEAR::isError($res) && !empty($res)){
            $oRegistry =& KTPluginRegistry::getSingleton();
            foreach ($res as $item){
                $name = $item['classname'];
                $filename = $item['pathname'];
                $sPluginName = $item['plugin'];

                require_once($filename);
                $oPlugin =& $oRegistry->getPlugin($sPluginName);

                $oDashlet = new $name;
                $oDashlet->setPlugin($oPlugin);
                if ($oDashlet->is_active($oUser)) {
                    $new[] = $name;
                }else{
                    $inactive[] = "'$name'";
                }
            }
            // Add new inactive dashlets
            $sNewInactive = implode(',', $inactive);
            $sInactive = (!empty($sInactive)) ? $sInactive.','.$sNewInactive : $sNewInactive;

            return $new;
        }
        return '';
    }

    // FIXME we might want to do the pruning now, but I'm unsure how to handle the preconditions.
    function getDashlets($oUser) {
        $aDashlets = array();
        $oRegistry =& KTPluginRegistry::getSingleton();
        // probably not the _best_ way to do things.
        foreach ($this->nsnames as $aPortlet) {
            $name = $aPortlet[0];
            $filename = $aPortlet[1];
            $sPluginName = $aPortlet[3];

            require_once($aPortlet[1]);
            $oPlugin =& $oRegistry->getPlugin($sPluginName);

            $oDashlet = new $name;
            $oDashlet->setPlugin($oPlugin);
            if ($oDashlet->is_active($oUser)) {
                $aDashlets[] = $oDashlet;
            }
        }

        return $aDashlets;
    }
}

?>
