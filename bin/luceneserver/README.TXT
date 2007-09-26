Lucene XML-RPC Server
---------------------

Prerequisites
=============

This requires Java 1.5+ to be installed.


Starting the Lucene Server Manually
===================================

cd c:\program files\ktdms\knowledgeTree\bin\luceneserver
;cd /opt/ktdms/knowledgeTree/bin/luceneserver
java -jar ktlucene.jar

Starting the Lucene Server in the background under Windows
==========================================================

The best is to install a service wrapper application called JavaService. This can be obtained from http://forge.objectweb.org/projects/javaservice/ and is licensed under LGPL.

Rename JavaService.exe to KTLuceneService.exe and place it in the KTLUCENEDIR folder.

To install the service:
	; you may need to edit the JVMLIB. note - there is also bin\client\jvm.dll sometimes.
	SET JVMLIB=c:\j2sdk\jre\bin\server\jvm.dll
	SET KTLUCENEDIR=c:\program files\ktdms\knowledgeTree\bin\luceneserver
	SET KTLUCENECLASS=%PROXYDIR%\ktlucene.jar
	SET OUTFILE=%KTLUCENEDIR%\out.txt
	SET ERRFILE=%KTLUCENEDIR%\err.txt
	KTLuceneService.exe -install "KTLuceneService" %JVMLIB% -Djava.class.path=%KTLUCENECLASS% -start KTLuceneService -auto -out %OUTFILE% -err %ERRFILE%


To uninstall the service:
	KTLuceneService -uninstall "KTLuceneService"  

Starting the Lucene Service in the background under Linux/Unix
==============================================================

cd c:\program files\ktdms\knowledgeTree\bin\luceneserver
;cd /opt/ktdms/knowledgeTree/bin/luceneserver
nohup java -jar ktlucene.jar 2>&1 &


KnowledgeTreeIndexer.Logging.properties
=======================================

The Lucene server uses log4j as the logging library. More information regarding
configuration may be obtained from http://www.apache.org

To enable debugging, change
	log4j.rootLogger=INFO, A1
to
	log4j.rootLogger=DEBUG, A1


TODO... discuss logrotation with log4j.


KnowledgeTreeIndexer.properties
===============================

; specifies the port on which the system will listen for requests
server.port=8080
; if paranoid, only 'acceptable ip addresses will be able to make requests.
server.paranoid=false
; the list of acceptable ip addresses
server.accept=127.0.0.1,192.168.1.1
; the folder where the lucene server will store the indexes
indexer.directory=../../var/indexes

Licensing
=========

The Java based KnowledgeTree XML-RPC Server utilises the following jar files:

commons-lang-2.3.jar 		- Apache License
log4j-1.2.14.jar  		- Apache License
lucene-core-2.1.0.jar		- Apache License
lucene-highlighter-2.1.0.jar 	- Apache License
NTEventLogAppender.dll		- Apache License
commons-logging-1.1.jar		- Apache License
ws-commons-util-1.0.1.jar	- Apache License
xmlrpc-common-3.0.jar		- Apache License
xmlrpc-server-3.0.jar		- Apache License

For more information, visit http://www.apache.org
