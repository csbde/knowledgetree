' This script should only be invoked for Vista and Windows 7

'Detecting current OS
strComputer = "." 
currOS = ""
doRunAs = false

Set objWMIService = GetObject("winmgmts:\\" & strComputer & "\root\cimv2")
Set colOperatingSystems = objWMIService.ExecQuery ("Select * from Win32_OperatingSystem")

For Each objOperatingSystem in colOperatingSystems               
	currOS = objOperatingSystem.Caption & " " & objOperatingSystem.Version
	currOS = trim(currOS)
Next

If left(currOS, 19) = "Microsoft Windows 7" Then
	doRunAs = true
End If

If doRunAs = true Then
	Set objShell = CreateObject("Shell.Application")
	Set objFolder = objShell.Namespace("C:\Program Files (x86)\Zend\ktdms\knowledgetree")
	Set objFolderItem = objFolder.ParseName("dmsctl_install.bat")
	objFolderItem.InvokeVerb "runas"
End If