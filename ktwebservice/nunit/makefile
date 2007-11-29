RESULTS= authentication.result document_detail.result document_contents.result document_history.result folder.result document_metadata.result document_add.result document_checkout.result document_type.result document_links.result document_owner.result document_rename.result  document_workflow.result document_copy.result   document_system_metadata.result  document_download.result
PROXY=KTproxy.cs
WSDL=ktdms.wsdl
ROOT_URL=http://ktdms.trunk
WSDL_URL=${ROOT_URL}/ktwebservice/index.php?wsdl

all:  ${RESULTS}

results: clean-results ${RESULTS}

KTproxy.dll: KTproxy.cs helper.cs 
	mcs -r:System.Web.Services -r:System.Data.dll /target:library KTproxy.cs helper.cs

KTproxy.cs: ktdms.wsdl
	wsdl -out:${PROXY} ${WSDL}

ktdms.wsdl:
	curl ${WSDL_URL} > ${WSDL}

clean:
	rm -f *.dll *.exe ${WSDL} ${PROXY} TestResult.xml *.dll.mdb *.result

clean-results:
	rm -f ${RESULTS}

%.dll: %.cs  KTproxy.dll 
	mcs -r:System.Web.Services -r:nunit.framework /r:KTproxy.dll   -debug /target:library -out:$@ $<

%.result: %.dll
	(export KT_ROOT_URL=${ROOT_URL}; nunit-console $<)
	mv TestResult.xml $@
