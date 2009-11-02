ALTER TABLE `folders` ADD `created` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `creator_id` ,
ADD `modified_user_id` INT( 11 ) NULL DEFAULT NULL AFTER `created` ,
ADD `modified` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `modified_user_id` ;

#The following lines are for inserting the data which should have been there if this table had always stored this data

UPDATE folders f set f.created =
    (SELECT datetime FROM folder_transactions ft
        WHERE ft.transaction_namespace = 'ktcore.transactions.create' AND ft.folder_id = f.id AND ft.user_id = f.creator_id 
            ORDER BY datetime DESC LIMIT 1) ;

#TODO check whether there are additional actions which should trigger a modified date/user change

UPDATE folders f set f.modified =
    (SELECT datetime FROM folder_transactions ft
        WHERE (ft.transaction_namespace = 'ktcore.transactions.move' OR ft.transaction_namespace = 'ktcore.transactions.rename')
            AND ft.folder_id = f.id 
            AND ft.datetime > f.created
            ORDER BY datetime DESC LIMIT 1) ;

UPDATE folders f set f.modified_user_id =
    (SELECT user_id FROM folder_transactions ft
        WHERE (ft.transaction_namespace = 'ktcore.transactions.move' OR ft.transaction_namespace = 'ktcore.transactions.rename')
            AND ft.folder_id = f.id
            AND ft.datetime > f.created
            ORDER BY datetime DESC LIMIT 1) ;

UPDATE folders f set f.modified = f.created WHERE f.modified IS NULL OR f.modified = '0000-00-00 00:00:00' ;

UPDATE folders f set f.modified_user_id = f.creator_id WHERE f.modified_user_id IS NULL ;

ALTER TABLE `folders` CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
ALTER TABLE `modified` CHANGE `modified` `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'