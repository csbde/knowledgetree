-- table definitions
CREATE TABLE active_sessions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER,
session_id CHAR(255),
lastused DATETIME,
ip CHAR(30)
) TYPE = InnoDB;

CREATE TABLE document_fields( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
data_type CHAR(100) NOT NULL,
is_generic BIT,
has_lookup BIT
)TYPE = InnoDB;

CREATE TABLE data_types( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL
)TYPE = InnoDB;

CREATE TABLE document_fields_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
document_field_id INTEGER NOT NULL,
value CHAR(255) NOT NULL
)TYPE = InnoDB;


CREATE TABLE document_transaction_types_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
)TYPE = InnoDB;

CREATE TABLE document_transactions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
version CHAR(50),
user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL,
ip CHAR(30),
filename CHAR(255) NOT NULL,
comment CHAR(255) NOT NULL,
transaction_id INTEGER
)TYPE = InnoDB;

CREATE TABLE document_type_fields_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_type_id INTEGER NOT NULL,
field_id INTEGER NOT NULL,
is_mandatory BIT NOT NULL
)TYPE = InnoDB;

CREATE TABLE document_types_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
)TYPE = InnoDB;

CREATE TABLE document_words_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word_id INTEGER NOT NULL,
document_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_type_id INTEGER NOT NULL,
name CHAR(80) NOT NULL,
filename CHAR(50) NOT NULL,
size BIGINT NOT NULL,
creator_id INTEGER NOT NULL,
modified DATETIME NOT NULL,
description CHAR(200) NOT NULL,
security INTEGER NOT NULL,
mime_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
major_version INTEGER NOT NULL,
minor_version INTEGER NOT NULL,
is_checked_out BIT NOT NULL,
parent_folder_ids TEXT,
full_path TEXT,
checked_out_user_id INTEGER
)TYPE = InnoDB;

CREATE TABLE document_subscriptions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL,
is_alerted BIT
)TYPE = InnoDB;

CREATE TABLE folders ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255),
description CHAR(255),
parent_id INTEGER,
creator_id INTEGER,
unit_id INTEGER,
is_public BIT NOT NULL,
parent_folder_ids TEXT,
full_path TEXT
)TYPE = InnoDB;

CREATE TABLE folder_subscriptions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
is_alerted BIT
)TYPE = InnoDB;

CREATE TABLE folders_users_roles_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
group_folder_approval_id INTEGER NOT NULL,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL,
datetime DATETIME,
done BIT,
active BIT
)TYPE = InnoDB;


CREATE TABLE folder_doctypes_link (
id int(11) NOT NULL auto_increment,
folder_id int(11) NOT NULL default '0',
document_type_id int(11) NOT NULL default '0',
UNIQUE KEY id (id)
) TYPE=InnoDB;

CREATE TABLE groups_folders_approval_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
folder_id INTEGER NOT NULL,
group_id INTEGER NOT NULL,
precedence INTEGER NOT NULL,
role_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE groups_folders_link (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
group_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
can_read BIT NOT NULL,
can_write BIT NOT NULL
)TYPE = InnoDB;

CREATE TABLE groups_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
is_sys_admin BIT NOT NULL,
is_unit_admin BIT NOT NULL
)TYPE = InnoDB;

CREATE TABLE groups_units_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
group_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE help (
  id int(11) NOT NULL auto_increment,
  fSection varchar(100) NOT NULL default '',
  help_info text NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY id (id)
) TYPE=InnoDB;

CREATE TABLE links ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
url CHAR(100) NOT NULL,
rank INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE language_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
)TYPE = InnoDB;

CREATE TABLE mime_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
filetypes CHAR(100) NOT NULL,
mimetypes CHAR(100) NOT NULL,
icon_path CHAR(255) 
)TYPE = InnoDB;

CREATE TABLE organisations_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
)TYPE = InnoDB;

CREATE TABLE roles ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
active BIT NOT NULL,
can_read BIT NOT NULL,
can_write BIT NOT NULL
)TYPE = InnoDB;

-- sitemap tables
CREATE TABLE site_sections_lookup (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255)
)TYPE = InnoDB;

CREATE TABLE site_access_lookup (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255)
)TYPE = InnoDB;

CREATE TABLE sitemap (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
action CHAR(50),
page CHAR(255),
section_id INTEGER,
access_id INTEGER,
link_text CHAR(255),
is_default BIT,
is_enabled BIT DEFAULT 1
)TYPE = InnoDB;

CREATE TABLE system_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
value CHAR(255) NOT NULL
)TYPE = InnoDB;

CREATE TABLE sys_deleted ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
table_name CHAR(255) NOT NULL,
datetime DATETIME NOT NULL
)TYPE = InnoDB;

CREATE TABLE units_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
)TYPE = InnoDB;

CREATE TABLE units_organisations_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
unit_id INTEGER NOT NULL,
organisation_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE users (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
username CHAR(255) NOT NULL,
name CHAR(255) NOT NULL,
password CHAR(255) NOT NULL,
quota_max INTEGER NOT NULL,
quota_current INTEGER NOT NULL,
email CHAR(255),
mobile CHAR(255),
email_notification BIT NOT NULL,
sms_notification BIT NOT NULL,
ldap_dn CHAR(255),
max_sessions INTEGER,
language_id INTEGER
) 
TYPE = InnoDB;

CREATE TABLE users_groups_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
group_id INTEGER NOT NULL
) 
TYPE = InnoDB;

CREATE TABLE web_documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
web_site_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL,
status_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
)TYPE = InnoDB;

CREATE TABLE web_documents_status_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(50) NOT NULL
)TYPE = InnoDB;



CREATE TABLE web_sites ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
web_site_name CHAR(100) NOT NULL,
web_site_url CHAR(50) NOT NULL,
web_master_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE words_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word CHAR(255) NOT NULL
)TYPE = InnoDB;

-- pk constraints
ALTER TABLE active_sessions
ADD CONSTRAINT PK_active_sessions
PRIMARY KEY (id)
;

ALTER TABLE folder_doctypes_link
ADD CONSTRAINT PK_folder_doctypes_link
PRIMARY KEY (id)
;

ALTER TABLE document_fields
ADD CONSTRAINT PK_document_fields
PRIMARY KEY (id)
;

ALTER TABLE document_fields_link
ADD CONSTRAINT PK_document_fields_link
PRIMARY KEY (id)
;

ALTER TABLE document_transaction_types_lookup
ADD CONSTRAINT PK_document_transaction_types_lookup
PRIMARY KEY (id)
;

ALTER TABLE document_transactions
ADD CONSTRAINT PK_document_transactions
PRIMARY KEY (id)
;

ALTER TABLE document_type_fields_link
ADD CONSTRAINT PK_document_type_fields_link
PRIMARY KEY (id)
;

ALTER TABLE document_types_lookup
ADD CONSTRAINT PK_document_types_lookup
PRIMARY KEY (id)
;

ALTER TABLE document_words_link
ADD CONSTRAINT PK_document_words_link
PRIMARY KEY (id)
;

ALTER TABLE documents
ADD CONSTRAINT PK_documents
PRIMARY KEY (id)
;

ALTER TABLE document_subscriptions
ADD CONSTRAINT PK_document_subscriptions
PRIMARY KEY (id)
;

ALTER TABLE folders
ADD CONSTRAINT PK_folders
PRIMARY KEY (id)
;

ALTER TABLE folder_subscriptions
ADD CONSTRAINT PK_folder_subscriptions
PRIMARY KEY (id)
;

ALTER TABLE folders_users_roles_link
ADD CONSTRAINT PK_folders_users_roles_link
PRIMARY KEY (id)
;

ALTER TABLE groups_folders_approval_link
ADD CONSTRAINT PK_groups_folders_approval_link
PRIMARY KEY (id)
;

ALTER TABLE groups_lookup
ADD CONSTRAINT PK_groups_lookup
PRIMARY KEY (id)
;

ALTER TABLE language_lookup
ADD CONSTRAINT PK_language_lookup
PRIMARY KEY (id)
;

ALTER TABLE groups_units_link
ADD CONSTRAINT PK_groups_units_link
PRIMARY KEY (id)
;

ALTER TABLE links
ADD CONSTRAINT PK_links
PRIMARY KEY (id)
;

ALTER TABLE mime_types
ADD CONSTRAINT PK_mimes_types
PRIMARY KEY (id)
;

ALTER TABLE groups_folders_link
ADD CONSTRAINT PK_groups_folders_link
PRIMARY KEY (id)
;

ALTER TABLE organisations_lookup
ADD CONSTRAINT PK_organisations_lookup
PRIMARY KEY (id)
;

ALTER TABLE roles
ADD CONSTRAINT PK_roles
PRIMARY KEY (id)
;

ALTER TABLE site_sections_lookup
ADD CONSTRAINT PK_site_sections_lookup
PRIMARY KEY (id)
;

ALTER TABLE site_access_lookup
ADD CONSTRAINT PK_site_access_lookup
PRIMARY KEY (id)
;

ALTER TABLE sitemap
ADD CONSTRAINT PK_sitemap
PRIMARY KEY (id)
;

ALTER TABLE system_settings
ADD CONSTRAINT PK_system_settings
PRIMARY KEY (id)
;

ALTER TABLE sys_deleted
ADD CONSTRAINT PK_sys_deleted
PRIMARY KEY (id)
;

ALTER TABLE units_lookup
ADD CONSTRAINT PK_units_lookup
PRIMARY KEY (id)
;

ALTER TABLE units_organisations_link
ADD CONSTRAINT PK_units_organisations_link
PRIMARY KEY (id)
;

ALTER TABLE users
ADD CONSTRAINT PK_users
PRIMARY KEY (id)
;

ALTER TABLE users_groups_link
ADD CONSTRAINT PK_users_groups_link
PRIMARY KEY (id)
;

ALTER TABLE web_documents
ADD CONSTRAINT PK_web_documents
PRIMARY KEY (id)
;

ALTER TABLE web_documents_status_lookup
ADD CONSTRAINT PK_web_documents_status
PRIMARY KEY (id);

ALTER TABLE web_sites
ADD CONSTRAINT PK_web_sites
PRIMARY KEY (id);

ALTER TABLE words_lookup
ADD CONSTRAINT PK_word_list
PRIMARY KEY (id);

-- mime types
-- TODO: add icon paths to inserts
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ai', 'application/postscript', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('aif', 'audio/x-aiff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('aifc', 'audio/x-aiff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('aiff', 'audio/x-aiff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('asc', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('au', 'audio/basic', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('avi', 'video/x-msvideo', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('bcpio', 'application/x-bcpio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('bin', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('bmp', 'image/bmp', 'icons/bmp.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('cdf', 'application/x-netcdf', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('class', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('cpio', 'application/x-cpio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('cpt', 'application/mac-compactpro', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('csh', 'application/x-csh', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('css', 'text/css', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('dcr', 'application/x-director', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('dir', 'application/x-director', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('dms', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('doc', 'application/msword', 'icons/word.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('dvi', 'application/x-dvi', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('dxr', 'application/x-director', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('eps', 'application/postscript', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('etx', 'text/x-setext', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('exe', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ez', 'application/andrew-inset', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('gif', 'image/gif', 'icons/gif.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('gtar', 'application/x-gtar', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('hdf', 'application/x-hdf', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('hqx', 'application/mac-binhex40', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('htm', 'text/html', 'icons/html.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('html', 'text/html', 'icons/html.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ice', 'x-conference/x-cooltalk', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ief', 'image/ief', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('iges', 'model/iges', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('igs', 'model/iges', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('jpe', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('jpeg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('jpg', 'image/jpeg', 'icons/jpg.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('js', 'application/x-javascript', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('kar', 'audio/midi', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('latex', 'application/x-latex', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('lha', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('lzh', 'application/octet-stream', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('man', 'application/x-troff-man', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mdb', 'application/access','icons/access.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mdf', 'application/access','icons/access.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('me', 'application/x-troff-me', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mesh', 'model/mesh', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mid', 'audio/midi', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('midi', 'audio/midi', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mif', 'application/vnd.mif', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mov', 'video/quicktime', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('movie', 'video/x-sgi-movie', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mp2', 'audio/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mp3', 'audio/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mpe', 'video/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mpeg', 'video/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mpg', 'video/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mpga', 'audio/mpeg', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('mpp', 'application/vnd.ms-project', 'icons/project.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ms', 'application/x-troff-ms', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('msh', 'model/mesh', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('nc', 'application/x-netcdf', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('oda', 'application/oda', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pbm', 'image/x-portable-bitmap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pdb', 'chemical/x-pdb', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pdf', 'application/pdf', 'icons/pdf.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pgm', 'image/x-portable-graymap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pgn', 'application/x-chess-pgn', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('png', 'image/png', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('pnm', 'image/x-portable-anymap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ppm', 'image/x-portable-pixmap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ppt', 'application/vnd.ms-powerpoint', 'icons/powerp.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ps', 'application/postscript', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('qt', 'video/quicktime', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ra', 'audio/x-realaudio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ram', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ras', 'image/x-cmu-raster', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('rgb', 'image/x-rgb', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('rm', 'audio/x-pn-realaudio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('roff', 'application/x-troff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('rpm', 'audio/x-pn-realaudio-plugin', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('rtf', 'text/rtf', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('rtx', 'text/richtext', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sgm', 'text/sgml', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sgml', 'text/sgml', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sh', 'application/x-sh', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('shar', 'application/x-shar', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('silo', 'model/mesh', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sit', 'application/x-stuffit', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('skd', 'application/x-koan', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('skm', 'application/x-koan', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('skp', 'application/x-koan', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('skt', 'application/x-koan', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('smi', 'application/smil', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('smil', 'application/smil', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('snd', 'audio/basic', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('spl', 'application/x-futuresplash', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('src', 'application/x-wais-source', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sv4cpio', 'application/x-sv4cpio', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('sv4crc', 'application/x-sv4crc', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('swf', 'application/x-shockwave-flash', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('t', 'application/x-troff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tar', 'application/x-tar', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tcl', 'application/x-tcl', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tex', 'application/x-tex', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('texi', 'application/x-texinfo', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('texinfo', 'application/x-texinfo', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tif', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tiff', 'image/tiff', 'icons/tiff.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tr', 'application/x-troff', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tsv', 'text/tab-separated-values', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('txt', 'text/plain', 'icons/txt.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('ustar', 'application/x-ustar', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('vcd', 'application/x-cdlink', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('vrml', 'model/vrml', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('vsd', 'application/vnd.visio', 'icons/visio.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('wav', 'audio/x-wav', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('wrl', 'model/vrml', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xbm', 'image/x-xbitmap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xls', 'application/vnd.ms-excel', 'icons/excel.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xml', 'text/xml', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xpm', 'image/x-xpixmap', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xwd', 'image/x-xwindowdump', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('xyz', 'chemical/x-pdb', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('zip', 'application/zip', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('gz', 'application/x-gzip', NULL);
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tgz', 'application/x-gzip', NULL);

-- data_types
insert into data_types (name) values ('STRING');
insert into data_types (name) values ('CHAR');
insert into data_types (name) values ('TEXT');
insert into data_types (name) values ('INT');
insert into data_types (name) values ('FLOAT');


-- supported languages (not really ;)
INSERT INTO language_lookup (name) VALUES ("English");
INSERT INTO language_lookup (name) VALUES ("Chinese");
INSERT INTO language_lookup (name) VALUES ("Danish");
INSERT INTO language_lookup (name) VALUES ("Deutsch");
INSERT INTO language_lookup (name) VALUES ("Dutch");
INSERT INTO language_lookup (name) VALUES ("Francais");
INSERT INTO language_lookup (name) VALUES ("Hungarian");
INSERT INTO language_lookup (name) VALUES ("Italian");
INSERT INTO language_lookup (name) VALUES ("Norwegian");
INSERT INTO language_lookup (name) VALUES ("Portuguese");
INSERT INTO language_lookup (name) VALUES ("Spanish");

---- system settings
-- ldap
INSERT INTO system_settings (name, value) values ("ldapServer", "192.168.1.9");
INSERT INTO system_settings (name, value) values ("ldapRootDn", "o=Medical Research Council");
-- email settings
INSERT INTO system_settings (name, value) values ("emailServer", "mail.jamwarehouse.com");
INSERT INTO system_settings (name, value) values ("emailFrom", "owl@jamwarehouse.com");
INSERT INTO system_settings (name, value) values ("emailFromName", "MRC Document Management System");
-- directories
INSERT INTO system_settings (name, value) values ("filesystemRoot", "/usr/local/www/owl/dms");
INSERT INTO system_settings (name, value) values ("documentRoot", "/usr/local/www/owl/dms/Documents");
INSERT INTO system_settings (name, value) values ("languageDirectory", "/usr/local/www/owl/dms/locale");
INSERT INTO system_settings (name, value) values ("uiDirectory", "/usr/local/www/owl/dms/presentation/lookAndFeel/knowledgeTree");
-- urls
INSERT INTO system_settings (name, value) values ("rootUrl", "/dms");
INSERT INTO system_settings (name, value) values ("graphicsUrl", "/dms/graphics");
INSERT INTO system_settings (name, value) values ("uiUrl", "/dms/presentation/lookAndFeel/knowledgeTree");
-- general settings
INSERT INTO system_settings (name, value) values ("useFs", "true");
INSERT INTO system_settings (name, value) values ("defaultLanguage", "NewEnglish");
--INSERT INTO system_settings (name, value) values ("notificationLink", "http://$_SERVER[SERVER_NAME]$default->rootUrl/");
INSERT INTO system_settings (name, value) values ("sessionTimeout", "1200");


-- document statuses
INSERT INTO web_documents_status_lookup (name) VALUES ("Pending");
INSERT INTO web_documents_status_lookup (name) VALUES ("Published");
INSERT INTO web_documents_status_lookup (name) VALUES ("Not Published");

-- document transaction types
INSERT INTO document_transaction_types_lookup (name) VALUES ("Create");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Update");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Delete");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Rename");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Move");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Download");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Check In");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Check Out");

-- roles
INSERT INTO roles (name, active, can_read, can_write) VALUES ('Editor', 1, 1, 1);
INSERT INTO roles (name, active, can_read, can_write) VALUES ('Spell Checker', 1, 1, 0);
INSERT INTO roles (name, active, can_read, can_write) VALUES ('Web Publisher', 1, 1, 0);

-- mrc organisation
INSERT INTO organisations_lookup (name) VALUES ("Medical Research Council");

-- setup mrc units
INSERT INTO units_lookup (name) VALUES ("ADARG"); -- id=1
INSERT INTO units_lookup (name) VALUES ("AfroAids"); -- id=2
INSERT INTO units_lookup (name) VALUES ("Diabetes"); -- id=3
INSERT INTO units_lookup (name) VALUES ("Burden of Disease"); -- id=4

INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (1, 1); -- id=1
INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (2, 1); -- id=2
INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (3, 1); -- id=3
INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (4, 1); -- id=4

-- setup groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("System Administrators", 1, 0); -- id=1
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("ADARG Unit Administrators", 0, 1); -- id=2
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("AfroAids Unit Administrators", 0, 1); -- id=3
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Diabetes Unit Administrators", 0, 1); -- id=4
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Burden of Disease Unit Administrators", 0, 1); -- id=5
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Anonymous", 0, 0); -- id=6
-- adarg unit groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Spell Checkers", 0, 0); -- id=7
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Editors", 0, 0); -- id=8
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Finance", 0, 0); -- id=9
-- afroaids unit groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Spell Checkers", 0, 0); -- id=10
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Editors", 0, 0); -- id=11
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Finance", 0, 0); -- id=12
-- diabetes unit groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Spell Checkers", 0, 0); -- id=13
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Editors", 0, 0); -- id=14
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Finance", 0, 0); -- id=15
-- disease unit groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Spell Checkers", 0, 0); -- id=16
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Editors", 0, 0); -- id=17
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Finance", 0, 0); -- id=18

------ map groups to units
---- adarg
-- administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (2, 1);
-- other groups
INSERT INTO groups_units_link (group_id, unit_id) VALUES (7, 1);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (8, 1);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (9, 1);

---- afroaids
-- administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (3, 2);
-- other groups
INSERT INTO groups_units_link (group_id, unit_id) VALUES (10, 2);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (11, 2);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (12, 2);

---- diabetes
-- administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (4, 3);
-- other groups
INSERT INTO groups_units_link (group_id, unit_id) VALUES (13, 3);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (14, 3);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (15, 3);

---- disease
-- administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (5, 4);
-- other groups
INSERT INTO groups_units_link (group_id, unit_id) VALUES (16, 4);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (17, 4);
INSERT INTO groups_units_link (group_id, unit_id) VALUES (18, 4);


------ users & map users to groups
---- system administrator
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("admin", "Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (1, 1);
            
---- guest user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("guest", "Anonymous", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 19, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (6, 2);
            
---- unit administrators
-- adarg
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("adargAdmin", "ADARG Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 0, 0, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (2, 3);

-- afroaids
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("afroaidsAdmin", "afroAIDS Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 0, 0, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (3, 4);

-- diabetes
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("diabetesAdmin", "Diabetes Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 0, 0, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (4, 5);    
        
-- disease
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("diseaseAdmin", "Burden of Disease Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 0, 0, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (5, 6);

---- unit users
-- adarg unit user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("adargUser", "adargUser", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 1, 1);
-- spell checker and editor
INSERT INTO users_groups_link (group_id, user_id) VALUES (7, 7);
INSERT INTO users_groups_link (group_id, user_id) VALUES (8, 7);

-- afroaids unit user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("afroaidsUser", "afroaidsUser", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 1, 1);
-- just spell checker
INSERT INTO users_groups_link (group_id, user_id) VALUES (10, 8);

-- diabetes unit user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("diabetesUser", "diabetesUser", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 1, 1);
-- editor and finance
INSERT INTO users_groups_link (group_id, user_id) VALUES (14, 9);
INSERT INTO users_groups_link (group_id, user_id) VALUES (15, 9);

-- disease unit user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("diseaseUser", "diseaseUser", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 1, 1);
-- spell checker, editor and finance
INSERT INTO users_groups_link (group_id, user_id) VALUES (16, 10);
INSERT INTO users_groups_link (group_id, user_id) VALUES (17, 10);
INSERT INTO users_groups_link (group_id, user_id) VALUES (18, 10);

-- default document type fields
INSERT INTO document_fields (name, data_type, is_generic) VALUES ("Category", "String", 1);
INSERT INTO document_fields (name, data_type, is_generic) VALUES ("Keywords", "String", 1);
INSERT INTO document_fields (name, data_type, is_generic) VALUES ("Comments", "String", 1);
INSERT INTO document_fields (name, data_type, is_generic) VALUES ("Author(s)", "String", 1);

-- default document types
INSERT INTO document_types_lookup (name) VALUES ("Admin");
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (1, 1, 1);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (1, 2, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (1, 3, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (1, 4, 1);

INSERT INTO document_types_lookup (name) VALUES ("Proposal");
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (2, 1, 1);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (2, 2, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (2, 3, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (2, 4, 1);

INSERT INTO document_types_lookup (name) VALUES ("Publications");
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (3, 1, 1);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (3, 2, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (3, 3, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (3, 4, 1);

INSERT INTO document_types_lookup (name) VALUES ("Research");
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (4, 1, 1);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (4, 2, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (4, 3, 0);
INSERT INTO document_type_fields_link (document_type_id, field_id, is_mandatory) VALUES (4, 4, 1);


-- define folder structure
---- mrc organisation root folder
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("Medical Research Council", "MRC Root Document Folder", 0, 1, 0, 0); -- id=1

---- adarg unit folders
-- [7,8,9]
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("ADARG", "ADARG Unit Root Folder", 1, 1, 1, 0);  -- id=2
             -- unit admins have write access
             INSERT INTO groups_folders_link (group_id, folder_id, can_read, can_write) VALUES (2, 2, 0, 1);
                 
---- afroaids unit folders
-- [10,11,12]
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("AfroAIDS", "AfroAIDS Unit Root Folder", 1, 1, 2, 0); -- id=3
             -- unit admins have write access
             INSERT INTO groups_folders_link (group_id, folder_id, can_read, can_write) VALUES (3, 3, 0, 1);             
             
---- diabetes unit folders
--[13,14,15]
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("Diabetes", "Diabetes Unit Root Folder", 1, 1, 3, 0);  -- id=4
             -- unit admins have write access
             INSERT INTO groups_folders_link (group_id, folder_id, can_read, can_write) VALUES (4, 4, 0, 1);
             
---- burden of disease unit folders
-- [16,17,18]
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("Burden of Disease", "Burden of Disease Unit Root Folder", 1, 1, 4, 0);  -- id=5
             -- unit admins have write access
             INSERT INTO groups_folders_link (group_id, folder_id, can_read, can_write) VALUES (5, 5, 0, 1);

-- link the folders to document types
INSERT INTO folder_doctypes_link (document_type_id, folder_id)
	SELECT	F.id, DTL.id
	FROM	folders AS F, document_types_lookup AS DTL;
             
             
-- TODO: populate categories_lookup

-- sitemap sections
INSERT INTO site_sections_lookup (name) VALUES ("General");
INSERT INTO site_sections_lookup (name) VALUES ("Manage Documents");
INSERT INTO site_sections_lookup (name) VALUES ("Administration");
INSERT INTO site_sections_lookup (name) VALUES ("Advanced Search");
INSERT INTO site_sections_lookup (name) VALUES ("Preferences");
INSERT INTO site_sections_lookup (name) VALUES ("Help");
INSERT INTO site_sections_lookup (name) VALUES ("Logout");
INSERT INTO site_sections_lookup (name) VALUES ("Tests");
-- sitemap access levels
INSERT INTO site_access_lookup (name) VALUES ("None");
INSERT INTO site_access_lookup (name) VALUES ("Guest");
INSERT INTO site_access_lookup (name) VALUES ("User");
INSERT INTO site_access_lookup (name) VALUES ("UnitAdmin");
INSERT INTO site_access_lookup (name) VALUES ("SysAdmin");
---- sitemap definition
-- general section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("login", "/presentation/login.php?loginAction=login", 1, 0, "", 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("loginForm", "/presentation/login.php?loginAction=loginForm", 1, 0, "login", 0); 
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("dashboard", "/presentation/dashboardBL.php", 1, 1, "dashboard", 0);
-- manage documents section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("browse", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php", 2, 2, "browse documents", 1);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("viewDocument", "/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php", 2, 2, "", 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("addDocument", "/presentation/documentmanagement/addDocument.php", 2, 3, "Add A Document", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("addFolder", "/presentation/documentmanagement/addFolder.php", 2, 4, "Add A Folder", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("modifyFolderProperties", "/presentation/documentmanagement/modifyFolder.php", 2, 4, "Modify Folder Properties", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("deleteFolder", "/presentation/documentmanagement/deleteFolder.php", 2, 4, "Delete A Folder", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("moveFolder", "/presentation/documentmanagement/moveFolder.php", 2, 4, "Move A Folder", 0, 0);
-- pages for administration section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("administration", "/admin.php", 3, 4, "Administration", 1, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("unitAdministration", "/presentation/unitAdmin.php", 3, 4, "Unit Administration", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("systemAdministration", "/presentation/sysAdmin.php", 3, 5, "System Administration", 0, 0);
-- pages for advanced search section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("advancedSearch", "/search.php", 4, 2, "Advanced Search", 1, 0);
-- pages for prefs section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("preferences", "/preferences.php", 5, 3, "Preferences", 1, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("viewPreferences", "/preferences.php", 5, 3, "View Preferences", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("editPreferences", "/preferences.php", 5, 3, "Edit Preferences", 0, 0);
-- pages for Help section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("help", "/help.php", 6, 2, "Help", 1);
-- pages for logout section section
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default) VALUES ("logout", "/presentation/logout.php", 7, 2, "Logout", 1);
-- test pages
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("scratchPad", "/tests/scratchPad.php", 8, 2, "scratch", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("sitemap", "/tests/session/SiteMap.php", 8, 2, "sitemap", 0, 0);
INSERT INTO sitemap (action, page, section_id, access_id, link_text, is_default, is_enabled) VALUES ("documentBrowserTest", "/tests/documentmanagement/DocumentBrowser.php", 8, 2, "test the document browser", 0, 0);
-- help pages
INSERT INTO help VALUES (1,'browse','dochelp.html');
INSERT INTO help VALUES (2,'dashboard','dashboardHelp.html');
INSERT INTO help VALUES (3,'addFolder','addFolderHelp.html');
INSERT INTO help VALUES (4,'editFolder','editFolderHelp.html');
INSERT INTO help VALUES (5,'addFolderCollaboration','addFolderCollaborationHelp.html');
INSERT INTO help VALUES (6,'modifyFolderCollaboration','addFolderCollaborationHelp.html');
INSERT INTO help VALUES (7,'addDocument','addDocumentHelp.html');
INSERT INTO help VALUES (8,'viewDocument','viewDocumentHelp.html');
INSERT INTO help VALUES (9,'modifyDocument','modifyDocumentHelp.html');
INSERT INTO help VALUES (10,'modifyDocumentRouting','modifyDocumentRoutingHelp.html');
INSERT INTO help VALUES (11,'emailDocument','emailDocumentHelp.html');
INSERT INTO help VALUES (12,'deleteDocument','deleteDocumentHelp.html');
INSERT INTO help VALUES (13,'administration','administrationHelp.html');
INSERT INTO help VALUES (14,'addGroup','addGroupHelp.html');
INSERT INTO help VALUES (15,'editGroup','editGroupHelp.html');
INSERT INTO help VALUES (16,'removeGroup','removeGroupHelp.html');
INSERT INTO help VALUES (17,'assignGroupToUnit','assignGroupToUnitHelp.html');
INSERT INTO help VALUES (18,'removeGroupFromUnit','removeGroupFromUnitHelp.html');
INSERT INTO help VALUES (19,'addUnit','addUnitHelp.html');
INSERT INTO help VALUES (20,'editUnit','editUnitHelp.html');
INSERT INTO help VALUES (21,'removeUnit','removeUnitHelp.html');
INSERT INTO help VALUES (22,'addOrg','addOrgHelp.html');
INSERT INTO help VALUES (23,'editOrg','editOrgHelp.html');
INSERT INTO help VALUES (24,'removeOrg','removeOrgHelp.html');
INSERT INTO help VALUES (25,'addRole','addRoleHelp.html');
INSERT INTO help VALUES (26,'editRole','editRoleHelp.html');
INSERT INTO help VALUES (27,'removeRole','removeRoleHelp.html');
INSERT INTO help VALUES (28,'addLink','addLinkHelp.html');
INSERT INTO help VALUES (29,'addLinkSuccess','addLinkHelp.html');
INSERT INTO help VALUES (30,'editLink','editLinkHelp.html');
INSERT INTO help VALUES (31,'removeLink','removeLinkHelp.html');
INSERT INTO help VALUES (32,'systemAdministration','systemAdministrationHelp.html');
INSERT INTO help VALUES (33,'deleteFolder','deleteFolderHelp.html');
INSERT INTO help VALUES (34,'editDocType','editDocTypeHelp.html');
INSERT INTO help VALUES (35,'removeDocType','removeDocTypeHelp.html');
INSERT INTO help VALUES (36,'addDocType','addDocTypeHelp.html');
INSERT INTO help VALUES (37,'addDocTypeSuccess','addDocTypeHelp.html');
INSERT INTO help VALUES (38,'manageSubscriptions','manageSubscriptionsHelp.html');
INSERT INTO help VALUES (39,'addSubscription','addSubscriptionHelp.html');
INSERT INTO help VALUES (40,'removeSubscription','removeSubscriptionHelp.html');
INSERT INTO help VALUES (41,'preferences','preferencesHelp.html');
INSERT INTO help VALUES (42,'editPrefsSuccess','preferencesHelp.html');
