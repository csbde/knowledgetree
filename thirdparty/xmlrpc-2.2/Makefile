# Makefile for phpxmlrpc library
# $Id: Makefile,v 1.33 2007/02/25 18:43:09 ggiunta Exp $

### USER EDITABLE VARS ###

# path to PHP executable, preferably CLI version
PHP=/usr/local/bin/php

# path were xmlrpc lib files will be copied to
PHPINCLUDEDIR=/usr/local/lib/php


#### DO NOT TOUCH FROM HERE ONWARDS ###

export VERSION=2.2
# better alternative: try to recover version number from code - still have to figure out bash escaping intricacies, damnit!
#export VERSION := $(shell egrep '[ \t]*$GLOBALS *\[ *''xmlrpcVersion'' *\] *= *''(.+)'' *;' xmlrpc.inc | sed 's/[ \t]*\$GLOBALS *\[ *''xmlrpcVersion'' *\] *= *''//' | sed 's/'' *;//')

LIBFILES=xmlrpc.inc xmlrpcs.inc xmlrpc_wrappers.inc compat/*.php

EXTRAFILES=test.pl \
 test.py \
 rsakey.pem \
 workspace.testPhpServer.fttb

DEMOFILES=vardemo.php \
 demo1.txt \
 demo2.txt \
 demo3.txt

DEMOSFILES=discuss.php \
 server.php \
 proxy.php

DEMOCFILES=agesort.php \
 client.php \
 comment.php \
 introspect.php \
 mail.php \
 simple_call.php \
 which.php \
 wrap.php \
 zopetest.php

TESTFILES=testsuite.php \
 benchmark.php \
 parse_args.php \
 phpunit.php \
 verify_compat.php \
 PHPUnit/*.php

INFOFILES=Changelog \
 Makefile \
 NEWS \
 README

DEBUGGERFILES=debugger/index.php \
 debugger/action.php \
 debugger/common.php \
 debugger/controller.php

all: install

install:
	cd lib && cp ${LIBFILES} ${PHPINCLUDEDIR}
	@echo Lib files have been copied to ${PHPINCLUDEDIR}
	cd doc && $(MAKE) install

dist:
	@echo ---${VERSION}---
	rm -rf xmlrpc-${VERSION}
	mkdir xmlrpc-${VERSION}
	mkdir xmlrpc-${VERSION}/demo
	mkdir xmlrpc-${VERSION}/demo/client
	mkdir xmlrpc-${VERSION}/demo/server
	cp --parents ${DEMOFILES} xmlrpc-${VERSION}/demo
	cp --parents ${DEMOCFILES} xmlrpc-${VERSION}/demo/client
	cp --parents ${DEMOSFILES} xmlrpc-${VERSION}/demo/server
	mkdir xmlrpc-${VERSION}/test
	mkdir xmlrpc-${VERSION}/test/PHPUnit
	cp --parents ${TESTFILES} xmlrpc-${VERSION}/test
	mkdir xmlrpc-${VERSION}/extras
	cp --parents ${EXTRAFILES} xmlrpc-${VERSION}/extras
	mkdir xmlrpc-${VERSION}/lib
	mkdir xmlrpc-${VERSION}/lib/compat
	cp --parents ${LIBFILES} xmlrpc-${VERSION}/lib
	mkdir xmlrpc-${VERSION}/debugger
	cp ${DEBUGGERFILES} xmlrpc-${VERSION}/debugger
	cp ${INFOFILES} xmlrpc-${VERSION}
	cd doc && $(MAKE)
	find xmlrpc-${VERSION} -type f -exec dos2unix {} \;
	tar -cvf xmlrpc-${VERSION}.tar xmlrpc-${VERSION}
	gzip xmlrpc-${VERSION}.tar
	zip -r xmlrpc-${VERSION}.zip xmlrpc-${VERSION}

test:
	cd test && ${PHP} -q testsuite.php

clean:
	rm -rf xmlrpc-${VERSION}
	cd doc && $(MAKE) clean
