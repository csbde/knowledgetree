CREATE TABLE active_sessions ( 
id INTEGER NOT NULL,
user_id INTEGER,
lastused DATETIME,
ip CHAR(30)
); 

CREATE TABLE document_transaction_types ( 
id INTEGER NOT NULL,
name CHAR(100) NOT NULL
);

CREATE TABLE document_transactions ( 
id INTEGER NOT NULL UNIQUE,
document_id INTEGER NOT NULL,
version CHAR(50),
user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL,
ip CHAR(30),
filename CHAR(100) NOT NULL,
comment CHAR(100) NOT NULL,
transaction_id INTEGER
); 

CREATE TABLE document_type_fields ( 
id INTEGER NOT NULL UNIQUE,
document_type_id INTEGER NOT NULL,
field_id INTEGER NOT NULL,
is_mandatory BOOL NOT NULL
);

CREATE TABLE document_type_fields_values ( 
id INTEGER NOT NULL UNIQUE,
document_id INTEGER NOT NULL,
document_type_field_id INTEGER NOT NULL,
value CHAR(255) NOT NULL
);

CREATE TABLE document_types ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100)
); 

CREATE TABLE document_words ( 
id INTEGER NOT NULL UNIQUE,
word_id INTEGER NOT NULL,
document_id INTEGER NOT NULL
); 

CREATE TABLE documents ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(80) NOT NULL,
filename CHAR(50) NOT NULL,
size BIGINT NOT NULL,
creatorid INTEGER NOT NULL,
parent_id INTEGER NOT NULL,
modified DATE NOT NULL,
description CHAR(200) NOT NULL,
security INTEGER NOT NULL,
mime_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
major_version INTEGER NOT NULL,
minor_version INTEGER NOT NULL,
is_checked_out BOOL NOT NULL
); 

CREATE TABLE document_fields ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(255) NOT NULL,
data_type CHAR(100) NOT NULL
) 
;

CREATE TABLE folder_user_role_types ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL
); 

CREATE TABLE folders ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100),
description CHAR(100),
parent_id INTEGER,
creator_id INTEGER,
document_type_id INTEGER NOT NULL,
unit_id INTEGER,
is_public BOOL NOT NULL
); 

CREATE TABLE folders_user_roles ( 
id INTEGER NOT NULL UNIQUE,
user_id INTEGER NOT NULL,
folder_id INTEGER NOT NULL,
role_type_id INTEGER NOT NULL
);

CREATE TABLE groups ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL
);

CREATE TABLE links ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL,
url CHAR(100) NOT NULL,
rank INTEGER NOT NULL
); 

CREATE TABLE membergroup ( 
id INTEGER NOT NULL UNIQUE,
user_id INTEGER NOT NULL,
group_id INTEGER NOT NULL
);

CREATE TABLE mimes ( 
id INTEGER NOT NULL UNIQUE,
filetypes CHAR(100) NOT NULL,
mimetypes CHAR(100) NOT NULL
); 

CREATE TABLE organisations ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL
); 

CREATE TABLE subscriptions ( 
id INTEGER NOT NULL UNIQUE,
user_id INTEGER NOT NULL,
document_id INTEGER NOT NULL
); 

CREATE TABLE system_settings ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL,
value INTEGER NOT NULL
); 

CREATE TABLE units ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(100) NOT NULL,
organisation_id INTEGER NOT NULL,
parent_id INTEGER NOT NULL
); 

CREATE TABLE users ( 
id INTEGER AUTO_INCREMENT NOT NULL UNIQUE,
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
ldap_dn CHAR(255) NOT NULL
); 

CREATE TABLE users_unit ( 
id INTEGER NOT NULL UNIQUE,
user_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL
); 

CREATE TABLE web_documents ( 
id INTEGER NOT NULL UNIQUE,
document_id INTEGER NOT NULL,
web_site_id INTEGER NOT NULL,
unit_id INTEGER NOT NULL,
status_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
); 

CREATE TABLE web_documents_status ( 
id INTEGER NOT NULL UNIQUE,
name CHAR(50) NOT NULL
); 

CREATE TABLE web_sites ( 
id INTEGER NOT NULL UNIQUE,
web_site_name CHAR(100) NOT NULL,
web_site_url CHAR(50) NOT NULL,
web_master_id INTEGER NOT NULL
); 

CREATE TABLE words ( 
id INTEGER NOT NULL UNIQUE,
word CHAR(100) NOT NULL
); 

ALTER TABLE active_sessions 
ADD CONSTRAINT PK_active_sessions 
PRIMARY KEY (id); 

ALTER TABLE document_transaction_types 
ADD CONSTRAINT PK_document_transaction_types 
PRIMARY KEY (id); 

ALTER TABLE document_transactions 
ADD CONSTRAINT PK_document_transactions 
PRIMARY KEY (id); 

ALTER TABLE document_type_fields 
ADD CONSTRAINT PK_document_type_fields 
PRIMARY KEY (id);

ALTER TABLE document_type_fields_values 
ADD CONSTRAINT PK_document_type_fields_values 
PRIMARY KEY (id); 

ALTER TABLE document_types 
ADD CONSTRAINT PK_document_types 
PRIMARY KEY (id); 

ALTER TABLE document_words 
ADD CONSTRAINT PK_document_words 
PRIMARY KEY (id); 

ALTER TABLE documents 
ADD CONSTRAINT PK_files 
PRIMARY KEY (id); 

ALTER TABLE document_fields 
ADD CONSTRAINT PK_fields 
PRIMARY KEY (id); 

ALTER TABLE folder_user_role_types 
ADD CONSTRAINT PK_Editors 
PRIMARY KEY (id); 

ALTER TABLE folders 
ADD CONSTRAINT PK_folders 
PRIMARY KEY (id); 

ALTER TABLE folders_user_roles 
ADD CONSTRAINT PK_authors 
PRIMARY KEY (id); 

ALTER TABLE groups 
ADD CONSTRAINT PK_groups 
PRIMARY KEY (id); 

ALTER TABLE links 
ADD CONSTRAINT PK_links 
PRIMARY KEY (id); 

ALTER TABLE membergroup 
ADD CONSTRAINT PK_membergroup 
PRIMARY KEY (id); 

ALTER TABLE mimes 
ADD CONSTRAINT PK_mimes 
PRIMARY KEY (id); 

ALTER TABLE organisations 
ADD CONSTRAINT PK_organisations 
PRIMARY KEY (id); 

ALTER TABLE subscriptions 
ADD CONSTRAINT PK_subscriptions 
PRIMARY KEY (id); 

ALTER TABLE system_settings 
ADD CONSTRAINT PK_system_settings 
PRIMARY KEY (id);

ALTER TABLE units 
ADD CONSTRAINT PK_units 
PRIMARY KEY (id);

ALTER TABLE users 
ADD CONSTRAINT PK_users 
PRIMARY KEY (id);  

ALTER TABLE users_unit 
ADD CONSTRAINT PK_users_unit 
PRIMARY KEY (id);  

ALTER TABLE web_documents 
ADD CONSTRAINT PK_web_documents 
PRIMARY KEY (id); 

ALTER TABLE web_sites 
ADD CONSTRAINT PK_web_sites 
PRIMARY KEY (id); 

ALTER TABLE words 
ADD CONSTRAINT PK_word_list 
PRIMARY KEY (id); 