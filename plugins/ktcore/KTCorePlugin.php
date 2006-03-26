<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

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
        $this->registerAction('documentaction', 'KTDocumentCopyAction', 'ktcore.actions.document.copy', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentRenameAction', 'ktcore.actions.document.rename', 'document/Rename.php');
        $this->registerAction('documentaction', 'KTDocumentTransactionHistoryAction', 'ktcore.actions.document.transactionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentVersionHistoryAction', 'ktcore.actions.document.versionhistory', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentArchiveAction', 'ktcore.actions.document.archive', 'KTDocumentActions.php');
        $this->registerAction('documentaction', 'KTDocumentWorkflowAction', 'ktcore.actions.document.workflow', 'KTDocumentActions.php');
        $this->registerAction('folderaction', 'KTFolderAddDocumentAction', 'ktcore.actions.folder.addDocument', 'folder/addDocument.php');
        $this->registerAction('folderaction', 'KTFolderAddFolderAction', 'ktcore.actions.folder.addFolder', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTFolderRenameAction', 'ktcore.actions.folder.rename', 'folder/Rename.php');
        $this->registerAction('folderaction', 'KTFolderPermissionsAction', 'ktcore.actions.folder.permissions', 'KTFolderActions.php');
        $this->registerAction('folderaction', 'KTBulkImportFolderAction', 'ktcore.actions.folder.bulkImport', 'folder/BulkImport.php');
        $this->registerAction('folderaction', 'KTBulkUploadFolderAction', 'ktcore.actions.folder.bulkUpload', 'folder/BulkUpload.php');

        // Permissions
        $this->registerAction('documentaction', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions', 'KTPermissions.php');
        $this->registerAction('folderaction', 'KTRoleAllocationPlugin', 'ktcore.actions.folder.roles', 'KTPermissions.php');
        $this->registerAction('documentaction', 'KTDocumentRolesAction', 'ktcore.actions.document.roles', 'KTPermissions.php');

        $this->registerDashlet('KTInfoDashlet', 'ktcore.dashlet.info', 'KTDashlets.php');
        $this->registerDashlet('KTNotificationDashlet', 'ktcore.dashlet.notifications', 'KTDashlets.php');
        $this->registerDashlet('KTCheckoutDashlet', 'ktcore.dashlet.checkout', 'KTDashlets.php');
        $this->registerDashlet('KTIndexerStatusDashlet', 'ktcore.dashlet.indexer_status', 'KTDashlets.php');

        $this->registerAdminPage('authentication', 'KTAuthenticationAdminPage', 'principals', _kt('Authentication'), _('By default, KnowledgeTree controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS.   These <strong>Authentication Sources</strong> allow the system administrator to  specify additional sources of authentication data.'), 'authentication/authenticationadminpage.inc.php');

        $this->registeri18n('knowledgeTree', KT_DIR . '/i18n');

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

        $this->setupAdmin();
    }

    function setupAdmin() {
        // set up the categories.
        $this->registerAdminCategory("principals", _kt("Users and Groups"),
            _kt("Control which users can log in, and are part of which groups and organisational units, from these management panels."));
        $this->registerAdminCategory("security", _kt("Security Management"),
            _kt("Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System."));
        //$this->registerAdminCategory("plugins", _kt("Plugin Management"),
        //    _kt("Control which plugins are loaded, register new plugins and configure individual plugins."));
        $this->registerAdminCategory("storage", _kt("Document Storage"),
            _kt("Manage checked-out, archived and deleted documents."));
        $this->registerAdminCategory("documents", _kt("Document Metadata and Workflow Configuration"),
            _kt("Configure the document metadata: Document Types, Document Fieldsets, Link Types and Workflows."));
        $this->registerAdminCategory("misc", _kt("Miscellaneous"),
            _kt("Various settings which do not fit into the other categories, including managing help and saved searches."));

        // users and groups
        $this->registerAdminPage("users", 'KTUserAdminDispatcher', "principals",
            _kt("Manage Users"), _("Add or remove users from the system."),
            'admin/userManagement.php', null);
        $this->registerAdminPage("groups", 'KTGroupAdminDispatcher', "principals",
            _kt("Manage Groups"), _("Add or remove groups from the system."),
            'admin/groupManagement.php', null);
        $this->registerAdminPage("units", 'KTUnitAdminDispatcher', "principals",
            _kt("Control Units"), _("Specify which organisational units are available within the repository."),
            'admin/unitManagement.php', null);

        // security
        $this->registerAdminPage("permissions", 'ManagePermissionsDispatcher', "security",
            _kt("Permissions"), _("Create or delete permissions."), 'admin/managePermissions.php', null);
        $this->registerAdminPage("roles", 'RoleAdminDispatcher', "security",
            _kt("Roles"), _("Create or delete roles"),
            'admin/roleManagement.php', null);
        $this->registerAdminPage("conditions", 'KTConditionDispatcher', "security",
            _kt("Dynamic Conditions"),
            _kt("Manage criteria which determine whether a user is permitted to perform a system action."),
            'admin/conditions.php', null);

        // documents
        $this->registerAdminPage("typemanagement", 'KTDocumentTypeDispatcher', 'documents',
            _kt('Document Types'),
            _kt('Manage the different classes of document which can be added to the system.'),
            'admin/documentTypes.php', null);
        $this->registerAdminPage("fieldmanagement", 'KTDocumentFieldDispatcher', 'documents',
             _kt('Document Fieldsets'),
            _kt('Manage the different types of information that can be associated with classes of documents.'),
            'admin/documentFields.php', null);
        $this->registerAdminPage("linkmanagement", 'KTDocLinkAdminDispatcher', 'documents',
            _kt('Link Type Management'),
            _kt('Manage the different ways documents can be associated with one another.'),
            'admin/documentLinks.php', null);
        $this->registerAdminPage("workflows", 'KTWorkflowDispatcher', 'documents',
            _kt('Workflows'), _('Configure the process documents go through.'),
            'admin/workflows.php', null);

        // storage
        $this->registerAdminPage("checkout", 'KTCheckoutAdminDispatcher', 'storage',
            _kt('Checked Out Document Control'),
            _kt('Override the checked-out status of documents if a user has failed to do so.'),
            'admin/documentCheckout.php', null);
        $this->registerAdminPage("archived", 'ArchivedDocumentsDispatcher', 'storage',
            _kt('Archived Document Restoration'), _("Restore old (archived) documents, usually at a user's request."),
            'admin/archivedDocuments.php', null);
        $this->registerAdminPage("expunge", 'DeletedDocumentsDispatcher', 'storage',
            _kt('Restore or Expunge Deleted Documents'), _('Restore previously deleted documents, or permanently expunge them.'),
            'admin/deletedDocuments.php', null);

        // misc
        $this->registerAdminPage("helpmanagement", 'ManageHelpDispatcher', 'misc',
            _kt('Edit Help files'), _('Change the help files that are displayed to users.'),
            'admin/manageHelp.php', null);
        $this->registerAdminPage("savedsearch", 'KTSavedSearchDispatcher', 'misc',
            _kt('Saved searches'),
            _kt('Manage saved searches - searches available by default to all users.'),
            'admin/savedSearch.php', null);
        $this->registerAdminPage("plugins", 'KTPluginDispatcher', 'misc',
            _kt('Manage plugins'), _('Register new plugins, disable plugins, and so forth'),
            'admin/plugins.php', null);
        $this->registerAdminPage("techsupport", 'KTSupportDispatcher', 'misc',
            _kt('Support and System information'), _('Information about this system and how to get support.'),
            'admin/techsupport.php', null);
        // plugins

    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTCorePlugin', 'ktcore.plugin', __FILE__);

require_once('KTPortlets.php');

require_once(KT_LIB_DIR . '/storage/ondiskpathstoragemanager.inc.php');
