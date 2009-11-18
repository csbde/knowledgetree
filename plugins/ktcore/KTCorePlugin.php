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

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTCorePlugin extends KTPlugin {
    var $bAlwaysInclude = true;
    var $sNamespace = 'ktcore.plugin';
    var $iOrder = -25;
    var $sFriendlyName = null;
    var $showInAdmin = false;

    function KTCorePlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Core Application Functionality');
        return $res;
    }

    function setup() {
        $this->registerAction('documentinfo', 'KTDocumentDetailsAction', 'ktcore.actions.document.displaydetails', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentViewAction', 'ktcore.actions.document.view', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTOwnershipChangeAction', 'ktcore.actions.document.ownershipchange', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckOutAction', 'ktcore.actions.document.checkout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCancelCheckOutAction', 'ktcore.actions.document.cancelcheckout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckInAction', 'ktcore.actions.document.checkin', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentEditAction', 'ktcore.actions.document.edit', 'document/edit.php');
        $this->registerAction('documentaction', 'KTDocumentDeleteAction', 'ktcore.actions.document.delete', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentMoveAction', 'ktcore.actions.document.move', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCopyAction', 'ktcore.actions.document.copy', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentRenameAction', 'ktcore.actions.document.rename', 'document/Rename.php');
        $this->registerAction('documentaction', 'DocumentIndexAction', 'ktcore.search2.index.action', KT_DIR . '/plugins/search2/DocumentIndexAction.php');
        $this->registerAction('documentinfo', 'KTDocumentTransactionHistoryAction', 'ktcore.actions.document.transactionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentinfo', 'KTDocumentVersionHistoryAction', 'ktcore.actions.document.versionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentArchiveAction', 'ktcore.actions.document.archive', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentWorkflowAction', 'ktcore.actions.document.workflow', 'KTDocumentActions.php');
        $this->registerAction('folderinfo', 'KTFolderViewAction', 'ktcore.actions.folder.view', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTFolderAddDocumentAction', 'ktcore.actions.folder.addDocument', 'folder/addDocument.php');
        $this->registerAction('folderaction', 'KTFolderAddFolderAction', 'ktcore.actions.folder.addFolder', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTFolderRenameAction', 'ktcore.actions.folder.rename', 'folder/Rename.php');
        $this->registerAction('folderaction', 'KTFolderPermissionsAction', 'ktcore.actions.folder.permissions', 'folder/Permissions.php');
        $this->registerAction('folderaction', 'KTBulkImportFolderAction', 'ktcore.actions.folder.bulkImport', 'folder/BulkImport.php');
        $this->registerAction('folderaction', 'KTBulkUploadFolderAction', 'ktcore.actions.folder.bulkUpload', 'folder/BulkUpload.php');
        $this->registerAction('folderaction', 'FolderIndexAction', 'ktcore.search2.index.folder.action', KT_DIR . '/plugins/search2/FolderIndexAction.php');
        $this->registerAction('folderinfo', 'KTFolderTransactionsAction', 'ktcore.actions.folder.transactions', 'folder/Transactions.php');

        $this->registerAction('documentaction', 'KTDocumentAssistAction', 'ktcore.actions.document.assist', 'KTAssist.php');
        // $this->registerAction('folderaction', 'KTDocumentAssistAction', 'ktcore.actions.folder.assist', 'KTAssist.php');

        // Viewlets
        $this->registerAction('documentviewlet', 'KTWorkflowViewlet', 'ktcore.viewlets.document.workflow', 'KTDocumentViewlets.php');

        // Notifications
        $this->registerNotificationHandler('KTAssistNotification', 'ktcore/assist', 'KTAssist.php');
        $this->registerNotificationHandler('KTSubscriptionNotification', 'ktcore/subscriptions', KT_LIB_DIR . '/dashboard/Notification.inc.php');
        $this->registerNotificationHandler('KTWorkflowNotification', 'ktcore/workflow', KT_LIB_DIR . '/dashboard/Notification.inc.php');

        // Permissions
        $this->registerAction('documentinfo', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions', 'KTPermissions.php');
        $this->registerAction('folderaction', 'KTRoleAllocationPlugin', 'ktcore.actions.folder.roles', 'KTPermissions.php');
        $this->registerAction('documentinfo', 'KTDocumentRolesAction', 'ktcore.actions.document.roles', 'KTPermissions.php');

        // Bulk Actions
        $this->registerAction('bulkaction', 'KTBulkDeleteAction', 'ktcore.actions.bulk.delete', 'KTBulkActions.php');
        $this->registerAction('bulkaction', 'KTBulkMoveAction', 'ktcore.actions.bulk.move', 'KTBulkActions.php');
        $this->registerAction('bulkaction', 'KTBulkCopyAction', 'ktcore.actions.bulk.copy', 'KTBulkActions.php');
        $this->registerAction('bulkaction', 'KTBulkArchiveAction', 'ktcore.actions.bulk.archive', 'KTBulkActions.php');
        $this->registerAction('bulkaction', 'KTBrowseBulkExportAction', 'ktcore.actions.bulk.export', 'KTBulkActions.php');
        $this->registerAction('bulkaction', 'KTBrowseBulkCheckoutAction', 'ktcore.actions.bulk.checkout', 'KTBulkActions.php');

        // Dashlets
        $this->registerDashlet('KTInfoDashlet', 'ktcore.dashlet.info', 'KTDashlets.php');
        $this->registerDashlet('KTNotificationDashlet', 'ktcore.dashlet.notifications', 'KTDashlets.php');
        $this->registerDashlet('KTCheckoutDashlet', 'ktcore.dashlet.checkout', 'KTDashlets.php');
        $this->registerDashlet('KTMailServerDashlet', 'ktcore.dashlet.mail_server', 'KTDashlets.php');
        $this->registerDashlet('LuceneMigrationDashlet', 'ktcore.dashlet.lucene_migration', KT_DIR . '/plugins/search2/MigrationDashlet.php');


        // THESE THREE DASHLETS HAVE BEEN MOVED TO ADMIN PAGES

        //$this->registerDashlet('ExternalResourceStatusDashlet', 'ktcore.dashlet.resource_status', KT_DIR . '/plugins/search2/ExternalDashlet.php');
        //$this->registerDashlet('IndexingStatusDashlet', 'ktcore.dashlet.indexing_status', KT_DIR . '/plugins/search2/IndexingStatusDashlet.php');
        //$this->registerDashlet('LuceneStatisticsDashlet', 'ktcore.dashlet.indexing_statss', KT_DIR . '/plugins/search2/LuceneStatisticsDashlet.php');

        $this->registerDashlet('schedulerDashlet', 'ktcore.schedulerdashlet.plugin', 'scheduler/schedulerDashlet.php');

        $this->registerAdminPage('scheduler', 'manageSchedulerDispatcher', 'misc', _kt('Manage Task Scheduler'), _kt('Manage the task scheduler'), 'scheduler/taskScheduler.php');
        $this->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', _kt('Authentication'), sprintf(_kt('By default, %s controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS.   These <strong>Authentication Sources</strong> allow the system administrator to  specify additional sources of authentication data.'), APP_NAME), 'authentication/authenticationadminpage.inc.php');

		    $this->registerPortlet(array('browse', 'dashboard'),
                'Search2Portlet', 'ktcore.search2.portlet',
                KT_DIR . '/plugins/search2/Search2Portlet.php');

        $this->registerPortlet(array('browse'),
                'KTAdminModePortlet', 'ktcore.portlets.admin_mode',
                'KTPortlets.php');

        $this->registerPortlet(array('browse'),
                'KTBrowseModePortlet', 'ktcore.portlets.browsemodes',
                'KTPortlets.php');

        $this->registerPortlet(array('administration'),
                'KTAdminSectionNavigation', 'ktcore.portlets.adminnavigation',
                'KTPortlets.php');

        $this->registerColumn(_kt('Title'), 'ktcore.columns.title', 'AdvancedTitleColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Selection'), 'ktcore.columns.selection', 'AdvancedSelectionColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Single Selection'), 'ktcore.columns.singleselection', 'AdvancedSingleSelectionColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Workflow State'), 'ktcore.columns.workflow_state', 'AdvancedWorkflowColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Checked Out By'), 'ktcore.columns.checkedout_by', 'CheckedOutByColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Creation Date'), 'ktcore.columns.creationdate', 'CreationDateColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Modification Date'), 'ktcore.columns.modificationdate', 'ModificationDateColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Creator'), 'ktcore.columns.creator', 'CreatorColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Download File'), 'ktcore.columns.download', 'AdvancedDownloadColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Document ID'), 'ktcore.columns.docid', 'DocumentIDColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Open Containing Folder'), 'ktcore.columns.containing_folder', 'ContainingFolderColumn', 'KTColumns.inc.php');
        $this->registerColumn(_kt('Document Type'), 'ktcore.columns.document_type', 'DocumentTypeColumn', 'KTColumns.inc.php');

        $this->registerView(_kt('Browse Documents'), 'ktcore.views.browse');
        $this->registerView(_kt('Search'), 'ktcore.views.search');

        // workflow triggers
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.permissionguard', 'PermissionGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.roleguard', 'RoleGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.groupguard', 'GroupGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.conditionguard', 'ConditionGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.checkoutguard', 'CheckoutGuardTrigger', 'KTWorkflowTriggers.inc.php');

        $this->registerWorkflowTrigger('ktcore.workflowtriggers.copyaction', 'CopyActionTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.moveaction', 'MoveActionTrigger', 'KTWorkflowTriggers.inc.php');

		// search triggers
        $this->registerTrigger('edit', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.edit', KT_DIR . '/plugins/search2/Search2Triggers.php');
		$this->registerTrigger('add', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.add', KT_DIR . '/plugins/search2/Search2Triggers.php');
		$this->registerTrigger('discussion', 'postValidate', 'SavedSearchSubscriptionTrigger', 'ktcore.search2.savedsearch.subscription.discussion', KT_DIR . '/plugins/search2/Search2Triggers.php');

		//Tag Cloud Triggers
		$this->registerTrigger('add', 'postValidate', 'KTAddDocumentTrigger', 'ktcore.triggers.tagcloud.add', KT_DIR.'/plugins/tagcloud/TagCloudTriggers.php');
        $this->registerTrigger('edit', 'postValidate', 'KTEditDocumentTrigger', 'ktcore.triggers.tagcloud.edit', KT_DIR.'/plugins/tagcloud/TagCloudTriggers.php');


        // widgets
        $this->registerWidget('KTCoreInfoWidget', 'ktcore.widgets.info', 'KTWidgets.php');
        $this->registerWidget('KTCoreHiddenWidget', 'ktcore.widgets.hidden', 'KTWidgets.php');
        $this->registerWidget('KTCoreStringWidget', 'ktcore.widgets.string', 'KTWidgets.php');
        $this->registerWidget('KTCoreSelectionWidget', 'ktcore.widgets.selection', 'KTWidgets.php');
        $this->registerWidget('KTCoreEntitySelectionWidget', 'ktcore.widgets.entityselection', 'KTWidgets.php');
        $this->registerWidget('KTCoreBooleanWidget', 'ktcore.widgets.boolean', 'KTWidgets.php');
        $this->registerWidget('KTCorePasswordWidget', 'ktcore.widgets.password', 'KTWidgets.php');
        $this->registerWidget('KTCoreTextWidget', 'ktcore.widgets.text', 'KTWidgets.php');
        $this->registerWidget('KTCoreReasonWidget', 'ktcore.widgets.reason', 'KTWidgets.php');
        $this->registerWidget('KTCoreFileWidget', 'ktcore.widgets.file', 'KTWidgets.php');
        $this->registerWidget('KTCoreFieldsetWidget', 'ktcore.widgets.fieldset', 'KTWidgets.php');
        $this->registerWidget('KTCoreTransparentFieldsetWidget', 'ktcore.widgets.transparentfieldset', 'KTWidgets.php');
        $this->registerWidget('KTCoreCollectionWidget', 'ktcore.widgets.collection', 'KTWidgets.php');
        $this->registerWidget('KTCoreTreeMetadataWidget', 'ktcore.widgets.treemetadata', 'KTWidgets.php');
        $this->registerWidget('KTDescriptorSelectionWidget', 'ktcore.widgets.descriptorselection', 'KTWidgets.php');
        $this->registerWidget('KTCoreFolderCollectionWidget', 'ktcore.widgets.foldercollection', 'KTWidgets.php');
        $this->registerWidget('KTCoreFolderCollectionWidget', 'ktcore.widgets.foldercollection', 'KTWidgets.php');

        $this->registerWidget('KTCoreTextAreaWidget', 'ktcore.widgets.textarea', 'KTWidgets.php');
        $this->registerWidget('KTCoreDateWidget', 'ktcore.widgets.date', 'KTWidgets.php');
        
		$this->registerWidget('KTCoreButtonWidget', 'ktcore.widgets.button', 'KTWidgets.php');
		$this->registerWidget('KTCoreLayerWidget', 'ktcore.widgets.layer', 'KTWidgets.php');
		
        $this->registerWidget('KTCoreConditionalSelectionWidget', 'ktcore.widgets.conditionalselection', 'KTWidgets.php');

        $this->registerPage('collection', 'KTCoreCollectionPage', 'KTWidgets.php');
        $this->registerPage('notifications', 'KTNotificationOverflowPage', 'KTMiscPages.php');

        // validators
        $this->registerValidator('KTStringValidator', 'ktcore.validators.string', 'KTValidators.php');
        $this->registerValidator('KTIllegalCharValidator', 'ktcore.validators.illegal_char', 'KTValidators.php');
        $this->registerValidator('KTEntityValidator', 'ktcore.validators.entity', 'KTValidators.php');
        $this->registerValidator('KTRequiredValidator', 'ktcore.validators.required', 'KTValidators.php');
        $this->registerValidator('KTEmailValidator', 'ktcore.validators.emailaddress', 'KTValidators.php');
        $this->registerValidator('KTBooleanValidator', 'ktcore.validators.boolean', 'KTValidators.php');
        $this->registerValidator('KTPasswordValidator', 'ktcore.validators.password', 'KTValidators.php');
        $this->registerValidator('KTMembershipValidator', 'ktcore.validators.membership', 'KTValidators.php');
        $this->registerValidator('KTFieldsetValidator', 'ktcore.validators.fieldset', 'KTValidators.php');
        $this->registerValidator('KTFileValidator', 'ktcore.validators.file', 'KTValidators.php');
        $this->registerValidator('KTRequiredFileValidator', 'ktcore.validators.requiredfile', 'KTValidators.php');
        $this->registerValidator('KTFileIllegalCharValidator', 'ktcore.validators.fileillegalchar', 'KTValidators.php');
        $this->registerValidator('KTArrayValidator', 'ktcore.validators.array', 'KTValidators.php');
        $this->registerValidator('KTDateValidator', 'ktcore.validators.date', 'KTValidators.php');

        // criterion
        $this->registerCriterion('NameCriterion', 'ktcore.criteria.name', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('IDCriterion', 'ktcore.criteria.id', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('TitleCriterion', 'ktcore.criteria.title', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('CreatorCriterion', 'ktcore.criteria.creator', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateCreatedCriterion', 'ktcore.criteria.datecreated', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DocumentTypeCriterion', 'ktcore.criteria.documenttype', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateModifiedCriterion', 'ktcore.criteria.datemodified', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('SizeCriterion', 'ktcore.criteria.size', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('WorkflowStateCriterion', 'ktcore.criteria.workflowstate', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateCreatedDeltaCriterion', 'ktcore.criteria.datecreateddelta', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateModifiedDeltaCriterion', 'ktcore.criteria.datemodifieddelta', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('GeneralMetadataCriterion', 'ktcore.criteria.generalmetadata', KT_LIB_DIR . '/browse/Criteria.inc');


        $this->setupAdmin();
    }

    function setupAdmin() {
        // set up the categories.
        $this->registerAdminCategory('principals', _kt('Users and Groups'),
            _kt('Control which users can log in, and are part of which groups and organisational units, from these management panels.'));
        $this->registerAdminCategory('security', _kt('Security Management'),
            _kt('Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System.'));
        //$this->registerAdminCategory("plugins", _kt("Plugin Management"),
        //    _kt("Control which plugins are loaded, register new plugins and configure individual plugins."));
        $this->registerAdminCategory('storage', _kt('Document Storage'),
            _kt('Manage checked-out, archived and deleted documents.'));
        $this->registerAdminCategory('documents', _kt('Document Metadata and Workflow Configuration'),
            _kt('Configure the document metadata: Document Types, Document Fieldsets, Link Types and Workflows.'));
        $this->registerAdminCategory('search', _kt('Search and Indexing'),
            _kt('Search and Indexing Settings'));
		$this->registerAdminCategory('config', _kt('System Configuration'),
            _kt('System Configuration Settings'));
        $this->registerAdminCategory('misc', _kt('Miscellaneous'),
            _kt('Various settings which do not fit into the other categories, including managing help and saved searches.'));

        // users and groups
        $this->registerAdminPage('users', 'KTUserAdminDispatcher', 'principals',
            _kt('Manage Users'), _kt('Add or remove users from the system.'),
            'admin/userManagement.php', null);
        $this->registerAdminPage('groups', 'KTGroupAdminDispatcher', 'principals',
            _kt('Manage Groups'), _kt('Add or remove groups from the system.'),
            'admin/groupManagement.php', null);
        $this->registerAdminPage('units', 'KTUnitAdminDispatcher', 'principals',
            _kt('Control Units'), _kt('Specify which organisational units are available within the repository.'),
            'admin/unitManagement.php', null);

        // security
        $this->registerAdminPage('permissions', 'ManagePermissionsDispatcher', 'security',
            _kt('Permissions'), _kt('Create or delete permissions.'), 'admin/managePermissions.php', null);
        $this->registerAdminPage('roles', 'RoleAdminDispatcher', 'security',
            _kt('Roles'), _kt('Create or delete roles'),
            'admin/roleManagement.php', null);
        $this->registerAdminPage('conditions', 'KTConditionDispatcher', 'security',
            _kt('Dynamic Conditions'),
            _kt('Manage criteria which determine whether a user is permitted to perform a system action.'),
            'admin/conditions.php', null);

        // documents
        $this->registerAdminPage('typemanagement', 'KTDocumentTypeDispatcher', 'documents',
            _kt('Document Types'),
            _kt('Manage the different classes of document which can be added to the system.'),
            'admin/documentTypes.php', null);
        $this->registerAdminPage('fieldmanagement2', 'KTDocumentFieldDispatcher', 'documents',
             _kt('Document Fieldsets'),
            _kt('Manage the different types of information that can be associated with classes of documents.'),
            'admin/documentFieldsv2.php', null);
        if(KTPluginUtil::pluginIsActive('ktdms.wintools'))
        {
            $this->registerAdminPage('emailtypemanagement', 'KTEmailDocumentTypeDispatcher', 'documents',
                    _kt('Email Document Types'),
                    _kt('Manage the addition of Email document types to the system.'),
                    '../wintools/email/emailDocumentTypes.php', null);
        }
        $this->registerAdminPage('workflows_2', 'KTWorkflowAdminV2', 'documents',
            _kt('Workflows'), _kt('Configure automated Workflows that map to document life-cycles.'),
            'admin/workflowsv2.php', null);

        // storage
        $this->registerAdminPage('checkout', 'KTCheckoutAdminDispatcher', 'storage',
            _kt('Checked Out Document Control'),
            _kt('Override the checked-out status of documents if a user has failed to do so.'),
            'admin/documentCheckout.php', null);
        $this->registerAdminPage('archived', 'ArchivedDocumentsDispatcher', 'storage',
            _kt('Archived Document Restoration'), _kt('Restore old (archived) documents, usually at a user\'s request.'),
            'admin/archivedDocuments.php', null);
        $this->registerAdminPage('expunge', 'DeletedDocumentsDispatcher', 'storage',
            _kt('Restore or Expunge Deleted Documents'), _kt('Restore previously deleted documents, or permanently expunge them.'),
            'admin/deletedDocuments.php', null);

		//Search and Indexing
		$this->registerAdminPage('managemimetypes', 'ManageMimeTypesDispatcher', 'search',
            _kt('Mime Types'), sprintf(_kt('This report lists all mime types and extensions that can be identified by %s.'), APP_NAME),
            '../search2/reporting/ManageMimeTypes.php', null);

        $this->registerAdminPage('extractorinfo', 'ExtractorInfoDispatcher', 'search',
            _kt('Extractor Information'), _kt('This report lists the text extractors and their supported mime types.'),
            '../search2/reporting/ExtractorInfo.php', null);

        $this->registerAdminPage('indexerrors', 'IndexErrorsDispatcher', 'search',
            _kt('Document Indexing Diagnostics'), _kt('This report will help to diagnose problems with document indexing.'),
            '../search2/reporting/IndexErrors.php', null);

		$this->registerAdminPage('pendingdocuments', 'PendingDocumentsDispatcher', 'search',
            _kt('Pending Documents Indexing Queue'), _kt('This report lists documents that are waiting to be indexed.'),
            '../search2/reporting/PendingDocuments.php', null);

        $this->registerAdminPage('reschedulealldocuments', 'RescheduleDocumentsDispatcher', 'search',
            _kt('Reschedule all documents'), _kt('This function allows you to re-index your entire repository.'),
            '../search2/reporting/RescheduleDocuments.php', null);


        // Admin Pages for Previous Dashlets
        $this->registerAdminPage('indexingstatus', 'IndexingStatusDispatcher', 'search',
            _kt('Document Indexer and External Resource Dependancy Status'), _kt('This report will show the status of external dependencies and the document indexer.'),
            '../search2/reporting/IndexingStatus.php', null);

        $this->registerAdminPage('lucenestatistics', 'LuceneStatisticsDispatcher', 'search',
            _kt('Document Indexer Statistics'), _kt('This report will show the Lucene Document Indexing Statistics '),
            '../search2/reporting/LuceneStatistics.php', null);




		//config
		$this->registerAdminPage('emailconfigpage', 'EmailConfigPageDispatcher', 'config',
            _kt('Email'), _kt('Define the sending email server address, email password, email port, and user name, and view and modify policies for emailing documents and attachments from KnowledgeTree.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('uiconfigpage', 'UIConfigPageDispatcher', 'config',
            _kt('User Interface'), _kt('View and modify settings on Browse View actions, OEM name, automatic refresh, search results restrictions, custom logo details, paths to dot binary, graphics, and log directory, and whether to enable/disable condensed UI, \'open\' from downloads, sort metadata, and skinning.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('searchandindexingconfigpage', 'SearchAndIndexingConfigPageDispatcher', 'config',
            _kt('Search and Indexing'), _kt('View and modify the number of documents indexed / migrated in a cron session, core indexing class, paths to the extractor hook, text extractors, indexing engine, Lucene indexes, and the Java Lucene URL. View and modify search date format, paths to search, indexing fields and libraries, results display format, and results per page.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('clientconfigpage', 'ClientSettingsConfigPageDispatcher', 'config',
            _kt('Client Tools'), _kt('View and change settings for the KnowledgeTree Tools Server, Client Tools Policies, WebDAV, and the OpenOffice.org service.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('generalconfigpage', 'GeneralConfigPageDispatcher', 'config',
            _kt('General Settings'), _kt('View and modify settings for the KnowledgeTree cache, custom error message handling, Disk Usage threshold percentages, location of zip binary, paths to external binaries, general server configuration, LDAP authentication, session management, KnowledgeTree storage manager, miscellaneous tweaks, and whether to always display \'Your Checked-out Documents\' dashlet.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('i18nconfigpage', 'i18nConfigPageDispatcher', 'config',
            _kt('Internationalization'), _kt('View and modify the default language.'),
            'admin/configSettings.php', null);

        $this->registerAdminPage('securityconfigpage', 'SecurityConfigPageDispatcher', 'config',
            _kt('Security'), _kt('View and modify the security settings.'),
            'admin/configSettings.php', null);

        // misc
        $this->registerAdminPage('helpmanagement', 'ManageHelpDispatcher', 'misc',
            _kt('Edit Help files'), _kt('Change the help files that are displayed to users.'),
            'admin/manageHelp.php', null);
        $this->registerAdminPage('plugins', 'KTPluginDispatcher', 'misc',
            _kt('Manage plugins'), _kt('Register new plugins, disable plugins, and so forth'),
            'admin/plugins.php', null);
        $this->registerAdminPage('techsupport', 'KTSupportDispatcher', 'misc',
            _kt('Support and System information'), _kt('Information about this system and how to get support.'),
            'admin/techsupport.php', null);
        $this->registerAdminPage('cleanup', 'ManageCleanupDispatcher', 'storage',
            _kt('Verify Document Storage'), _kt('Performs a check to see if the documents in your repositories all are stored on the back-end storage (usually on disk).'),
            'admin/manageCleanup.php', null);
        $this->registerAdminPage('views', 'ManageViewDispatcher', 'misc',
            _kt('Manage views'), _kt('Allows you to specify the columns that are to be used by a particular view (e.g. Browse documents, Search)'),
            'admin/manageViews.php', null);

        // plugins

    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCorePlugin', 'ktcore.plugin', __FILE__);

require_once('KTPortlets.php');

require_once(KT_LIB_DIR . '/storage/ondiskpathstoragemanager.inc.php');
require_once(KT_LIB_DIR . '/storage/ondiskhashedstoragemanager.inc.php');
