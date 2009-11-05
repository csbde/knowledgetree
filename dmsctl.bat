@echo off

rem KnowledgeTree Control Script


rem ============= SET ENVIRONMENT VARIABLES ==============
set ZEND_PATH=C:\PROGRA~1\Zend\
set INSTALL_PATH=%ZEND_PATH%\KnowledgeTree
set JAVA_BIN=%INSTALL_PATH%\jre\bin\java.exe
set SOFFICE_PATH=%INSTALL_PATH%\openoffice
set SOFFICE_BIN=%SOFFICE_PATH%\program\soffice.exe
set SOFFICE_PORT=8100

set OpenofficeServiceName=KTOpenoffice
set SchedulerServiceName=KTScheduler
set LuceneServiceName=KTLucene


rem ============= MAIN ==============
if NOT ""%1"" == ""help"" IF NOT ""%1"" == ""start"" IF NOT ""%1"" == ""stop"" IF NOT ""%1"" == ""restart"" IF NOT ""%1"" == ""install"" IF NOT ""%1"" == ""uninstall"" goto help
goto %1

:help
echo USAGE:
echo.
echo dmsctl.bat ^<start^|stop^|restart^|install^|uninstall^>
echo.
echo help	- this screen
echo.
echo start	- start the services
echo stop	- stop the services
echo restart	- restart the services
echo. 
echo uninstall	- uninstall the services
echo. 

goto end

:start
echo Starting services
sc start %OpenofficeServiceName%
sc start %LuceneServiceName%
ping -n 7 127.0.0.1 > null
sc start %SchedulerServiceName%
goto end

:stop
echo Stopping services
sc stop %LuceneServiceName% 
sc stop %SchedulerServiceName%
sc stop %OpenofficeServiceName%
ping -n 7 127.0.0.1 > null
IF ""%1"" == ""restart"" goto start
goto end

:restart
goto stop

:uninstall
echo Uninstalling services
sc delete %LuceneServiceName%
sc delete %SchedulerServiceName%
sc delete %OpenofficeServiceName%
goto end

:end