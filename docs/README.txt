=================
KnowledgeTree DMS
=================

The KnowledgeTree is an open-source Document Management System that currently provides
the following features:

o  Document management
   - folder and document creation
   - versioning (check in / check out)
   - configurable document meta-data (document types)
o  Document approval routing
o  Group-based access control
o  Subscriptions (document and folder)
o  Document searching
o  Context-sensitive help

DIRECTORY STRUCTURE
-------------------

Here is a description of what each of the top level directories contain.

bin/           This contains helper *nix shell scripts.
config/        KnowledgeTree configuration files live.
docs/          Documentation directory.
Documents/     The physical documents reside here.
etc/           Contains sample configuration for PHP, MySQL and Apache.
graphics/      This is where the user interface graphics live.
lib/           This is where the object model/backend KnowledgeTre classes live.
locale/        Language specific files..
log/           The application log files live here
phpSniff/      phpSniff v 2.1.1..
phplib/        Database handler classes.
phpmailer/     phpmailer v 1.62..
presentation/  the presentation and business logic scripts
sql/           Database table creation and population files.
sync/          Scripts to synchronise the knowledgeTree
tests/         This is where all of the unit tests live.

INSTALLATION
------------

*  Ensure that you have PHP, MySQL and Apache installed and configured.

*  Move the knowledgeTree folder to the directory it is going to be served from:
   $ mv knowledgeTree /path/to/your/html/directory/

*  Create a database:
   $ mysqladmin -p create intranet

*  Create and populate the tables:
   $ mysql -p dms < sql/tables.sql

*  Configure your installation by changing the following attributes in config/environment.php:
   - $default->fileSystemRoot
   - $default->serverName
   - $default->sslEnabled = true;
   - $default->authenticationClass
   - $default->dbUser
   - $default->dbPass
   - $default->dbHost
   - $default->dbName

*  Check permissions on Documents folder
   - The "/Documents" folder MUST be able to be written to by your web server.
     If your web server is running as user "nobody" and group "nobody" (apache default)
     then cd to the files directory and type:

     *nix: 'chown -R nobody.nobody Documents'
     Windows: Check the permissions and security tabs

*  Login:
   - in a web browser goto http://$default->serverName/$default->rootUrl/
   - default user is "admin" with password "admin"

*  Customise:
   - in the Administration section, click on System Settings and modify the default values in the database.
   
- The KnowledgeTree Team
$Id$
