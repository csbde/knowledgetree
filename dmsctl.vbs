'
'     KnowledgeTree 
'
'
'

' Service Name Consts
Const KTOFFICE = "KTOpenoffice"
Const KTSCHEDULER = "KTScheduler"
Const KTLUCENE = "KTLucene"

' Service Control Manager Error Code Consts
Const SVC_SUCCESS = 0 ' Success
Const SVC_NOT_SUPPORTED = 1 ' Not Supported
Const SVC_ACCESS_DENIED = 2 ' Access Denied
Const SVC_DEPENDENT_SERVICES_RUNNING = 3 ' Dependent Services Running
Const SVC_INVALID_SERVICE_CONTROL = 4 ' Invalid Service Control
Const SVC_SERVICE_CANNOT_ACCEPT_CONTROL = 5 ' Service Cannot Accept Control
Const SVC_SERVICE_NOT_ACTIVE = 6 ' Service Not Active
Const SVC_SERVICE_REQUEST_TIMEOUT = 7 ' Service Request Timeout
Const SVC_UNKNOWN_FAILURE = 8 ' Unknown Failure
Const SVC_PATH_NOT_FOUND = 9 ' Path Not Found
Const SVC_SERVICE_ALREADY_RUNNING = 10 ' Service Already Running
Const SVC_SERVICE_DATABASE_LOCKED = 11 ' Service Database Locked
Const SVC_SERVICE_DEPENDENCY_DELETED = 12 ' Service Dependency Deleted
Const SVC_SERVICE_DEPENDENCY_FAILURE = 13 ' Service Dependency Failure
Const SVC_SERVICE_DISABLED = 14 ' Service Disabled
Const SVC_SERVICE_LOGON_FAILURE = 15 ' Service Logon Failure
Const SVC_SERVICE_MARKED_FOR_DELETION = 16 ' Service Marked For Deletion
Const SVC_SERVICES_NO_THREAD = 17 ' Service No Thread
Const SVC_STATUS_CIRCULAR_DEPENDENCY = 18 ' Status Circular Dependency
Const SVC_STATUS_DUPLICATE_NAME = 19 ' Status Duplicate Name
Const SVC_INVALID_NAME = 20 ' Status Invalid Name
Const SVC_STATUS_INVALID_PARAMETER = 21 ' Status Invalid Parameter
Const SVC_INVALID_SERVICES_ACCOUNT = 22 ' Status Invalid Service Account
Const SVC_STATUS_SERVICE_EXISTS = 23 ' Status Service Exists
Const SVC_SERVICE_ALREADY_PAUSED = 24 ' Service Already Paused

Dim strComputer, currOS, currDir, doRunAs, lastErrorCode

' Detecting the current directory
Set oFso = CreateObject("Scripting.FileSystemObject")
currDir = oFso.GetParentFolderName(Wscript.ScriptFullName)

strComputer = "."
currOS = ""
doRunAs = false

' Detecting the current OS
Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\cimv2")
Set colOperatingSystems = objWMIService.ExecQuery ("Select * from Win32_OperatingSystem")

For Each objOperatingSystem in colOperatingSystems
    currOS = objOperatingSystem.Caption & " " & objOperatingSystem.Version
    currOS = trim(currOS)
Next

Public Function isWindowsVista()
	isWindowsVista = false
	If left(currOS, 19) = "Microsoft Windows Vista" Then
	    isWindowsVista = TRUE
	End If
End Function

Public Function isWindows7()
	isWindows7 = false
	If left(currOS, 19) = "Microsoft Windows 7" Then
	    isWindows7 = TRUE
	End If
End Function

Public Function isWindows2008()
	isWindows2008 = false
	If mid(currOS, 27, 42) = "2008 Enterprise" Then
	    isWindows2008 = TRUE
	End If
End Function

' Will call this further down when the individual services need starting
If doRunAs = TRUE Then
    'runAs "C:\Program Files (x86)\Zend\ktdms\knowledgetree", "dmsctl_install.bat"
End If

Public Sub runAs(ByVal strFolder, ByVal strFile)
    Set objShell = CreateObject("Shell.Application")
    Set objFolder = objShell.Namespace(strFolder)
    Set objFolderItem = objFolder.ParseName(strFile)
    objFolderItem.InvokeVerb "runas"
End Sub

dim objArgs, errMsg, result, strUsage, isSuccess

strUsage = "USAGE:" &_
"dmsctl.vbs <start|stop|restart|install|uninstall> [servicename]" & vbNewLine &_
vbNewLine &_
"help        - this screen " & vbNewLine &_
"start       - start the services" & vbNewLine &_
"stop        - stop the services" & vbNewLine &_
"restart    - restart the services" & vbNewLine &_
"install      - install the services" & vbNewLine &_
"uninstall  - uninstall the services" & vbNewLine &_
vbNewLine &_
"servicename    - optional service name to start/stop only that service." & vbNewLine &_
"                           only mysql is supported for individual control at this time."

Set objArgs = WScript.Arguments
If (objArgs.count < 1) Then
	Wscript.Echo strUsage
Else
	Select Case objArgs.Item(0)
	Case "install"
		isSuccess = TRUE ' Track if anything went wrong
		
		' Installing KTOffice
		result = exec(currDir & "\var\bin\officeinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			writeLog "The " & KTOFFICE & " KnowledgeTree service could not be installed."
		Else 
			writeLog "The " & KTOFFICE & " KnowledgeTree service was successfully installed."
		End If
	
		' Installing KTScheduler
		result = exec(currDir & "\var\bin\schedulerinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			writeLog "The " & KTSCHEDULER & " KnowledgeTree service could not be installed."
		Else 
			writeLog "The " & KTSCHEDULER & " KnowledgeTree service was successfully installed."
		End If
		
		' Installing KTLucene
		result = exec(currDir & "\var\bin\luceneinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			writeLog "The " & KTLUCENE & " KnowledgeTree service could not be installed."
		Else 
			writeLog "The " & KTLUCENE & " KnowledgeTree service was successfully installed."
		End If
		
		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were successfully installed"
		Else 
			Wscript.Echo "There were errors installing the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If
	
	Case "start"
		If (objArgs.count > 1) Then
			Select Case objArgs.Item(1)
			Case "soffice"
				isSuccess = TRUE
				svcName = KTOFFICE
				If (NOT isServiceStarted(svcName)) Then
					If (NOT startService(svcName)) Then
						isSuccess = FALSE
						writeLog "The " & KTOFFICE & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTOFFICE & " KnowledgeTree service was successfully started"
					End If
					
					writeLog "Successfully started " & KTOFFICE
				Else
					writeLog KTOFFICE & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
				
				If (isSuccess) Then
					Wscript.Echo "The " & KTOFFICE & " KnowledgeTree service was successfully started"
				Else
					Wscript.Echo "The " & KTOFFICE & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
			
			Case "scheduler"
				isSuccess = TRUE
				svcName = KTSCHEDULER
				If (NOT isServiceStarted(svcName)) Then
					If (NOT startService(svcName)) Then
						isSuccess = FALSE
						writeLog "The " & KTSCHEDULER & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTSCHEDULER & " KnowledgeTree service was successfully started"
					End If
					
					writeLog "Successfully started " & KTSCHEDULER
				Else
					writeLog KTSCHEDULER & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If

				If (isSuccess) Then
					Wscript.Echo "The " & KTSCHEDULER & " KnowledgeTree service was successfully started"
				Else
					Wscript.Echo "The " & KTSCHEDULER & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
				
			Case "lucene"
				isSuccess = TRUE
				svcName = KTLUCENE
				If (NOT isServiceStarted(svcName)) Then
					If (NOT startService(svcName)) Then
						isSuccess = false
						writeLog "The " & KTLUCENE & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTLUCENE & " KnowledgeTree service was successfully started"
					End If
					
					writeLog "Successfully started " & KTLUCENE
				Else
					writeLog KTLUCENE & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If

				If (isSuccess) Then
					Wscript.Echo "The " & KTLUCENE & " KnowledgeTree service was successfully started"
				Else
					Wscript.Echo "The " & KTLUCENE & " KnowledgeTree service could not be started. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
			End Select
		Else
			isSuccess = TRUE
			
			svcName = KTOFFICE
			If (NOT isServiceStarted(svcName)) Then
				If (NOT startService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't start. " & KTOFFICE & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully started " & KTOFFICE
				End If
			Else
				writeLog KTOFFICE & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			svcName = KTSCHEDULER
			If (NOT isServiceStarted(svcName)) Then
				If (NOT startService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't start " & KTSCHEDULER & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully started " & KTSCHEDULER
				End If
			Else
				writeLog KTSCHEDULER & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			svcName = KTLUCENE
			If (NOT isServiceStarted(svcName)) Then
				If (NOT startService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't start " & KTLUCENE & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully started " & KTLUCENE
				End If
			Else
				writeLog KTLUCENE & " already started. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			If (isSuccess) Then
				Wscript.Echo "The KnowledgeTree services were successfully started"
			Else 
				Wscript.Echo "There were errors starting the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
			End If
		End If
	Case "stop"
		If (objArgs.count > 1) Then
			Select Case objArgs.Item(1)
			Case "soffice"	
				isSuccess = TRUE
				svcName = KTOFFICE
				If (isServiceStarted(svcName)) Then
					If (NOT stopService(svcName)) Then
						isSuccess = false
						writeLog "The " & KTOFFICE & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTOFFICE & " KnowledgeTree service was successfully stopped"
					End If
					
					writeLog "Successfully stopped " & KTOFFICE
				Else
					writeLog KTOFFICE & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If

				If (isSuccess) Then
					Wscript.Echo "The " & KTOFFICE & " KnowledgeTree service was successfully stopped"
				Else
					Wscript.Echo "The " & KTOFFICE & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
				
			Case "scheduler"	
				isSuccess = TRUE
				svcName = KTSCHEDULER
				If (isServiceStarted(svcName)) Then
					If (NOT stopService(svcName)) Then
						isSuccess = false
						writeLog "The " & KTSCHEDULER & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTSCHEDULER & " KnowledgeTree service was successfully stopped"
					End If
					
					writeLog "Successfully stopped " & KTSCHEDULER
				Else
					writeLog KTSCHEDULER & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If

				If (isSuccess) Then
					Wscript.Echo "The " & KTSCHEDULER & " KnowledgeTree service was successfully stopped"
				Else
					Wscript.Echo "The " & KTSCHEDULER & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
				
			Case "lucene"	
				isSuccess = TRUE
				svcName = KTLUCENE
				If (isServiceStarted(svcName)) Then
					If (NOT stopService(svcName)) Then
						isSuccess = false
						writeLog "The " & KTLUCENE & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
					Else
						writeLog "The " & KTLUCENE & " KnowledgeTree service was successfully stopped"
					End If
					
					writeLog "Successfully stopped " & KTLUCENE
				Else
					writeLog KTLUCENE & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
				
				If (isSuccess) Then
					Wscript.Echo "The " & KTLUCENE & " KnowledgeTree service was successfully stopped"
				Else
					Wscript.Echo "The " & KTLUCENE & " KnowledgeTree service could not be stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
				End If
			End Select
		Else
			'Stopping all the services
			isSuccess = TRUE
			
			svcName = KTOFFICE
			If (isServiceStarted(svcName)) Then
				If (NOT stopService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't stop." & KTOFFICE & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully stopped " & KTOFFICE
				End If
			Else
				writeLog KTOFFICE & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			svcName = KTSCHEDULER
			If (isServiceStarted(svcName)) Then
				If (NOT stopService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't stop." & KTSCHEDULER & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully stopped " & KTSCHEDULER
				End If
			Else
				writeLog KTSCHEDULER & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			svcName = KTLUCENE
			If (isServiceStarted(svcName)) Then
				If (NOT stopService(svcName)) Then
					isSuccess = false
					writeLog "Couldn't stop." & KTLUCENE & " Result Code: " & getServiceErrorMessage(lastErrorCode)
				Else
					writeLog "Successfully stopped " & KTLUCENE
				End If
			Else
				writeLog KTLUCENE & " already stopped. Result Code: " & getServiceErrorMessage(lastErrorCode)
			End If

			If (isSuccess) Then
				Wscript.Echo "The KnowledgeTree services were successfully stopped"
			Else 
				Wscript.Echo "There were errors sopping the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
			End If
		End If
		
	Case "uninstall"
		isSuccess = TRUE
		
		svcName = KTOFFICE
		If (NOT uninstallService(svcName)) Then
			isSuccess = false
			writeLog "The KnowledgeTree KTOffice service could not be uninstalled"
		End If

		svcName = KTSCHEDULER
		If (NOT uninstallService(svcName)) Then
			isSuccess = false
			writeLog "The KnowledgeTree KTScheduler service could not be uninstalled"
		End If

		svcName = KTLUCENE
		If (NOT uninstallService(svcName)) Then
			isSuccess = false
			writeLog "The KnowledgeTree KTLucene service could not be uninstalled"
		End If

		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were uninstalled"
		Else 
			Wscript.Echo "There were errors uninstalling the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If

	Case "uninstall_old" 'Depricated : This method prevents verbose error logging, using WMI to uninstall instead
		isSuccess = TRUE ' Track if anything went wrong
		
		' Stopping Then FALSE
		'svcName = KTOFFICE
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = false
		'	End If
		'End If
		
		' FALSE KTOffice
		result = exec("sc delete " & KTOFFICE)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = false
			writeLog "The KnowledgeTree KTOffice service could not be uninstalled"
		End If

		' Stopping Then FALSE
		'svcName = KTSCHEDULER
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = false
		'	End If
		'End If
		
		' FALSE KTScheduler
		result = exec("sc delete " & KTSCHEDULER)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = false
			writeLog "The KnowledgeTree KTScheduler service could not be uninstalled"
		End If
		
		' Stopping Then FALSE
		'svcName = KTLUCENE
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = FALSE
		'	End If
		'End If
		
		' FALSE KTLucene
		result = exec("sc delete " & KTLUCENE)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = FALSE
			writeLog "The KnowledgeTree KTLucene service could not be uninstalled"
		End If
		
		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were uninstalled"
		Else 
			Wscript.Echo "There were errors uninstalling the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If

		
	Case Else
		Wscript.Echo strUsage
	End Select

End If

' Method to check if a service is installed
Public Function isServiceInstalled(ByVal svcName)
	strComputer = "."
	Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\CIMV2") 
	
	' Obtain an instance of the the class 
	' using a key property value.
	
	isServiceInstalled = FALSE
	
	svcQry = "SELECT * from Win32_Service"
	Set objOutParams = objWMIService.ExecQuery(svcQry)

	For Each objSvc in objOutParams
	
	    Select Case objSvc.Name
	        Case svcName
				isServiceInstalled = TRUE
	    End Select
		
	Next

	If (Not isServiceInstalled) Then
		lastErrorCode = SVC_INVALID_NAME
	End If
	
End Function

' Method to interrogate a service
Public Function isServiceStarted(ByVal svcName)
	If (NOT isServiceInstalled( svcName )) Then
		isServiceStarted = FALSE
		Exit Function
	End If
	
	strComputer = "."
	Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\CIMV2") 
	
	' Obtain an instance of the the class 
	' using a key property value.
	Set objShare = objWMIService.Get("Win32_Service.Name='" & svcName & "'")
	
	' no InParameters to define

	' Execute the method and obtain the return status.
	' The OutParameters object in objOutParams
	' is created by the provider.
	Set objOutParams = objWMIService.ExecMethod("Win32_Service.Name='" & svcName & "'", "InterrogateService")

	lastErrorCode = objOutParams.ReturnValue
	
	If (objOutParams.ReturnValue = SVC_SERVICE_NOT_ACTIVE) Then
		isServiceStarted = FALSE
	Else 
		isServiceStarted = TRUE
	End If
	
end Function

' Method to start a service
Public Function startService(ByVal svcName)
	If (NOT isServiceInstalled( svcName )) Then
		writeLog "The KnowledgeTree " & svcName & " service could not be started because it isn't installed. Run 'dmsctl.vbs install' to correct."
		startService = FALSE
		Exit Function
	End If
	
	strComputer = "." 
	Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\CIMV2") 
	' Obtain an instance of the the class 
	' using a key property value.
	Set objShare = objWMIService.Get("Win32_Service.Name='" & svcName &"'")

	' no InParameters to define

	' Execute the method and obtain the return status.
	' The OutParameters object in objOutParams
	' is created by the provider.
	Set objOutParams = objWMIService.ExecMethod("Win32_Service.Name='" & svcName & "'", "StartService")

	lastErrorCode = objOutParams.ReturnValue

	If (objOutParams.ReturnValue = SVC_SUCCESS) Then
		startService = TRUE
	Else 
		startService = FALSE
	End If
	
End Function

' Method to stop a service
Public Function stopService(ByVal svcName)
	If (NOT isServiceInstalled( svcName )) Then
		writeLog "The KnowledgeTree " & svcName & " service could not be stopped because it isn't installed. Run 'dmsctl.vbs install' to correct."
		stopService = FALSE
		Exit Function
	End If

	strComputer = "." 
	Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\CIMV2") 
	' Obtain an instance of the the class 
	' using a key property value.
	Set objShare = objWMIService.Get("Win32_Service.Name='" & svcName &"'")

	' no InParameters to define

	' Execute the method and obtain the return status.
	' The OutParameters object in objOutParams
	' is created by the provider.
	Set objOutParams = objWMIService.ExecMethod("Win32_Service.Name='" & svcName & "'", "StopService")
	
	lastErrorCode = objOutParams.ReturnValue

	If (objOutParams.ReturnValue = SVC_SUCCESS) Then
		stopService = TRUE
	Else 
		stopService = FALSE
	End If
	
End Function

' Method to uninstall a service
Public Function uninstallService(ByVal svcName)
	If (NOT isServiceInstalled( svcName )) Then
		uninstallService = TRUE ' Service already uninstalled so return TRUE
		Exit Function
	End If

	wasStopped = TRUE
	If (NOT stopService(svcName)) Then
		wasStopped = FALSE
	End If
	
	strComputer = "." 
	Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\CIMV2") 
	' Obtain an instance of the the class 
	' using a key property value.
	Set objShare = objWMIService.Get("Win32_Service.Name='" & svcName & "'")

	' no InParameters to define

	' Execute the method and obtain the return status.
	' The OutParameters object in objOutParams
	' is created by the provider.
	Set objOutParams = objWMIService.ExecMethod("Win32_Service.Name='" & svcName & "'", "Delete")
	
	lastErrorCode = objOutParams.ReturnValue

	If (objOutParams.ReturnValue = SVC_SUCCESS) Then
		uninstallService = TRUE
	Else 
		uninstallService = FALSE
		
		' Extra Event logging to assist the Administrator
		If (wasStopped) Then
			writeLog "The KnowledgeTree " & svcName & " service could not be uninstalled because it could not be stopped. Stop this service manually before uninstalling."
			uninstallService = FALSE ' Must be stop service before it can be uninstalled
		End If

	End If

End Function


' Execute a command
Public Function exec(ByVal cmd)

	Dim WshShell, oExec
	Set WshShell = CreateObject("WScript.Shell")

	Set oExec = WshShell.Exec(cmd)

	Do While oExec.Status = 0
	     WScript.Sleep 100
	Loop

	exec = oExec.Status

End Function
	
Public Sub createCustomEventLog(ByVal strName)
	Const NO_VALUE = Empty

	Set WshShell = WScript.CreateObject("WScript.Shell")
	WshShell.RegWrite _
	    "HKLM\System\CurrentControlSet\Services\EventLog\" & strName & "\", NO_VALUE
End Sub

' Event logging only works on Server 2003, 2000 and XP
Public Sub logEvent(ByVal strMessage)
    ' Disabled until Windows Vista detection has been tested.
	'If (NOT isWindowsVista() AND NOT isWindows7() AND NOT isWindows2008()) Then 
	'	Const EVENT_SUCCESS = 0
	'	Set objShell = Wscript.CreateObject("Wscript.Shell")
	'	objShell.LogEvent EVENT_SUCCESS, strMessage
	'End If
End Sub

' Event logging only works on Server 2003, 2000 and XP
Public Sub writeLog(ByVal strMessage)
	Set objFSO = CreateObject("Scripting.FileSystemObject")
	ForAppending = 8
	Set objTextFile = objFSO.OpenTextFile (currDir & "\var\log\dmsctl.log", ForAppending, True)
	objTextFile.WriteLine strMessage
	objTextFile.Close
End Sub

Public Function getServiceErrorMessage(ByVal errCode)
	Select Case errCode
		Case SVC_SUCCESS 
			getServiceErrorMessage = "Success"
		Case SVC_NOT_SUPPORTED 
			getServiceErrorMessage =  "Not Supported"
		Case SVC_ACCESS_DENIED
			getServiceErrorMessage = "Access Denied"
		Case SVC_DEPENDENT_SERVICES_RUNNING
			getServiceErrorMessage = "Dependent Services Running"
		Case SVC_INVALID_SERVICE_CONTROL
			getServiceErrorMessage = "Invalid Service Control"
		Case SVC_SERVICE_CANNOT_ACCEPT_CONTROL 
			getServiceErrorMessage = "Service Cannot Accept Control"
		Case SVC_SERVICE_NOT_ACTIVE 
			getServiceErrorMessage = "Service Not Active"
		Case SVC_SERVICE_REQUEST_TIMEOUT 
			getServiceErrorMessage = "Service Request Timeout"
		Case SVC_UNKNOWN_FAILURE 
			getServiceErrorMessage = "Unknown Failure"
		Case SVC_PATH_NOT_FOUND 
			getServiceErrorMessage = "Path Not Found"
		Case SVC_SERVICE_ALREADY_RUNNING 
			getServiceErrorMessage = "Service Already Running"
		Case SVC_SERVICE_DATABASE_LOCKED 
			getServiceErrorMessage = "Service Database Locked"
		Case SVC_SERVICE_DEPENDENCY_DELETED 
			getServiceErrorMessage = "Service Dependency Deleted"
		Case SVC_SERVICE_DEPENDENCY_FAILURE 
			getServiceErrorMessage = "Service Dependency Failure"
		Case SVC_SERVICE_DISABLED 
			getServiceErrorMessage = "Service Disabled"
		Case SVC_SERVICE_LOGON_FAILURE 
			getServiceErrorMessage = "Service Logon Failure"
		Case SVC_SERVICE_MARKED_FOR_DELETION 
			getServiceErrorMessage = "Service Marked For Deletion"
		Case SVC_SERVICES_NO_THREAD 
			getServiceErrorMessage = "Service No Thread"
		Case SVC_STATUS_CIRCULAR_DEPENDENCY 
			getServiceErrorMessage = "Status Circular Dependency"
		Case SVC_STATUS_DUPLICATE_NAME 
			getServiceErrorMessage = "Status Duplicate Name"
		Case SVC_INVALID_NAME 
			getServiceErrorMessage = "Status Invalid Name"
		Case SVC_STATUS_INVALID_PARAMETER 
			getServiceErrorMessage = "Status Invalid Parameter"
		Case SVC_INVALID_SERVICES_ACCOUNT 
			getServiceErrorMessage = "Status Invalid Service Account"
		Case SVC_STATUS_SERVICE_EXISTS 
			getServiceErrorMessage = "Status Service Exists"
		Case SVC_SERVICE_ALREADY_PAUSED 
			getServiceErrorMessage = "Service Already Paused"
		Case Else
			getServiceErrorMessage = "Unknown Failure"
	End Select
End Function
