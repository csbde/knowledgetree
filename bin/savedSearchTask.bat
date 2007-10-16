@echo off

SET PATH=%PATH%;..\..\php;..\..\php\bin
cd "@@BITROCK_INSTALLDIR@@\knowledgeTree\search2\search\bin"

php -Cq cronSavedSearch.php