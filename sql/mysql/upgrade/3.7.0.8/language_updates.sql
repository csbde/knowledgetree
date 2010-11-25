UPDATE `document_transaction_types_lookup` SET `name` = "Check-In"        WHERE `namespace` = "ktcore.transactions.check_in"  LIMIT 1;
UPDATE `document_transaction_types_lookup` SET `name` = "Check-Out"       WHERE `namespace` = "ktcore.transactions.check_out"  LIMIT 1;
UPDATE `document_transaction_types_lookup` SET `name` = "Force Check-In"  WHERE `namespace` = "ktcore.transactions.force_checkin"  LIMIT 1;

UPDATE `config_settings` SET `display_name` = "Always Force Original Filename on Check-in" WHERE `item` = "disableForceFilenameOption"  LIMIT 1;

