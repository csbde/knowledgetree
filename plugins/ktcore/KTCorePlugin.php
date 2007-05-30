<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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

    function KTCorePlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Core Application Functionality');
        return $res;
    }
    
    function setup() {
        $this->registerAction('documentinfo', 'KTDocumentDetailsAction', 'ktcore.actions.document.displaydetails', 'KTDocumentActions.php');
        $this->registerAction('documentinfo', 'KTDocumentViewAction', 'ktcore.actions.document.view', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTOwnershipChangeAction', 'ktcore.actions.document.ownershipchange', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckOutAction', 'ktcore.actions.document.checkout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCancelCheckOutAction', 'ktcore.actions.document.cancelcheckout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckInAction', 'ktcore.actions.document.checkin', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentEditAction', 'ktcore.actions.document.edit', 'document/edit.php');
        $this->registerAction('documentaction', 'KTDocumentDeleteAction', 'ktcore.actions.document.delete', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentMoveAction', 'ktcore.actions.document.move', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCopyAction', 'ktcore.actions.document.copy', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentRenameAction', 'ktcore.actions.document.rename', 'document/Rename.php');
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
        $this->registerAction('folderinfo', 'KTFolderTransactionsAction', 'ktcore.actions.folder.transactions', 'folder/Transactions.php');

        $this->registerAction('documentaction', 'KTDocumentAssistAction', 'ktcore.actions.document.assist', 'KTAssist.php');
        // $this->registerAction('folderaction', 'KTDocumentAssistAction', 'ktcore.actions.folder.assist', 'KTAssist.php');

        // Viewlets
        $this->registerAction('documentviewlet', 'KTWorkflowViewlet', 'ktcore.viewlets.document.workflow', 'KTDocumentViewlets.php');        


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


        // Dashlets
        $this->registerDashlet('KTInfoDashlet', 'ktcore.dashlet.info', 'KTDashlets.php');
        $this->registerDashlet('KTNotificationDashlet', 'ktcore.dashlet.notifications', 'KTDashlets.php');
        $this->registerDashlet('KTCheckoutDashlet', 'ktcore.dashlet.checkout', 'KTDashlets.php');
        $this->registerDashlet('KTIndexerStatusDashlet', 'ktcore.dashlet.indexer_status', 'KTDashlets.php');
        $this->registerDashlet('KTMailServerDashlet', 'ktcore.dashlet.mail_server', 'KTDashlets.php');

        $this->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', _kt('Authentication'), sprintf(_kt('By default, %s controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS.   These <strong>Authentication Sources</strong> allow the system administrator to  specify additional sources of authentication data.'), APP_NAME), 'authentication/authenticationadminpage.inc.php');

        $this->registerPortlet(array('browse'),
                'KTAdminModePortlet', 'ktcore.portlets.admin_mode',
                'KTPortlets.php');
        $this->registerPortlet(array('browse', 'dashboard'),
                'KTSearchPortlet', 'ktcore.portlets.search',
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
        $this->registerColumn(_kt('Creation Date'), 'ktcore.columns.creationdate', 'CreationDateColumn', 'KTColumns.inc.php');        
        $this->registerColumn(_kt('Modification Date'), 'ktcore.columns.modificationdate', 'ModificationDateColumn', 'KTColumns.inc.php');                                        
        $this->registerColumn(_kt('Creator'), 'ktcore.columns.creator', 'CreatorColumn', 'KTColumns.inc.php');                                                
        $this->registerColumn(_kt('Download File'), 'ktcore.columns.download', 'AdvancedDownloadColumn', 'KTColumns.inc.php');                                                        
        $this->registerColumn(_kt('Document ID'), 'ktcore.columns.docid', 'DocumentIDColumn', 'KTColumns.inc.php');                                                                
        $this->registerColumn(_kt('Open Containing Folder'), 'ktcore.columns.containing_folder', 'ContainingFolderColumn', 'KTColumns.inc.php');                
        
        $this->registerView(_kt('Browse Documents'), 'ktcore.views.browse');
        $this->registerView(_kt('Search'), 'ktcore.views.search');        

        // workflow triggers
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.permissionguard', 'PermissionGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.roleguard', 'RoleGuardTrigger', 'KTWorkflowTriggers.inc.php');
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.groupguard', 'GroupGuardTrigger', 'KTWorkflowTriggers.inc.php');        
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.conditionguard', 'ConditionGuardTrigger', 'KTWorkflowTriggers.inc.php');        
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.checkoutguard', 'CheckoutGuardTrigger', 'KTWorkflowTriggers.inc.php');        
        
        $this->registerWorkflowTrigger('ktcore.workflowtriggers.copyaction', 'CopyActionTrigger', 'KTWorkflowTriggers.inc.php');        
        
        // widgets
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
                
        $this->registerPage('collection', 'KTCoreCollectionPage', 'KTWidgets.php');
        $this->registerPage('notifications', 'KTNotificationOverflowPage', 'KTMiscPages.php');

        
        // validators
        $this->registerValidator('KTStringValidator', 'ktcore.validators.string', 'KTValidators.php');
        $this->registerValidator('KTEntityValidator', 'ktcore.validators.entity', 'KTValidators.php');
        $this->registerValidator('KTRequiredValidator', 'ktcore.validators.required', 'KTValidators.php');
        $this->registerValidator('KTEmailValidator', 'ktcore.validators.emailaddress', 'KTValidators.php');        
        $this->registerValidator('KTBooleanValidator', 'ktcore.validators.boolean', 'KTValidators.php');                
        $this->registerValidator('KTPasswordValidator', 'ktcore.validators.password', 'KTValidators.php');            
        $this->registerValidator('KTMembershipValidator', 'ktcore.validators.membership', 'KTValidators.php');                
        $this->registerValidator('KTFieldsetValidator', 'ktcore.validators.fieldset', 'KTValidators.php');                
        $this->registerValidator('KTFileValidator', 'ktcore.validators.file', 'KTValidators.php');                        
        $this->registerValidator('KTRequiredFileValidator', 'ktcore.validators.requiredfile', 'KTValidators.php');                   
        $this->registerValidator('KTArrayValidator', 'ktcore.validators.array', 'KTValidators.php');           

        // criterion
        $this->registerCriterion('NameCriterion', 'ktcore.criteria.name', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('IDCriterion', 'ktcore.criteria.id', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('TitleCriterion', 'ktcore.criteria.title', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('CreatorCriterion', 'ktcore.criteria.creator', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateCreatedCriterion', 'ktcore.criteria.datecreated', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DocumentTypeCriterion', 'ktcore.criteria.documenttype', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DateModifiedCriterion', 'ktcore.criteria.datemodified', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('SizeCriterion', 'ktcore.criteria.size', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('ContentCriterion', 'ktcore.criteria.content', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('WorkflowStateCriterion', 'ktcore.criteria.workflowstate', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('DiscussionTextCriterion', 'ktcore.criteria.discussiontext', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('SearchableTextCriterion', 'ktcore.criteria.searchabletext', KT_LIB_DIR . '/browse/Criteria.inc');
        $this->registerCriterion('TransactionTextCriterion', 'ktcore.criteria.transactiontext', KT_LIB_DIR . '/browse/Criteria.inc');
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

        // misc
        $this->registerAdminPage('helpmanagement', 'ManageHelpDispatcher', 'misc',
            _kt('Edit Help files'), _kt('Change the help files that are displayed to users.'),
            'admin/manageHelp.php', null);
        $this->registerAdminPage('savedsearch', 'KTSavedSearchDispatcher', 'misc',
            _kt('Saved searches'),
            _kt('Manage saved searches - searches available by default to all users.'),
            'admin/savedSearch.php', null);
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
