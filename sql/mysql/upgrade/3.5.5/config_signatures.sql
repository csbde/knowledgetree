INSERT INTO config_groups (name, display_name, description, category)
VALUES ('e_signatures', 'Electronic Signatures', 'Configuration settings for the electronic signatures', 'Security Settings');

INSERT INTO config_settings (group_name, display_name, description, item, value, default_value, type, options, can_edit)
VALUES ('e_signatures', 'Enable Electronic Signatures', 'Enables the electronic signature functionality on write actions.', 'enableESignatures', 'true', 'false', 'boolean', '', 1);