@echo off

SET PATH=%PATH%;..\..\php;..\..\php\bin
cd "@@BITROCK_INSTALLDIR@@\knowledgeTree\bin"

php -Cq scheduler.php