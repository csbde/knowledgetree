#!/bin/sh
/usr/bin/soffice -nofirststartwizard -nologo -headless -accept="socket,host=localhost,port=8100;urp;StarOffice.ServiceManager" &> /dev/null &
