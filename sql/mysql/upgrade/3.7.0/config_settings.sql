INSERT INTO `dms`.`config_settings` (
`id` ,
`group_name` ,
`display_name` ,
`description` ,
`item` ,
`value` ,
`default_value` ,
`type` ,
`options` ,
`can_edit`
)
VALUES (
NULL , 'externalBinary', 'image magick', 'Path to binary', 'imagemagick', 'default', 'convert', 'string', NULL , '1'
);

INSERT INTO `dms`.`config_settings` (
`id` ,
`group_name` ,
`display_name` ,
`description` ,
`item` ,
`value` ,
`default_value` ,
`type` ,
`options` ,
`can_edit`
)
VALUES (
NULL , 'externalBinary', 'pdf2swf', 'Path to binary', 'pdf2swf', 'default', 'pdf2swf', 'string', NULL , '1'
);