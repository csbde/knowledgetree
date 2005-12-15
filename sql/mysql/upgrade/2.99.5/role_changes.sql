SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `roles` DROP `active`;
ALTER TABLE `roles` DROP `can_read`;
ALTER TABLE `roles` DROP `can_write`;

SET FOREIGN_KEY_CHECKS=1;
