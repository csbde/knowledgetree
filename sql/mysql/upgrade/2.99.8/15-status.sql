INSERT INTO `status_lookup` (`id` , `name`) VALUES ('5', 'Incomplete');
UPDATE `zseq_status_lookup` SET `id` = '5' WHERE `id` = 4 LIMIT 1 ;
