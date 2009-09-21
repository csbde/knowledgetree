@echo off

rem KnowledgeTree Control Script


rem ============= SET ENVIRONMENT VARIABLES ==============
set INSTALL_PATH=@@BITROCK_KT_INSTALLDIR@@
set JAVA_BIN=%INSTALL_PATH%\java\bin\java.exe
rem set MAGICK_HOME=
set SOFFICE_PATH=%INSTALL_PATH%\openoffice
set SOFFICE_BIN=%SOFFICE_PATH%\program\soffice.exe
set SOFFICE_PORT=8100
set PATH=%PATH%;%INSTALL_PATH%\php\extensions

set MysqlServiceName=@@BITROCK_MYSQL_SERVICENAME@@
set ApacheServiceName=@@BITROCK_APACHE_SERVICENAME@@
set OpenofficeServiceName=@@BITROCK_OPENOFFICE_SERVICENAME@@
set SchedulerServiceName=@@BITROCK_SCHEDULER_SERVICENAME@@
set LuceneServiceName=@@BITROCK_LUCENE_SERVICENAME@@


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
echo install	- install the services
echo uninstall	- uninstall the services
echo. 

goto end

:start
echo Starting services
sc start %MysqlServiceName%
sc start %ApacheServiceName%
sc start %OpenofficeServiceName%
sc start %LuceneServiceName%
ping -n 7 127.0.0.1 > null
sc start %SchedulerServiceName%
IF EXIST "%INSTALL_PATH%\bin\networkservice.bat" call "%INSTALL_PATH%\bin\networkservice.bat" start

goto end

:stop
echo Stopping services
IF EXIST "%INSTALL_PATH%\bin\networkservice.bat" call "%INSTALL_PATH%\bin\networkservice.bat" stop
sc stop %LuceneServiceName% 
sc stop %SchedulerServiceName%
sc stop %OpenofficeServiceName%
sc stop %ApacheServiceName%
ping -n 7 127.0.0.1 > null
sc stop %MysqlServiceName%
IF ""%1"" == ""restart"" goto start
goto end

:restart
goto stop

:install
echo Installing services
"%INSTALL_PATH%\mysql\bin\mysqld.exe" --install %MysqlServiceName% --defaults-file="%INSTALL_PATH%\mysql\my.ini"
"%INSTALL_PATH%\apache2\bin\httpd.exe" -k install -n "%ApacheServiceName%" -f "%INSTALL_PATH%\apache2\conf\httpd.conf"
"%INSTALL_PATH%\bin\winserv.exe" install %OpenofficeServiceName% -displayname "%OpenofficeServiceName%" -start auto %SOFFICE_BIN% "-accept=socket,host=127.0.0.1,port=%SOFFICE_PORT%;urp;StarOffice.ServiceManager" -nologo -headless -nofirststartwizard

call "%INSTALL_PATH%\bin\schedulerserviceinstall.bat"
call "%INSTALL_PATH%\bin\luceneserviceinstall.bat"
IF EXIST "%INSTALL_PATH%\bin\networkservice.bat" call "%INSTALL_PATH%\bin\networkservice.bat" install

goto end

:uninstall
echo Uninstalling services
IF EXIST "%INSTALL_PATH%\bin\networkservice.bat" call "%INSTALL_PATH%\bin\networkservice.bat" uninstall
sc delete %LuceneServiceName%
sc delete %SchedulerServiceName%
sc delete %OpenofficeServiceName%
sc delete %ApacheServiceName%
sc delete %MysqlServiceName%
goto end

:end
