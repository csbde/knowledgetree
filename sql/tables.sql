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
modified DATE NOT NULL,
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


CREATE TABLE folder_doctypes_link (
  id int(11) NOT NULL auto_increment,
  folder_id int(11) NOT NULL default '0',
  document_type_id int(11) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=InnoDB;

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
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ai', 'application/postscript');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('aif', 'audio/x-aiff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('aifc', 'audio/x-aiff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('aiff', 'audio/x-aiff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('asc', 'text/plain');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('au', 'audio/basic');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('avi', 'video/x-msvideo');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('bcpio', 'application/x-bcpio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('bin', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('bmp', 'image/bmp');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('cdf', 'application/x-netcdf');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('class', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('cpio', 'application/x-cpio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('cpt', 'application/mac-compactpro');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('csh', 'application/x-csh');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('css', 'text/css');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('dcr', 'application/x-director');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('dir', 'application/x-director');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('dms', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('doc', 'application/msword');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('dvi', 'application/x-dvi');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('dxr', 'application/x-director');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('eps', 'application/postscript');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('etx', 'text/x-setext');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('exe', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ez', 'application/andrew-inset');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('gif', 'image/gif');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('gtar', 'application/x-gtar');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('hdf', 'application/x-hdf');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('hqx', 'application/mac-binhex40');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('htm', 'text/html');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('html', 'text/html');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ice', 'x-conference/x-cooltalk');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ief', 'image/ief');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('iges', 'model/iges');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('igs', 'model/iges');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('jpe', 'image/jpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('jpeg', 'image/jpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('jpg', 'image/jpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('js', 'application/x-javascript');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('kar', 'audio/midi');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('latex', 'application/x-latex');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('lha', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('lzh', 'application/octet-stream');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('man', 'application/x-troff-man');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('me', 'application/x-troff-me');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mesh', 'model/mesh');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mid', 'audio/midi');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('midi', 'audio/midi');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mif', 'application/vnd.mif');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mov', 'video/quicktime');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('movie', 'video/x-sgi-movie');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mp2', 'audio/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mp3', 'audio/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mpe', 'video/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mpeg', 'video/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mpg', 'video/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('mpga', 'audio/mpeg');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ms', 'application/x-troff-ms');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('msh', 'model/mesh');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('nc', 'application/x-netcdf');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('oda', 'application/oda');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pbm', 'image/x-portable-bitmap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pdb', 'chemical/x-pdb');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pdf', 'application/pdf');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pgm', 'image/x-portable-graymap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pgn', 'application/x-chess-pgn');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('png', 'image/png');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('pnm', 'image/x-portable-anymap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ppm', 'image/x-portable-pixmap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ppt', 'application/vnd.ms-powerpoint');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ps', 'application/postscript');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('qt', 'video/quicktime');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ra', 'audio/x-realaudio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ram', 'audio/x-pn-realaudio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ras', 'image/x-cmu-raster');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('rgb', 'image/x-rgb');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('rm', 'audio/x-pn-realaudio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('roff', 'application/x-troff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('rpm', 'audio/x-pn-realaudio-plugin');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('rtf', 'text/rtf');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('rtx', 'text/richtext');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sgm', 'text/sgml');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sgml', 'text/sgml');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sh', 'application/x-sh');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('shar', 'application/x-shar');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('silo', 'model/mesh');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sit', 'application/x-stuffit');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('skd', 'application/x-koan');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('skm', 'application/x-koan');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('skp', 'application/x-koan');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('skt', 'application/x-koan');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('smi', 'application/smil');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('smil', 'application/smil');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('snd', 'audio/basic');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('spl', 'application/x-futuresplash');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('src', 'application/x-wais-source');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sv4cpio', 'application/x-sv4cpio');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('sv4crc', 'application/x-sv4crc');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('swf', 'application/x-shockwave-flash');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('t', 'application/x-troff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tar', 'application/x-tar');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tcl', 'application/x-tcl');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tex', 'application/x-tex');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('texi', 'application/x-texinfo');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('texinfo', 'application/x-texinfo');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tif', 'image/tiff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tiff', 'image/tiff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tr', 'application/x-troff');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tsv', 'text/tab-separated-values');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('txt', 'text/plain');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('ustar', 'application/x-ustar');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('vcd', 'application/x-cdlink');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('vrml', 'model/vrml');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('wav', 'audio/x-wav');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('wrl', 'model/vrml');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xbm', 'image/x-xbitmap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xls', 'application/vnd.ms-excel');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xml', 'text/xml');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xpm', 'image/x-xpixmap');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xwd', 'image/x-xwindowdump');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('xyz', 'chemical/x-pdb');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('zip', 'application/zip');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('gz', 'application/x-gzip');
INSERT INTO mime_types (filetypes, mimetypes) VALUES ('tgz', 'application/x-gzip');

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
INSERT INTO help VALUES (1,'browse','dochelp.htm');
INSERT INTO help VALUES (2,'dashboard','dashboardHelp.htm');
INSERT INTO help VALUES (3,'addFolder','addFolderHelp.htm');
INSERT INTO help VALUES (4,'editFolder','editFolderHelp.htm');
INSERT INTO help VALUES (5,'addFolderCollaboration','addFolderCollaborationHelp.htm');
INSERT INTO help VALUES (6,'modifyFolderCollaboration','addFolderCollaborationHelp.htm');
INSERT INTO help VALUES (7,'addDocument','addDocumentHelp.htm');
INSERT INTO help VALUES (8,'viewDocument','viewDocumentHelp.htm');
INSERT INTO help VALUES (9,'modifyDocument','modifyDocumentHelp.htm');
INSERT INTO help VALUES (10,'modifyDocumentRouting','modifyDocumentRoutingHelp.htm');
INSERT INTO help VALUES (11,'emailDocument','emailDocumentHelp.htm');
INSERT INTO help VALUES (12,'deleteDocument','deleteDocumentHelp.htm');
INSERT INTO help VALUES (13,'administration','administrationHelp.htm');
INSERT INTO help VALUES (14,'addGroup','addGroupHelp.htm');
INSERT INTO help VALUES (15,'editGroup','editGroupHelp.htm');
INSERT INTO help VALUES (16,'removeGroup','removeGroupHelp.htm');
INSERT INTO help VALUES (17,'assignGroupToUnit','assignGroupToUnitHelp.htm');
INSERT INTO help VALUES (18,'removeGroupFromUnit','removeGroupFromUnitHelp.htm');
INSERT INTO help VALUES (19,'addUnit','addUnitHelp.htm');
INSERT INTO help VALUES (20,'editUnit','editUnitHelp.htm');
INSERT INTO help VALUES (21,'removeUnit','removeUnitHelp.htm');
INSERT INTO help VALUES (22,'addOrg','addOrgHelp.htm');
INSERT INTO help VALUES (23,'editOrg','editOrgHelp.htm');
INSERT INTO help VALUES (24,'removeOrg','removeOrgHelp.htm');
INSERT INTO help VALUES (25,'addRole','addRoleHelp.htm');
INSERT INTO help VALUES (26,'editRole','editRoleHelp.htm');
INSERT INTO help VALUES (27,'removeRole','removeRoleHelp.htm');
INSERT INTO help VALUES (28,'addLink','addLinkHelp.htm');
INSERT INTO help VALUES (29,'addLinkSuccess','addLinkHelp.htm');
INSERT INTO help VALUES (30,'editLink','editLinkHelp.htm');
INSERT INTO help VALUES (31,'removeLink','removeLinkHelp.htm');
INSERT INTO help VALUES (32,'systemAdministration','systemAdministrationHelp.htm');
INSERT INTO help VALUES (33,'deleteFolder','deleteFolderHelp.htm');
INSERT INTO help VALUES (34,'editDocType','editDocTypeHelp.htm');
INSERT INTO help VALUES (35,'removeDocType','removeDocTypeHelp.htm');

