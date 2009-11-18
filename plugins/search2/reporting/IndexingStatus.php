<?php
/**
 * $Id:$
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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_DIR . '/search2/bin/cronResources.php');
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');
require_once(KT_DIR .'/plugins/search2/IndexingHelp.php');

class IndexingStatusDispatcher extends KTAdminDispatcher
{
    /**
    * Dispatch function
    */
    function do_main() {

        // Load Templating Engine
        $oTemplating =& KTTemplating::getSingleton();
        // Set Template to use
        $oTemplate = $oTemplating->loadTemplate('ktcore/search2/indexing_status');

        // Do a runtime Service Resource Check
        $checker = new ResourceChecker();
        $checker->check();

        // Get Results
        $serviceErrors = KTUtil::getSystemSetting('externalResourceIssues');

        $serviceErrors = unserialize($serviceErrors);

        // Array to Hold Items not working
        $this->serviceErrors = array();
        // A reference array to use with extractors that depend on the service
        $errorReference = array();


        if (count($serviceErrors) > 0) {
            // Add Service Errors
            foreach($serviceErrors as $error)
            {
                $this->serviceErrors[$error['name']] = array(
                        'name'=>$error['name'],
                        'status'=>$error['status'],
                        'help'=> IndexingHelp::getHelp($error['name'])
                    );

                // Create Reference to the array
                $errorReference[$error['status']] =& $this->serviceErrors[$error['name']];
            }
        }



        // ---------------------------

        // Do a run time check for extractors not working
        $indexer = Indexer::get();
        $extractorDiagnosis = $indexer->diagnoseExtractors();

        // Create an Array to store errors
        $this->extractorErrors = array();

        if (count($extractorDiagnosis > 0)) {
            foreach ($extractorDiagnosis as $extractor=>$props)
            {
                // Check if Extractor fault is due to a service not running
                if (array_key_exists($props['diagnosis'], $errorReference)) {
                    // One service may affect multiple extractors
                    $errorReference[$props['diagnosis']]['alsoaffects'] = array(array('extractor'=>$props['name'], 'affectedtypes'=> IndexingHelp::affectedTypes($extractor)));
                } else {

                    // Else list as normal extractor error
                    $this->extractorErrors[$extractor] = array(
                            'name'=> $props['name'],
                            'status'=> $props['diagnosis'],
                            'help'=> IndexingHelp::getHelp($extractor),
                            'affectedtypes'=> IndexingHelp::affectedTypes($extractor)
                        );
                }
            }
        }



        // Create URL to Send to Template
        $url = KTUtil::kt_url();

        // Prepare Template Data
        $aTemplateData = array(
                'context' => $this,
                'serviceErrors' => $this->serviceErrors,
                'extractorErrors' => $this->extractorErrors,
                'url' => $url
            );

        // Send to template and render
        return $oTemplate->render($aTemplateData);

    }
}
?>
