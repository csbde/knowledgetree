<?php

require_once(KT_LIB_DIR . "/plugins/KTAdminNavigation.php");

$oAdminRegistry =& KTAdminNavigationRegistry::getSingleton();

// set up the categories.
$oAdminRegistry->registerCategory("principals", "Users and Groups", " Control which users can log in, and are part of which groups and organisational units from these management panels.");
$oAdminRegistry->registerCategory("security", "Security Management", " Assign permissions to users and groups, and specify which permissions are required to interact with various parts of the Document Management System.");
$oAdminRegistry->registerCategory("storage", "Document Storage", "Manage how and where the actual documents will be stored, work with document archives and deal with other document related problems.");
$oAdminRegistry->registerCategory("documents", "Document Type Configuration", "Configure the information that needs to be collected about different kinds of documents.");
$oAdminRegistry->registerCategory("collections", "Collections", "Specify how groups of documents are displayed in browse and search mode.");
$oAdminRegistry->registerCategory("misc", "Miscellaneous", "Various settings which do not fit into the other categories, including help, etc.");



// set up the items
/* there are two ways to do this: 
 *
 *  - by url [LEGACY ONLY]
 *  - by class & path (relative to KT_LIB_DIR)
 */

//registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null) {

// FIXME url traversal DOESN'T WORK

// users and groups
$oAdminRegistry->registerLocation("users",'KTUserAdminDispatcher',"principals", "Manage Users","Add or remove users from the system.", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/usermanagement/userManagement.php', null);
$oAdminRegistry->registerLocation("groups",'KTGroupAdminDispatcher',"principals", "Manage Groups","Add or remove groups from the system.", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/groupmanagement/groupManagement.php', null);
$oAdminRegistry->registerLocation("units",'KTUnitAdminDispatcher',"principals", "Control Units","Specify which organisation units are available.", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/unitmanagement/unitManagement.php', null);
// disabled until it actually makes sense.
//$oAdminRegistry->registerLocation("orgs",'KTOrgAdminDispatcher',"principals", "Control Organisations","Specify which organisations are available.", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/orgmanagement/orgManagement.php', null);

// security
$oAdminRegistry->registerLocation("permissions",'ManagePermissionsDispatcher',"security", "Permissions","Create or Delete permissions.", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/permissions/managePermissions.php', null);
$oAdminRegistry->registerLocation("roles",'RoleAdminDispatcher',"security", "Roles","Create or Delete roles (incomplete).", KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/rolemanagement/roleManagement.php', null);

// documents
$oAdminRegistry->registerLocation("typemanagement",'KTDocumentTypeDispatcher','documents', 'Document Types','Manage the different classes of document which can be added to the system.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/doctypemanagement/documentTypes.php', null);
$oAdminRegistry->registerLocation("fieldmanagement",'KTDocumentFieldDispatcher','documents', 'Document Fieldsets','Control which kinds of documents have which sets of information associated with them.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/docfieldmanagement/documentFields.php', null);
$oAdminRegistry->registerLocation("linkmanagement",'KTDocLinkAdminDispatcher','documents', 'Link Type Management','Specify the different "link types" - ways to relate different documents togeter.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/doclinkmanagement/documentLinks.php', null);
$oAdminRegistry->registerLocation("workflows",'KTWorkflowDispatcher','documents', 'Workflows','Configure the process documents go through..', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/workflow/workflows.php', null);

// storage
$oAdminRegistry->registerLocation("checkout",'KTCheckoutAdminDispatcher','storage', 'Checked Out Document Control','Override the checked-out status of documents if a user has failed to do so.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/doccheckoutmanagement/documentCheckout.php', null);
$oAdminRegistry->registerLocation("archived",'ArchivedDocumentsDispatcher','storage', 'Archived Document Restoration','Restore old (archived) documents, usually at a user\'s request.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/archivedDocuments.php', null);
$oAdminRegistry->registerLocation("expunge",'DeletedDocumentsDispatcher','storage', 'Expunge Deleted Documents','Permanently expunge deleted documents.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/documentmanagement/deletedDocuments.php', null);

// misc
$oAdminRegistry->registerLocation("helpmanagement",'ManageHelpDispatcher','misc', 'Edit Help files','Change the help files that are displayed to users.', KT_DIR . '/presentation/lookAndFeel/knowledgeTree/administration/help/manageHelp.php', null);



?>
