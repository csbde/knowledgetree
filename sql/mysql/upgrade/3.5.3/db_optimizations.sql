alter table  active_sessions change `session_id` `session_id` varchar (32) NULL, change `ip` `ip` varchar (15)  NULL;

alter table  archiving_type_lookup change `name` `name` varchar (100) NULL;

alter table  authentication_sources change `name` `name` varchar (50) NOT NULL;

alter table  data_types change `name` `name` varchar (255) NOT NULL;

alter table  document_fields change `is_mandatory` `is_mandatory` tinyint(1)  NOT NULL default 0;

alter table  document_link_types change `name` `name` varchar(100) NOT NULL, change `reverse_name` `reverse_name` varchar(100) NOT NULL, change `description` `description` varchar(255) NOT NULL;

alter table  document_metadata_version change `description` `description` varchar(255)  NULL;

alter table  document_tags change `document_id` `document_id` int(11) NOT NULL, change `tag_id` `tag_id` int(11) NOT NULL;

alter table  document_content_version change `storage_path` `storage_path` varchar(1024)  NULL;

alter table  document_transaction_types_lookup change `namespace` `namespace` varchar(255) NOT NULL;

alter table  document_types_lookup change `name` `name` varchar(100)  NULL, change `disabled` `disabled` tinyint(1) NOT NULL default 0;

alter table  field_behaviours change `name` `name` varchar(255) NOT NULL, change `human_name` `human_name` varchar(100) NOT NULL;

alter table  fieldsets change `mandatory` `mandatory` tinyint(1) NOT NULL default 0, change `disabled` `disabled` tinyint(1) NOT NULL default 0;

alter table  folder_transactions change `ip` `ip` varchar(15) NULL, change `comment` `comment` varchar(255) NOT NULL, change `transaction_namespace` `transaction_namespace` varchar(255) NOT NULL;

alter table  links change `name` `name` varchar(100) NOT NULL, change `url` `url` varchar(100) NOT NULL;

alter table  metadata_lookup change `name` `name` varchar(255) NULL, change `disabled` `disabled` tinyint(1) NOT NULL default 0;

alter table  metadata_lookup_tree change `name` `name` varchar(255) NULL;

alter table  mime_types change `filetypes` `filetypes` varchar(100) NOT NULL,  change `mimetypes` `mimetypes` varchar(100) NOT NULL, change `icon_path` `icon_path` varchar(255) NULL, change `friendly_name` `friendly_name` varchar(255) NOT NULL default '';

alter table  mime_extractors change `active` `active` tinyint(1) NOT NULL default 0;

alter table  organisations_lookup change `name` `name` varchar(100) NOT NULL;

alter table  permissions change `name` `name` varchar(100) NOT NULL,  change `human_name` `human_name` varchar(100) NOT NULL,  change `built_in` `built_in` tinyint(1) NOT NULL default 0;

alter table  roles change `name` `name` varchar(255) NOT NULL;

alter table  saved_searches change `name` `name` varchar(255) NOT NULL, change `namespace` `namespace` varchar(255) NOT NULL;

alter table  status_lookup change `name` `name` varchar(255) NOT NULL;

alter table  scheduler_tasks change `is_complete` `is_complete` tinyint(1) NOT NULL default 0;

alter table  system_settings change `name` `name` varchar(255) NOT NULL;

alter table  time_unit_lookup change `name` `name` varchar(100)  NOT NULL;

alter table  units_lookup change `name` `name` varchar(100) NOT NULL;

alter table  upgrades change `descriptor` `descriptor` varchar(100) NOT NULL, change `description` `description` varchar(255) NOT NULL, change `result` `result` tinyint(1) NOT NULL default 0, change `parent` `parent` varchar(40) NULL;

alter table  workflow_actions change `action_name` `action_name` varchar(255) NOT NULL;

alter table  workflow_state_actions change `action_name` `action_name` varchar(255) NOT NULL;

alter table  workflow_state_disabled_actions change `action_name` `action_name` varchar(255) NOT NULL;

alter table  workflow_states change `name` `name` varchar(255) NOT NULL, change `human_name` `human_name` varchar(100) NOT NULL, change `manage_permissions` `manage_permissions` tinyint(1) NOT NULL default 0, change `manage_actions` `manage_actions` tinyint(1) NOT NULL default 0;

alter table  workflow_transitions change `name` `name` varchar(255) NOT NULL,  change `human_name` `human_name` varchar(255) NOT NULL;

alter table  workflow_trigger_instances change `namespace` `namespace` varchar(255) NOT NULL;

alter table  workflows change `name` `name` varchar(255) NOT NULL, change `human_name` `human_name` varchar(100) NOT NULL, change `enabled` `enabled` tinyint(1) NOT NULL default 1;
