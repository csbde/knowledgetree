@echo off

SET PATH=%PATH%;..\..\php;..\..\php\bin
cd "@@BITROCK_INSTALLDIR@@\knowledgeTree\search2\indexing\bin"

php -Cq optimise.php