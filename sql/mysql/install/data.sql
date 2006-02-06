-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2-Debian-1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 06, 2006 at 12:26 PM
-- Server version: 5.0.18
-- PHP Version: 4.4.2-1

SET FOREIGN_KEY_CHECKS=0;
-- 
-- Database: `ktpristine`
-- 

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
-- Dumping data for table `archiving_type_lookup`
-- 

INSERT INTO `archiving_type_lookup` VALUES (1, 'Date');
INSERT INTO `archiving_type_lookup` VALUES (2, 'Utilisation');

-- 
-- Dumping data for table `authentication_sources`
-- 


-- 
-- Dumping data for table `dashlet_disables`
-- 


-- 
-- Dumping data for table `data_types`
-- 

INSERT INTO `data_types` VALUES (1, 'STRING');
INSERT INTO `data_types` VALUES (2, 'CHAR');
INSERT INTO `data_types` VALUES (3, 'TEXT');
INSERT INTO `data_types` VALUES (4, 'INT');
INSERT INTO `data_types` VALUES (5, 'FLOAT');

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
-- Dumping data for table `document_content_version`
-- 


-- 
-- Dumping data for table `document_fields`
-- 

INSERT INTO `document_fields` VALUES (1, 'Category', 'STRING', 1, 0, 0, 1, 0, 'The category to which the document belongs.');

-- 
-- Dumping data for table `document_fields_link`
-- 


-- 
-- Dumping data for table `document_incomplete`
-- 


-- 
-- Dumping data for table `document_link`
-- 


-- 
-- Dumping data for table `document_link_types`
-- 

INSERT INTO `document_link_types` VALUES (-1, 'depended on', 'was depended on by', 'Depends relationship whereby one documents depends on another''s creation to go through approval');
INSERT INTO `document_link_types` VALUES (0, 'Default', 'Default (reverse)', 'Default link type');

-- 
-- Dumping data for table `document_metadata_version`
-- 


-- 
-- Dumping data for table `document_searchable_text`
-- 


-- 
-- Dumping data for table `document_subscriptions`
-- 


-- 
-- Dumping data for table `document_text`
-- 


-- 
-- Dumping data for table `document_transaction_text`
-- 


-- 
-- Dumping data for table `document_transaction_types_lookup`
-- 

INSERT INTO `document_transaction_types_lookup` VALUES (1, 'Create', 'ktcore.transactions.create');
INSERT INTO `document_transaction_types_lookup` VALUES (2, 'Update', 'ktcore.transactions.update');
INSERT INTO `document_transaction_types_lookup` VALUES (3, 'Delete', 'ktcore.transactions.delete');
INSERT INTO `document_transaction_types_lookup` VALUES (4, 'Rename', 'ktcore.transactions.rename');
INSERT INTO `document_transaction_types_lookup` VALUES (5, 'Move', 'ktcore.transactions.move');
INSERT INTO `document_transaction_types_lookup` VALUES (6, 'Download', 'ktcore.transactions.download');
INSERT INTO `document_transaction_types_lookup` VALUES (7, 'Check In', 'ktcore.transactions.check_in');
INSERT INTO `document_transaction_types_lookup` VALUES (8, 'Check Out', 'ktcore.transactions.check_out');
INSERT INTO `document_transaction_types_lookup` VALUES (9, 'Collaboration Step Rollback', 'ktcore.transactions.collaboration_step_rollback');
INSERT INTO `document_transaction_types_lookup` VALUES (10, 'View', 'ktcore.transactions.view');
INSERT INTO `document_transaction_types_lookup` VALUES (11, 'Expunge', 'ktcore.transactions.expunge');
INSERT INTO `document_transaction_types_lookup` VALUES (12, 'Force CheckIn', 'ktcore.transactions.force_checkin');
INSERT INTO `document_transaction_types_lookup` VALUES (13, 'Email Link', 'ktcore.transactions.email_link');
INSERT INTO `document_transaction_types_lookup` VALUES (14, 'Collaboration Step Approve', 'ktcore.transactions.collaboration_step_approve');
INSERT INTO `document_transaction_types_lookup` VALUES (15, 'Email Attachment', 'ktcore.transactions.email_attachment');
INSERT INTO `document_transaction_types_lookup` VALUES (16, 'Workflow state transition', 'ktcore.transactions.workflow_state_transition');

-- 
-- Dumping data for table `document_transactions`
-- 


-- 
-- Dumping data for table `document_type_fields_link`
-- 


-- 
-- Dumping data for table `document_type_fieldsets_link`
-- 


-- 
-- Dumping data for table `document_types_lookup`
-- 

INSERT INTO `document_types_lookup` VALUES (1, 'Default', 0);

-- 
-- Dumping data for table `documents`
-- 


-- 
-- Dumping data for table `field_behaviour_options`
-- 


-- 
-- Dumping data for table `field_behaviours`
-- 


-- 
-- Dumping data for table `field_orders`
-- 


-- 
-- Dumping data for table `field_value_instances`
-- 


-- 
-- Dumping data for table `fieldsets`
-- 

INSERT INTO `fieldsets` VALUES (1, 'Category', 'local.category', 0, 0, 1, 1, 0, 1, 0, 'Categorisation information for the document. ');

-- 
-- Dumping data for table `folder_doctypes_link`
-- 

INSERT INTO `folder_doctypes_link` VALUES (1, 1, 1);
INSERT INTO `folder_doctypes_link` VALUES (2, 2, 1);

-- 
-- Dumping data for table `folder_subscriptions`
-- 


-- 
-- Dumping data for table `folder_workflow_map`
-- 


-- 
-- Dumping data for table `folders`
-- 

INSERT INTO `folders` VALUES (1, 'Root Folder', 'Root Document Folder', 0, 1, 0, '0', '0', 1, 2, 0);
INSERT INTO `folders` VALUES (2, 'Default Unit', 'Default Unit Root Folder', 1, 1, 0, '1', 'Root Folder', 1, 2, 0);

-- 
-- Dumping data for table `folders_users_roles_link`
-- 


-- 
-- Dumping data for table `groups_groups_link`
-- 


-- 
-- Dumping data for table `groups_lookup`
-- 

INSERT INTO `groups_lookup` VALUES (1, 'System Administrators', 1, 0, NULL, NULL, NULL, NULL);
INSERT INTO `groups_lookup` VALUES (2, 'Unit Administrators', 0, 1, 1, NULL, NULL, NULL);
INSERT INTO `groups_lookup` VALUES (3, 'Anonymous', 0, 0, NULL, NULL, NULL, NULL);

-- 
-- Dumping data for table `help`
-- 

INSERT INTO `help` VALUES (1, 'browse', 'dochelp.html');
INSERT INTO `help` VALUES (2, 'dashboard', 'dashboardHelp.html');
INSERT INTO `help` VALUES (3, 'addFolder', 'addFolderHelp.html');
INSERT INTO `help` VALUES (4, 'editFolder', 'editFolderHelp.html');
INSERT INTO `help` VALUES (5, 'addFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO `help` VALUES (6, 'modifyFolderCollaboration', 'addFolderCollaborationHelp.html');
INSERT INTO `help` VALUES (7, 'addDocument', 'addDocumentHelp.html');
INSERT INTO `help` VALUES (8, 'viewDocument', 'viewDocumentHelp.html');
INSERT INTO `help` VALUES (9, 'modifyDocument', 'modifyDocumentHelp.html');
INSERT INTO `help` VALUES (10, 'modifyDocumentRouting', 'modifyDocumentRoutingHelp.html');
INSERT INTO `help` VALUES (11, 'emailDocument', 'emailDocumentHelp.html');
INSERT INTO `help` VALUES (12, 'deleteDocument', 'deleteDocumentHelp.html');
INSERT INTO `help` VALUES (13, 'administration', 'administrationHelp.html');
INSERT INTO `help` VALUES (14, 'addGroup', 'addGroupHelp.html');
INSERT INTO `help` VALUES (15, 'editGroup', 'editGroupHelp.html');
INSERT INTO `help` VALUES (16, 'removeGroup', 'removeGroupHelp.html');
INSERT INTO `help` VALUES (17, 'assignGroupToUnit', 'assignGroupToUnitHelp.html');
INSERT INTO `help` VALUES (18, 'removeGroupFromUnit', 'removeGroupFromUnitHelp.html');
INSERT INTO `help` VALUES (19, 'addUnit', 'addUnitHelp.html');
INSERT INTO `help` VALUES (20, 'editUnit', 'editUnitHelp.html');
INSERT INTO `help` VALUES (21, 'removeUnit', 'removeUnitHelp.html');
INSERT INTO `help` VALUES (22, 'addOrg', 'addOrgHelp.html');
INSERT INTO `help` VALUES (23, 'editOrg', 'editOrgHelp.html');
INSERT INTO `help` VALUES (24, 'removeOrg', 'removeOrgHelp.html');
INSERT INTO `help` VALUES (25, 'addRole', 'addRoleHelp.html');
INSERT INTO `help` VALUES (26, 'editRole', 'editRoleHelp.html');
INSERT INTO `help` VALUES (27, 'removeRole', 'removeRoleHelp.html');
INSERT INTO `help` VALUES (28, 'addLink', 'addLinkHelp.html');
INSERT INTO `help` VALUES (29, 'addLinkSuccess', 'addLinkHelp.html');
INSERT INTO `help` VALUES (30, 'editLink', 'editLinkHelp.html');
INSERT INTO `help` VALUES (31, 'removeLink', 'removeLinkHelp.html');
INSERT INTO `help` VALUES (32, 'systemAdministration', 'systemAdministrationHelp.html');
INSERT INTO `help` VALUES (33, 'deleteFolder', 'deleteFolderHelp.html');
INSERT INTO `help` VALUES (34, 'editDocType', 'editDocTypeHelp.html');
INSERT INTO `help` VALUES (35, 'removeDocType', 'removeDocTypeHelp.html');
INSERT INTO `help` VALUES (36, 'addDocType', 'addDocTypeHelp.html');
INSERT INTO `help` VALUES (37, 'addDocTypeSuccess', 'addDocTypeHelp.html');
INSERT INTO `help` VALUES (38, 'manageSubscriptions', 'manageSubscriptionsHelp.html');
INSERT INTO `help` VALUES (39, 'addSubscription', 'addSubscriptionHelp.html');
INSERT INTO `help` VALUES (40, 'removeSubscription', 'removeSubscriptionHelp.html');
INSERT INTO `help` VALUES (41, 'preferences', 'preferencesHelp.html');
INSERT INTO `help` VALUES (42, 'editPrefsSuccess', 'preferencesHelp.html');
INSERT INTO `help` VALUES (43, 'modifyDocumentGenericMetaData', 'modifyDocumentGenericMetaDataHelp.html');
INSERT INTO `help` VALUES (44, 'viewHistory', 'viewHistoryHelp.html');
INSERT INTO `help` VALUES (45, 'checkInDocument', 'checkInDocumentHelp.html');
INSERT INTO `help` VALUES (46, 'checkOutDocument', 'checkOutDocumentHelp.html');
INSERT INTO `help` VALUES (47, 'advancedSearch', 'advancedSearchHelp.html');
INSERT INTO `help` VALUES (48, 'deleteFolderCollaboration', 'deleteFolderCollaborationHelp.html');
INSERT INTO `help` VALUES (49, 'addFolderDocType', 'addFolderDocTypeHelp.html');
INSERT INTO `help` VALUES (50, 'deleteFolderDocType', 'deleteFolderDocTypeHelp.html');
INSERT INTO `help` VALUES (51, 'addGroupFolderLink', 'addGroupFolderLinkHelp.html');
INSERT INTO `help` VALUES (52, 'deleteGroupFolderLink', 'deleteGroupFolderLinkHelp.html');
INSERT INTO `help` VALUES (53, 'addWebsite', 'addWebsiteHelp.html');
INSERT INTO `help` VALUES (54, 'addWebsiteSuccess', 'addWebsiteHelp.html');
INSERT INTO `help` VALUES (55, 'editWebsite', 'editWebsiteHelp.html');
INSERT INTO `help` VALUES (56, 'removeWebSite', 'removeWebSiteHelp.html');
INSERT INTO `help` VALUES (57, 'standardSearch', 'standardSearchHelp.html');
INSERT INTO `help` VALUES (58, 'modifyDocumentTypeMetaData', 'modifyDocumentTypeMetaDataHelp.html');
INSERT INTO `help` VALUES (59, 'addDocField', 'addDocFieldHelp.html');
INSERT INTO `help` VALUES (60, 'editDocField', 'editDocFieldHelp.html');
INSERT INTO `help` VALUES (61, 'removeDocField', 'removeDocFieldHelp.html');
INSERT INTO `help` VALUES (62, 'addMetaData', 'addMetaDataHelp.html');
INSERT INTO `help` VALUES (63, 'editMetaData', 'editMetaDataHelp.html');
INSERT INTO `help` VALUES (64, 'removeMetaData', 'removeMetaDataHelp.html');
INSERT INTO `help` VALUES (65, 'addUser', 'addUserHelp.html');
INSERT INTO `help` VALUES (66, 'editUser', 'editUserHelp.html');
INSERT INTO `help` VALUES (67, 'removeUser', 'removeUserHelp.html');
INSERT INTO `help` VALUES (68, 'addUserToGroup', 'addUserToGroupHelp.html');
INSERT INTO `help` VALUES (69, 'removeUserFromGroup', 'removeUserFromGroupHelp.html');
INSERT INTO `help` VALUES (70, 'viewDiscussion', 'viewDiscussionThread.html');
INSERT INTO `help` VALUES (71, 'addComment', 'addDiscussionComment.html');
INSERT INTO `help` VALUES (72, 'listNews', 'listDashboardNewsHelp.html');
INSERT INTO `help` VALUES (73, 'editNews', 'editDashboardNewsHelp.html');
INSERT INTO `help` VALUES (74, 'previewNews', 'previewDashboardNewsHelp.html');
INSERT INTO `help` VALUES (75, 'addNews', 'addDashboardNewsHelp.html');
INSERT INTO `help` VALUES (76, 'modifyDocumentArchiveSettings', 'modifyDocumentArchiveSettingsHelp.html');
INSERT INTO `help` VALUES (77, 'addDocumentArchiveSettings', 'addDocumentArchiveSettingsHelp.html');
INSERT INTO `help` VALUES (78, 'listDocFields', 'listDocumentFieldsAdmin.html');
INSERT INTO `help` VALUES (79, 'editDocFieldLookups', 'editDocFieldLookups.html');
INSERT INTO `help` VALUES (80, 'addMetaDataForField', 'addMetaDataForField.html');
INSERT INTO `help` VALUES (81, 'editMetaDataForField', 'editMetaDataForField.html');
INSERT INTO `help` VALUES (82, 'removeMetaDataFromField', 'removeMetaDataFromField.html');
INSERT INTO `help` VALUES (83, 'listDocs', 'listDocumentsCheckoutHelp.html');
INSERT INTO `help` VALUES (84, 'editDocCheckout', 'editDocCheckoutHelp.html');
INSERT INTO `help` VALUES (85, 'listDocTypes', 'listDocTypesHelp.html');
INSERT INTO `help` VALUES (86, 'editDocTypeFields', 'editDocFieldHelp.html');
INSERT INTO `help` VALUES (87, 'addDocTypeFieldsLink', 'addDocTypeFieldHelp.html');
INSERT INTO `help` VALUES (88, 'listGroups', 'listGroupsHelp.html');
INSERT INTO `help` VALUES (89, 'editGroupUnit', 'editGroupUnitHelp.html');
INSERT INTO `help` VALUES (90, 'listOrg', 'listOrgHelp.html');
INSERT INTO `help` VALUES (91, 'listRole', 'listRolesHelp.html');
INSERT INTO `help` VALUES (92, 'listUnits', 'listUnitHelp.html');
INSERT INTO `help` VALUES (93, 'editUnitOrg', 'editUnitOrgHelp.html');
INSERT INTO `help` VALUES (94, 'removeUnitFromOrg', 'removeUnitFromOrgHelp.html');
INSERT INTO `help` VALUES (95, 'addUnitToOrg', 'addUnitToOrgHelp.html');
INSERT INTO `help` VALUES (96, 'listUsers', 'listUsersHelp.html');
INSERT INTO `help` VALUES (97, 'editUserGroups', 'editUserGroupsHelp.html');
INSERT INTO `help` VALUES (98, 'listWebsites', 'listWebsitesHelp.html');

-- 
-- Dumping data for table `help_replacement`
-- 


-- 
-- Dumping data for table `links`
-- 


-- 
-- Dumping data for table `metadata_lookup`
-- 


-- 
-- Dumping data for table `metadata_lookup_tree`
-- 


-- 
-- Dumping data for table `mime_types`
-- 

INSERT INTO `mime_types` VALUES (1, 'ai', 'application/postscript', 'pdf');
INSERT INTO `mime_types` VALUES (2, 'aif', 'audio/x-aiff', NULL);
INSERT INTO `mime_types` VALUES (3, 'aifc', 'audio/x-aiff', NULL);
INSERT INTO `mime_types` VALUES (4, 'aiff', 'audio/x-aiff', NULL);
INSERT INTO `mime_types` VALUES (5, 'asc', 'text/plain', 'text');
INSERT INTO `mime_types` VALUES (6, 'au', 'audio/basic', NULL);
INSERT INTO `mime_types` VALUES (7, 'avi', 'video/x-msvideo', NULL);
INSERT INTO `mime_types` VALUES (8, 'bcpio', 'application/x-bcpio', NULL);
INSERT INTO `mime_types` VALUES (9, 'bin', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (10, 'bmp', 'image/bmp', 'image');
INSERT INTO `mime_types` VALUES (11, 'cdf', 'application/x-netcdf', NULL);
INSERT INTO `mime_types` VALUES (12, 'class', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (13, 'cpio', 'application/x-cpio', NULL);
INSERT INTO `mime_types` VALUES (14, 'cpt', 'application/mac-compactpro', NULL);
INSERT INTO `mime_types` VALUES (15, 'csh', 'application/x-csh', NULL);
INSERT INTO `mime_types` VALUES (16, 'css', 'text/css', NULL);
INSERT INTO `mime_types` VALUES (17, 'dcr', 'application/x-director', NULL);
INSERT INTO `mime_types` VALUES (18, 'dir', 'application/x-director', NULL);
INSERT INTO `mime_types` VALUES (19, 'dms', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (20, 'doc', 'application/msword', 'word');
INSERT INTO `mime_types` VALUES (21, 'dvi', 'application/x-dvi', NULL);
INSERT INTO `mime_types` VALUES (22, 'dxr', 'application/x-director', NULL);
INSERT INTO `mime_types` VALUES (23, 'eps', 'application/postscript', 'pdf');
INSERT INTO `mime_types` VALUES (24, 'etx', 'text/x-setext', NULL);
INSERT INTO `mime_types` VALUES (25, 'exe', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (26, 'ez', 'application/andrew-inset', NULL);
INSERT INTO `mime_types` VALUES (27, 'gif', 'image/gif', 'image');
INSERT INTO `mime_types` VALUES (28, 'gtar', 'application/x-gtar', 'compressed');
INSERT INTO `mime_types` VALUES (29, 'hdf', 'application/x-hdf', NULL);
INSERT INTO `mime_types` VALUES (30, 'hqx', 'application/mac-binhex40', NULL);
INSERT INTO `mime_types` VALUES (31, 'htm', 'text/html', 'html');
INSERT INTO `mime_types` VALUES (32, 'html', 'text/html', 'html');
INSERT INTO `mime_types` VALUES (33, 'ice', 'x-conference/x-cooltalk', NULL);
INSERT INTO `mime_types` VALUES (34, 'ief', 'image/ief', 'image');
INSERT INTO `mime_types` VALUES (35, 'iges', 'model/iges', NULL);
INSERT INTO `mime_types` VALUES (36, 'igs', 'model/iges', NULL);
INSERT INTO `mime_types` VALUES (37, 'jpe', 'image/jpeg', 'image');
INSERT INTO `mime_types` VALUES (38, 'jpeg', 'image/jpeg', 'image');
INSERT INTO `mime_types` VALUES (39, 'jpg', 'image/jpeg', 'image');
INSERT INTO `mime_types` VALUES (40, 'js', 'application/x-javascript', 'html');
INSERT INTO `mime_types` VALUES (41, 'kar', 'audio/midi', NULL);
INSERT INTO `mime_types` VALUES (42, 'latex', 'application/x-latex', NULL);
INSERT INTO `mime_types` VALUES (43, 'lha', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (44, 'lzh', 'application/octet-stream', NULL);
INSERT INTO `mime_types` VALUES (45, 'man', 'application/x-troff-man', NULL);
INSERT INTO `mime_types` VALUES (46, 'mdb', 'application/access', 'database');
INSERT INTO `mime_types` VALUES (47, 'mdf', 'application/access', 'database');
INSERT INTO `mime_types` VALUES (48, 'me', 'application/x-troff-me', NULL);
INSERT INTO `mime_types` VALUES (49, 'mesh', 'model/mesh', NULL);
INSERT INTO `mime_types` VALUES (50, 'mid', 'audio/midi', NULL);
INSERT INTO `mime_types` VALUES (51, 'midi', 'audio/midi', NULL);
INSERT INTO `mime_types` VALUES (52, 'mif', 'application/vnd.mif', NULL);
INSERT INTO `mime_types` VALUES (53, 'mov', 'video/quicktime', NULL);
INSERT INTO `mime_types` VALUES (54, 'movie', 'video/x-sgi-movie', NULL);
INSERT INTO `mime_types` VALUES (55, 'mp2', 'audio/mpeg', NULL);
INSERT INTO `mime_types` VALUES (56, 'mp3', 'audio/mpeg', NULL);
INSERT INTO `mime_types` VALUES (57, 'mpe', 'video/mpeg', NULL);
INSERT INTO `mime_types` VALUES (58, 'mpeg', 'video/mpeg', NULL);
INSERT INTO `mime_types` VALUES (59, 'mpg', 'video/mpeg', NULL);
INSERT INTO `mime_types` VALUES (60, 'mpga', 'audio/mpeg', NULL);
INSERT INTO `mime_types` VALUES (61, 'mpp', 'application/vnd.ms-project', 'office');
INSERT INTO `mime_types` VALUES (62, 'ms', 'application/x-troff-ms', NULL);
INSERT INTO `mime_types` VALUES (63, 'msh', 'model/mesh', NULL);
INSERT INTO `mime_types` VALUES (64, 'nc', 'application/x-netcdf', NULL);
INSERT INTO `mime_types` VALUES (65, 'oda', 'application/oda', NULL);
INSERT INTO `mime_types` VALUES (66, 'pbm', 'image/x-portable-bitmap', 'image');
INSERT INTO `mime_types` VALUES (67, 'pdb', 'chemical/x-pdb', NULL);
INSERT INTO `mime_types` VALUES (68, 'pdf', 'application/pdf', 'pdf');
INSERT INTO `mime_types` VALUES (69, 'pgm', 'image/x-portable-graymap', 'image');
INSERT INTO `mime_types` VALUES (70, 'pgn', 'application/x-chess-pgn', NULL);
INSERT INTO `mime_types` VALUES (71, 'png', 'image/png', 'image');
INSERT INTO `mime_types` VALUES (72, 'pnm', 'image/x-portable-anymap', 'image');
INSERT INTO `mime_types` VALUES (73, 'ppm', 'image/x-portable-pixmap', 'image');
INSERT INTO `mime_types` VALUES (74, 'ppt', 'application/vnd.ms-powerpoint', 'office');
INSERT INTO `mime_types` VALUES (75, 'ps', 'application/postscript', 'pdf');
INSERT INTO `mime_types` VALUES (76, 'qt', 'video/quicktime', NULL);
INSERT INTO `mime_types` VALUES (77, 'ra', 'audio/x-realaudio', NULL);
INSERT INTO `mime_types` VALUES (78, 'ram', 'audio/x-pn-realaudio', NULL);
INSERT INTO `mime_types` VALUES (79, 'ras', 'image/x-cmu-raster', 'image');
INSERT INTO `mime_types` VALUES (80, 'rgb', 'image/x-rgb', 'image');
INSERT INTO `mime_types` VALUES (81, 'rm', 'audio/x-pn-realaudio', NULL);
INSERT INTO `mime_types` VALUES (82, 'roff', 'application/x-troff', NULL);
INSERT INTO `mime_types` VALUES (83, 'rpm', 'audio/x-pn-realaudio-plugin', NULL);
INSERT INTO `mime_types` VALUES (84, 'rtf', 'text/rtf', NULL);
INSERT INTO `mime_types` VALUES (85, 'rtx', 'text/richtext', NULL);
INSERT INTO `mime_types` VALUES (86, 'sgm', 'text/sgml', NULL);
INSERT INTO `mime_types` VALUES (87, 'sgml', 'text/sgml', NULL);
INSERT INTO `mime_types` VALUES (88, 'sh', 'application/x-sh', NULL);
INSERT INTO `mime_types` VALUES (89, 'shar', 'application/x-shar', NULL);
INSERT INTO `mime_types` VALUES (90, 'silo', 'model/mesh', NULL);
INSERT INTO `mime_types` VALUES (91, 'sit', 'application/x-stuffit', NULL);
INSERT INTO `mime_types` VALUES (92, 'skd', 'application/x-koan', NULL);
INSERT INTO `mime_types` VALUES (93, 'skm', 'application/x-koan', NULL);
INSERT INTO `mime_types` VALUES (94, 'skp', 'application/x-koan', NULL);
INSERT INTO `mime_types` VALUES (95, 'skt', 'application/x-koan', NULL);
INSERT INTO `mime_types` VALUES (96, 'smi', 'application/smil', NULL);
INSERT INTO `mime_types` VALUES (97, 'smil', 'application/smil', NULL);
INSERT INTO `mime_types` VALUES (98, 'snd', 'audio/basic', NULL);
INSERT INTO `mime_types` VALUES (99, 'spl', 'application/x-futuresplash', NULL);
INSERT INTO `mime_types` VALUES (100, 'src', 'application/x-wais-source', NULL);
INSERT INTO `mime_types` VALUES (101, 'sv4cpio', 'application/x-sv4cpio', NULL);
INSERT INTO `mime_types` VALUES (102, 'sv4crc', 'application/x-sv4crc', NULL);
INSERT INTO `mime_types` VALUES (103, 'swf', 'application/x-shockwave-flash', NULL);
INSERT INTO `mime_types` VALUES (104, 't', 'application/x-troff', NULL);
INSERT INTO `mime_types` VALUES (105, 'tar', 'application/x-tar', 'compressed');
INSERT INTO `mime_types` VALUES (106, 'tcl', 'application/x-tcl', NULL);
INSERT INTO `mime_types` VALUES (107, 'tex', 'application/x-tex', NULL);
INSERT INTO `mime_types` VALUES (108, 'texi', 'application/x-texinfo', NULL);
INSERT INTO `mime_types` VALUES (109, 'texinfo', 'application/x-texinfo', NULL);
INSERT INTO `mime_types` VALUES (110, 'tif', 'image/tiff', 'image');
INSERT INTO `mime_types` VALUES (111, 'tiff', 'image/tiff', 'image');
INSERT INTO `mime_types` VALUES (112, 'tr', 'application/x-troff', NULL);
INSERT INTO `mime_types` VALUES (113, 'tsv', 'text/tab-separated-values', NULL);
INSERT INTO `mime_types` VALUES (114, 'txt', 'text/plain', 'text');
INSERT INTO `mime_types` VALUES (115, 'ustar', 'application/x-ustar', NULL);
INSERT INTO `mime_types` VALUES (116, 'vcd', 'application/x-cdlink', NULL);
INSERT INTO `mime_types` VALUES (117, 'vrml', 'model/vrml', NULL);
INSERT INTO `mime_types` VALUES (118, 'vsd', 'application/vnd.visio', 'office');
INSERT INTO `mime_types` VALUES (119, 'wav', 'audio/x-wav', NULL);
INSERT INTO `mime_types` VALUES (120, 'wrl', 'model/vrml', NULL);
INSERT INTO `mime_types` VALUES (121, 'xbm', 'image/x-xbitmap', 'image');
INSERT INTO `mime_types` VALUES (122, 'xls', 'application/vnd.ms-excel', 'excel');
INSERT INTO `mime_types` VALUES (123, 'xml', 'text/xml', NULL);
INSERT INTO `mime_types` VALUES (124, 'xpm', 'image/x-xpixmap', 'image');
INSERT INTO `mime_types` VALUES (125, 'xwd', 'image/x-xwindowdump', 'image');
INSERT INTO `mime_types` VALUES (126, 'xyz', 'chemical/x-pdb', NULL);
INSERT INTO `mime_types` VALUES (127, 'zip', 'application/zip', 'compressed');
INSERT INTO `mime_types` VALUES (128, 'gz', 'application/x-gzip', 'compressed');
INSERT INTO `mime_types` VALUES (129, 'tgz', 'application/x-gzip', 'compressed');
INSERT INTO `mime_types` VALUES (130, 'sxw', 'application/vnd.sun.xml.writer', 'openoffice');
INSERT INTO `mime_types` VALUES (131, 'stw', 'application/vnd.sun.xml.writer.template', 'openoffice');
INSERT INTO `mime_types` VALUES (132, 'sxc', 'application/vnd.sun.xml.calc', 'openoffice');
INSERT INTO `mime_types` VALUES (133, 'stc', 'application/vnd.sun.xml.calc.template', 'openoffice');
INSERT INTO `mime_types` VALUES (134, 'sxd', 'application/vnd.sun.xml.draw', 'openoffice');
INSERT INTO `mime_types` VALUES (135, 'std', 'application/vnd.sun.xml.draw.template', 'openoffice');
INSERT INTO `mime_types` VALUES (136, 'sxi', 'application/vnd.sun.xml.impress', 'openoffice');
INSERT INTO `mime_types` VALUES (137, 'sti', 'application/vnd.sun.xml.impress.template', 'openoffice');
INSERT INTO `mime_types` VALUES (138, 'sxg', 'application/vnd.sun.xml.writer.global', 'openoffice');
INSERT INTO `mime_types` VALUES (139, 'sxm', 'application/vnd.sun.xml.math', 'openoffice');
INSERT INTO `mime_types` VALUES (140, 'xlt', 'application/vnd.ms-excel', 'excel');
INSERT INTO `mime_types` VALUES (141, 'dot', 'application/msword', 'word');

-- 
-- Dumping data for table `news`
-- 


-- 
-- Dumping data for table `notifications`
-- 


-- 
-- Dumping data for table `organisations_lookup`
-- 

INSERT INTO `organisations_lookup` VALUES (1, 'Default Organisation');

-- 
-- Dumping data for table `permission_assignments`
-- 

INSERT INTO `permission_assignments` VALUES (1, 1, 1, 2);
INSERT INTO `permission_assignments` VALUES (2, 2, 1, 2);
INSERT INTO `permission_assignments` VALUES (3, 3, 1, 2);

-- 
-- Dumping data for table `permission_descriptor_groups`
-- 

INSERT INTO `permission_descriptor_groups` VALUES (2, 1);

-- 
-- Dumping data for table `permission_descriptor_roles`
-- 


-- 
-- Dumping data for table `permission_descriptor_users`
-- 


-- 
-- Dumping data for table `permission_descriptors`
-- 

INSERT INTO `permission_descriptors` VALUES (1, 'd41d8cd98f00b204e9800998ecf8427e', '');
INSERT INTO `permission_descriptors` VALUES (2, 'a689e7c4dc953de8d93b1ed4843b2dfe', 'group(1)');

-- 
-- Dumping data for table `permission_dynamic_assignments`
-- 


-- 
-- Dumping data for table `permission_dynamic_conditions`
-- 


-- 
-- Dumping data for table `permission_lookup_assignments`
-- 

INSERT INTO `permission_lookup_assignments` VALUES (1, 1, 1, 1);
INSERT INTO `permission_lookup_assignments` VALUES (2, 2, 1, 1);
INSERT INTO `permission_lookup_assignments` VALUES (3, 3, 1, 1);
INSERT INTO `permission_lookup_assignments` VALUES (4, 1, 2, 2);
INSERT INTO `permission_lookup_assignments` VALUES (5, 2, 2, 2);
INSERT INTO `permission_lookup_assignments` VALUES (6, 3, 2, 2);

-- 
-- Dumping data for table `permission_lookups`
-- 

INSERT INTO `permission_lookups` VALUES (1);
INSERT INTO `permission_lookups` VALUES (2);

-- 
-- Dumping data for table `permission_objects`
-- 

INSERT INTO `permission_objects` VALUES (1);

-- 
-- Dumping data for table `permissions`
-- 

INSERT INTO `permissions` VALUES (1, 'ktcore.permissions.read', 'Core: Read', 1);
INSERT INTO `permissions` VALUES (2, 'ktcore.permissions.write', 'Core: Write', 1);
INSERT INTO `permissions` VALUES (3, 'ktcore.permissions.addFolder', 'Core: Add Folder', 1);

-- 
-- Dumping data for table `plugins`
-- 


-- 
-- Dumping data for table `role_allocations`
-- 


-- 
-- Dumping data for table `roles`
-- 


-- 
-- Dumping data for table `saved_searches`
-- 


-- 
-- Dumping data for table `search_document_user_link`
-- 


-- 
-- Dumping data for table `status_lookup`
-- 

INSERT INTO `status_lookup` VALUES (1, 'Live');
INSERT INTO `status_lookup` VALUES (2, 'Published');
INSERT INTO `status_lookup` VALUES (3, 'Deleted');
INSERT INTO `status_lookup` VALUES (4, 'Archived');
INSERT INTO `status_lookup` VALUES (5, 'Incomplete');

-- 
-- Dumping data for table `system_settings`
-- 

INSERT INTO `system_settings` VALUES (1, 'lastIndexUpdate', '0');
INSERT INTO `system_settings` VALUES (2, 'knowledgeTreeVersion', '2.99.8');
INSERT INTO `system_settings` VALUES (3, 'databaseVersion', '2.99.5');

-- 
-- Dumping data for table `time_period`
-- 


-- 
-- Dumping data for table `time_unit_lookup`
-- 

INSERT INTO `time_unit_lookup` VALUES (1, 'Years');
INSERT INTO `time_unit_lookup` VALUES (2, 'Months');
INSERT INTO `time_unit_lookup` VALUES (3, 'Days');

-- 
-- Dumping data for table `trigger_selection`
-- 


-- 
-- Dumping data for table `type_workflow_map`
-- 


-- 
-- Dumping data for table `units_lookup`
-- 

INSERT INTO `units_lookup` VALUES (1, 'Default Unit', 2);

-- 
-- Dumping data for table `units_organisations_link`
-- 

INSERT INTO `units_organisations_link` VALUES (1, 1, 1);

-- 
-- Dumping data for table `upgrades`
-- 

INSERT INTO `upgrades` VALUES (1, 'sql*2.0.6*0*2.0.6/create_upgrade_table.sql', 'Database upgrade to version 2.0.6: Create upgrade table', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (2, 'upgrade*2.0.6*0*upgrade2.0.6', 'Upgrade from version 2.0.2 to 2.0.6', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (3, 'func*2.0.6*0*addTemplateMimeTypes', 'Add MIME types for Excel and Word templates', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (4, 'sql*2.0.6*0*2.0.6/add_email_attachment_transaction_type.sql', 'Database upgrade to version 2.0.6: Add email attachment transaction type', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (5, 'sql*2.0.6*0*2.0.6/create_link_type_table.sql', 'Database upgrade to version 2.0.6: Create link type table', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (6, 'sql*2.0.6*1*2.0.6/1-update_database_version.sql', 'Database upgrade to version 2.0.6: Update database version', '2005-06-16 00:30:06', 1, 'upgrade*2.0.6*0*upgrade2.0.6');
INSERT INTO `upgrades` VALUES (7, 'upgrade*2.0.7*0*upgrade2.0.7', 'Upgrade from version 2.0.7 to 2.0.7', '2005-07-21 22:35:15', 1, 'upgrade*2.0.7*0*upgrade2.0.7');
INSERT INTO `upgrades` VALUES (8, 'sql*2.0.7*0*2.0.7/document_link_update.sql', 'Database upgrade to version 2.0.7: Document link update', '2005-07-21 22:35:16', 1, 'upgrade*2.0.7*0*upgrade2.0.7');
INSERT INTO `upgrades` VALUES (9, 'sql*2.0.8*0*2.0.8/nestedgroups.sql', 'Database upgrade to version 2.0.8: Nestedgroups', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (10, 'sql*2.0.8*0*2.0.8/help_replacement.sql', 'Database upgrade to version 2.0.8: Help replacement', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (11, 'upgrade*2.0.8*0*upgrade2.0.8', 'Upgrade from version 2.0.7 to 2.0.8', '2005-08-02 16:02:06', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (12, 'sql*2.0.8*0*2.0.8/permissions.sql', 'Database upgrade to version 2.0.8: Permissions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (13, 'func*2.0.8*1*setPermissionObject', 'Set the permission object in charge of a document or folder', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (14, 'sql*2.0.8*1*2.0.8/1-metadata_versions.sql', 'Database upgrade to version 2.0.8: Metadata versions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (15, 'sql*2.0.8*2*2.0.8/2-permissions.sql', 'Database upgrade to version 2.0.8: Permissions', '2005-08-02 16:02:07', 1, 'upgrade*2.0.8*0*upgrade2.0.8');
INSERT INTO `upgrades` VALUES (16, 'sql*2.0.9*0*2.0.9/storagemanager.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (17, 'sql*2.0.9*0*2.0.9/metadata_tree.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (18, 'sql*2.0.9*0*2.0.9/document_incomplete.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (20, 'upgrade*2.99.1*0*upgrade2.99.1', 'Upgrade from version 2.0.8 to 2.99.1', '2005-10-07 14:26:15', 1, 'upgrade*2.99.1*0*upgrade2.99.1');
INSERT INTO `upgrades` VALUES (21, 'sql*2.99.1*0*2.99.1/workflow.sql', 'Database upgrade to version 2.99.1: Workflow', '2005-10-07 14:26:15', 1, 'upgrade*2.99.1*0*upgrade2.99.1');
INSERT INTO `upgrades` VALUES (22, 'sql*2.99.1*0*2.99.1/fieldsets.sql', 'Database upgrade to version 2.99.1: Fieldsets', '2005-10-07 14:26:16', 1, 'upgrade*2.99.1*0*upgrade2.99.1');
INSERT INTO `upgrades` VALUES (23, 'func*2.99.1*1*createFieldSets', 'Create a fieldset for each field without one', '2005-10-07 14:26:16', 1, 'upgrade*2.99.1*0*upgrade2.99.1');
INSERT INTO `upgrades` VALUES (24, 'sql*2.99.2*0*2.99.2/saved_searches.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (25, 'sql*2.99.2*0*2.99.2/transactions.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (26, 'sql*2.99.2*0*2.99.2/field_mandatory.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (27, 'sql*2.99.2*0*2.99.2/fieldsets_system.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (28, 'sql*2.99.2*0*2.99.2/permission_by_user_and_roles.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (29, 'sql*2.99.2*0*2.99.2/disabled_metadata.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (30, 'sql*2.99.2*0*2.99.2/searchable_text.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (31, 'sql*2.99.2*0*2.99.2/workflow.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (32, 'sql*2.99.2*1*2.99.2/1-constraints.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (33, 'sql*2.99.3*0*2.99.3/notifications.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (34, 'sql*2.99.3*0*2.99.3/last_modified_user.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (35, 'sql*2.99.3*0*2.99.3/authentication_sources.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (36, 'sql*2.99.3*0*2.99.3/document_fields_constraints.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (37, 'sql*2.99.5*0*2.99.5/dashlet_disabling.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (38, 'sql*2.99.5*0*2.99.5/role_allocations.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (39, 'sql*2.99.5*0*2.99.5/transaction_namespaces.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (40, 'sql*2.99.5*0*2.99.5/fieldset_field_descriptions.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (41, 'sql*2.99.5*0*2.99.5/role_changes.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (42, 'sql*2.99.6*0*2.99.6/table_cleanup.sql', 'Database upgrade to version 2.99.6: Table cleanup', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (43, 'sql*2.99.6*0*2.99.6/plugin-registration.sql', 'Database upgrade to version 2.99.6: Plugin-registration', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (44, 'sql*2.99.7*0*2.99.7/documents_normalisation.sql', 'Database upgrade to version 2.99.7: Documents normalisation', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (45, 'sql*2.99.7*0*2.99.7/help_replacement.sql', 'Database upgrade to version 2.99.7: Help replacement', '2006-01-20 17:04:05', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (46, 'sql*2.99.7*0*2.99.7/table_cleanup.sql', 'Database upgrade to version 2.99.7: Table cleanup', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (47, 'func*2.99.7*1*normaliseDocuments', 'Normalise the documents table', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (48, 'sql*2.99.7*10*2.99.7/10-documents_normalisation.sql', 'Database upgrade to version 2.99.7: Documents normalisation', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (49, 'sql*2.99.7*20*2.99.7/20-fields.sql', 'Database upgrade to version 2.99.7: Fields', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (50, 'upgrade*2.99.7*99*upgrade2.99.7', 'Upgrade from version 2.99.5 to 2.99.7', '2006-01-20 17:04:07', 1, 'upgrade*2.99.7*99*upgrade2.99.7');
INSERT INTO `upgrades` VALUES (51, 'sql*2.99.7*0*2.99.7/discussion.sql', '', '0000-00-00 00:00:00', 1, NULL);
INSERT INTO `upgrades` VALUES (52, 'func*2.99.7*-1*applyDiscussionUpgrade', 'func upgrade to version 2.99.7 phase -1', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (53, 'sql*2.99.8*0*2.99.8/mime_types.sql', 'Database upgrade to version 2.99.8: Mime types', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (54, 'sql*2.99.8*0*2.99.8/category-correction.sql', 'Database upgrade to version 2.99.8: Category-correction', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (55, 'sql*2.99.8*0*2.99.8/trigger_selection.sql', 'Database upgrade to version 2.99.8: Trigger selection', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (56, 'sql*2.99.8*0*2.99.8/units.sql', 'Database upgrade to version 2.99.8: Units', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (57, 'sql*2.99.8*0*2.99.8/type_workflow_map.sql', 'Database upgrade to version 2.99.8: Type workflow map', '2006-02-06 12:23:41', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (58, 'sql*2.99.8*0*2.99.8/disabled_documenttypes.sql', 'Database upgrade to version 2.99.8: Disabled documenttypes', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (59, 'func*2.99.8*1*fixUnits', 'func upgrade to version 2.99.8 phase 1', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (60, 'sql*2.99.8*10*2.99.8/10-units.sql', 'Database upgrade to version 2.99.8: Units', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (61, 'sql*2.99.8*15*2.99.8/15-status.sql', 'Database upgrade to version 2.99.8: Status', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (62, 'sql*2.99.8*20*2.99.8/20-state_permission_assignments.sql', 'Database upgrade to version 2.99.8: State permission assignments', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (63, 'sql*2.99.8*25*2.99.8/25-authentication_details.sql', 'Database upgrade to version 2.99.8: Authentication details', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');
INSERT INTO `upgrades` VALUES (64, 'upgrade*2.99.8*99*upgrade2.99.8', 'Upgrade from version 2.99.7 to 2.99.8', '2006-02-06 12:23:42', 1, 'upgrade*2.99.8*99*upgrade2.99.8');

-- 
-- Dumping data for table `users`
-- 

INSERT INTO `users` VALUES (1, 'admin', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1, NULL, NULL);
INSERT INTO `users` VALUES (2, 'unitAdmin', 'Unit Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 1, 1, NULL, NULL);
INSERT INTO `users` VALUES (3, 'guest', 'Anonymous', '084e0343a0486ff05530df6c705c8bb4', 0, 0, '', '', 0, 0, '', 19, 1, NULL, NULL);

-- 
-- Dumping data for table `users_groups_link`
-- 

INSERT INTO `users_groups_link` VALUES (1, 1, 1);
INSERT INTO `users_groups_link` VALUES (2, 2, 2);
INSERT INTO `users_groups_link` VALUES (3, 3, 3);

-- 
-- Dumping data for table `workflow_actions`
-- 


-- 
-- Dumping data for table `workflow_documents`
-- 


-- 
-- Dumping data for table `workflow_state_actions`
-- 


-- 
-- Dumping data for table `workflow_state_permission_assignments`
-- 


-- 
-- Dumping data for table `workflow_state_transitions`
-- 


-- 
-- Dumping data for table `workflow_states`
-- 


-- 
-- Dumping data for table `workflow_transitions`
-- 


-- 
-- Dumping data for table `workflows`
-- 


-- 
-- Dumping data for table `zseq_active_sessions`
-- 

INSERT INTO `zseq_active_sessions` VALUES (1);

-- 
-- Dumping data for table `zseq_archive_restoration_request`
-- 

INSERT INTO `zseq_archive_restoration_request` VALUES (1);

-- 
-- Dumping data for table `zseq_archiving_settings`
-- 

INSERT INTO `zseq_archiving_settings` VALUES (1);

-- 
-- Dumping data for table `zseq_archiving_type_lookup`
-- 

INSERT INTO `zseq_archiving_type_lookup` VALUES (2);

-- 
-- Dumping data for table `zseq_authentication_sources`
-- 

INSERT INTO `zseq_authentication_sources` VALUES (1);

-- 
-- Dumping data for table `zseq_browse_criteria`
-- 

INSERT INTO `zseq_browse_criteria` VALUES (5);

-- 
-- Dumping data for table `zseq_dashlet_disables`
-- 

INSERT INTO `zseq_dashlet_disables` VALUES (1);

-- 
-- Dumping data for table `zseq_data_types`
-- 

INSERT INTO `zseq_data_types` VALUES (5);

-- 
-- Dumping data for table `zseq_dependant_document_instance`
-- 

INSERT INTO `zseq_dependant_document_instance` VALUES (1);

-- 
-- Dumping data for table `zseq_dependant_document_template`
-- 

INSERT INTO `zseq_dependant_document_template` VALUES (1);

-- 
-- Dumping data for table `zseq_discussion_comments`
-- 

INSERT INTO `zseq_discussion_comments` VALUES (1);

-- 
-- Dumping data for table `zseq_discussion_threads`
-- 

INSERT INTO `zseq_discussion_threads` VALUES (1);

-- 
-- Dumping data for table `zseq_document_archiving_link`
-- 

INSERT INTO `zseq_document_archiving_link` VALUES (1);

-- 
-- Dumping data for table `zseq_document_content_version`
-- 

INSERT INTO `zseq_document_content_version` VALUES (1);

-- 
-- Dumping data for table `zseq_document_fields`
-- 

INSERT INTO `zseq_document_fields` VALUES (1);

-- 
-- Dumping data for table `zseq_document_fields_link`
-- 

INSERT INTO `zseq_document_fields_link` VALUES (1);

-- 
-- Dumping data for table `zseq_document_link`
-- 

INSERT INTO `zseq_document_link` VALUES (1);

-- 
-- Dumping data for table `zseq_document_link_types`
-- 

INSERT INTO `zseq_document_link_types` VALUES (2);

-- 
-- Dumping data for table `zseq_document_metadata_version`
-- 

INSERT INTO `zseq_document_metadata_version` VALUES (1);

-- 
-- Dumping data for table `zseq_document_subscriptions`
-- 

INSERT INTO `zseq_document_subscriptions` VALUES (1);

-- 
-- Dumping data for table `zseq_document_transaction_types_lookup`
-- 

INSERT INTO `zseq_document_transaction_types_lookup` VALUES (16);

-- 
-- Dumping data for table `zseq_document_transactions`
-- 

INSERT INTO `zseq_document_transactions` VALUES (1);

-- 
-- Dumping data for table `zseq_document_type_fields_link`
-- 

INSERT INTO `zseq_document_type_fields_link` VALUES (1);

-- 
-- Dumping data for table `zseq_document_type_fieldsets_link`
-- 

INSERT INTO `zseq_document_type_fieldsets_link` VALUES (1);

-- 
-- Dumping data for table `zseq_document_types_lookup`
-- 

INSERT INTO `zseq_document_types_lookup` VALUES (1);

-- 
-- Dumping data for table `zseq_documents`
-- 

INSERT INTO `zseq_documents` VALUES (1);

-- 
-- Dumping data for table `zseq_field_behaviours`
-- 

INSERT INTO `zseq_field_behaviours` VALUES (1);

-- 
-- Dumping data for table `zseq_field_value_instances`
-- 

INSERT INTO `zseq_field_value_instances` VALUES (1);

-- 
-- Dumping data for table `zseq_fieldsets`
-- 

INSERT INTO `zseq_fieldsets` VALUES (1);

-- 
-- Dumping data for table `zseq_folder_doctypes_link`
-- 

INSERT INTO `zseq_folder_doctypes_link` VALUES (2);

-- 
-- Dumping data for table `zseq_folder_subscriptions`
-- 

INSERT INTO `zseq_folder_subscriptions` VALUES (1);

-- 
-- Dumping data for table `zseq_folders`
-- 

INSERT INTO `zseq_folders` VALUES (2);

-- 
-- Dumping data for table `zseq_folders_users_roles_link`
-- 

INSERT INTO `zseq_folders_users_roles_link` VALUES (1);

-- 
-- Dumping data for table `zseq_groups_groups_link`
-- 

INSERT INTO `zseq_groups_groups_link` VALUES (1);

-- 
-- Dumping data for table `zseq_groups_lookup`
-- 

INSERT INTO `zseq_groups_lookup` VALUES (3);

-- 
-- Dumping data for table `zseq_help`
-- 

INSERT INTO `zseq_help` VALUES (98);

-- 
-- Dumping data for table `zseq_help_replacement`
-- 

INSERT INTO `zseq_help_replacement` VALUES (1);

-- 
-- Dumping data for table `zseq_links`
-- 

INSERT INTO `zseq_links` VALUES (1);

-- 
-- Dumping data for table `zseq_metadata_lookup`
-- 

INSERT INTO `zseq_metadata_lookup` VALUES (1);

-- 
-- Dumping data for table `zseq_metadata_lookup_tree`
-- 

INSERT INTO `zseq_metadata_lookup_tree` VALUES (1);

-- 
-- Dumping data for table `zseq_mime_types`
-- 

INSERT INTO `zseq_mime_types` VALUES (141);

-- 
-- Dumping data for table `zseq_news`
-- 

INSERT INTO `zseq_news` VALUES (1);

-- 
-- Dumping data for table `zseq_notifications`
-- 

INSERT INTO `zseq_notifications` VALUES (1);

-- 
-- Dumping data for table `zseq_organisations_lookup`
-- 

INSERT INTO `zseq_organisations_lookup` VALUES (1);

-- 
-- Dumping data for table `zseq_permission_assignments`
-- 

INSERT INTO `zseq_permission_assignments` VALUES (3);

-- 
-- Dumping data for table `zseq_permission_descriptors`
-- 

INSERT INTO `zseq_permission_descriptors` VALUES (2);

-- 
-- Dumping data for table `zseq_permission_dynamic_conditions`
-- 

INSERT INTO `zseq_permission_dynamic_conditions` VALUES (1);

-- 
-- Dumping data for table `zseq_permission_lookup_assignments`
-- 

INSERT INTO `zseq_permission_lookup_assignments` VALUES (6);

-- 
-- Dumping data for table `zseq_permission_lookups`
-- 

INSERT INTO `zseq_permission_lookups` VALUES (2);

-- 
-- Dumping data for table `zseq_permission_objects`
-- 

INSERT INTO `zseq_permission_objects` VALUES (1);

-- 
-- Dumping data for table `zseq_permissions`
-- 

INSERT INTO `zseq_permissions` VALUES (3);

-- 
-- Dumping data for table `zseq_plugins`
-- 

INSERT INTO `zseq_plugins` VALUES (1);

-- 
-- Dumping data for table `zseq_role_allocations`
-- 

INSERT INTO `zseq_role_allocations` VALUES (1);

-- 
-- Dumping data for table `zseq_roles`
-- 

INSERT INTO `zseq_roles` VALUES (1);

-- 
-- Dumping data for table `zseq_saved_searches`
-- 

INSERT INTO `zseq_saved_searches` VALUES (1);

-- 
-- Dumping data for table `zseq_status_lookup`
-- 

INSERT INTO `zseq_status_lookup` VALUES (5);

-- 
-- Dumping data for table `zseq_system_settings`
-- 

INSERT INTO `zseq_system_settings` VALUES (3);

-- 
-- Dumping data for table `zseq_time_period`
-- 

INSERT INTO `zseq_time_period` VALUES (1);

-- 
-- Dumping data for table `zseq_time_unit_lookup`
-- 

INSERT INTO `zseq_time_unit_lookup` VALUES (3);

-- 
-- Dumping data for table `zseq_units_lookup`
-- 

INSERT INTO `zseq_units_lookup` VALUES (1);

-- 
-- Dumping data for table `zseq_units_organisations_link`
-- 

INSERT INTO `zseq_units_organisations_link` VALUES (1);

-- 
-- Dumping data for table `zseq_upgrades`
-- 

INSERT INTO `zseq_upgrades` VALUES (64);

-- 
-- Dumping data for table `zseq_users`
-- 

INSERT INTO `zseq_users` VALUES (3);

-- 
-- Dumping data for table `zseq_users_groups_link`
-- 

INSERT INTO `zseq_users_groups_link` VALUES (3);

-- 
-- Dumping data for table `zseq_workflow_state_permission_assignments`
-- 


-- 
-- Dumping data for table `zseq_workflow_states`
-- 

INSERT INTO `zseq_workflow_states` VALUES (1);

-- 
-- Dumping data for table `zseq_workflow_transitions`
-- 

INSERT INTO `zseq_workflow_transitions` VALUES (1);

-- 
-- Dumping data for table `zseq_workflows`
-- 

INSERT INTO `zseq_workflows` VALUES (1);

SET FOREIGN_KEY_CHECKS=1;
