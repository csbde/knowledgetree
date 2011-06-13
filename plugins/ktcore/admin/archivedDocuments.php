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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . '/browse/BrowseColumns.inc.php');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/PhysicalDocumentManager.inc');

// FIXME Chain in a notification alert for un-archival requests.
class KTArchiveTitle extends TitleColumn {

    public function renderDocumentLink($dataRow)
    {
        return $dataRow['document']->getName();
    }

    public function buildFolderLink($dataRow)
    {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fFolderId=%d', $dataRow['folder']->getId()));
    }

}

class ArchivedDocumentsDispatcher extends KTAdminDispatcher {

    var $sHelpPage = 'ktcore/admin/archived documents.html';

    public function do_main()
    {
        $folder = $this->getFolder();
        $collection = new AdvancedCollection();
        $columnRegistry = KTColumnRegistry::getSingleton();

        $column = $columnRegistry->getColumn('ktcore.columns.selection');
        $columnOptions = array();
        $columnOptions['show_folders'] = false;
        $columnOptions['show_documents'] = true;
        $columnOptions['rangename'] = '_d[]';
        $column->setOptions($columnOptions);
        $collection->addColumn($column);

        $column = $columnRegistry->getColumn('ktcore.columns.title');
        $sectionQueryParams = array(
                                'fCategory' => $this->category,
                                'subsection' => $this->subsection,
                                'expanded' => 1
        );
        $column->setOptions(array('qs_params' => $sectionQueryParams));
        $column->setOptions(array('link_documents' => false));
        $collection->addColumn($column);

        $archivedBrowseQuery = new ArchivedBrowseQuery($folder->getId());
        $collection->setQueryObject($archivedBrowseQuery);

        $options = $collection->getEnvironOptions();
        $options['result_url'] = KTUtil::addQueryString(
                                        $_SERVER['PHP_SELF'],
                                        array(array('fFolderId' => $folder->getId()))
        );

        $collection->setOptions($options);

        $urlParams = array('action' => 'restore');
        $breadcrumbs = KTUtil::generate_breadcrumbs($folder, $iFolderId, $urlParams);

        $templateData = array(
              'context' => $this,
              'folder' => $folder,
              'breadcrumbs' => $breadcrumbs,
              'collection' => $collection,
              'section_query_string' => $this->sectionQueryString
        );

        $template = $this->oValidator->validateTemplate('ktcore/document/admin/archivebrowse');

        return $template->render($templateData);
    }

    private function getFolder()
    {
        $folder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($folder)) {
            $this->errorRedirectToMain(_kt('Invalid folder selected.'));
            exit(0);
        }

        return $folder;
    }

    public function do_confirm_restore()
    {
        $selectedDocs = KTUtil::arrayGet($_REQUEST, '_d', array());

        $this->oPage->setTitle(sprintf(_kt('Confirm Restore of %d documents'), count($selectedDocs)));
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Archived Documents'));
        $this->oPage->setBreadcrumbDetails(sprintf(_kt('confirm restore of %d documents'), count($selectedDocs)));

        $documents = $this->getDocumentsForRestore($selectedDocs);

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/admin/dearchiveconfirmlist');
        $template->setData(array(
            'context' => $this,
            'documents' => $documents,
            'section_query_string' => $this->sectionQueryString
        ));

        return $template->render();
    }

    private function getDocumentsForRestore($selectedDocs)
    {
        $documents = array();

        foreach ($selectedDocs as $docId) {
            $doc = Document::get($docId);
            if (PEAR::isError($doc) || ($doc === false)) {
                $this->errorRedirectToMain(_kt('Invalid document id specified. Aborting restore.'));
            }

            if ($doc->getStatusId() != ARCHIVED) {
                $this->errorRedirectToMain(
                                sprintf(_kt('%s is not an archived document. Aborting restore.'), $doc->getName())
                );
            }

            $documents[] = $doc;
        }

        return $documents;
    }

    public function do_finish_restore()
    {
        $selectedDocs = KTUtil::arrayGet($_REQUEST, 'selected_docs', array());
        $documents = $this->getDocumentsForRestore($selectedDocs);

        $this->startTransaction();

        foreach ($documents as $doc) {
            // FIXME find de-archival source.
            // FIXME purge old notifications.
            // FIXME create de-archival notices to those who sent in old notifications.
            $doc->setStatusId(LIVE);
            $res = $doc->update();
            if (PEAR::isError($res) || ($res == false)) {
                $this->errorRedirectToMain(sprintf(_kt('%s could not be made "live".'), $doc->getName));
            }

            $documentTransaction = new DocumentTransaction($doc, _kt('Document restored.'), 'ktcore.transactions.update');
            $documentTransaction->create();
        }

        $this->commitTransaction();

        $msg = sprintf(_kt('%d documents made active.'), count($documents));
        $this->successRedirectToMain($msg);
    }

    public function handleOutput($output)
    {
        print $output;
    }

}

?>
