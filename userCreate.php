<?php

$filename = "c:\script.txt";

$fp = fopen ($filename, "w");

// write to file
for ($i = 0; $i < 1000; $i++) {
	fwrite($fp, "INSERT INTO users (username, name, password, quota_max, quota_current, email, mobile, email_notification, sms_notification, ldap_dn, max_sessions, language_id) " .
            "VALUES ('admin" . $i . "', 'Administrator', '21232f297a57a5a743894a0e4a801fc3', 0, 0, '', '', 1, 1, '', 10000, 1);\n");
	fwrite($fp, "INSERT INTO users_groups_link (group_id, user_id) SELECT 1, ID FROM users where username = 'admin" . $i . "';\n");
}


fclose($fp);

?>
