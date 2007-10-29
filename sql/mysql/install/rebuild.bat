@ECHO OFF
CLS
ECHO.
FOR /F "tokens=5* delims= " %%A IN ('VOL C: ^| FIND "drive C"') DO SET OLDLABEL=%%B
ECHO Enter name of database (default is dms):
FOR /F "TOKENS=*" %%? IN ('LABEL C: 2^>NUL') DO SET INPUT=%%?
SET INPUT
CLS
LABEL C: %OLDLABEL%
PAUSE
CLS
ECHO ---- Dropping database %INPUT% ----
mysqladmin -u root -p -f drop %INPUT%
ECHO ---- Creating database %INPUT% ----
mysqladmin -u root -p create %INPUT%
ECHO ---- Creating structure for database %INPUT% ----
mysql -u root -p %INPUT%<structure.sql
ECHO ---- Inserting data into database %INPUT% ----
mysql -u root -p %INPUT%< data.sql
ECHO ---- Creating user information for database %INPUT% ----
mysql -u root -p %INPUT%<user.sql