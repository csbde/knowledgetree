DELETE FROM organisations_lookup;
DELETE FROM units_lookup;
DELETE FROM units_organisations_link;
DELETE FROM groups_lookup;
DELETE FROM groups_units_link;
DELETE FROM users;
DELETE FROM users_groups_link;
DELETE FROM folders;
DELETE FROM document_types_lookup;

-- organisation
INSERT INTO organisations_lookup (name) VALUES ("Default Organisation");
-- units
INSERT INTO units_lookup (name) VALUES ("Default Unit");
INSERT INTO units_organisations_link (unit_id, organisation_id) VALUES (1, 1);
-- setup groups
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (1,"System Administrators", 1, 0); -- id=1
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (2,"Unit Administrators", 0, 1); -- id=2
INSERT INTO groups_lookup (id, name, is_sys_admin, is_unit_admin) VALUES (3,"Anonymous", 0, 0); -- id=3
-- unit administrators
INSERT INTO groups_units_link (group_id, unit_id) VALUES (2, 1);
-- system administrator
-- passwords are md5'ed
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (1,"admin", "Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (1, 1);
-- unit administrator
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (2,"unitAdmin", "Unit Administrator", "21232f297a57a5a743894a0e4a801fc3", "0", "0", "", "", 1, 1, "", 1, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (2, 2);
-- guest user
INSERT INTO users (id, username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id)
            VALUES (3,"guest", "Anonymous", "084e0343a0486ff05530df6c705c8bb4", "0", "0", "", "", 0, 0, "", 19, 1);
INSERT INTO users_groups_link (group_id, user_id) VALUES (3, 3);
-- define folder structure
INSERT INTO folders (id,name, description, parent_id, creator_id, unit_id, is_public)
             VALUES (1,"Root Folder", "Root Document Folder", 0, 1, 0, 0);
INSERT INTO folders (id,name, description, parent_id, creator_id, unit_id, is_public)
             VALUES (2,"Default Unit", "Default Unit Root Folder", 1, 1, 1, 0);