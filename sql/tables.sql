CREATE TABLE active_sessions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER,
session_id CHAR(255),
lastused DATETIME,
ip CHAR(30)

);

CREATE TABLE document_fields ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
data_type CHAR(100) NOT NULL
);

CREATE TABLE document_fields_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
document_field_id INTEGER NOT NULL,
value CHAR(255) NOT NULL
);

CREATE TABLE document_transaction_types_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
);

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
);

CREATE TABLE document_type_fields_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_type_id INTEGER NOT NULL,
field_id INTEGER NOT NULL,
is_mandatory BOOL NOT NULL
);

CREATE TABLE document_types_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
);

CREATE TABLE document_words_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word_id INTEGER NOT NULL,
document_id INTEGER NOT NULL
);

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
is_checked_out BOOL NOT NULL
);

CREATE TABLE folders ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255),
description CHAR(255),
parent_id INTEGER,
creator_id INTEGER,
document_type_id INTEGER NOT NULL,
unit_id INTEGER,
is_public BOOL NOT NULL
);

CREATE TABLE folders_user_roles_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
role_type_id INTEGER NOT NULL,
datetime DATETIME,
done BOOL
) 
;


CREATE TABLE groups_folders_approval_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
folder_id INTEGER NOT NULL,
group_id INTEGER NOT NULL,
precedence INTEGER NOT NULL,
role_id INTEGER NOT NULL
);

CREATE TABLE groups_folders_link (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
group_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
can_read BIT NOT NULL,
can_write BIT NOT NULL
);

CREATE TABLE groups_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
);

CREATE TABLE groups_units_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
group_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL
);

CREATE TABLE links ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
url CHAR(100) NOT NULL,
rank INTEGER NOT NULL
);

CREATE TABLE mime_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
filetypes CHAR(100) NOT NULL,
mimetypes CHAR(100) NOT NULL
);

CREATE TABLE organisations_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
);

CREATE TABLE roles ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
can_read BOOL NOT NULL,
can_write BOOL NOT NULL
);

CREATE TABLE subscriptions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL
);

CREATE TABLE system_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
value CHAR(255) NOT NULL
);

CREATE TABLE units ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
organisation_id INTEGER NOT NULL,
parent_id INTEGER NOT NULL
);

CREATE TABLE users (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
username CHAR(255) NOT NULL,
name CHAR(255) NOT NULL,
password CHAR(255) NOT NULL,
quota_max INTEGER NOT NULL,
quota_current INTEGER NOT NULL,
email CHAR(255),
mobile CHAR(255),
email_notification BOOL NOT NULL,
sms_notification BOOL NOT NULL,
ldap_dn CHAR(255),
max_sessions INTEGER,
language CHAR(100)
) 
;

CREATE TABLE user_group_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
group_id INTEGER NOT NULL
) 
;



CREATE TABLE web_documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
web_site_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL,
status_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
);

CREATE TABLE web_documents_status_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(50) NOT NULL
);

CREATE TABLE web_sites ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
web_site_name CHAR(100) NOT NULL,
web_site_url CHAR(50) NOT NULL,
web_master_id INTEGER NOT NULL
);

CREATE TABLE words_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word CHAR(255) NOT NULL
);

ALTER TABLE active_sessions 
ADD CONSTRAINT PK_active_sessions 
PRIMARY KEY (id) 
;

ALTER TABLE document_fields 
ADD CONSTRAINT PK_fields 
PRIMARY KEY (id) 
;

ALTER TABLE document_fields_link 
ADD CONSTRAINT PK_document_type_fields_valookupes 
PRIMARY KEY (id) 
;

ALTER TABLE document_transaction_types_lookup 
ADD CONSTRAINT PK_document_transaction_types 
PRIMARY KEY (id) 
;

ALTER TABLE document_transactions 
ADD CONSTRAINT PK_document_transactions 
PRIMARY KEY (id) 
;

ALTER TABLE document_type_fields_link 
ADD CONSTRAINT PK_document_type_fields 
PRIMARY KEY (id) 
;

ALTER TABLE document_types_lookup 
ADD CONSTRAINT PK_document_types 
PRIMARY KEY (id) 
;

ALTER TABLE document_words_link 
ADD CONSTRAINT PK_document_words 
PRIMARY KEY (id) 
;

ALTER TABLE documents 
ADD CONSTRAINT PK_files 
PRIMARY KEY (id) 
;

ALTER TABLE folders_user_roles_link 
ADD CONSTRAINT PK_authors 
PRIMARY KEY (id) 
;

ALTER TABLE groups_folders_approval_link 
ADD CONSTRAINT PK_groups_folders_approval_link 
PRIMARY KEY (id) 
;

ALTER TABLE groups_lookup 
ADD CONSTRAINT PK_groups 
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
ADD CONSTRAINT PK_mimes 
PRIMARY KEY (id)
;

ALTER TABLE groups_folders_link 
ADD CONSTRAINT PK_groups_folders_link 
PRIMARY KEY (id)  
;

ALTER TABLE organisations_lookup 
ADD CONSTRAINT PK_organisations 
PRIMARY KEY (id) 
;

ALTER TABLE roles 
ADD CONSTRAINT PK_Editors 
PRIMARY KEY (id) 
;

ALTER TABLE subscriptions 
ADD CONSTRAINT PK_subscriptions 
PRIMARY KEY (id) 
;

ALTER TABLE system_settings 
ADD CONSTRAINT PK_system_settings 
PRIMARY KEY (id) 
;

ALTER TABLE units 
ADD CONSTRAINT PK_units 
PRIMARY KEY (id) 
;


ALTER TABLE users 
ADD CONSTRAINT PK_users 
PRIMARY KEY (id) 
;

ALTER TABLE web_documents 
ADD CONSTRAINT PK_web_documents 
PRIMARY KEY (id) 
;

ALTER TABLE web_documents_status_lookup 
ADD CONSTRAINT PK_web_documents_status 
PRIMARY KEY (id) 
;

ALTER TABLE web_sites 
ADD CONSTRAINT PK_web_sites 
PRIMARY KEY (id) 
;

ALTER TABLE words_lookup 
ADD CONSTRAINT PK_word_list 
PRIMARY KEY (id) 
;

-- insert into system_settings (these are from the old html table)
INSERT INTO system_settings (name, value) values ("table_border", "0");
INSERT INTO system_settings (name, value) values ("table_header_bg", "gray");
INSERT INTO system_settings (name, value) values ("table_cell_bg", "#FFCCCC");
INSERT INTO system_settings (name, value) values ("table_cell_bg_alt", "#CCCCFF");
INSERT INTO system_settings (name, value) values ("table_expand_width", "90%");
INSERT INTO system_settings (name, value) values ("table_collapse_width", "50%");
INSERT INTO system_settings (name, value) values ("body_bgcolor", "#FFEEDD");
INSERT INTO system_settings (name, value) values ("body_textcolor", "#000066");
INSERT INTO system_settings (name, value) values ("body_link", "#000000");
INSERT INTO system_settings (name, value) values ("body_vlink", "#000000");
INSERT INTO system_settings (name, value) values ("main_header_bgcolor", "#d0d0d0");

--INSERT INTO prefs (email_from, email_fromname,email_replyto,email_server, lookathd, def_file_security, def_file_group_owner, def_file_owner, def_file_title, def_file_meta, def_fold_security, def_fold_group_owner, def_fold_owner,max_filesize, timeout, expand, version_control, restrict_view, dbdump_path, gzip_path, tar_path) values ("owl@yourdomain.com", "OWL Intranet","noreply@yourdomain.com","localhost", "false", "0", "0", "1", "<font color=red>No Info</font>", "not in db", "50", "1", "0", "5120000", "900","1","1","0", "/usr/bin/mysqldump", "/bin/gzip", "/bin/tar");

INSERT INTO groups_lookup (name) VALUES ("System Administrators");
INSERT INTO groups_lookup (name) VALUES ("Unit Administrators");
INSERT INTO groups_lookup (name) VALUES ("Anonymous");

INSERT INTO organisations_lookup (name) VALUES ("Medical Research Council");

INSERT into units (name, organisation_id, parent_id) values ("Administration Unit", 1, 0);

INSERT INTO users (name, username, password, quota_max, quota_current, email, mobile, email_notification, sms_notification,ldap_dn, max_sessions) 
            VALUES ("Administrator", "admin", "admin", "0", "0", "", "", 1, 1, "", 0);
INSERT INTO users (name, username, password, quota_max, quota_current, email, mobile, email_notification, sms_notification,ldap_dn, max_sessions) 
            VALUES ("Anonymous", "guest", "guest", "0", "0", "", "", 0, 0, "", 19);
            
UPDATE users SET language = 'NewEnglish';
UPDATE users SET password = '21232f297a57a5a743894a0e4a801fc3' WHERE name = "Administrator";
UPDATE users SET password = '084e0343a0486ff05530df6c705c8bb4' WHERE name = "Anonymous";

INSERT INTO folders (name, description, parent_id, creator_id, document_type_id, unit_id, is_public)
             VALUES ("Documents", "Root Document Folder", 0, 0, 51, 0, 0);
             
INSERT INTO documents (name, filename, size, creator_id, modified, description, security, mime_id, folder_id, major_version, minor_version, is_checked_out) 
            VALUES ("Test File", "test.txt", "36", 0, "Dec 27th, 2000 at 05:17 pm", "", 0, 0, 0, 0, 1, 0);

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

INSERT INTO web_documents_status_lookup (name) VALUES ("Pending");
INSERT INTO web_documents_status_lookup (name) VALUES ("Published");