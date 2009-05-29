<?php
/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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

class PendingDocumentsDispatcher extends KTAdminDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Pending Documents Indexing Queue'),
        );
        return parent::check();
    }

    function do_main() {

        //registerTypes registers the mime types and populates the needed tables.
        $indexer = Indexer::get();
        $indexer->registerTypes();

 		$aPendingDocs = Indexer::getPendingIndexingQueue();
 		foreach($aPendingDocs as $key=>$doc)
 		{
 			$extractor = $indexer->getExtractor($doc['extractor']);
 			if (is_null($extractor))
 			{
 				$doc['extractor'] = 'n/a';
 				continue;
 			}
 			$doc['extractor'] = $extractor->getDisplayName();
 			$aPendingDocs[$key] = $doc;
 		}

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/search2/reporting/pendingdocuments');

        $config = KTConfig::getSingleton();
        $rootUrl = $config->get('KnowledgeTree/rootUrl');

        $oTemplate->setData(array(
            'context' => $this,
            'pending_docs' => $aPendingDocs,
            'root_url' => $rootUrl
        ));
        return $oTemplate;
    }

}


?>
