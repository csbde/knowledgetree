CREATE TABLE archiving_type_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
)  TYPE = InnoDB;

-- archiving types lookup
INSERT INTO archiving_type_lookup (name) VALUES ("Date");
INSERT INTO archiving_type_lookup (name) VALUES ("Utilisation");

CREATE TABLE archiving_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
expiration_date DATE,
document_transaction_id INTEGER,
time_period_id INTEGER
)  TYPE = InnoDB;

CREATE TABLE archiving_date_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
expiration_date DATE,
time_period_id INTEGER
)  TYPE = InnoDB;

CREATE TABLE archiving_utilisation_settings ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_transaction_id INTEGER,
time_period_id INTEGER
)  TYPE = InnoDB;

CREATE TABLE archive_restoration_request (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
request_user_id INTEGER NOT NULL,
admin_user_id INTEGER NOT NULL,
datetime DATETIME NOT NULL
)  TYPE = InnoDB;

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

CREATE TABLE document_link ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
parent_document_id INTEGER NOT NULL,
child_document_id INTEGER NOT NULL
) TYPE = InnoDB;

ALTER TABLE documents ADD column status_id INTEGER;
UPDATE documents SET status_id=1;

CREATE TABLE document_archiving ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
document_id INTEGER NOT NULL,
archiving_type_id INTEGER,
archiving_settings_id INTEGER
)  TYPE = InnoDB;

ALTER TABLE folders_users_roles_link ADD column dependant_documents_created bit;
update folders_users_roles_link set dependant_documents_created = 1;

CREATE TABLE status_lookup  (
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(255)
)TYPE = InnoDB;

-- document status
INSERT INTO status_lookup (name) VALUES ("Live");
INSERT INTO status_lookup (name) VALUES ("Published");
INSERT INTO status_lookup (name) VALUES ("Deleted");
INSERT INTO status_lookup (name) VALUES ("Archived");


CREATE TABLE time_period ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
time_unit_id INTEGER,
units INTEGER
)  TYPE = InnoDB;

CREATE TABLE time_unit_lookup ( 
id INTEGER NOT NULL UNIQUE AUTO_INCREMENT,
name CHAR(100)
)  TYPE = InnoDB;

-- time lookups
INSERT INTO time_unit_lookup (name) VALUES ("Years");
INSERT INTO time_unit_lookup (name) VALUES ("Months");
INSERT INTO time_unit_lookup (name) VALUES ("Days");

INSERT INTO document_transaction_types_lookup (name) VALUES ("View");
UPDATE document_transactions SET transaction_id=10 WHERE transaction_id=6 AND comment='Inline view'
ALTER TABLE groups_folders_approval_link ADD COLUMN user_id INT;
/*
UPDATE groups_folders_approval_link SET user_id = 1;*/