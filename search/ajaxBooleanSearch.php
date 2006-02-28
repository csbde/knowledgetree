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

require_once("../config/dmsDefaults.php");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");

require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class AjaxBooleanSearchDispatcher extends KTDispatcher {

    function handle_output($data) {
        return $data;
    }
    
    function do_getNewCriteria() {
        $criteriaType = KTUtil::arrayGet($_REQUEST, 'type');
        if (empty($criteriaType)) {
            return 'AJAX Error:  no criteria type specified.';
        } 
        $critObj = Criteria::getCriterionByNumber($criteriaType);
        if (PEAR::isError($critObj)) {
           return 'AJAX Error:  failed to initialise critiria of type "'.$type.'".';
        }
        // NBM:  there appears to be no reason to take $aRequest into searchWidget...
        $noRequest = array();
        return $critObj->searchWidget($noRequest);
    }

    function do_main() {
        return "Ajax Error.  ajaxBooleanSearch::do_main should not be reachable."; 
    }
    
    
}

$oDispatcher = new AjaxBooleanSearchDispatcher();
$oDispatcher->dispatch();

?>
