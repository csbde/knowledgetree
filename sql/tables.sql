CREATE TABLE active_sessions ( 
id INTEGER NOT NULL,
user_id INTEGER,
lastused DATETIME,
ip CHAR(30),
PRIMARY KEY (id)
); 

CREATE TABLE document_transaction_types ( 
id INTEGER NOT NULL AUTO_INCREMENT,
name CHAR(100) NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE document_transactions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
version CHAR(50),
user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL,
ip CHAR(30),
filename CHAR(100) NOT NULL,
comment CHAR(100) NOT NULL,
transaction_id INTEGER,
PRIMARY KEY (id)
); 

CREATE TABLE document_type_fields ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_type_id INTEGER NOT NULL,
field_id INTEGER NOT NULL,
is_mandatory BOOL NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE document_type_fields_values ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
document_type_field_id INTEGER NOT NULL,
value CHAR(255) NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE document_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100),
PRIMARY KEY (id)
); 

CREATE TABLE document_words ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word_id INTEGER NOT NULL,
document_id INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(80) NOT NULL,
filename CHAR(50) NOT NULL,
size BIGINT NOT NULL,
creator_id INTEGER NOT NULL,
parent_id INTEGER NOT NULL,
modified DATE NOT NULL,
description CHAR(200) NOT NULL,
security INTEGER NOT NULL,
mime_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
major_version INTEGER NOT NULL,
minor_version INTEGER NOT NULL,
is_checked_out BOOL NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE document_fields ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
data_type CHAR(100) NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE folder_user_role_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE folders ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100),
description CHAR(100),
parent_id INTEGER,
creator_id INTEGER,
document_type_id INTEGER NOT NULL,
unit_id INTEGER,
is_public BOOL NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE folders_user_roles ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
role_type_id INTEGER NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE groups ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE links ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
url CHAR(100) NOT NULL,
rank INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE membergroup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
group_id INTEGER NOT NULL,
PRIMARY KEY (id)
);

CREATE TABLE mimes ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
filetypes CHAR(100) NOT NULL,
mimetypes CHAR(100) NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE organisations ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE subscriptions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE system_settings ( 
id INTEGER NOT NULL AUTO_INCREMENT,
name CHAR(100) NOT NULL,
value CHAR(255) NOT NULL,
PRIMARY KEY(id)
); 

CREATE TABLE units ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL,
organisation_id INTEGER NOT NULL,
parent_id INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE users ( 
id INTEGER AUTO_INCREMENT NOT NULL UNIQUE AUTO_INCREMENT,
group_id INTEGER NOT NULL,
username CHAR(100) NOT NULL,
name CHAR(100) NOT NULL,
password CHAR(100) NOT NULL,
quota_max INTEGER NOT NULL,
quota_current INTEGER NOT NULL,
email CHAR(100),
mobile CHAR(30),
email_notification BOOL NOT NULL,
sms_notification BOOL NOT NULL,
ldap_dn CHAR(255) NOT NULL,
max_sessions INTEGER(4) DEFAULT 0,
language CHAR(30),
PRIMARY KEY (id)
); 

CREATE TABLE users_unit ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE web_documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
web_site_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL,
status_id INTEGER NOT NULL,
datetime DATETIME NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE web_documents_status ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(50) NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE web_sites ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
web_site_name CHAR(100) NOT NULL,
web_site_url CHAR(50) NOT NULL,
web_master_id INTEGER NOT NULL,
PRIMARY KEY (id)
); 

CREATE TABLE words ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
word CHAR(100) NOT NULL,
PRIMARY KEY (id)
); 

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

INSERT INTO groups (name) VALUES ("System Administrators");
INSERT INTO groups (name) VALUES ("Unit Administrators");
INSERT INTO groups (name) VALUES ("Unit User");
INSERT INTO groups (name) VALUES ("Anonymous");

INSERT INTO organisations (name) VALUES ("Medical Research Council");

INSERT into units (name, organisation_id, parent_id) values ("Administration Unit", 1, 0);

INSERT INTO users (group_id, name, username, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions) 
            VALUES (0, "Administrator", "admin", "admin", "0", "0", "", "", 1, 1, "", 0);
INSERT INTO users (group_id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions) 
            VALUES (4, "Anonymous", "guest", "guest", "0", "0", "", "", 0, 0, "", 19);
            
UPDATE users SET language = 'NewEnglish';
UPDATE users SET password = '21232f297a57a5a743894a0e4a801fc3' WHERE name = "Administrator";
UPDATE users SET password = '084e0343a0486ff05530df6c705c8bb4' WHERE name = "Anonymous";

INSERT INTO folders (name, description, parent_id, creator_id, document_type_id, unit_id, is_public)
             VALUES ("Documents", "Root Document Folder", 0, 0, 51, 0, 0);
             
INSERT INTO documents (name, filename, size, creator_id, parent_id, modified, description, security, mime_id, folder_id, major_version, minor_version, is_checked_out) 
            VALUES ("Test File", "test.txt", "36", 0, 0, "Dec 27th, 2000 at 05:17 pm", "", 0, 0, 0, 0, 1, 0);

INSERT INTO mimes (filetypes, mimetypes) VALUES ('ai', 'application/postscript');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('aif', 'audio/x-aiff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('aifc', 'audio/x-aiff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('aiff', 'audio/x-aiff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('asc', 'text/plain');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('au', 'audio/basic');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('avi', 'video/x-msvideo');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('bcpio', 'application/x-bcpio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('bin', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('bmp', 'image/bmp');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('cdf', 'application/x-netcdf');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('class', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('cpio', 'application/x-cpio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('cpt', 'application/mac-compactpro');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('csh', 'application/x-csh');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('css', 'text/css');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('dcr', 'application/x-director');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('dir', 'application/x-director');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('dms', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('doc', 'application/msword');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('dvi', 'application/x-dvi');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('dxr', 'application/x-director');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('eps', 'application/postscript');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('etx', 'text/x-setext');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('exe', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ez', 'application/andrew-inset');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('gif', 'image/gif');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('gtar', 'application/x-gtar');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('hdf', 'application/x-hdf');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('hqx', 'application/mac-binhex40');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('htm', 'text/html');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('html', 'text/html');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ice', 'x-conference/x-cooltalk');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ief', 'image/ief');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('iges', 'model/iges');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('igs', 'model/iges');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('jpe', 'image/jpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('jpeg', 'image/jpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('jpg', 'image/jpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('js', 'application/x-javascript');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('kar', 'audio/midi');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('latex', 'application/x-latex');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('lha', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('lzh', 'application/octet-stream');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('man', 'application/x-troff-man');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('me', 'application/x-troff-me');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mesh', 'model/mesh');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mid', 'audio/midi');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('midi', 'audio/midi');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mif', 'application/vnd.mif');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mov', 'video/quicktime');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('movie', 'video/x-sgi-movie');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mp2', 'audio/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mp3', 'audio/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mpe', 'video/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mpeg', 'video/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mpg', 'video/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('mpga', 'audio/mpeg');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ms', 'application/x-troff-ms');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('msh', 'model/mesh');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('nc', 'application/x-netcdf');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('oda', 'application/oda');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pbm', 'image/x-portable-bitmap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pdb', 'chemical/x-pdb');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pdf', 'application/pdf');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pgm', 'image/x-portable-graymap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pgn', 'application/x-chess-pgn');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('png', 'image/png');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('pnm', 'image/x-portable-anymap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ppm', 'image/x-portable-pixmap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ppt', 'application/vnd.ms-powerpoint');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ps', 'application/postscript');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('qt', 'video/quicktime');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ra', 'audio/x-realaudio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ram', 'audio/x-pn-realaudio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ras', 'image/x-cmu-raster');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('rgb', 'image/x-rgb');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('rm', 'audio/x-pn-realaudio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('roff', 'application/x-troff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('rpm', 'audio/x-pn-realaudio-plugin');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('rtf', 'text/rtf');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('rtx', 'text/richtext');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sgm', 'text/sgml');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sgml', 'text/sgml');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sh', 'application/x-sh');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('shar', 'application/x-shar');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('silo', 'model/mesh');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sit', 'application/x-stuffit');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('skd', 'application/x-koan');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('skm', 'application/x-koan');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('skp', 'application/x-koan');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('skt', 'application/x-koan');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('smi', 'application/smil');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('smil', 'application/smil');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('snd', 'audio/basic');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('spl', 'application/x-futuresplash');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('src', 'application/x-wais-source');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sv4cpio', 'application/x-sv4cpio');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('sv4crc', 'application/x-sv4crc');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('swf', 'application/x-shockwave-flash');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('t', 'application/x-troff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tar', 'application/x-tar');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tcl', 'application/x-tcl');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tex', 'application/x-tex');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('texi', 'application/x-texinfo');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('texinfo', 'application/x-texinfo');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tif', 'image/tiff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tiff', 'image/tiff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tr', 'application/x-troff');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tsv', 'text/tab-separated-values');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('txt', 'text/plain');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('ustar', 'application/x-ustar');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('vcd', 'application/x-cdlink');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('vrml', 'model/vrml');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('wav', 'audio/x-wav');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('wrl', 'model/vrml');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xbm', 'image/x-xbitmap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xls', 'application/vnd.ms-excel');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xml', 'text/xml');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xpm', 'image/x-xpixmap');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xwd', 'image/x-xwindowdump');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('xyz', 'chemical/x-pdb');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('zip', 'application/zip');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('gz', 'application/x-gzip');
INSERT INTO mimes (filetypes, mimetypes) VALUES ('tgz', 'application/x-gzip');

INSERT INTO web_documents_status (name) VALUES ("Pending");
INSERT INTO web_documents_status (name) VALUES ("Published");