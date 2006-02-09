<?php

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class KTCorePlugin extends KTPlugin {
    var $bAlwaysInclude = true;
    var $sNamespace = "ktcore.plugin";

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentDetailsAction', 'ktcore.actions.document.displaydetails', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentViewAction', 'ktcore.actions.document.view', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCheckOutAction', 'ktcore.actions.document.checkout', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentCancelCheckOutAction', 'ktcore.actions.document.cancelcheckout', 'KTDocumentActions.php');
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

        $this->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', _('Authentication'), _('By default, KnowledgeTree controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS.   These <strong>Authentication Sources</strong> allow the system administrator to  specify additional sources of authentication data.'), 'authentication/authenticationadminpage.inc.php');        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');

        $this->registerPortlet(array('browse', 'dashboard'),
                'KTSearchPortlet', 'ktcore.portlets.search',
                'KTPortlets.php');
        $this->registerPortlet(array('browse'),
                'KTBrowseModePortlet', 'ktcore.portlets.browsemodes',
                'KTPortlets.php');

        $this->setupAdmin();
    }

    function setupAdmin() {
        // set up the categories.
        $this->registerAdminCategory("principals", _("Users and Groups"),
            _("Control which users can log in, and are part of which groups and organisational units, from these management panels."));
        $this->registerAdminCategory("security", _("Security Management"),
            _("Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System."));
        //$this->registerAdminCategory("plugins", _("Plugin Management"),
        //    _("Control which plugins are loaded, register new plugins and configure individual plugins."));
        $this->registerAdminCategory("storage", _("Document Storage"),
            _("Manage checked-out, archived and deleted documents."));
        $this->registerAdminCategory("documents", _("Document Metadata and Workflow Configuration"),
            _("Configure the document metadata: Document Types, Document Fieldsets, Link Types and Workflows."));
        $this->registerAdminCategory("misc", _("Miscellaneous"),
            _("Various settings which do not fit into the other categories, including managing help and saved searches."));

        // users and groups
        $this->registerAdminPage("users", 'KTUserAdminDispatcher', "principals",
            _("Manage Users"), _("Add or remove users from the system."),
            'admin/userManagement.php', null);
        $this->registerAdminPage("groups", 'KTGroupAdminDispatcher', "principals",
            _("Manage Groups"), _("Add or remove groups from the system."),
            'admin/groupManagement.php', null);
        $this->registerAdminPage("units", 'KTUnitAdminDispatcher', "principals",
            _("Control Units"), _("Specify which organisational units are available within the repository."),
            'admin/unitManagement.php', null);

        // security
        $this->registerAdminPage("permissions", 'ManagePermissionsDispatcher', "security",
            _("Permissions"), _("Create or delete permissions."), 'admin/managePermissions.php', null);
        $this->registerAdminPage("roles", 'RoleAdminDispatcher', "security",
            _("Roles"), _("Create or delete roles"),
            'admin/roleManagement.php', null);
        $this->registerAdminPage("conditions", 'KTConditionDispatcher', "security",
            _("Dynamic Conditions"),
            _("Manage criteria which determine whether a user is permitted to perform a system action."),
            'admin/conditions.php', null);

        // documents
        $this->registerAdminPage("typemanagement", 'KTDocumentTypeDispatcher', 'documents',
            _('Document Types'),
            _('Manage the different classes of document which can be added to the system.'),
            'admin/documentTypes.php', null);
        $this->registerAdminPage("fieldmanagement", 'KTDocumentFieldDispatcher', 'documents',
             _('Document Fieldsets'),
            _('Manage the different types of information that can be associated with classes of documents.'),
            'admin/documentFields.php', null);
        $this->registerAdminPage("linkmanagement", 'KTDocLinkAdminDispatcher', 'documents',
            _('Link Type Management'),
            _('Manage the different ways documents can be associated with one another.'),
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
            _('Restore or Expunge Deleted Documents'), _('Restore previously deleted documents, or permanently expunge them.'),
            'admin/deletedDocuments.php', null);

        // misc
        $this->registerAdminPage("helpmanagement", 'ManageHelpDispatcher', 'misc',
            _('Edit Help files'), _('Change the help files that are displayed to users.'),
            'admin/manageHelp.php', null);
        $this->registerAdminPage("savedsearch", 'KTSavedSearchDispatcher', 'misc',
            _('Saved searches'),
            _('Manage saved searches - searches available by default to all users.'),
            'admin/savedSearch.php', null);
        $this->registerAdminPage("plugins", 'KTPluginDispatcher', 'misc',
            _('Manage plugins'), _('Register new plugins, disable plugins, and so forth'),
            'admin/plugins.php', null);
        
        // plugins

    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCorePlugin', 'ktcore.plugin', __FILE__);

require_once('KTPortlets.php');

require_once(KT_LIB_DIR . '/storage/ondiskpathstoragemanager.inc.php');
