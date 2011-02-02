ALTER TABLE `document_alerts` CHANGE `alert_date` `alert_date` DATETIME NOT NULL
ALTER TABLE `document_alerts` CHANGE `last_alert` `last_alert` DATETIME NULL DEFAULT NULL