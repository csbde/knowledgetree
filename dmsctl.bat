@echo off

rem KnowledgeTree Control Script


rem ============= SET ENVIRONMENT VARIABLES ==============
set INSTALL_PATH=%CD%
cd ..
cd ..
set ZEND_PATH=%CD%
cd %INSTALL_PATH%
set JAVA_BIN=%ZEND_PATH%\jre\bin\java.exe
set SOFFICE_PATH=%ZEND_PATH%\openoffice
set SOFFICE_BIN=%SOFFICE_PATH%\program\soffice.exe
set SOFFICE_PORT=8100

set OpenofficeServiceName=KTOpenoffice
set SchedulerServiceName=KTScheduler
set LuceneServiceName=KTLucene

rem ============= MAIN ==============
if NOT ""%1"" == ""help"" IF NOT ""%1"" == ""start"" IF NOT ""%1"" == ""path"" IF NOT ""%1"" == ""stop"" IF NOT ""%1"" == ""restart"" IF NOT ""%1"" == ""install"" IF NOT ""%1"" == ""uninstall"" goto help
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
echo install	- install the services
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

:path
echo ZEND_PATH     == %ZEND_PATH%
echo INSTALL_PATH  == %INSTALL_PATH%
echo JAVA_BIN      == %JAVA_BIN%
echo SOFFICE_PATH  == %SOFFICE_PATH%
echo SOFFICE_BIN   == %SOFFICE_BIN%
goto end

:install
echo Installing services
IF EXIST "%INSTALL_PATH%\var\bin\officeinstall.bat" call "%INSTALL_PATH%\var\bin\officeinstall.bat"
echo The Open Office automatic service was successfully installed
IF EXIST "%INSTALL_PATH%\var\bin\schedulerinstall.bat" call "%INSTALL_PATH%\var\bin\schedulerinstall.bat"
echo The Scheduler automatic service was successfully installed
IF EXIST "%INSTALL_PATH%\var\bin\luceneinstall.bat" call "%INSTALL_PATH%\var\bin\luceneinstall.bat"
goto end

:end
