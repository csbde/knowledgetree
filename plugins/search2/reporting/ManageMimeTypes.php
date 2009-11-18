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
require_once(KT_LIB_DIR . '/mime.inc.php');

class ManageMimeTypesDispatcher extends KTAdminDispatcher {

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Manage Mime Types'),
        );
        return parent::check();
    }

    function do_main() {

        //registerTypes registers the mime types and populates the needed tables.
        $indexer = Indexer::get();
        $indexer->registerTypes();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/search2/reporting/managemimetypes');

        $aMimeTypes = KTMime::getAllMimeTypesInformation();

        $indexer = Indexer::get();

        $numExtensions = 0;
        $numIndexedExtensions = 0;

        foreach($aMimeTypes as $key=>$mimeType)
        {
        	$extractorClass = $mimeType['extractor'];
        	$numExtensions++;
        	if (empty($extractorClass))
        	{
        		continue;
        	}
        	$extractor = $indexer->getExtractor($extractorClass);
        	$aMimeTypes[$key]['extractor'] = $extractor->getDisplayName();
        	$numIndexedExtensions++;
        }

        $indexedPercentage = 0;
        if ($numExtensions > 0)
        {
        	$indexedPercentage = number_format(($numIndexedExtensions * 100)/$numExtensions,2,'.',',');
        }

        $oTemplate->setData(array(
            'context' => $this,
            'mime_types' => $aMimeTypes,
            'numExtensions'=>$numExtensions,
            'numIndexedExtensions'=>$numIndexedExtensions,
            'indexedPercentage'=>$indexedPercentage

        ));
        return $oTemplate;
    }
}
