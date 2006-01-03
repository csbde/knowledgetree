<?php

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTCorePlugin extends KTPlugin {
    function setup() {
        $this->registerAction('documentaction', 'KTDocumentViewAction', 'ktcore.actions.document.view', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckOutAction', 'ktcore.actions.document.checkout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckInAction', 'ktcore.actions.document.checkin', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentEditAction', 'ktcore.actions.document.edit', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentDeleteAction', 'ktcore.actions.document.delete', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentMoveAction', 'ktcore.actions.document.move', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentTransactionHistoryAction', 'ktcore.actions.document.transactionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentVersionHistoryAction', 'ktcore.actions.document.versionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentArchiveAction', 'ktcore.actions.document.archive', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentWorkflowAction', 'ktcore.actions.document.workflow', 'KTDocumentActions.php');
        $this->registerAction('folderaction', 'KTFolderAddDocumentAction', 'ktcore.actions.folder.addDocument', 'folder/addDocument.php');
        $this->registerAction('folderaction', 'KTFolderAddFolderAction', 'ktcore.actions.folder.addFolder', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTFolderPermissionsAction', 'ktcore.actions.folder.permissions', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTBulkImportFolderAction', 'ktcore.actions.folder.bulkImport', 'folder/BulkImport.php');
        $this->registerAction('folderaction', 'KTBulkUploadFolderAction', 'ktcore.actions.folder.bulkUpload', 'folder/BulkUpload.php');

        // Permissions
        $this->registerAction('documentaction', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions', 'KTPermissions.php');
        $this->registerAction('folderaction', 'KTRoleAllocationPlugin', 'ktcore.actions.folder.roles', 'KTPermissions.php');

        $this->registerDashlet('KTBeta1InfoDashlet', 'ktcore.dashlet.beta1info', 'KTDashlets.php');
        $this->registerDashlet('KTNotificationDashlet', 'ktcore.dashlet.notifications', 'KTDashlets.php');
        $this->registerDashlet('KTCheckoutDashlet', 'ktcore.dashlet.checkout', 'KTDashlets.php');

        $this->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', 'Authentication', 'FIXME: describe authentication', 'authentication/authenticationadminpage.inc.php');
        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');

        $this->setupAdmin();
    }

    function setupAdmin() {
        // set up the categories.
        $this->registerAdminCategory("principals", _("Users and Groups"),
            _("Control which users can log in, and are part of which groups and organisational units from these management panels."));
        $this->registerAdminCategory("security", _("Security Management"),
            _("Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System."));
        $this->registerAdminCategory("storage", _("Document Storage"),
            _("Manage how and where the actual documents will be stored, work with document archives and deal with other document related problems."));
        $this->registerAdminCategory("documents", _("Document Type Configuration"),
            _("Configure the information that needs to be collected about different kinds of documents."));
        $this->registerAdminCategory("misc", _("Miscellaneous"),
            _("Various settings which do not fit into the other categories, including help, etc."));

        // users and groups
        $this->registerAdminPage("users", 'KTUserAdminDispatcher', "principals",
            _("Manage Users"), _("Add or remove users from the system."),
            'admin/userManagement.php', null);
        $this->registerAdminPage("groups", 'KTGroupAdminDispatcher', "principals",
            _("Manage Groups"), _("Add or remove groups from the system."),
            'admin/groupManagement.php', null);
        $this->registerAdminPage("units", 'KTUnitAdminDispatcher', "principals",
            _("Control Units"), _("Specify which organisation units are available."),
            'admin/unitManagement.php', null);
        // disabled until it actually makes sense.
        //$this->registerAdminPage("orgs",'KTOrgAdminDispatcher',"principals", "Control Organisations","Specify which organisations are available.", 'admin/orgManagement.php', null);

        // security
        $this->registerAdminPage("permissions", 'ManagePermissionsDispatcher', "security",
            _("Permissions"), _("Create or Delete permissions."), 'admin/managePermissions.php', null);
        $this->registerAdminPage("roles", 'RoleAdminDispatcher', "security",
            _("Roles"), _("Create or Delete roles"),
            'admin/roleManagement.php', null);
        $this->registerAdminPage("conditions", 'KTConditionDispatcher', "security",
            _("Conditions"),
            _("Manage document conditions, which can be used to control whether certain actions are permitted or not."),
            'admin/conditions.php', null);

        // documents
        $this->registerAdminPage("typemanagement", 'KTDocumentTypeDispatcher', 'documents',
            _('Document Types'),
            _('Manage the different classes of document which can be added to the system.'),
            'admin/documentTypes.php', null);
        $this->registerAdminPage("fieldmanagement", 'KTDocumentFieldDispatcher', 'documents',
             _('Document Fieldsets'),
            _('Control which kinds of documents have which sets of information associated with them.'),
            'admin/documentFields.php', null);
        $this->registerAdminPage("linkmanagement", 'KTDocLinkAdminDispatcher', 'documents',
            _('Link Type Management'),
            _('Specify the different "link types" - ways to relate different documents togeter.'),
            'admin/documentLinks.php', null);
        $this->registerAdminPage("workflows", 'KTWorkflowDispatcher', 'documents',
            _('Workflows'), _('Configure the process documents go through.'),
            'admin/workflows.php', null);

        // storage
        $this->registerAdminPage("checkout", 'KTCheckoutAdminDispatcher', 'storage',
            _('Checked Out Document Control'),
            _('Override the checked-out status of documents if a user has failed to do so.'),
            'admin/documentCheckout.php', null);
        $this->registerAdminPage("archived", 'ArchivedDocumentsDispatcher', 'storage',
            _('Archived Document Restoration'), _("Restore old (archived) documents, usually at a user's request."),
            'admin/archivedDocuments.php', null);
        $this->registerAdminPage("expunge", 'DeletedDocumentsDispatcher', 'storage',
            _('Expunge Deleted Documents'), _('Permanently expunge deleted documents.'),
            'admin/deletedDocuments.php', null);

        // misc
        $this->registerAdminPage("helpmanagement", 'ManageHelpDispatcher', 'misc',
            _('Edit Help files'), _('Change the help files that are displayed to users.'),
            'admin/manageHelp.php', null);
        $this->registerAdminPage("savedsearch", 'KTSavedSearchDispatcher', 'misc',
            _('Saved searches'),
            _('Manage saved searches - searches available by default to all users.'),
            'admin/manageHelp.php', null);

        $this->registerPortlet(array('browse', 'dashboard'),
                'KTSearchPortlet', 'ktcore.portlets.search',
                'KTPortlets.php');
        $this->registerPortlet(array('browse'),
                'KTBrowseModePortlet', 'ktcore.portlets.browsemodes',
                'KTPortlets.php');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCorePlugin', 'ktcore.plugin', __FILE__);
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

require_once('KTPortlets.php');

require_once(KT_LIB_DIR . '/storage/ondiskpathstoragemanager.inc.php');
