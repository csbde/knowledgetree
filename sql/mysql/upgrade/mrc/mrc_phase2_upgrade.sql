-- new tables
CREATE TABLE archive_restoration_request (
  id int(11) NOT NULL auto_increment,
  document_id int(11) NOT NULL default '0',
  request_user_id int(11) NOT NULL default '0',
  admin_user_id int(11) NOT NULL default '0',
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE archiving_settings (
  id int(11) NOT NULL auto_increment,
  archiving_type_id int(11) NOT NULL default '0',
  expiration_date date default NULL,
  document_transaction_id int(11) default NULL,
  time_period_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE archiving_type_lookup (
  id int(11) NOT NULL auto_increment,
  name char(100) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE dependant_document_instance (
  id int(11) NOT NULL auto_increment,
  document_title text NOT NULL,
  user_id int(11) NOT NULL default '0',
  template_document_id int(11) default NULL,
  parent_document_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE dependant_document_template (
  id int(11) NOT NULL auto_increment,
  document_title text NOT NULL,
  default_user_id int(11) NOT NULL default '0',
  template_document_id int(11) default NULL,
  group_folder_approval_link_id int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE time_period (
  id int(11) NOT NULL auto_increment,
  time_unit_id int(11) default NULL,
  units int(11) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE time_unit_lookup (
  id int(11) NOT NULL auto_increment,
  name char(100) default NULL,
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE document_archiving_link (
  id int(11) NOT NULL auto_increment,
  document_id int(11) NOT NULL default '0',
  archiving_settings_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=MyISAM;

CREATE TABLE document_link (
  id int(11) NOT NULL auto_increment,
  parent_document_id int(11) NOT NULL default '0',
  child_document_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=MyISAM;

-- altered tables
ALTER TABLE discussion_comments MODIFY  subject VARCHAR(255) NOT NULL default '';
-- test this!
ALTER TABLE documents MODIFY filename text NOT NULL;
ALTER TABLE documents MODIFY name text NOT NULL;
ALTER TABLE folders_users_roles_link ADD column dependant_documents_created tinyint(1) default NULL;
-- does this make sense??! what are the implications
UPDATE folders_users_roles_link set dependant_documents_created = 1;
ALTER TABLE groups_folders_approval_link ADD COLUMN user_id INT DEFAULT NULL;

-- old tables
DROP TABLE document_words_link;
DROP TABLE words_lookup;

-- update lookups
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxw', 'application/vnd.sun.xml.writer', 'icons/oowriter.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('stw','application/vnd.sun.xml.writer.template', 'icons/oowriter.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxc','application/vnd.sun.xml.calc', 'icons/oocalc.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('stc','application/vnd.sun.xml.calc.template', 'icons/oocalc.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxd','application/vnd.sun.xml.draw', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('std','application/vnd.sun.xml.draw.template', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxi','application/vnd.sun.xml.impress', 'icons/ooimpress.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sti','application/vnd.sun.xml.impress.template', 'icons/ooimpress.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxg','application/vnd.sun.xml.writer.global', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sxm','application/vnd.sun.xml.math', NULL);

INSERT INTO document_transaction_types_lookup (name) VALUES ("View");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Expunge");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Force CheckIn");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Email Link");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Collaboration Step Approve");

INSERT INTO archiving_type_lookup (name) VALUES ("Date");
INSERT INTO archiving_type_lookup (name) VALUES ("Utilisation");

INSERT INTO time_unit_lookup (name) VALUES ("Years");
INSERT INTO time_unit_lookup (name) VALUES ("Months");
INSERT INTO time_unit_lookup (name) VALUES ("Days");

INSERT INTO help VALUES (57,'standardSearch','standardSearchHelp.html');
INSERT INTO help VALUES (58,'modifyDocumentTypeMetaData','modifyDocumentTypeMetaDataHelp.html');
INSERT INTO help VALUES (59,'addDocField','addDocFieldHelp.html');
INSERT INTO help VALUES (60,'editDocField','editDocFieldHelp.html');
INSERT INTO help VALUES (61,'removeDocField','removeDocFieldHelp.html');
INSERT INTO help VALUES (62,'addMetaData','addMetaDataHelp.html');
INSERT INTO help VALUES (63,'editMetaData','editMetaDataHelp.html');
INSERT INTO help VALUES (64,'removeMetaData','removeMetaDataHelp.html');
INSERT INTO help VALUES (65,'addUser','addUserHelp.html');
INSERT INTO help VALUES (66,'editUser','editUserHelp.html');
INSERT INTO help VALUES (67,'removeUser','removeUserHelp.html');
INSERT INTO help VALUES (68,'addUserToGroup','addUserToGroupHelp.html');
INSERT INTO help VALUES (69,'removeUserFromGroup','removeUserFromGroupHelp.html');
INSERT INTO help VALUES (70,'viewDiscussion','viewDiscussionThread.html');
INSERT INTO help VALUES (71,'addComment','addDiscussionComment.html');
INSERT INTO help VALUES (72,'listNews','listDashboardNewsHelp.html');
INSERT INTO help VALUES (73,'editNews','editDashboardNewsHelp.html');
INSERT INTO help VALUES (74,'previewNews','previewDashboardNewsHelp.html');
INSERT INTO help VALUES (75,'addNews','addDashboardNewsHelp.html');
INSERT INTO help VALUES (76,'modifyDocumentArchiveSettings','modifyDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (77,'addDocumentArchiveSettings','addDocumentArchiveSettingsHelp.html');
INSERT INTO help VALUES (78,'listDocFields','listDocumentFieldsAdmin.html');
INSERT INTO help VALUES (79,'editDocFieldLookups','editDocFieldLookups.html');
INSERT INTO help VALUES (80,'addMetaDataForField','addMetaDataForField.html'); 
INSERT INTO help VALUES (81,'editMetaDataForField','editMetaDataForField.html'); 
INSERT INTO help VALUES (82,'removeMetaDataFromField','removeMetaDataFromField.html'); 
INSERT INTO help VALUES (83,'listDocs','listDocumentsCheckoutHelp.html'); 
INSERT INTO help VALUES (84,'editDocCheckout','editDocCheckoutHelp.html'); 
INSERT INTO help VALUES (85,'listDocTypes','listDocTypesHelp.html'); 
INSERT INTO help VALUES (86,'editDocTypeFields','editDocFieldHelp.html'); 
INSERT INTO help VALUES (87,'addDocTypeFieldsLink','addDocTypeFieldHelp.html'); 
INSERT INTO help VALUES (88,'listGroups','listGroupsHelp.html'); 
INSERT INTO help VALUES (89,'editGroupUnit','editGroupUnitHelp.html'); 
INSERT INTO help VALUES (90,'listOrg','listOrgHelp.html'); 
INSERT INTO help VALUES (91,'listRole','listRolesHelp.html'); 
INSERT INTO help VALUES (92,'listUnits','listUnitHelp.html'); 
INSERT INTO help VALUES (93,'editUnitOrg','editUnitOrgHelp.html'); 
INSERT INTO help VALUES (94,'removeUnitFromOrg','removeUnitFromOrgHelp.html'); 
INSERT INTO help VALUES (95,'addUnitToOrg','addUnitToOrgHelp.html'); 
INSERT INTO help VALUES (96,'listUsers','listUsersHelp.html'); 
INSERT INTO help VALUES (97,'editUserGroups','editUserGroupsHelp.html'); 
INSERT INTO help VALUES (98,'listWebsites','listWebsitesHelp.html'); 