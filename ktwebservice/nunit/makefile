RESULTS=authentication.result folder.result document.result 
PROXY=KTproxy.cs
WSDL=ktdms.wsdl
WSDL_URL=http://ktdms.search/ktwebservice/index.php?wsdl

all:  ${RESULTS}
	 
results: clean-results ${RESULTS}
		 
KTproxy.dll: KTproxy.cs
	mcs -r:System.Web.Services /target:library KTproxy.cs

KTproxy.cs: ktdms.wsdl
	wsdl -out:${PROXY} ${WSDL}

ktdms.wsdl:
	curl ${WSDL_URL} > ${WSDL}

clean:
	rm -f *.dll *.exe ${WSDL} ${PROXY} TestResult.xml *.dll.mdb *.result

clean-results:
	rm -f ${RESULTS}

%.dll: %.cs  KTproxy.dll
	mcs -r:System.Web.Services -r:nunit.framework /r:KTproxy.dll -debug /target:library -out:$@ $<

%.result: %.dll
	nunit-console $<
	mv TestResult.xml $@
