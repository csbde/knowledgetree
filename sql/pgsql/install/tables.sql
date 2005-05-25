BEGIN;

-- --------------------------------------------------------

-- 
-- Table structure for table `active_sessions`
-- 

CREATE TABLE active_sessions (
  id int NOT NULL DEFAULT '0',
  user_id int DEFAULT NULL,
  session_id char(255) DEFAULT NULL,
  lastused TIMESTAMP DEFAULT NULL,
  ip char(30) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `archive_restoration_request`
-- 

CREATE TABLE archive_restoration_request (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  request_user_id INT4 NOT NULL DEFAULT '0',
  admin_user_id INT4 NOT NULL DEFAULT '0',
  datetime TIMESTAMP NOT NULL DEFAULT '0001-01-01 00:00:00'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `archiving_settings`
-- 

CREATE TABLE archiving_settings (
  id INT4 NOT NULL DEFAULT '0',
  archiving_type_id INT4 NOT NULL DEFAULT '0',
  expiration_date DATE DEFAULT NULL,
  document_transaction_id INT4 DEFAULT NULL,
  time_period_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `archiving_type_lookup`
-- 

CREATE TABLE archiving_type_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `browse_criteria`
-- 

CREATE TABLE browse_criteria (
  id INT4 NOT NULL DEFAULT '0',
  criteria_id INT4 NOT NULL DEFAULT '0',
  precedence INT4 NOT NULL DEFAULT '0',
  PRIMARY KEY (id)

);

-- --------------------------------------------------------

-- 
-- Table structure for table `data_types`
-- 

CREATE TABLE data_types (
  id INT4 NOT NULL DEFAULT '0',
  name char(255) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `dependant_document_instance`
-- 

CREATE TABLE dependant_document_instance (
  id INT4 NOT NULL DEFAULT '0',
  document_title TEXT DEFAULT '' NOT NULL,
  user_id INT4 NOT NULL DEFAULT '0',
  template_document_id INT4 DEFAULT NULL,
  parent_document_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `dependant_document_template`
-- 

CREATE TABLE dependant_document_template (
  id INT4 NOT NULL DEFAULT '0',
  document_title TEXT DEFAULT '' NOT NULL,
  default_user_id INT4 NOT NULL DEFAULT '0',
  template_document_id INT4 DEFAULT NULL,
  group_folder_approval_link_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `discussion_comments`
-- 

CREATE TABLE discussion_comments (
  id INT4 NOT NULL DEFAULT '0',
  thread_id INT4 NOT NULL DEFAULT '0',
  in_reply_to INT4 DEFAULT NULL,
  user_id INT4 NOT NULL DEFAULT '0',
  subject text,
  body text,
  DATE TIMESTAMP DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `discussion_threads`
-- 

CREATE TABLE discussion_threads (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  first_comment_id INT4 NOT NULL DEFAULT '0',
  last_comment_id INT4 NOT NULL DEFAULT '0',
  views INT4 NOT NULL DEFAULT '0',
  replies INT4 NOT NULL DEFAULT '0',
  creator_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_archiving_link`
-- 

CREATE TABLE document_archiving_link (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  archiving_settings_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_fields`
-- 

CREATE TABLE document_fields (
  id INT4 NOT NULL DEFAULT '0',
  name char(255) NOT NULL DEFAULT '',
  data_type char(100) NOT NULL DEFAULT '',
  is_generic boolean DEFAULT NULL,
  has_lookup boolean DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_fields_link`
-- 

CREATE TABLE document_fields_link (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  document_field_id INT4 NOT NULL DEFAULT '0',
  value char(255) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_link`
-- 

CREATE TABLE document_link (
  id INT4 NOT NULL DEFAULT '0',
  parent_document_id INT4 NOT NULL DEFAULT '0',
  child_document_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_subscriptions`
-- 

CREATE TABLE document_subscriptions (
  id INT4 NOT NULL DEFAULT '0',
  user_id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  is_alerted boolean DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_text`
-- 

CREATE TABLE document_text (
  document_id INT4 DEFAULT NULL,
  document_text text
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_transaction_types_lookup`
-- 

CREATE TABLE document_transaction_types_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_transactions`
-- 

CREATE TABLE document_transactions (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  version char(50) DEFAULT NULL,
  user_id INT4 NOT NULL DEFAULT '0',
  datetime TIMESTAMP NOT NULL DEFAULT '0001-01-01 00:00:00',
  ip char(30) DEFAULT NULL,
  filename char(255) NOT NULL DEFAULT '',
  comment char(255) NOT NULL DEFAULT '',
  transaction_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_type_fields_link`
-- 

CREATE TABLE document_type_fields_link (
  id INT4 NOT NULL DEFAULT '0',
  document_type_id INT4 NOT NULL DEFAULT '0',
  field_id INT4 NOT NULL DEFAULT '0',
  is_mandatory boolean NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `document_types_lookup`
-- 

CREATE TABLE document_types_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `documents`
-- 

CREATE TABLE documents (
  id INT4 NOT NULL DEFAULT '0',
  document_type_id INT4 NOT NULL DEFAULT '0',
  name TEXT DEFAULT '' NOT NULL,
  filename TEXT DEFAULT '' NOT NULL,
  size INT8 NOT NULL DEFAULT '0',
  creator_id INT4 NOT NULL DEFAULT '0',
  modified TIMESTAMP NOT NULL DEFAULT '0001-01-01 00:00:00',
  description varchar(200) NOT NULL DEFAULT '',
  security INT4 NOT NULL DEFAULT '0',
  mime_id INT4 NOT NULL DEFAULT '0',
  folder_id INT4 NOT NULL DEFAULT '0',
  major_version INT4 NOT NULL DEFAULT '0',
  minor_version INT4 NOT NULL DEFAULT '0',
  is_checked_out boolean NOT NULL DEFAULT '0',
  parent_folder_ids text,
  full_path text,
  checked_out_user_id INT4 DEFAULT NULL,
  status_id INT4 DEFAULT NULL,
  created TIMESTAMP NOT NULL DEFAULT '0001-01-01 00:00:00'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `folder_doctypes_link`
-- 

CREATE TABLE folder_doctypes_link (
  id INT4 NOT NULL DEFAULT '0',
  folder_id INT4 NOT NULL DEFAULT '0',
  document_type_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `folder_subscriptions`
-- 

CREATE TABLE folder_subscriptions (
  id INT4 NOT NULL DEFAULT '0',
  user_id INT4 NOT NULL DEFAULT '0',
  folder_id INT4 NOT NULL DEFAULT '0',
  is_alerted boolean DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `folders`
-- 

CREATE TABLE folders (
  id INT4 NOT NULL DEFAULT '0',
  name varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  parent_id INT4 DEFAULT NULL,
  creator_id INT4 DEFAULT NULL,
  unit_id INT4 DEFAULT NULL,
  is_public boolean NOT NULL DEFAULT '0',
  parent_folder_ids text,
  full_path text,
  inherit_parent_folder_permission INT4 DEFAULT NULL,
  permission_folder_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `folders_users_roles_link`
-- 

CREATE TABLE folders_users_roles_link (
  id INT4 NOT NULL DEFAULT '0',
  group_folder_approval_id INT4 NOT NULL DEFAULT '0',
  user_id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  datetime TIMESTAMP DEFAULT NULL,
  done boolean DEFAULT NULL,
  active boolean DEFAULT NULL,
  dependant_documents_created boolean DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_folders_approval_link`
-- 

CREATE TABLE groups_folders_approval_link (
  id INT4 NOT NULL DEFAULT '0',
  folder_id INT4 NOT NULL DEFAULT '0',
  group_id INT4 NOT NULL DEFAULT '0',
  precedence INT4 NOT NULL DEFAULT '0',
  role_id INT4 NOT NULL DEFAULT '0',
  user_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_folders_link`
-- 

CREATE TABLE groups_folders_link (
  id INT4 NOT NULL DEFAULT '0',
  group_id INT4 NOT NULL DEFAULT '0',
  folder_id INT4 NOT NULL DEFAULT '0',
  can_read boolean NOT NULL DEFAULT '0',
  can_write boolean NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_lookup`
-- 

CREATE TABLE groups_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) NOT NULL DEFAULT '',
  is_sys_admin boolean NOT NULL DEFAULT '0',
  is_unit_admin boolean NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `groups_units_link`
-- 

CREATE TABLE groups_units_link (
  id INT4 NOT NULL DEFAULT '0',
  group_id INT4 NOT NULL DEFAULT '0',
  unit_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `help`
-- 

CREATE TABLE help (
  id INT4 NOT NULL DEFAULT '0',
  fSection varchar(100) NOT NULL DEFAULT '',
  help_info TEXT DEFAULT '' NOT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `links`
-- 

CREATE TABLE links (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) NOT NULL DEFAULT '',
  url char(100) NOT NULL DEFAULT '',
  rank INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `metadata_lookup`
-- 

CREATE TABLE metadata_lookup (
  id INT4 NOT NULL DEFAULT '0',
  document_field_id INT4 NOT NULL DEFAULT '0',
  name char(255) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `mime_types`
-- 

CREATE TABLE mime_types (
  id INT4 NOT NULL DEFAULT '0',
  filetypes char(100) NOT NULL DEFAULT '',
  mimetypes char(100) NOT NULL DEFAULT '',
  icon_path char(255) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `news`
-- 

CREATE TABLE news (
  id INT4 NOT NULL DEFAULT '0',
  synopsis varchar(255) NOT NULL DEFAULT '',
  body text,
  rank INT4 DEFAULT NULL,
  image text,
  image_size INT4 DEFAULT NULL,
  image_mime_type_id INT4 DEFAULT NULL,
  active boolean DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `organisations_lookup`
-- 

CREATE TABLE organisations_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `roles`
-- 

CREATE TABLE roles (
  id INT4 NOT NULL DEFAULT '0',
  name char(255) NOT NULL DEFAULT '',
  active boolean NOT NULL DEFAULT '0',
  can_read boolean NOT NULL DEFAULT '0',
  can_write boolean NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `search_document_user_link`
-- 

CREATE TABLE search_document_user_link (
  document_id INT4 DEFAULT NULL,
  user_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `status_lookup`
-- 

CREATE TABLE status_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(255) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `system_settings`
-- 

CREATE TABLE system_settings (
  id INT4 NOT NULL DEFAULT '0',
  name char(255) NOT NULL DEFAULT '',
  value char(255) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `time_period`
-- 

CREATE TABLE time_period (
  id INT4 NOT NULL DEFAULT '0',
  time_unit_id INT4 DEFAULT NULL,
  units INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `time_unit_lookup`
-- 

CREATE TABLE time_unit_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `units_lookup`
-- 

CREATE TABLE units_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(100) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `units_organisations_link`
-- 

CREATE TABLE units_organisations_link (
  id INT4 NOT NULL DEFAULT '0',
  unit_id INT4 NOT NULL DEFAULT '0',
  organisation_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE users (
  id INT4 NOT NULL DEFAULT '0',
  username char(255) NOT NULL DEFAULT '',
  name char(255) NOT NULL DEFAULT '',
  password char(255) NOT NULL DEFAULT '',
  quota_max INT4 NOT NULL DEFAULT '0',
  quota_current INT4 NOT NULL DEFAULT '0',
  email char(255) DEFAULT NULL,
  mobile char(255) DEFAULT NULL,
  email_notification boolean NOT NULL DEFAULT '0',
  sms_notification boolean NOT NULL DEFAULT '0',
  ldap_dn char(255) DEFAULT NULL,
  max_sessions INT4 DEFAULT NULL,
  language_id INT4 DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Table structure for table `users_groups_link`
-- 

CREATE TABLE users_groups_link (
  id INT4 NOT NULL DEFAULT '0',
  user_id INT4 NOT NULL DEFAULT '0',
  group_id INT4 NOT NULL DEFAULT '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `web_documents`
-- 

CREATE TABLE web_documents (
  id INT4 NOT NULL DEFAULT '0',
  document_id INT4 NOT NULL DEFAULT '0',
  web_site_id INT4 NOT NULL DEFAULT '0',
  unit_id INT4 NOT NULL DEFAULT '0',
  status_id INT4 NOT NULL DEFAULT '0',
  datetime TIMESTAMP NOT NULL DEFAULT '0001-01-01 00:00:00'
);

-- --------------------------------------------------------

-- 
-- Table structure for table `web_documents_status_lookup`
-- 

CREATE TABLE web_documents_status_lookup (
  id INT4 NOT NULL DEFAULT '0',
  name char(50) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

-- 
-- Table structure for table `web_sites`
-- 

CREATE TABLE web_sites (
  id INT4 NOT NULL DEFAULT '0',
  web_site_name char(100) NOT NULL DEFAULT '',
  web_site_url char(50) NOT NULL DEFAULT '',
  web_master_id INT4 NOT NULL DEFAULT '0'
);

-- 
-- Dumping data for table `active_sessions`
-- 


-- 
-- Dumping data for table `archive_restoration_request`
-- 


-- 
-- Dumping data for table `archiving_settings`
-- 

-- 
-- Dumping data for table `browse_criteria`
-- 

INSERT INTO browse_criteria VALUES (1, -1, 0);
INSERT INTO browse_criteria VALUES (2, -2, 1);
INSERT INTO browse_criteria VALUES (3, -3, 2);
INSERT INTO browse_criteria VALUES (4, -4, 3);
INSERT INTO browse_criteria VALUES (5, -5, 4);

-- 
-- Dumping data for table `archiving_type_lookup`
-- 

INSERT INTO archiving_type_lookup VALUES (1, 'Date');
INSERT INTO archiving_type_lookup VALUES (2, 'Utilisation');

-- 
-- Dumping data for table `data_types`
-- 

INSERT INTO data_types VALUES (1, 'STRING');
INSERT INTO data_types VALUES (2, 'CHAR');
INSERT INTO data_types VALUES (3, 'TEXT');
INSERT INTO data_types VALUES (4, 'INT');
INSERT INTO data_types VALUES (5, 'FLOAT');

-- 
-- Dumping data for table `dependant_document_instance`
-- 


-- 
-- Dumping data for table `dependant_document_template`
-- 


-- 
-- Dumping data for table `discussion_comments`
-- 


-- 
-- Dumping data for table `discussion_threads`
-- 


-- 
-- Dumping data for table `document_archiving_link`
-- 


-- 
-- Dumping data for table `document_fields`
-- 

INSERT INTO document_fields VALUES (1, 'Category', 'STRING', TRUE, NULL);

-- 
-- Dumping data for table `document_fields_link`
-- 


-- 
-- Dumping data for table `document_link`
-- 


-- 
-- Dumping data for table `document_subscriptions`
-- 


-- 
-- Dumping data for table `document_text`
-- 


-- 
-- Dumping data for table `document_transaction_types_lookup`
-- 

INSERT INTO document_transaction_types_lookup VALUES (1, 'Create');
INSERT INTO document_transaction_types_lookup VALUES (2, 'Update');
INSERT INTO document_transaction_types_lookup VALUES (3, 'Delete');
INSERT INTO document_transaction_types_lookup VALUES (4, 'Rename');
INSERT INTO document_transaction_types_lookup VALUES (5, 'Move');
INSERT INTO document_transaction_types_lookup VALUES (6, 'Download');
INSERT INTO document_transaction_types_lookup VALUES (7, 'Check In');
INSERT INTO document_transaction_types_lookup VALUES (8, 'Check Out');
INSERT INTO document_transaction_types_lookup VALUES (9, 'Collaboration Step Rollback');
INSERT INTO document_transaction_types_lookup VALUES (10, 'View');
INSERT INTO document_transaction_types_lookup VALUES (11, 'Expunge');
INSERT INTO document_transaction_types_lookup VALUES (12, 'Force CheckIn');
INSERT INTO document_transaction_types_lookup VALUES (13, 'Email Link');
INSERT INTO document_transaction_types_lookup VALUES (14, 'Collaboration Step Approve');

-- 
-- Dumping data for table `document_transactions`
-- 


-- 
-- Dumping data for table `document_type_fields_link`
-- 


-- 
-- Dumping data for table `document_types_lookup`
-- 

INSERT INTO document_types_lookup VALUES (1, 'Default');

-- 
-- Dumping data for table `documents`
-- 


-- 
-- Dumping data for table `folder_doctypes_link`
-- 

INSERT INTO folder_doctypes_link VALUES (1, 2, 1);

-- 
-- Dumping data for table `folder_subscriptions`
-- 


-- 
-- Dumping data for table `folders`
-- 

INSERT INTO folders VALUES (1, 'Root Folder', 'Root Document Folder', 0, 1, 0, FALSE, NULL, NULL, NULL, 1);
INSERT INTO folders VALUES (2, 'Default Unit', 'Default Unit Root Folder', 1, 1, 1, FALSE, '1', 'Root Folder', NULL, 1);

-- 
-- Dumping data for table `folders_users_roles_link`
-- 


-- 
-- Dumping data for table `groups_folders_approval_link`
-- 


-- 
-- Dumping data for table `groups_folders_link`
-- 


-- 
-- Dumping data for table `groups_lookup`
-- 

INSERT INTO groups_lookup VALUES (1, 'System Administrators', TRUE, FALSE);
INSERT INTO groups_lookup VALUES (2, 'Unit Administrators', FALSE, TRUE);
INSERT INTO groups_lookup VALUES (3, 'Anonymous', FALSE, FALSE);

-- 
-- Dumping data for table `groups_units_link`
-- 

INSERT INTO groups_units_link VALUES (1, 2, 1);

-- 
-- Dumping data for table `help`
-- 

INSERT INTO help VALUES (1, 'browse', 'dochelp.html');
INSERT INTO help VALUES (2, 'dashboard', 'dashboardHelp.html');
INSERT INTO help VALUES (3, 'addFolder', 'addFolderHelp.html');
INSERT INTO help VALUES (4, 'editFolder', 'editFolderHelp.html');
INSERT INTO help VALUES (5, 'addFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO help VALUES (6, 'modifyFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO help VALUES (7, 'addDocument', 'addDocumentHelp.html');
INSERT INTO help VALUES (8, 'viewDocument', 'viewDocumentHelp.html');
INSERT INTO help VALUES (9, 'modifyDocument', 'modifyDocumentHelp.html');
INSERT INTO help VALUES (10, 'modifyDocumentRouting', 'modifyDocumentRoutingHelp.html');
INSERT INTO help VALUES (11, 'emailDocument', 'emailDocumentHelp.html');
INSERT INTO help VALUES (12, 'deleteDocument', 'deleteDocumentHelp.html');
INSERT INTO help VALUES (13, 'administration', 'administrationHelp.html');
INSERT INTO help VALUES (14, 'addGroup', 'addGroupHelp.html');
INSERT INTO help VALUES (15, 'editGroup', 'editGroupHelp.html');
INSERT INTO help VALUES (16, 'removeGroup', 'removeGroupHelp.html');
INSERT INTO help VALUES (17, 'assignGroupToUnit', 'assignGroupToUnitHelp.html');
INSERT INTO help VALUES (18, 'removeGroupFromUnit', 'removeGroupFromUnitHelp.html');
INSERT INTO help VALUES (19, 'addUnit', 'addUnitHelp.html');
INSERT INTO help VALUES (20, 'editUnit', 'editUnitHelp.html');
INSERT INTO help VALUES (21, 'removeUnit', 'removeUnitHelp.html');
INSERT INTO help VALUES (22, 'addOrg', 'addOrgHelp.html');
INSERT INTO help VALUES (23, 'editOrg', 'editOrgHelp.html');
INSERT INTO help VALUES (24, 'removeOrg', 'removeOrgHelp.html');
INSERT INTO help VALUES (25, 'addRole', 'addRoleHelp.html');
INSERT INTO help VALUES (26, 'editRole', 'editRoleHelp.html');
INSERT INTO help VALUES (27, 'removeRole', 'removeRoleHelp.html');
INSERT INTO help VALUES (28, 'addLink', 'addLinkHelp.html');
INSERT INTO help VALUES (29, 'addLinkSuccess', 'addLinkHelp.html');
INSERT INTO help VALUES (30, 'editLink', 'editLinkHelp.html');
INSERT INTO help VALUES (31, 'removeLink', 'removeLinkHelp.html');
INSERT INTO help VALUES (32, 'systemAdministration', 'systemAdministrationHelp.html');
INSERT INTO help VALUES (33, 'deleteFolder', 'deleteFolderHelp.html');
INSERT INTO help VALUES (34, 'editDocType', 'editDocTypeHelp.html');
INSERT INTO help VALUES (35, 'removeDocType', 'removeDocTypeHelp.html');
INSERT INTO help VALUES (36, 'addDocType', 'addDocTypeHelp.html');
INSERT INTO help VALUES (37, 'addDocTypeSuccess', 'addDocTypeHelp.html');
INSERT INTO help VALUES (38, 'manageSubscriptions', 'manageSubscriptionsHelp.html');
INSERT INTO help VALUES (39, 'addSubscription', 'addSubscriptionHelp.html');
INSERT INTO help VALUES (40, 'removeSubscription', 'removeSubscriptionHelp.html');
INSERT INTO help VALUES (41, 'preferences', 'preferencesHelp.html');
INSERT INTO help VALUES (42, 'editPrefsSuccess', 'preferencesHelp.html');
INSERT INTO help VALUES (43, 'modifyDocumentGenericMetaData', 'modifyDocumentGenericMetaDataHelp.html');
INSERT INTO help VALUES (44, 'viewHistory', 'viewHistoryHelp.html');
INSERT INTO help VALUES (45, 'checkInDocument', 'checkInDocumentHelp.html');
INSERT INTO help VALUES (46, 'checkOutDocument', 'checkOutDocumentHelp.html');
INSERT INTO help VALUES (47, 'advancedSearch', 'advancedSearchHelp.html');
INSERT INTO help VALUES (48, 'deleteFolderCollaboration', 'deleteFolderCollaborationHelp.html');
INSERT INTO help VALUES (49, 'addFolderDocType', 'addFolderDocTypeHelp.html');
INSERT INTO help VALUES (50, 'deleteFolderDocType', 'deleteFolderDocTypeHelp.html');
INSERT INTO help VALUES (51, 'addGroupFolderLink', 'addGroupFolderLinkHelp.html');
INSERT INTO help VALUES (52, 'deleteGroupFolderLink', 'deleteGroupFolderLinkHelp.html');
INSERT INTO help VALUES (53, 'addWebsite', 'addWebsiteHelp.html');
INSERT INTO help VALUES (54, 'addWebsiteSuccess', 'addWebsiteHelp.html');
INSERT INTO help VALUES (55, 'editWebsite', 'editWebsiteHelp.html');
INSERT INTO help VALUES (56, 'removeWebSite', 'removeWebSiteHelp.html');
INSERT INTO help VALUES (57, 'standardSearch', 'standardSearchHelp.html');
INSERT INTO help VALUES (58, 'modifyDocumentTypeMetaData', 'modifyDocumentTypeMetaDataHelp.html');
INSERT INTO help VALUES (59, 'addDocField', 'addDocFieldHelp.html');
INSERT INTO help VALUES (60, 'editDocField', 'editDocFieldHelp.html');
INSERT INTO help VALUES (61, 'removeDocField', 'removeDocFieldHelp.html');
INSERT INTO help VALUES (62, 'addMetaData', 'addMetaDataHelp.html');
INSERT INTO help VALUES (63, 'editMetaData', 'editMetaDataHelp.html');
INSERT INTO help VALUES (64, 'removeMetaData', 'removeMetaDataHelp.html');
INSERT INTO help VALUES (65, 'addUser', 'addUserHelp.html');
INSERT INTO help VALUES (66, 'editUser', 'editUserHelp.html');
INSERT INTO help VALUES (67, 'removeUser', 'removeUserHelp.html');
INSERT INTO help VALUES (68, 'addUserToGroup', 'addUserToGroupHelp.html');
INSERT INTO help VALUES (69, 'removeUserFromGroup', 'removeUserFromGroupHelp.html');
INSERT INTO help VALUES (70, 'viewDiscussion', 'viewDiscussionThread.html');
INSERT INTO help VALUES (71, 'addComment', 'addDiscussionComment.html');
INSERT INTO help VALUES (72, 'listNews', 'listDashboardNewsHelp.html');
INSERT INTO help VALUES (73, 'editNews', 'editDashboardNewsHelp.html');
INSERT INTO help VALUES (74, 'previewNews', 'previewDashboardNewsHelp.html');
INSERT INTO help VALUES (75, 'addNews', 'addDashboardNewsHelp.html');
INSERT INTO help VALUES (76, 'modifyDocumentArchiveSettings', 'modifyDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (77, 'addDocumentArchiveSettings', 'addDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (78, 'listDocFields', 'listDocumentFieldsAdmin.html');
INSERT INTO help VALUES (79, 'editDocFieldLookups', 'editDocFieldLookups.html');
INSERT INTO help VALUES (80, 'addMetaDataForField', 'addMetaDataForField.html');
INSERT INTO help VALUES (81, 'editMetaDataForField', 'editMetaDataForField.html');
INSERT INTO help VALUES (82, 'removeMetaDataFromField', 'removeMetaDataFromField.html');
INSERT INTO help VALUES (83, 'listDocs', 'listDocumentsCheckoutHelp.html');
INSERT INTO help VALUES (84, 'editDocCheckout', 'editDocCheckoutHelp.html');
INSERT INTO help VALUES (85, 'listDocTypes', 'listDocTypesHelp.html');
INSERT INTO help VALUES (86, 'editDocTypeFields', 'editDocFieldHelp.html');
INSERT INTO help VALUES (87, 'addDocTypeFieldsLink', 'addDocTypeFieldHelp.html');
INSERT INTO help VALUES (88, 'listGroups', 'listGroupsHelp.html');
INSERT INTO help VALUES (89, 'editGroupUnit', 'editGroupUnitHelp.html');
INSERT INTO help VALUES (90, 'listOrg', 'listOrgHelp.html');
INSERT INTO help VALUES (91, 'listRole', 'listRolesHelp.html');
INSERT INTO help VALUES (92, 'listUnits', 'listUnitHelp.html');
INSERT INTO help VALUES (93, 'editUnitOrg', 'editUnitOrgHelp.html');
INSERT INTO help VALUES (94, 'removeUnitFromOrg', 'removeUnitFromOrgHelp.html');
INSERT INTO help VALUES (95, 'addUnitToOrg', 'addUnitToOrgHelp.html');
INSERT INTO help VALUES (96, 'listUsers', 'listUsersHelp.html');
INSERT INTO help VALUES (97, 'editUserGroups', 'editUserGroupsHelp.html');
INSERT INTO help VALUES (98, 'listWebsites', 'listWebsitesHelp.html');

-- 
-- Dumping data for table `links`
-- 


-- 
-- Dumping data for table `metadata_lookup`
-- 


-- 
-- Dumping data for table `mime_types`
-- 

INSERT INTO mime_types VALUES (1, 'ai', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (2, 'aif', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (3, 'aifc', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (4, 'aiff', 'audio/x-aiff', NULL);
INSERT INTO mime_types VALUES (5, 'asc', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types VALUES (6, 'au', 'audio/basic', NULL);
INSERT INTO mime_types VALUES (7, 'avi', 'video/x-msvideo', NULL);
INSERT INTO mime_types VALUES (8, 'bcpio', 'application/x-bcpio', NULL);
INSERT INTO mime_types VALUES (9, 'bin', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (10, 'bmp', 'image/bmp', 'icons/bmp.gif');
INSERT INTO mime_types VALUES (11, 'cdf', 'application/x-netcdf', NULL);
INSERT INTO mime_types VALUES (12, 'class', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (13, 'cpio', 'application/x-cpio', NULL);
INSERT INTO mime_types VALUES (14, 'cpt', 'application/mac-compactpro', NULL);
INSERT INTO mime_types VALUES (15, 'csh', 'application/x-csh', NULL);
INSERT INTO mime_types VALUES (16, 'css', 'text/css', NULL);
INSERT INTO mime_types VALUES (17, 'dcr', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (18, 'dir', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (19, 'dms', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (20, 'doc', 'application/msword', 'icons/word.gif');
INSERT INTO mime_types VALUES (21, 'dvi', 'application/x-dvi', NULL);
INSERT INTO mime_types VALUES (22, 'dxr', 'application/x-director', NULL);
INSERT INTO mime_types VALUES (23, 'eps', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (24, 'etx', 'text/x-setext', NULL);
INSERT INTO mime_types VALUES (25, 'exe', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (26, 'ez', 'application/andrew-inset', NULL);
INSERT INTO mime_types VALUES (27, 'gif', 'image/gif', 'icons/gif.gif');
INSERT INTO mime_types VALUES (28, 'gtar', 'application/x-gtar', NULL);
INSERT INTO mime_types VALUES (29, 'hdf', 'application/x-hdf', NULL);
INSERT INTO mime_types VALUES (30, 'hqx', 'application/mac-binhex40', NULL);
INSERT INTO mime_types VALUES (31, 'htm', 'text/html', 'icons/html.gif');
INSERT INTO mime_types VALUES (32, 'html', 'text/html', 'icons/html.gif');
INSERT INTO mime_types VALUES (33, 'ice', 'x-conference/x-cooltalk', NULL);
INSERT INTO mime_types VALUES (34, 'ief', 'image/ief', NULL);
INSERT INTO mime_types VALUES (35, 'iges', 'model/iges', NULL);
INSERT INTO mime_types VALUES (36, 'igs', 'model/iges', NULL);
INSERT INTO mime_types VALUES (37, 'jpe', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (38, 'jpeg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (39, 'jpg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types VALUES (40, 'js', 'application/x-javascript', NULL);
INSERT INTO mime_types VALUES (41, 'kar', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (42, 'latex', 'application/x-latex', NULL);
INSERT INTO mime_types VALUES (43, 'lha', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (44, 'lzh', 'application/octet-stream', NULL);
INSERT INTO mime_types VALUES (45, 'man', 'application/x-troff-man', NULL);
INSERT INTO mime_types VALUES (46, 'mdb', 'application/access', 'icons/access.gif');
INSERT INTO mime_types VALUES (47, 'mdf', 'application/access', 'icons/access.gif');
INSERT INTO mime_types VALUES (48, 'me', 'application/x-troff-me', NULL);
INSERT INTO mime_types VALUES (49, 'mesh', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (50, 'mid', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (51, 'midi', 'audio/midi', NULL);
INSERT INTO mime_types VALUES (52, 'mif', 'application/vnd.mif', NULL);
INSERT INTO mime_types VALUES (53, 'mov', 'video/quicktime', NULL);
INSERT INTO mime_types VALUES (54, 'movie', 'video/x-sgi-movie', NULL);
INSERT INTO mime_types VALUES (55, 'mp2', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (56, 'mp3', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (57, 'mpe', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (58, 'mpeg', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (59, 'mpg', 'video/mpeg', NULL);
INSERT INTO mime_types VALUES (60, 'mpga', 'audio/mpeg', NULL);
INSERT INTO mime_types VALUES (61, 'mpp', 'application/vnd.ms-project', 'icons/project.gif');
INSERT INTO mime_types VALUES (62, 'ms', 'application/x-troff-ms', NULL);
INSERT INTO mime_types VALUES (63, 'msh', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (64, 'nc', 'application/x-netcdf', NULL);
INSERT INTO mime_types VALUES (65, 'oda', 'application/oda', NULL);
INSERT INTO mime_types VALUES (66, 'pbm', 'image/x-portable-bitmap', NULL);
INSERT INTO mime_types VALUES (67, 'pdb', 'chemical/x-pdb', NULL);
INSERT INTO mime_types VALUES (68, 'pdf', 'application/pdf', 'icons/pdf.gif');
INSERT INTO mime_types VALUES (69, 'pgm', 'image/x-portable-graymap', NULL);
INSERT INTO mime_types VALUES (70, 'pgn', 'application/x-chess-pgn', NULL);
INSERT INTO mime_types VALUES (71, 'png', 'image/png', NULL);
INSERT INTO mime_types VALUES (72, 'pnm', 'image/x-portable-anymap', NULL);
INSERT INTO mime_types VALUES (73, 'ppm', 'image/x-portable-pixmap', NULL);
INSERT INTO mime_types VALUES (74, 'ppt', 'application/vnd.ms-powerpoint', 'icons/powerp.gif');
INSERT INTO mime_types VALUES (75, 'ps', 'application/postscript', NULL);
INSERT INTO mime_types VALUES (76, 'qt', 'video/quicktime', NULL);
INSERT INTO mime_types VALUES (77, 'ra', 'audio/x-realaudio', NULL);
INSERT INTO mime_types VALUES (78, 'ram', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types VALUES (79, 'ras', 'image/x-cmu-raster', NULL);
INSERT INTO mime_types VALUES (80, 'rgb', 'image/x-rgb', NULL);
INSERT INTO mime_types VALUES (81, 'rm', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types VALUES (82, 'roff', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (83, 'rpm', 'audio/x-pn-realaudio-plugin', NULL);
INSERT INTO mime_types VALUES (84, 'rtf', 'text/rtf', NULL);
INSERT INTO mime_types VALUES (85, 'rtx', 'text/richtext', NULL);
INSERT INTO mime_types VALUES (86, 'sgm', 'text/sgml', NULL);
INSERT INTO mime_types VALUES (87, 'sgml', 'text/sgml', NULL);
INSERT INTO mime_types VALUES (88, 'sh', 'application/x-sh', NULL);
INSERT INTO mime_types VALUES (89, 'shar', 'application/x-shar', NULL);
INSERT INTO mime_types VALUES (90, 'silo', 'model/mesh', NULL);
INSERT INTO mime_types VALUES (91, 'sit', 'application/x-stuffit', NULL);
INSERT INTO mime_types VALUES (92, 'skd', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (93, 'skm', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (94, 'skp', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (95, 'skt', 'application/x-koan', NULL);
INSERT INTO mime_types VALUES (96, 'smi', 'application/smil', NULL);
INSERT INTO mime_types VALUES (97, 'smil', 'application/smil', NULL);
INSERT INTO mime_types VALUES (98, 'snd', 'audio/basic', NULL);
INSERT INTO mime_types VALUES (99, 'spl', 'application/x-futuresplash', NULL);
INSERT INTO mime_types VALUES (100, 'src', 'application/x-wais-source', NULL);
INSERT INTO mime_types VALUES (101, 'sv4cpio', 'application/x-sv4cpio', NULL);
INSERT INTO mime_types VALUES (102, 'sv4crc', 'application/x-sv4crc', NULL);
INSERT INTO mime_types VALUES (103, 'swf', 'application/x-shockwave-flash', NULL);
INSERT INTO mime_types VALUES (104, 't', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (105, 'tar', 'application/x-tar', NULL);
INSERT INTO mime_types VALUES (106, 'tcl', 'application/x-tcl', NULL);
INSERT INTO mime_types VALUES (107, 'tex', 'application/x-tex', NULL);
INSERT INTO mime_types VALUES (108, 'texi', 'application/x-texinfo', NULL);
INSERT INTO mime_types VALUES (109, 'texinfo', 'application/x-texinfo', NULL);
INSERT INTO mime_types VALUES (110, 'tif', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types VALUES (111, 'tiff', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types VALUES (112, 'tr', 'application/x-troff', NULL);
INSERT INTO mime_types VALUES (113, 'tsv', 'text/tab-separated-values', NULL);
INSERT INTO mime_types VALUES (114, 'txt', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types VALUES (115, 'ustar', 'application/x-ustar', NULL);
INSERT INTO mime_types VALUES (116, 'vcd', 'application/x-cdlink', NULL);
INSERT INTO mime_types VALUES (117, 'vrml', 'model/vrml', NULL);
INSERT INTO mime_types VALUES (118, 'vsd', 'application/vnd.visio', 'icons/visio.gif');
INSERT INTO mime_types VALUES (119, 'wav', 'audio/x-wav', NULL);
INSERT INTO mime_types VALUES (120, 'wrl', 'model/vrml', NULL);
INSERT INTO mime_types VALUES (121, 'xbm', 'image/x-xbitmap', NULL);
INSERT INTO mime_types VALUES (122, 'xls', 'application/vnd.ms-excel', 'icons/excel.gif');
INSERT INTO mime_types VALUES (123, 'xml', 'text/xml', NULL);
INSERT INTO mime_types VALUES (124, 'xpm', 'image/x-xpixmap', NULL);
INSERT INTO mime_types VALUES (125, 'xwd', 'image/x-xwindowdump', NULL);
INSERT INTO mime_types VALUES (126, 'xyz', 'chemical/x-pdb', NULL);
INSERT INTO mime_types VALUES (127, 'zip', 'application/zip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (128, 'gz', 'application/x-gzip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (129, 'tgz', 'application/x-gzip', 'icons/zip.gif');
INSERT INTO mime_types VALUES (130, 'sxw', 'application/vnd.sun.xml.writer', 'icons/oowriter.gif');
INSERT INTO mime_types VALUES (131, 'stw', 'application/vnd.sun.xml.writer.template', 'icons/oowriter.gif');
INSERT INTO mime_types VALUES (132, 'sxc', 'application/vnd.sun.xml.calc', 'icons/oocalc.gif');
INSERT INTO mime_types VALUES (133, 'stc', 'application/vnd.sun.xml.calc.template', 'icons/oocalc.gif');
INSERT INTO mime_types VALUES (134, 'sxd', 'application/vnd.sun.xml.draw', NULL);
INSERT INTO mime_types VALUES (135, 'std', 'application/vnd.sun.xml.draw.template', NULL);
INSERT INTO mime_types VALUES (136, 'sxi', 'application/vnd.sun.xml.impress', 'icons/ooimpress.gif');
INSERT INTO mime_types VALUES (137, 'sti', 'application/vnd.sun.xml.impress.template', 'icons/ooimpress.gif');
INSERT INTO mime_types VALUES (138, 'sxg', 'application/vnd.sun.xml.writer.global', NULL);
INSERT INTO mime_types VALUES (139, 'sxm', 'application/vnd.sun.xml.math', NULL);

-- 
-- Dumping data for table `news`
-- 


-- 
-- Dumping data for table `organisations_lookup`
-- 

INSERT INTO organisations_lookup VALUES (1, 'Default Organisation');

-- 
-- Dumping data for table `roles`
-- 


-- 
-- Dumping data for table `search_document_user_link`
-- 


-- 
-- Dumping data for table `status_lookup`
-- 

INSERT INTO status_lookup VALUES (1, 'Live');
INSERT INTO status_lookup VALUES (2, 'Published');
INSERT INTO status_lookup VALUES (3, 'Deleted');
INSERT INTO status_lookup VALUES (4, 'Archived');

-- 
-- Dumping data for table `system_settings`
-- 

INSERT INTO system_settings VALUES (1, 'lastIndexUpdate', '0');
INSERT INTO system_settings VALUES (2, 'knowledgeTreeVersion', '2.0.2');

-- 
-- Dumping data for table `time_period`
-- 


-- 
-- Dumping data for table `time_unit_lookup`
-- 

INSERT INTO time_unit_lookup VALUES (1, 'Years');
INSERT INTO time_unit_lookup VALUES (2, 'Months');
INSERT INTO time_unit_lookup VALUES (3, 'Days');

-- 
-- Dumping data for table `units_lookup`
-- 

INSERT INTO units_lookup VALUES (1, 'Default Unit');

-- 
-- Dumping data for table `units_organisations_link`
-- 

INSERT INTO units_organisations_link VALUES (1, 1, 1);

-- 
-- Dumping data for table `users`
-- 

INSERT INTO users VALUES (1, 'admin', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', TRUE, TRUE, '', 1, 1);
INSERT INTO users VALUES (2, 'unitAdmin', 'Unit Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', TRUE, TRUE, '', 1, 1);
INSERT INTO users VALUES (3, 'guest', 'Anonymous', '084e0343a0486ff05530df6c705c8bb4', 0, 0, '', '', FALSE, FALSE, '', 19, 1);

-- 
-- Dumping data for table `users_groups_link`
-- 

INSERT INTO users_groups_link VALUES (1, 1, 1);
INSERT INTO users_groups_link VALUES (2, 2, 2);
INSERT INTO users_groups_link VALUES (3, 3, 3);

-- 
-- Dumping data for table `web_documents`
-- 


-- 
-- Dumping data for table `web_documents_status_lookup`
-- 

INSERT INTO web_documents_status_lookup VALUES (1, 'Pending');
INSERT INTO web_documents_status_lookup VALUES (2, 'Published');
INSERT INTO web_documents_status_lookup VALUES (3, 'Not Published');

-- 
-- Dumping data for table `web_sites`
-- 

CREATE SEQUENCE zseq_active_sessions;
CREATE SEQUENCE zseq_archive_restoration_request;
CREATE SEQUENCE zseq_archiving_settings;
CREATE SEQUENCE zseq_archiving_type_lookup;
CREATE SEQUENCE zseq_browse_criteria;
CREATE SEQUENCE zseq_data_types;
CREATE SEQUENCE zseq_dependant_document_instance;
CREATE SEQUENCE zseq_dependant_document_template;
CREATE SEQUENCE zseq_discussion_comments;
CREATE SEQUENCE zseq_discussion_threads;
CREATE SEQUENCE zseq_document_archiving_link;
CREATE SEQUENCE zseq_document_fields;
CREATE SEQUENCE zseq_document_fields_link;
CREATE SEQUENCE zseq_document_link;
CREATE SEQUENCE zseq_document_subscriptions;
CREATE SEQUENCE zseq_document_text;
CREATE SEQUENCE zseq_document_transaction_types_lookup;
CREATE SEQUENCE zseq_document_transactions;
CREATE SEQUENCE zseq_document_type_fields_link;
CREATE SEQUENCE zseq_document_types_lookup;
CREATE SEQUENCE zseq_documents;
CREATE SEQUENCE zseq_folder_doctypes_link;
CREATE SEQUENCE zseq_folder_subscriptions;
CREATE SEQUENCE zseq_folders;
CREATE SEQUENCE zseq_folders_users_roles_link;
CREATE SEQUENCE zseq_groups_folders_approval_link;
CREATE SEQUENCE zseq_groups_folders_link;
CREATE SEQUENCE zseq_groups_lookup;
CREATE SEQUENCE zseq_groups_units_link;
CREATE SEQUENCE zseq_help;
CREATE SEQUENCE zseq_links;
CREATE SEQUENCE zseq_metadata_lookup;
CREATE SEQUENCE zseq_mime_types;
CREATE SEQUENCE zseq_news;
CREATE SEQUENCE zseq_organisations_lookup;
CREATE SEQUENCE zseq_roles;
CREATE SEQUENCE zseq_search_document_user_link;
CREATE SEQUENCE zseq_status_lookup;
CREATE SEQUENCE zseq_system_settings;
CREATE SEQUENCE zseq_time_period;
CREATE SEQUENCE zseq_time_unit_lookup;
CREATE SEQUENCE zseq_units_lookup;
CREATE SEQUENCE zseq_units_organisations_link;
CREATE SEQUENCE zseq_users;
CREATE SEQUENCE zseq_users_groups_link;
CREATE SEQUENCE zseq_web_documents;
CREATE SEQUENCE zseq_web_documents_status_lookup;
CREATE SEQUENCE zseq_web_sites;

COMMIT;
