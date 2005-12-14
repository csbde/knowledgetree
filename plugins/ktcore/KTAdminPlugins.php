<?php

require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");

$oRegistry =& KTPluginRegistry::getSingleton();
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');

// set up the categories.
$oPlugin->registerAdminCategory("principals", _("Users and Groups"), _("Control which users can log in, and are part of which groups and organisational units from these management panels."));
$oPlugin->registerAdminCategory("security", _("Security Management"), _("Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System."));
$oPlugin->registerAdminCategory("storage", _("Document Storage"), _("Manage how and where the actual documents will be stored, work with document archives and deal with other document related problems."));
$oPlugin->registerAdminCategory("documents", _("Document Type Configuration"), _("Configure the information that needs to be collected about different kinds of documents."));
$oPlugin->registerAdminCategory("collections", _("Collections"), _("Specify how groups of documents are displayed in browse and search mode."));
$oPlugin->registerAdminCategory("misc", _("Miscellaneous"), _("Various settings which do not fit into the other categories, including help, etc."));



// set up the items
/* there are two ways to do this: 
 *
 *  - by url [LEGACY ONLY]
 *  - by class & path (relative to KT_LIB_DIR)
 */

//registerAdminPage($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null) {

// FIXME url traversal DOESN'T WORK

// users and groups
$oPlugin->registerAdminPage("users",'KTUserAdminDispatcher',"principals", _("Manage Users"), _("Add or remove users from the system."), 'admin/userManagement.php', null);
$oPlugin->registerAdminPage("groups",'KTGroupAdminDispatcher',"principals", _("Manage Groups"), _("Add or remove groups from the system."), 'admin/groupManagement.php', null);
$oPlugin->registerAdminPage("units",'KTUnitAdminDispatcher',"principals", _("Control Units"), _("Specify which organisation units are available."), 'admin/unitManagement.php', null);
// disabled until it actually makes sense.
//$oPlugin->registerAdminPage("orgs",'KTOrgAdminDispatcher',"principals", "Control Organisations","Specify which organisations are available.", 'admin/orgManagement.php', null);

// security
$oPlugin->registerAdminPage("permissions",'ManagePermissionsDispatcher',"security", _("Permissions"), _("Create or Delete permissions."), 'admin/managePermissions.php', null);
$oPlugin->registerAdminPage("roles",'RoleAdminDispatcher',"security", _("Roles"), _("Create or Delete roles") . " (incomplete).", 'admin/roleManagement.php', null);
$oPlugin->registerAdminPage("conditions",'KTConditionDispatcher',"security", _("Conditions"), _("Manage document conditions, which can be used to control whether certain actions are permitted or not."), 'admin/conditions.php', null);

// documents
$oPlugin->registerAdminPage("typemanagement",'KTDocumentTypeDispatcher','documents', _('Document Types'), _('Manage the different classes of document which can be added to the system.'), 'admin/documentTypes.php', null);
$oPlugin->registerAdminPage("fieldmanagement",'KTDocumentFieldDispatcher','documents', _('Document Fieldsets'), _('Control which kinds of documents have which sets of information associated with them.'), 'admin/documentFields.php', null);
$oPlugin->registerAdminPage("linkmanagement",'KTDocLinkAdminDispatcher','documents', _('Link Type Management'), _('Specify the different "link types" - ways to relate different documents togeter.'), 'admin/documentLinks.php', null);
$oPlugin->registerAdminPage("workflows",'KTWorkflowDispatcher','documents', _('Workflows'), _('Configure the process documents go through.'), 'admin/workflows.php', null);

// storage
$oPlugin->registerAdminPage("checkout",'KTCheckoutAdminDispatcher','storage', _('Checked Out Document Control'), _('Override the checked-out status of documents if a user has failed to do so.'), 'admin/documentCheckout.php', null);
$oPlugin->registerAdminPage("archived",'ArchivedDocumentsDispatcher','storage', _('Archived Document Restoration'), _("Restore old (archived) documents, usually at a user's request."), 'admin/archivedDocuments.php', null);
$oPlugin->registerAdminPage("expunge",'DeletedDocumentsDispatcher','storage', _('Expunge Deleted Documents'), _('Permanently expunge deleted documents.'), 'admin/deletedDocuments.php', null);

// misc
$oPlugin->registerAdminPage("helpmanagement",'ManageHelpDispatcher','misc', _('Edit Help files'), _('Change the help files that are displayed to users.'), 'admin/manageHelp.php', null);
$oPlugin->registerAdminPage("savedsearch",'KTSavedSearchDispatcher','misc', _('Saved searches'), _('Manage saved searches - searches available by default to all users.'), 'admin/manageHelp.php', null);

?>
