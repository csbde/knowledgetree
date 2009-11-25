'
'     KnowledgeTree 
'
'
'

' Service Name Consts
Const KTOFFICE = "KTOpenOffice"
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

'Detecting current OS
Dim strComputer, currOS, doRunAs

strComputer = "."
currOS = ""
doRunAs = false

Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\cimv2")
Set colOperatingSystems = objWMIService.ExecQuery ("Select * from Win32_OperatingSystem")

For Each objOperatingSystem in colOperatingSystems
    currOS = objOperatingSystem.Caption & " " & objOperatingSystem.Version
    currOS = trim(currOS)
Next

Public Function isWindowsVista()
	isWindows7 = false
	If left(currOS, 19) = "Microsoft Windows Vista" Then
	    isWindows7 = true
	End If
End Function

Public Function isWindows7()
	isWindows7 = false
	If left(currOS, 19) = "Microsoft Windows 7" Then
	    isWindows7 = true
	End If
End Function

Public Function isWindows2008()
	isWindows7 = false
	If left(currOS, 19) = "Microsoft Windows 2008" Then
	    isWindows7 = true
	End If
End Function

' Will call this further down when the individual services need starting
If doRunAs = true Then
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
"dmsctl.bat <start|stop|restart|install|uninstall> [servicename]" & vbNewLine &_
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
If objArgs.count < 1 Then
	Wscript.Echo strUsage
Else
	Select Case objArgs.Item(0)
	Case "install"
		isSuccess = true ' Track if anything went wrong
		
		' Installing KTOffice
		result = exec("C:\Program Files\Zend\ktdms\knowledgetree\var\bin\officeinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTOffice service could not be installed"
		End If
	
		' Installing KTScheduler
		result = exec("C:\Program Files\Zend\ktdms\knowledgetree\var\bin\schedulerinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTScheduler service could not be installed"
		End If
		
		' Installing KTLucene
		result = exec("C:\Program Files\Zend\ktdms\knowledgetree\var\bin\luceneinstall.bat")
		
		'Install Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTLucene service could not be installed"
		End If
		
		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were successfully installed"
		Else 
			Wscript.Echo "There were errors installing the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If
	
	Case "start"
		isSuccess = true
		
		svcName = KTOFFICE
		If (NOT isServiceStarted(svcName)) Then
			If (NOT startService(svcName)) Then
				isSuccess = false
			End If
		End If

		svcName = KTSCHEDULER
		If (NOT isServiceStarted(svcName)) Then
			If (NOT startService(svcName)) Then
				isSuccess = false
			End If
		End If

		svcName = KTLUCENE
		If (NOT isServiceStarted(svcName)) Then
			If (NOT startService(svcName)) Then
				isSuccess = false
			End If
		End If

		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were successfully started"
		Else 
			Wscript.Echo "There were errors starting the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If

	Case "stop"
		isSuccess = true
		
		svcName = KTOFFICE
		If (isServiceStarted(svcName)) Then
			If (NOT stopService(svcName)) Then
				isSuccess = false
			End If
		End If

		svcName = KTSCHEDULER
		If (isServiceStarted(svcName)) Then
			If (NOT stopService(svcName)) Then
				isSuccess = false
			End If
		End If

		svcName = KTLUCENE
		If (isServiceStarted(svcName)) Then
			If (NOT stopService(svcName)) Then
				isSuccess = false
			End If
		End If

		If (isSuccess) Then
			Wscript.Echo "The KnowledgeTree services were successfully stopped"
		Else 
			Wscript.Echo "There were errors sopping the KnowledgeTree services please see the log for details ('knowledgetree/var/log/dmsctl.log')"
		End If

	Case "uninstall"
		isSuccess = true ' Track if anything went wrong
		
		' Stopping Then Uninstalling
		'svcName = KTOFFICE
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = false
		'	End If
		'End If
		
		' Uninstalling KTOffice
		result = exec("sc delete " & KTOFFICE)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTOffice service could not be uninstalled"
		End If

		' Stopping Then Uninstalling
		'svcName = KTSCHEDULER
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = false
		'	End If
		'End If
		
		' Uninstalling KTScheduler
		result = exec("sc delete " & KTSCHEDULER)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTScheduler service could not be uninstalled"
		End If


		
		' Stopping Then Uninstalling
		'svcName = KTLUCENE
		'If (isServiceStarted(svcName)) Then
		'	If (NOT stopService(svcName)) Then
		'		isSuccess = false
		'	End If
		'End If
		
		' Uninstalling KTLucene
		result = exec("sc delete " & KTLUCENE)
		
		'Uninstall Failed
		If result = 0 Then
			isSuccess = false
			logEvent "The KnowledgeTree KTLucene service could not be uninstalled"
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
	
' Method to interrogate a service
Public Function isServiceStarted(ByVal svcName)
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

	If (objOutParams.ReturnValue = SVC_SERVICE_NOT_ACTIVE) Then
		isServiceStarted = FALSE
	Else 
		isServiceStarted = TRUE
	End If
		
end Function

' Method to start a service
Public Function startService(ByVal svcName)
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

	If (objOutParams.ReturnValue = SVC_SUCCESS) Then
		startService = TRUE
	Else 
		startService = FALSE
	End If
	
End Function

' Method to stop a service
Public Function stopService(ByVal svcName)
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

	If (objOutParams.ReturnValue = SVC_SUCCESS) Then
		stopService = TRUE
	Else 
		stopService = FALSE
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

	If (NOT isWindowsVista() AND NOT isWindows7 AND NOT isWindows2008) Then 
		Const EVENT_SUCCESS = 0
		Set objShell = Wscript.CreateObject("Wscript.Shell")
		objShell.LogEvent EVENT_SUCCESS, strMessage
	End If
	
End Sub
