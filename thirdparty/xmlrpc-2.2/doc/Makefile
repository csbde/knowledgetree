WEB=/var/www/xmlrpc/doc

dist: xmlrpc_php.xml
	-mkdir out
	@echo Jade cmd yet to be rebuilt, starting from xml file and putting output in ./out dir, e.g. jade -t xml -d custom.dsl xmlrpc_php.xml
	@echo convertdoc command for xmlmind xxe editor
	convertdoc docb.toHTML xmlrpc_php.xml -u out
	doxygen phpxmlrpc.dox
	mkdir -p ../xmlrpc-${VERSION}/doc
	-cp out/*.css ../xmlrpc-${VERSION}/doc
	-cp out/*.html ../xmlrpc-${VERSION}/doc
	-cp out/*.pdf ../xmlrpc-${VERSION}/doc
	cp xmlrpc_php.xml ../xmlrpc-${VERSION}/doc
	cp Makefile ../xmlrpc-${VERSION}/doc

clean:
	-rm -f out/*.html
	-rm -f out/*.css
	-rm -f javadoc-out/*.html
	-rm -f javadoc-out/*.css

install:
	mkdir -p ${WEB}
	cp *.html ${WEB}
	cp *.css ${WEB}
