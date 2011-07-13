UPDATE config_settings SET can_edit = 0 WHERE item = 'enableApiSignatures';
UPDATE config_settings SET description ='Defines whether browsers may provide the option to \'open\' a document from download. Default is \'OFF\'. Change to \'ON\' to prevent (most) browsers from giving users the \'Open\' option.' WHERE item ='fakeMimetype';
UPDATE config_settings SET description ='Defines whether to restrict users to sending emails only within their KnowledgeTree user group. Default is \'OFF\'.  Set to \'ON\' to disable sending of emails outside of the user\'s group.' WHERE item ='onlyOwnGroups';
UPDATE config_settings SET description ='Defines whether to allow anonymous users to log in automatically. Default is OFF.  Best practice is not to allow automatic login of anonymous users unless you understand KnowledgeTree\'s security mechanisms, and have sensibly applied the roles \'Everyone\' and \'Authenticated Users\'' WHERE item ='allowAnonymousLogin';

