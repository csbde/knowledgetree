<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 * Contributor(s): ______________________________________
 */

class ViewDocumentDispatcher extends KTStandardDispatcher {

    public $sName = 'ktcore.actions.document.displaydetails';
    public $sSection = 'view_details';
    public $sHelpPage = 'ktcore/browse.html';

    public $actions;

    public function ViewDocumentDispatcher() {
        $this->aBreadcrumbs = array(array('action' => 'browse', 'name' => _kt('Browse')));
        parent::KTStandardDispatcher();
    }

    public function check() {
        if (!parent::check()) {
            return false;
        }

        $this->persistParams(array('fDocumentId'));

        return true;
    }

    // FIXME identify the current location somehow.
    public function addPortlets($currentaction = null) {
        $currentaction = $this->sName;

        $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentinfo');
        $portlet = new KTActionPortlet(sprintf(_kt('Info')));
        $portlet->setActions($actions, $currentaction);
        $this->oPage->addPortlet($portlet);

        $this->actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $portlet = new KTActionPortlet(sprintf(_kt('Actions'), $this->oDocument->getName()));
        $portlet->setActions($this->actions, $currentaction);
        $this->oPage->addPortlet($portlet);
    }

    public function do_main() {
        // fix legacy, broken items.
        if (KTUtil::arrayGet($_REQUEST, 'fDocumentID', true) !== true) {
            $_REQUEST['fDocumentId'] = sanitizeForSQL(KTUtil::arrayGet($_REQUEST, 'fDocumentID'));
            unset($_REQUEST['fDocumentID']);
        }

        $documentData = array();
        $documentId = sanitizeForSQL(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if ($documentId === null) {
            $this->oPage->addError(sprintf(_kt("No document was requested.  Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }
        // try get the document.
        $document =& Document::get($documentId);
        if (PEAR::isError($document)) {
            $this->oPage->addError(sprintf(_kt("The document you attempted to retrieve is invalid.   Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            $this->oPage->booleanLink = true;

            return $this->do_error();
        }

        $documentId = $document->getId();
        $documentData['document_id'] = $document->getId();

        if (!KTBrowseUtil::inAdminMode($this->oUser, $document->getFolderId())) {
            if ($document->getStatusID() == ARCHIVED) {
                $this->oPage->addError(_kt('This document has been archived.'));
                return $this->do_request($document);
            } else if ($document->getStatusID() == DELETED) {
                $this->oPage->addError(_kt('This document has been deleted.'));
                return $this->do_error();
            } else if (!Permission::userHasDocumentReadPermission($document)) {
                $this->oPage->addError(_kt('You are not allowed to view this document'));
                return $this->permissionDenied();
            }
        }

        if ($document->getStatusID() == ARCHIVED) {
            $this->oPage->addError(_kt('This document has been archived.'));
        } else if ($document->getStatusID() == DELETED) {
            $this->oPage->addError(_kt('This document has been deleted.'));
        }

        $this->oPage->setSecondaryTitle($document->getName());

        $options = array(
            'documentaction' => 'viewDocument',
            'folderaction' => 'browse',
        );

        $this->oDocument =& $document;

        //Figure out if we came here by navigating through a shortcut.
        //If we came here from a shortcut, the breadcrumbspath should be relative
        //to the shortcut folder.
        $symLinkFolderId = KTUtil::arrayGet($_REQUEST, 'fShortcutFolder', null);
        if (is_numeric($symLinkFolderId)) {
            $breadcrumbsFolder = Folder::get($symLinkFolderId);
            $options['final'] = false;
            $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForFolder($breadcrumbsFolder,$options));
            $this->aBreadcrumbs[] = array('name'=>$this->oDocument->getName());
        } else {
            $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($document, $options, $symLinkFolderId));
        }

        //$this->addPortlets('Document Details');
        $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $actionBtns = $this->createButtons($actions);

        $documentData['document'] = $document;
        $documentData['document_type'] =& DocumentType::get($document->getDocumentTypeID());
        $isValidDoctype = true;

        $documentTypes = & DocumentType::getList('disabled=0');

        if (PEAR::isError($documentData['document_type'])) {
            $this->oPage->addError(_kt('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.'));
            $isValidDoctype = false;
        }

        // we want to grab all the metadata for this doc, since its faster that way.
        $metadata =& DocumentFieldLink::getByDocument($document);

        $GLOBALS['default']->log->debug('mdlist ' . print_r($metadata, true));

        $fieldValues = array();
        foreach ($metadata as $fieldLink) {
            $fieldValues[$fieldLink->getDocumentFieldID()] = $fieldLink->getValue();
        }

        //var_dump($fieldValues);

        $documentData['field_values'] = $fieldValues;

        // Fieldset generation.
        //
        //   we need to create a set of FieldsetDisplay objects
        //   that adapt the Fieldsets associated with this lot
        //   to the view (i.e. ZX3).   Unfortunately, we don't have
        //   any of the plumbing to do it, so we handle this here.
        $genericFieldsets = array();
        $fieldsets = array();

        // we always have a generic.
        array_push($genericFieldsets, new GenericFieldsetDisplay());

        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        $docFieldsets = KTMetadataUtil::fieldsetsForDocument($document);

        //$GLOBALS['default']->log->debug('viewdocument aDocFieldsets '.print_r($docFieldsets, true));

        foreach ($docFieldsets as $fieldset) {
            //$GLOBALS['default']->log->debug('viewdocument oFieldset namespace :'.$fieldset->getNamespace().':');
            //$GLOBALS['default']->log->debug('viewdocument oFieldset namespace !=== tagcloud '.$fieldset->getNamespace() !== 'tagcloud');
            if ($fieldset->getNamespace() == 'tagcloud') {
                $tags = $this->getDocumentTags($document, $fieldset);
            }
            else {
                //$GLOBALS['default']->log->debug('viewdocument oFieldset '.print_r($fieldset, true));
                $displayClass = $fieldsetDisplayReg->getHandler($fieldset->getNamespace());

                //$GLOBALS['default']->log->debug('fieldsetdisplayclass '.print_r(new $displayClass($fieldset), true));
                array_push($fieldsets, new $displayClass($fieldset));
            }
        }

        //$GLOBALS['default']->log->debug('viewdocument fieldsets '.print_r($fieldsets, true));

        $checkedOutUser = 'Unknown user';
        if ($document->getIsCheckedOut() == 1) {
            $cou = User::get($document->getCheckedOutUserId());
            if (!(PEAR::isError($cou) || ($cou == false))) {
                $checkedOutUser = $cou->getName();
            }
        }

        /*
        // is the checkout action active?
        $canCheckin = false;
        foreach ($this->actions as $docAction) {
            if ($docAction->sName == 'ktcore.actions.document.cancelcheckout') {
                if ($docAction->getInfo()) {
                    $canCheckin = true;
                }
                break;
            }
        }

        $canEdit = true;
        */

        // viewlets
        $viewlets = array();
        $viewlets2 = array();
        $viewletActions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentviewlet');
        foreach ($viewletActions as $action) {
            $info = $action->getInfo();
            if ($info !== null) {
                if (($info['ns'] == 'ktcore.viewlet.document.activityfeed') || ($info['ns'] == 'thumbnail.viewlets')) {
                    $viewlets[] = $action->display_viewlet(); // use the action, since we display_viewlet() later.
                } else {
                    $viewlets2[] = $action->display_viewlet(); // use the action, since we display_viewlet() later.
                }
            }
        }

        $viewletData = implode(' ', $viewlets);
        $viewletData = trim($viewletData);
        //        $viewletData2 = implode(' ', $viewlets2);
        //        $viewletData2 = trim($viewletData2);

        $contentClass = 'view';
        if (!empty($viewletData)) {
            $contentClass = 'view withviewlets';
        }
        $this->oPage->setContentClass($contentClass);

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/view');

        if (KTPluginUtil::pluginIsActive('instaview.processor.plugin')) {
            $path = KTPluginUtil::getPluginPath ('instaview.processor.plugin');
            try {
                require_once($path . 'instaViewLinkAction.php');
                $livePreviewAction = new instaViewLinkAction($document, $this->oUser, null);
                $livePreview = $livePreviewAction->do_main();
            } catch(Exception $e) {}
        }

        $ownerUser = KTUserUtil::getUserField($document->getOwnerID(), 'name');
        $creatorUser = KTUserUtil::getUserField($document->getCreatorID(), 'name');
        $lastModifierUser = KTUserUtil::getUserField($document->getModifiedUserId(), 'name');

        $FieldsetDisplayHelper = new KTFieldsetDisplay();

        $this->recordView();

        $blocks = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentblock');
        $documentBlocks = isset($blocks[0]) ? $blocks[0] : array();
        $sidebars = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'maindocsidebar');
        $documentSidebars = isset($sidebars[0]) ? $sidebars[0] : array();

        $tagPluginPath = KTPluginUtil::getPluginPath('ktcore.tagcloud.plugin', true);

        $templateData = array(
            'doc_data' => array(
                'owner' => $ownerUser[0]['name'],
                'creator' => $creatorUser[0]['name'],
                'lastModifier' => $lastModifierUser[0]['name']
            ),
            'context' => $this,
            'sCheckoutUser' => $checkedOutUser,
            'isCheckoutUser' => ($this->oUser->getId() == $document->getCheckedOutUserId()),
            //'canCheckin' => $canCheckin,
            //'bCanEdit' => $canEdit,
            'actionBtns' => $actionBtns,
            'document_id' => $documentId,
            'document' => $document,
            'documentName' => $document->getName(),
            'document_data' => $documentData,
            'document_types' => $documentTypes,
            'generic_fieldsets' => $genericFieldsets,
            'fieldsets' => $fieldsets,
            'viewlet_data' => $viewletData,
            //'viewlet_data2' => $viewletData2,
            'hasNotifications' => false,
            'fieldsetDisplayHelper' => $FieldsetDisplayHelper,
            'documentBlocks' => $documentBlocks,
            'documentSidebars' => $documentSidebars,
            'tagFilterScript' => "/{$tagPluginPath}filterTags.php?documentId=$documentId",
            'tags' => implode(',', $tags)
        );

        // Conditionally include live_preview
        if ($livePreview) {
            $templateData['live_preview'] = $livePreview;
        }

        // Setting Document Notifications Status
        if ($document->getIsCheckedOut() || $document->getImmutable()) {
            $templateData['hasNotifications'] = true;
        }

        //$this->oPage->setBreadcrumbDetails(_kt("Document Details"));

        return $template->render($templateData);
    }

    private function getDocumentTags($document, $fieldset)
    {
        // FIXME I think this is horribly inefficient and we should probably get the tags directly.
        //       Do like this for now and once working look at alternative for speed.
        $fields = $fieldset->getFields();
        $fieldId = $fields[0]->getId();

        $fieldValue = DocumentFieldLink::getByDocumentAndField($document, $fields[0]);
        if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) {
            $tags = $fieldValue->getValue();
        }

        if (empty($tags)) {
            return array();
        }

        $tags = explode(',', $tags);
        foreach ($tags as $key => $tag) {
            $tags[$key] = '{id: "' . $tag . '", name: "' . $tag . '"}';
        }

        return $tags;
    }

    // FIXME refactor out the document-info creation into a single utility function.
    // this gets in:
    //   fDocumentId (document to compare against)
    //   fComparisonVersion (the metadata_version of the appropriate document)
    public function do_viewComparison() {
        $documentData = array();
        $documentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if ($documentId === null) {
            $this->oPage->addError(sprintf(_kt("No document was requested.  Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }

        $documentData['document_id'] = $documentId;

        $baseVersion = KTUtil::arrayGet($_REQUEST, 'fBaseVersion');

        // try get the document.
        $document =& Document::get($documentId, $baseVersion);
        if (PEAR::isError($document)) {
            $this->oPage->addError(sprintf(_kt("The base document you attempted to retrieve is invalid.   Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }

        if (!Permission::userHasDocumentReadPermission($document)) {
            // FIXME inconsistent.
            $this->oPage->addError(_kt('You are not allowed to view this document'));
            return $this->permissionDenied();
        }

        $this->oDocument =& $document;
        $this->oPage->setSecondaryTitle($document->getName());
        $options = array(
            'documentaction' => 'viewDocument',
            'folderaction' => 'browse',
        );

        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($document, $options));
        $this->oPage->setBreadcrumbDetails(_kt('compare versions'));

        $comparisonVersion = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');
        if ($comparisonVersion=== null) {
            $this->oPage->addError(sprintf(_kt("No comparison version was requested.  Please <a href=\"%s\">select a version</a>."), KTUtil::addQueryStringSelf('action=history&fDocumentId=' . $documentId)));
            return $this->do_error();
        }

        $comparison =& Document::get($document->getId(), $comparisonVersion);
        if (PEAR::isError($comparison)) {
            $this->errorRedirectToMain(_kt('Invalid document to compare against.'));
        }

        $comparisonData = array();
        $comparisonData['document_id'] = $comparison->getId();
        $documentData['document'] = $document;
        $comparisonData['document'] = $comparison;
        $documentData['document_type'] =& DocumentType::get($document->getDocumentTypeID());
        $comparisonData['document_type'] =& DocumentType::get($comparison->getDocumentTypeID());

        // follow twice:  once for normal, once for comparison.
        $isValidDoctype = true;
        if (PEAR::isError($documentData['document_type'])) {
            $this->oPage->addError(_kt('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.'));
            $isValidDoctype = false;
        }

        // we want to grab all the md for this doc, since its faster that way.
        $metadata =& DocumentFieldLink::getList(array('metadata_version_id = ?', array($baseVersion)));

        $fieldValues = array();
        foreach ($metadata as $fieldLink) {
            $fieldValues[$fieldLink->getDocumentFieldID()] = $fieldLink->getValue();
        }

        $documentData['field_values'] = $fieldValues;
        $metadata =& DocumentFieldLink::getList(array('metadata_version_id = ?', array($comparisonVersion)));

        $fieldValues = array();
        foreach ($metadata as $fieldLink) {
            $fieldValues[$fieldLink->getDocumentFieldID()] = $fieldLink->getValue();
        }

        $comparisonData['field_values'] = $fieldValues;

        // Fieldset generation.
        //
        //   we need to create a set of FieldsetDisplay objects
        //   that adapt the Fieldsets associated with this lot
        //   to the view (i.e. ZX3).   Unfortunately, we don't have
        //   any of the plumbing to do it, so we handle this here.
        $fieldsets = array();

        // we always have a generic.
        array_push($fieldsets, new GenericFieldsetDisplay());

        // FIXME can we key this on fieldset namespace?  or can we have duplicates?
        // now we get the other fieldsets, IF there is a valid doctype.

        if ($isValidDoctype) {
            // these are the _actual_ fieldsets.
            $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();

            // and the generics
            $activesets = KTFieldset::getGenericFieldsets();
            foreach ($activesets as $fieldset) {
                $displayClass = $fieldsetDisplayReg->getHandler($fieldset->getNamespace());
                array_push($fieldsets, new $displayClass($fieldset));
            }

            $activesets = KTFieldset::getForDocumentType($document->getDocumentTypeID());
            foreach ($activesets as $fieldset) {
                $displayClass = $fieldsetDisplayReg->getHandler($fieldset->getNamespace());
                array_push($fieldsets, new $displayClass($fieldset));
            }
        }

        // FIXME handle ad-hoc fieldsets.
        //$this->addPortlets();
        $template = $this->oValidator->validateTemplate('ktcore/document/compare');
        $templateData = array(
            'context' => $this,
            'document_id' => $documentId,
            'document' => $document,
            'document_data' => $documentData,
            'comparison_data' => $comparisonData,
            'comparison_document' => $comparison,
            'fieldsets' => $fieldsets,
        );

        //var_dump($templateData['comparison_data']);
        return $template->render($templateData);
    }

    public function do_error() {
        return '&nbsp;'; // don't actually do anything.
    }

    public function do_request($document) {
        // Display form for sending a request through the the sys admin to unarchive the document
        // name, document, request, submit

        $form = new KTForm;
        $form->setOptions(array(
            'submit_label' => _kt('Send request'),
            'identifier' => '',
            'cancel_url' => KTBrowseUtil::getUrlForFolder($folder),
            'fail_action' => 'main',
            'context' => $this,
        ));

        $form->addWidget(
            array('ktcore.widgets.text',
                array(
                    'label' => _kt('Note'),
                    'name' => 'reason',
                    'required' => true,
                )
            )
        );

        $data = isset($_REQUEST['data']) ? $_REQUEST['data'] : array();

        $folderId = $document->getFolderID();
        $folder = Folder::get($folderId);
        $folderUrl = KTBrowseUtil::getUrlForFolder($folder);

        if (!empty($data)) {
            $res = $form->validate();
            if (!empty($res['errors'])) {
                return $form->handleError('', $errors);
            }

            $adminGroups = Group::getAdministratorGroups();
            if (!PEAR::isError($adminGroups) && !empty($adminGroups)) {
                foreach ($adminGroups as $group) {
                    $groupUsers = $group->getMembers();

                    // ensure unique users
                    foreach ($groupUsers as $user) {
                        $users[$user->getId()] = $user;
                    }
                }

                $subject = _kt('Request for an archived document to be restored');
                $details = $data['reason'];

                // Send request
                foreach ($users as $user) {
                    if (!PEAR::isError($user)) {
                        include_once(KT_DIR.'/plugins/ktcore/KTAssist.php');
                        KTAssistNotification::newNotificationForDocument($document, $user, $this->oUser, $subject, $details);
                    }
                }

                // Redirect to folder
                $this->addInfoMessage(_kt('The administrator has been notified of your request.'));
                redirect($folderUrl);
                exit();
            }
        }

        return $form->renderPage(_kt('Archived document request') . ': ' . $document->getName());
    }

    public function getUserForId($userId) {
        $user = User::get($userId);
        if (PEAR::isError($user) || ($user == false)) {
            return _kt('User no longer exists');
        }

        return $u->getName();
    }

    /**
     * Record the transaction for viewing the page.
     * Only record once unless the user has viewed a different document and returned to the current one.
     */
    private function recordView()
    {
        $docId = $this->oDocument->getId();
        if (isset($_SESSION['current_document']) && $_SESSION['current_document'] == $docId) {
            // If the document view has already been recorded, don't record it again
            return ;
        }

        $documentTransaction = new DocumentTransaction($this->oDocument, 'Document details page view',
                                                        'ktcore.transactions.view');
        $documentTransaction->create();
        $_SESSION['current_document'] = $docId;
    }

    /**
     * Get the info for displaying the action buttons on the page
     *
     * @param array $actions
     * @return array
     */
    private function createButtons($actions)
    {
        $list = array();
        $menus = array();

        // Create the "more" button
        $btn = array('btn_position' => 'below', 'url' => '#', 'name' => _kt('More'), 'icon_class' => 'more', 'ns' => 'more');
        $list[$btn['btn_position']][$btn['ns']] = $btn;

        foreach ($actions as $oAction) {
            $info = $oAction->getInfo();

            // Skip if action is disabled
            if (is_null($info)) {
                continue;
            }

            // Skip if no name provided - action may be disabled for permissions reasons
            if (empty($info['name'])) {
                continue;
            }

            // Check whether the button has a parent i.e. is in the drop down menu of a split button
            if (!$info['parent_btn']) {
                // Determine the position of the button on the page
                $pos = $info['btn_position'];
                $list[$pos][$info['ns']] = $info;
            } else {
                $menus[$info['parent_btn']]['menu'][$info['ns']] = $info;
            }
        }

        if (!empty($menus)) {
            // Add the menu's to the correct buttons
            foreach ($list as $key => $item) {
                foreach ($menus as $subkey => $subitem) {
                    if (array_key_exists($subkey, $item)) {
                        // Order alphabetically
                        $submenu = $subitem['menu'];
                        uasort($submenu, array($this, 'sort_menus'));

                        $item[$subkey]['menu'] = $submenu;
                        $list[$key] = $item;
                    }
                }
            }
        }
        uasort($list['above'], array($this, 'sort_btns'));

        return $list;
    }

    function sort_btns($a, $b)
    {
        if ($a['btn_order'] < $b['btn_order']) return -1;
        if ($a['btn_order'] > $b['btn_order']) return 1;
        return 0;
    }

    function sort_menus($a, $b)
    {
        if ($a['name'] < $b['name']) return -1;
        if ($a['name'] > $b['name']) return 1;
        return 0;
    }
}
?>
