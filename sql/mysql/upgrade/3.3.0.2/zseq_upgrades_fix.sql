TRUNCATE `zseq_upgrades`;
INSERT INTO `zseq_upgrades` SELECT MAX(`id`) FROM `upgrades`;
