update config_groups set category = 'WebDAV Settings' where name = 'KTWebDAVSettings';
update config_groups set category = 'Explorer CP Settings' where name = 'explorerCPSettings';
update config_groups set category = 'Web Services Settings' where name = 'webservice';
update config_groups set category = 'Session Management Settings' where name = 'session';
update config_groups set category = 'Timezone Settings' where name = 'timezone';
update config_groups set display_name = 'Regional Settings' where name = 'timezone';
