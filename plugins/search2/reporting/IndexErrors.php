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

class IndexErrorsDispatcher extends KTAdminDispatcher {

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Document Indexing Diagnostics'),
        );
        return parent::check();
    }

    function do_main() {

		//Number of items on a page
		$itemsPerPage = 50;
		$pageNum = 1;

        if(isset($_REQUEST['itemsPerPage'])){
            $itemsPerPage = $_REQUEST['itemsPerPage'];
        }

        //registerTypes registers the mime types and populates the needed tables.
        $indexer = Indexer::get();
        $indexer->registerTypes();

        switch ($_REQUEST['rescheduleValue'])
        {
            case 'reschedule':
                foreach(KTUtil::arrayGet($_REQUEST, 'index_error', array()) as $sDocId => $v)
                {
                    Indexer::reindexDocument($sDocId);
                }
                break;
            case 'remove':
                foreach(KTUtil::arrayGet($_REQUEST, 'index_error', array()) as $sDocId => $v)
                {
                    Indexer::unqueueDocument($sDocId, 'Document removed from queue via admin interface. Normally this is because an indexer is not able to process the document.');
                }
                break;
            case 'rescheduleall':
                $aIndexerValues = Indexer::getIndexingQueue();
                foreach ($aIndexerValues as $sDocValues)
                {
                    Indexer::reindexDocument($sDocValues['document_id']);
                }
                break;
            case 'removeall':
                $aIndexerValues = Indexer::getIndexingQueue();
                foreach ($aIndexerValues as $sDocValues)
                {
                    Indexer::unqueueDocument($sDocValues['document_id'], 'Document removed from queue via admin interface. Normally this is because an indexer is not able to process the document.');
                }
                break;
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/search2/reporting/indexerrors');

        $aIndexerValues = Indexer::getIndexingQueue();
        foreach($aIndexerValues as $key=>$doc)
        {
        	$extractor=$indexer->getExtractor($doc['extractor']);
        	if (is_null($extractor))
 			{
 				$doc['extractor'] = 'n/a';
 				continue;
 			}
        	$doc['extractor'] = $extractor->getDisplayName();
        	$aIndexerValues[$key] = $doc;
        }

		$aIndexList = array();

		//creating page variables and loading the items for the current page
		if(!empty($aIndexerValues)){
        	$items = count($aIndexerValues);

			if(fmod($items, $itemsPerPage) > 0){
				$pages = floor($items/$itemsPerPage)+1;
			}else{
				$pages = ($items/$itemsPerPage);
			}
			for($i=1; $i<=$pages; $i++){
				$aPages[] = $i;
			}
			if($items < $itemsPerPage){
				$limit = $items-1;
			}else{
				$limit = $itemsPerPage-1;
			}

			if(isset($_REQUEST['pageValue']))
			{
				$pageNum = (int)$_REQUEST['pageValue'];

                if($pageNum > $pages){
                    $pageNum = $pages;
                }

				$start = (($pageNum-1)*$itemsPerPage)-1;
				$limit = $start+$itemsPerPage;
				for($i = $start; $i <= $limit; $i++){
					if(isset($aIndexerValues[$i]))
					{
						$aIndexList[] = $aIndexerValues[$i];
					}
				}
			}
			else
			{
				for($i = 0; $i <= $limit; $i++){
					$aIndexList[] = $aIndexerValues[$i];
				}
			}
        }

        $config = KTConfig::getSingleton();
        $rootUrl = $config->get('KnowledgeTree/rootUrl');

        $oTemplate->setData(array(
            'context' => $this,
            'pageList' => $aPages,
            'pageCount' => $pages,
            'pageNum' => $pageNum,
            'itemCount' => $items,
            'itemsPerPage' => $itemsPerPage,
            'indexErrors' => $aIndexList,
            'root_url' => $rootUrl

        ));
        return $oTemplate;
    }



}


?>
