-- table definitions
CREATE TABLE active_sessions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER,
session_id CHAR(255),
lastused DATETIME,
ip CHAR(30)
) TYPE = InnoDB;

CREATE TABLE archive_restoration_request (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
request_user_id INTEGER NOT NULL,
admin_user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
)  TYPE = InnoDB;

CREATE TABLE archiving_type_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
)  TYPE = InnoDB;

CREATE TABLE archiving_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
archiving_type_id INTEGER NOT NULL,
expiration_date DATE,
document_transaction_id INTEGER,
time_period_id INTEGER
)  TYPE = InnoDB;

CREATE TABLE data_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL
)TYPE = InnoDB;

CREATE TABLE dependant_document_instance ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_title TEXT NOT NULL,
user_id INTEGER NOT NULL,
template_document_id INTEGER,
parent_document_id INTEGER
) TYPE = InnoDB;

CREATE TABLE dependant_document_template ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_title TEXT NOT NULL,
default_user_id INTEGER NOT NULL,
template_document_id INTEGER,
group_folder_approval_link_id INTEGER
) TYPE = InnoDB;

CREATE TABLE discussion_threads ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
first_comment_id INTEGER NOT NULL,
last_comment_id INTEGER NOT NULL,
views INTEGER NOT NULL,
replies INTEGER NOT NULL,
creator_id INTEGER NOT NULL
)TYPE = InnoDB;

CREATE TABLE discussion_comments ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
thread_id INTEGER NOT NULL,
user_id INTEGER NOT NULL,
subject TEXT,
body TEXT,
date datetime
)TYPE = InnoDB;

CREATE TABLE documents ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_type_id INTEGER NOT NULL,
name TEXT NOT NULL,
filename TEXT NOT NULL,
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
checked_out_user_id INTEGER,
status_id INTEGER
)TYPE = InnoDB;

CREATE TABLE document_archiving_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
archiving_settings_id INTEGER NOT NULL
)  TYPE = InnoDB;

CREATE TABLE document_fields ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
data_type CHAR(100) NOT NULL,
is_generic BIT,
has_lookup BIT
)TYPE = InnoDB;

CREATE TABLE document_fields_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
document_field_id INTEGER NOT NULL,
value CHAR(255) NOT NULL
)TYPE = InnoDB;

CREATE TABLE document_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
parent_document_id INTEGER NOT NULL,
child_document_id INTEGER NOT NULL
) TYPE = InnoDB;

CREATE TABLE document_subscriptions ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL,
is_alerted BIT
)TYPE = InnoDB;

CREATE TABLE document_text (
  id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
  document_id integer,
  document_text MEDIUMTEXT,
  FULLTEXT (document_text),
  KEY document_text_document_id_indx (document_id)
) Type = MyISAM;


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

CREATE TABLE document_transaction_types_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100) NOT NULL
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

CREATE TABLE folders ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255),
description CHAR(255),
parent_id INTEGER,
creator_id INTEGER,
unit_id INTEGER,
is_public BIT NOT NULL,
parent_folder_ids TEXT,
full_path TEXT,
inherit_parent_folder_permission INTEGER
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
active BIT,
dependant_documents_created BIT
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
role_id INTEGER NOT NULL,
user_id INTEGER NOT NULL
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
  id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
  fSection varchar(100) NOT NULL default '',
  help_info text NOT NULL
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


CREATE TABLE metadata_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_field_id INTEGER NOT NULL,
name CHAR(255) 
)TYPE = InnoDB;

CREATE TABLE mime_types ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
filetypes CHAR(100) NOT NULL,
mimetypes CHAR(100) NOT NULL,
icon_path CHAR(255) 
)TYPE = InnoDB;

CREATE TABLE news ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
synopsis VARCHAR(255) NOT NULL,
body TEXT,
rank INTEGER,
image TEXT,
image_size INTEGER,
image_mime_type_id INTEGER,
active BIT
) TYPE = InnoDB;

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

CREATE TABLE search_document_user_link (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER,
user_id INTEGER
) Type = InnoDB;
ALTER TABLE search_document_user_link ADD INDEX search_document_user_link_user_id_indx (user_id);
ALTER TABLE search_document_user_link ADD INDEX search_document_user_link_document_id_indx (document_id);

CREATE TABLE status_lookup  (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255)
)TYPE = InnoDB;

CREATE TABLE system_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255) NOT NULL,
value CHAR(255) NOT NULL
)TYPE = InnoDB;

CREATE TABLE time_period ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
time_unit_id INTEGER,
units INTEGER
)  TYPE = InnoDB;

CREATE TABLE time_unit_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
)  TYPE = InnoDB;

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

-- mime types
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
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('zip', 'application/zip', 'icons/zip.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('gz', 'application/x-gzip', 'icons/zip.gif');
INSERT INTO mime_types (filetypes, mimetypes, icon_path) VALUES ('tgz', 'application/x-gzip', 'icons/zip.gif');
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

-- data_types
insert into data_types (name) values ('STRING');
insert into data_types (name) values ('CHAR');
insert into data_types (name) values ('TEXT');
insert into data_types (name) values ('INT');
insert into data_types (name) values ('FLOAT');

-- category field
INSERT INTO document_fields (name, data_type, is_generic) VALUES ("Category", "STRING", 1);

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

-- system settings
INSERT INTO system_settings (name, value) values ("lastIndexUpdate", "0");
INSERT INTO system_settings (name, value) values ("knowledgeTreeVersion", "1.2.0");

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
INSERT INTO document_transaction_types_lookup (name) VALUES ("Collaboration Step Rollback");
INSERT INTO document_transaction_types_lookup (name) VALUES ("View");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Expunge");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Force CheckIn");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Email Link");
INSERT INTO document_transaction_types_lookup (name) VALUES ("Collaboration Step Approve");

-- document status
INSERT INTO status_lookup (name) VALUES ("Live");
INSERT INTO status_lookup (name) VALUES ("Published");
INSERT INTO status_lookup (name) VALUES ("Deleted");
INSERT INTO status_lookup (name) VALUES ("Archived");

-- archiving types lookup
INSERT INTO archiving_type_lookup (name) VALUES ("Date");
INSERT INTO archiving_type_lookup (name) VALUES ("Utilisation");

-- time lookups
INSERT INTO time_unit_lookup (name) VALUES ("Years");
INSERT INTO time_unit_lookup (name) VALUES ("Months");
INSERT INTO time_unit_lookup (name) VALUES ("Days");

-- help
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
INSERT INTO help VALUES (43,'modifyDocumentGenericMetaData','modifyDocumentGenericMetaDataHelp.html');
INSERT INTO help VALUES (44,'viewHistory','viewHistoryHelp.html');
INSERT INTO help VALUES (45,'checkInDocument','checkInDocumentHelp.html');
INSERT INTO help VALUES (46,'checkOutDocument','checkOutDocumentHelp.html');
INSERT INTO help VALUES (47,'advancedSearch','advancedSearchHelp.html');
INSERT INTO help VALUES (48,'deleteFolderCollaboration','deleteFolderCollaborationHelp.html');
INSERT INTO help VALUES (49,'addFolderDocType','addFolderDocTypeHelp.html');
INSERT INTO help VALUES (50,'deleteFolderDocType','deleteFolderDocTypeHelp.html');
INSERT INTO help VALUES (51,'addGroupFolderLink','addGroupFolderLinkHelp.html');
INSERT INTO help VALUES (52,'deleteGroupFolderLink','deleteGroupFolderLinkHelp.html');
INSERT INTO help VALUES (53,'addWebsite','addWebsiteHelp.html');
INSERT INTO help VALUES (54,'addWebsiteSuccess','addWebsiteHelp.html');
INSERT INTO help VALUES (55,'editWebsite','editWebsiteHelp.html');
INSERT INTO help VALUES (56,'removeWebSite','removeWebSiteHelp.html');
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

-- setup default information
-- organisation
INSERT INTO organisations_lookup (name) VALUES ("Default Organisation");

-- units
INSERT INTO units_lookup (name) VALUES ("Default Unit");

INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (1, 1);

-- setup groups
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("System Administrators", 1, 0); -- id=1
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Unit Administrators", 0, 1); -- id=2
INSERT INTO groups_lookup (name, is_sys_admin, is_unit_admin) VALUES ("Anonymous", 0, 0); -- id=3

-- unit administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (2, 1);

-- system administrator
-- passwords are md5'ed
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("admin", "Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (1, 1);

-- unit administrator
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("unitAdmin", "Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (2, 2);
                        
-- guest user
INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES ("guest", "Anonymous", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 19, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (3, 3);
            
-- define folder structure
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public)
             VALUES ("Root Folder", "Root Document Folder", 0, 1, 0, 0);
INSERT INTO folders (name, description, parent_id, creator_id, unit_id, is_public, parent_folder_ids, full_path)
             VALUES ("Default Unit", "Default Unit Root Folder", 1, 1, 1, 0, "1", "Root Folder");
             
-- default document type
INSERT INTO document_types_lookup (name) VALUES ("Default");
-- map folder to document type
INSERT INTO folder_doctypes_link (folder_id, document_type_id) VALUES (2, 1);