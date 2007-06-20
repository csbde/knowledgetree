
; TEST ALL PEAR LIBRARIES BEFORE UPGRADING INTO RELEASE

PATH=%PATH%;c:\php5\PEAR

pear channel-update pear.php.net
pear config-set php_dir "C:\kt\kt.trunk\thirdparty\pear"

pear config-set preferred_state stable

pear upgrade --alldeps PEAR
pear upgrade --alldeps Cache_Lite
pear upgrade --alldeps Config
pear upgrade --alldeps DB
pear upgrade --alldeps File

;pear upgrade --alldeps MDB2#mysql

pear upgrade --alldeps Log
pear upgrade --alldeps PHP_Compat

pear config-set preferred_state beta
pear upgrade --alldeps File_Gettext
pear upgrade --alldeps Net_LDAP
pear upgrade --alldeps SOAP
pear config-set preferred_state stable

