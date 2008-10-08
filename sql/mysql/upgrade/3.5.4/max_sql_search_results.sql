INSERT INTO `config_settings`
(group_name,display_name,description,item,value,default_value,type,options,can_edit)
VALUES
('search', 'Maximum results from SQL query', 'The maximum results from an SQL query', 'maxSqlResults', 'default', '1000', 'numeric_string', NULL, 1);
