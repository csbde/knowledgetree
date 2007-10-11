{   
  Copyright (c) 2007, The Jam Warehouse Software (Pty) Ltd.

  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions are met:

     i) Redistributions of source code must retain the above copyright notice,
        this list of conditions and the following disclaimer.
    ii) Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.
   iii) Neither the name of the The Jam Warehouse Software (Pty) Ltd nor the
        names of its contributors may be used to endorse or promote products
        derived from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
  EXEMPLARY, OR CONSEQUENTIAL DAMAGES ( INCLUDING, BUT NOT LIMITED TO,
  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
  PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
  LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT ( INCLUDING
  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
}

{*
  This is a Delphi port of the php api for KnowledgeTree WebService.

  @Author Bjarte Kalstveit Vebjørnsen <bjarte@macaos.com>
  @Version 1.0  BKV 24.09.2007  Initial revision
*}

unit uktwsapi;

interface

uses
  Classes, SysUtils, SOAPHTTPClient, uwebservice;

type

  /// Base exception class
  EKTWSAPI_Exception = class(Exception);

  TKTWSAPI_FolderItem = class;
  TKTWSAPI_Folder = class;
  TKTWSAPI_Document = class;
  TKTWSAPI = class;

  /// Base class for documents and folders
  TKTWSAPI_FolderItem = class(TObject)
  private
      FKTAPI: TKTWSAPI;             /// Handle to KTAPI object
      FParentId: Integer;           /// Id of parent folder

      function _GetFileSize(FileName: WideString): WideString;
      function _UploadFile(FileName, Action: WideString; DocumentId: Integer = 0): WideString;
      function _DownloadFile(Url, LocalPath, FileName: WideString): Boolean;
      function _SaveBase64StringAsFile(Base64String, LocalPath, FileName: WideString): Boolean;
      function _LoadFileAsBase64String(FileName: WideString): WideString;

  public
      function GetParentFolder:TKTWSAPI_Folder;
  end;

  /// Class representing a folder
  TKTWSAPI_Folder = class(TKTWSAPI_FolderItem)
  private
      FFolderName,                  /// Name of folder
      FFullPath: WideString;        /// Full path to folder

      FFolderId: Integer;           /// Id to folder
  public
      constructor Create(KTAPI:TKTWSAPI; FolderDetail: kt_folder_detail); overload;
      class function Get(KTAPI: TKTWSAPI; FolderId:Integer): TKTWSAPI_Folder;
      function GetParentFolderId: Integer;
      function GetFolderName: WideString;
      function GetFolderId: Integer;
      function GetFolderByName(FolderName: WideString): TKTWSAPI_Folder;
      function GetFullPath: WideString;
      function GetListing(Depth: Integer=1; What: WideString = 'DF'): kt_folder_contents;
      function GetDocumentByName(Title: WideString): TKTWSAPI_Document;
      function GetDocumentByFileName(FileName: WideString): TKTWSAPI_Document;
      function AddFolder(FolderName: WideString): TKTWSAPI_Folder;
      function Delete(Reason: WideString): Boolean;
      function Rename(NewName: WideString): Boolean;
      function Move(TargetFolder:TKTWSAPI_Folder; Reason: WideString): Boolean;
      function Copy(TargetFolder:TKTWSAPI_Folder; Reason: WideString): Boolean;
      function AddDocument(FileName: WideString; Title: WideString = '';
          DocumentType: WideString = ''): TKTWSAPI_Document;
      function AddDocumentBase64(FileName: WideString; Title: WideString = '';
          DocumentType: WideString = ''): TKTWSAPI_Document;
  published
      property FolderName: WideString read FFolderName write FFolderName;
      property FullPath: WideString read FFullPath write FFullPath;
      property FolderId: Integer read FFolderId write FFolderId;
  end;

  /// Class representing a document
  TKTWSAPI_Document = class(TKTWSAPI_FolderItem)
  private

      FDocumentId: Integer;           /// Id of document
      FTitle,                         /// Title of document
      FDocumentType,                  /// Type of document
      FVersion,                       /// Document version
      FFileName,                      /// Original filename
      FCreatedDate,                   /// Date created
      FCreatedBy,                     /// Name of user who created
      FUpdatedDate,                   /// Date updated
      FUpdatedBy,                     /// Name of user who updated
      FWorkflow,                      /// Workflow
      FWorkflowState,                 /// Workflow state
      FCheckoutBy,                    /// Name of user who checked out
      FFullPath: WideString;          /// Full path to document
  public
      constructor Create(KTAPI: TKTWSAPI; DocumentDetail: kt_document_detail);

      class function Get(KTAPI: TKTWSAPI; DocumentId: Integer;
        LoadInfo: Boolean = System.True): TKTWSAPI_Document;
      function Checkin(FileName, Reason: WideString; MajorUpdate: Boolean): Boolean;
      function Checkout(Reason: WideString; LocalPath: WideString = '';
        DownloadFile: Boolean = True): Boolean;
      function UndoCheckout(Reason: WideString): Boolean;
      function Download(Version: WideString = ''; LocalPath: WideString = ''; FileName: WideString = ''): Boolean;
      function Delete(Reason: WideString): Boolean;
      function ChangeOwner(UserName, Reason: WideString): Boolean;
      function Copy(Folder: TKTWSAPI_Folder; Reason: WideString;
        NewTitle: WideString = ''; NewFileName: WideString = ''): Boolean;
      function Move(Folder: TKTWSAPI_Folder; Reason: WideString;
        NewTitle: WideString = ''; NewFileName: WideString = ''): Boolean;
      function ChangeDocumentType(DocumentType: WideString): Boolean;
      function RenameTitle(NewTitle: WideString): Boolean;
      function RenameFilename(NewFilename: WideString): Boolean;
      function StartWorkflow(WorkFlow: WideString): Boolean;
      function DeleteWorkflow: Boolean;
      function PeformWorkflowTransition(Transition, Reason: WideString): Boolean;
      function GetMetadata:kt_metadata_response;
      function UpdateMetadata(Metadata: kt_metadata_fieldsets): Boolean;
      function GetTransactionHistory: kt_document_transaction_history_response;
      function GetVersionHistory: kt_document_version_history_response;
      function GetLinks: kt_linked_document_response;
      function Link(DocumentId: Integer; const LinkType: WideString): Boolean;
      function Unlink(DocumentId: Integer): Boolean;
      function CheckinBase64(FileName, Reason: WideString; MajorUpdate: Boolean): Boolean;
      function CheckoutBase64(Reason: WideString; LocalPath: WideString = '';
        DownloadFile: Boolean = True): Boolean;
      function DownloadBase64(Version: WideString = ''; LocalPath: WideString = ''): Boolean;
      function GetTypes: kt_document_types_response;
      function GetLinkTypes: kt_document_types_response;

      property DocumentId: Integer read FDocumentId write FDocumentId;
      property Title: WideString read FTitle write FTitle;
      property DocumentType: WideString read FDocumentType write FDocumentType;
      property Version: WideString read FVersion write FVersion;
      property FileName: WideString read FFileName write FFileName;
      property CreatedDate: WideString read FCreatedBy write FCreatedBy;
      property CreatedBy: WideString read FCreatedBy write FCreatedBy;
      property UpdatedDate: WideString read FUpdatedDate write FUpdatedDate;
      property UpdatedBy: WideString read FUpdatedBy write FUpdatedBy;
      property Workflow: WideString read FWorkflow write FWorkflow;
      property WorkflowState: WideString read FWorkflowState write FWorkflowState;
      property CheckoutBy: WideString read FCheckoutBy write FCheckoutBy;
      property FullPath: WideString read FFullPath write FFullPath;
  end;

  /// Api entry point
  TKTWSAPI = class
  private

      FSession,                           /// Current session id
      FDownloadPath: WideString;          /// Current download path

      FSoapClient:KnowledgeTreePort;      /// Object implementing the
                                          /// KnowledgeTreePort interface
  public
      constructor Create();
      function GetDownloadPath: WideString;
      function SetDownloadPath(DownloadPath:WideString): Boolean;
      function StartAnonymousSession(Ip: WideString = ''): WideString;
      function StartSession(Username, Password: WideString; Ip: WideString = ''): WideString;
      function ActiveSession(Session: WideString; Ip: WideString = ''): WideString;
      function Logout: Boolean;
      function GetRootFolder: TKTWSAPI_Folder;
      function GetFolderById(FolderId: Integer): TKTWSAPI_Folder;
      function GetDocumentById(DocumentId: Integer): TKTWSAPI_Document;
  published
      property SoapClient: KnowledgeTreePort read FSoapClient write FSoapClient;
      property Session: WideString read FSession write FSession;
  end;
var
  KTWebServerUrl: WideString; /// Your webserver url
  KTUploadUrl: WideString; /// URL to the web-service upload.php
  KTWebServiceUrl: WideString;  /// URL to the web-service wsdl

implementation

uses
  IdComponent, IdURI, IdHttp, IdMultipartFormData, IdGlobalProtocols,
  uPHPSerialize, EncdDecd;
const
 KTWSAPI_ERR_SESSION_IN_USE =
 'There is a session already active.'; /// Exception message when session is in use
 KTWSAPI_ERR_SESSION_NOT_STARTED =
 'An active session has not been started.';  /// Exception message when session is not started


{ TKTWSAPI_FolderItem }

{*
  Finds the filesize of a file.

  @param FileName Path to the file
  @return The size of the file as a string
*}
function TKTWSAPI_FolderItem._GetFileSize(FileName: WideString): WideString;
var
  SearchRec: TSearchRec;
  sgPath: string;
  inRetval, I1: Integer;
begin
  sgPath := ExpandFileName(FileName);
  try
    inRetval := FindFirst(ExpandFileName(FileName), faAnyFile, SearchRec);
    if inRetval = 0 then
      I1 := SearchRec.Size
    else
      I1 := -1;
  finally
    SysUtils.FindClose(SearchRec);
  end;
  Result := IntToStr(I1);
end;

{*
  Reads a file into a string and base64 encodes it.

  @param Base64String Base64 encoded string
  @param LocalPath Path to load from
  @param FileName FileName to read
  @return base64 encoded string
  @throws EKTWSAPI_Exception 'Could not access file to read.'
*}
function TKTWSAPI_FolderItem._LoadFileAsBase64String(FileName: WideString): WideString;
var
  Stream: TFileStream;
  InString: AnsiString;
begin
  if not FileExists(FileName) then
    raise EKTWSAPI_Exception.Create('Could not access file to read.');
  Stream := TFileStream.Create(FileName, fmOpenRead);
  try
    SetLength(InString, Stream.Size);
    Stream.ReadBuffer(InString[1], Length(InString));
    Result := EncodeString(InString);
  finally
    Stream.Free;
  end;
end;

{*
  Save a Base64 encoded string as a file.

  @param Base64String Base64 encoded string
  @param LocalPath Path to save to
  @param FileName FileName to save as
  @return true if success
*}
function TKTWSAPI_FolderItem._SaveBase64StringAsFile(Base64String, LocalPath,
  FileName: WideString): Boolean;
var
  OutString: AnsiString;
  Stream: TFileStream;
  LocalFileName: String;
begin
  LocalFileName := LocalPath + '/' + FileName;
  OutString := DecodeString(Base64String);
  Stream := TFileStream.Create(LocalFileName, fmCreate);
  try
    // For some reason it fails if I use WideString instead of AnsiString
    Stream.WriteBuffer(Pointer(OutString)^, Length(OutString));
    Result := true;
  finally
    Stream.Free;
  end;
end;

{*
  Upload a file to KT.

  @param FileName Path to upload file
  @param Action Which action to perform with the file (A = Add, C = Checkin)
  @param DocumentId Id of the document
  @return The temporary filename on the server
  @throws EKTWSAPI_Exception Could not access file to upload.
  @throws EKTWSAPI_Exception No response from server.
  @throws EKTWSAPI_Exception Could not upload file.
*}
function TKTWSAPI_FolderItem._UploadFile(FileName, Action: WideString;
  DocumentId: Integer): WideString;
var
  UploadName, UploadStatus, SessionId, StatusCode: WideString;
  PostStream: TIdMultiPartFormDataStream;
  ResponseStream: TStringStream;
  Fields: TStringList;
  HTTP: TIdHTTP;
  UploadData: TPHPValue;
  FilesArr: TPHPArray;
begin
  Result := '';
  if not FileExists(FileName) then
    raise EKTWSAPI_Exception.Create('Could not access file to upload.');
  // TODO: Check if file is readable

  if (DocumentId = 0) then
    UploadName := 'upload_document'
  else
    UploadName := 'upload_'+IntToStr(DocumentId);
  SessionId := FKTAPI.Session;

  HTTP := TIdHttp.Create(nil);
  try
    PostStream := TIdMultiPartFormDataStream.Create;
    ResponseStream := TStringStream.Create('');
    Fields := TStringList.Create;
    try
      PostStream.AddFormField('session_id', SessionId);
      PostStream.AddFormField('action', Action);
      PostStream.AddFormField('document_id',IntToStr(DocumentId));
      PostStream.AddFormField(UploadName,'@' + FileName);
      PostStream.AddFile('file',FileName,GetMIMETypeFromFile(FileName));

      HTTP.Request.ContentType := PostStream.RequestContentType;
      HTTP.Post(KTUploadURL, PostStream, ResponseStream);
      if (ResponseStream.DataString = '') then
        raise EKTWSAPI_Exception.Create('No response from server.');
      ExtractStrings(['&'], [' '], pAnsiChar(ResponseStream.DataString), Fields);

      StatusCode := Copy(Fields[0], Pos('=',Fields[0])+1, 1);
      if (StatusCode <> '0') then
        raise EKTWSAPI_Exception.Create('Could not upload file.');


      UploadStatus := Copy(Fields[1], Pos('=',Fields[1])+1, Length(Fields[1]));
      UploadStatus := TIdURI.URLDecode(UploadStatus);
      UploadData := TPHPSerialize.Unserialize(TIdURI.URLDecode(UploadStatus));
      Assert(Assigned(UploadData));
      Assert(Assigned(UploadData.AsArray['file']));
      try
        FilesArr := UploadData.AsArray['file'].AsArray;

        if (FilesArr['size'].AsString <> _GetFileSize(FileName)) then
          raise EKTWSAPI_Exception.Create('Could not upload file.');

        Result := FilesArr['tmp_name'].AsString;
      finally
        UploadData.Free;
      end;
    finally
      PostStream.Free;
      ResponseStream.Free;
      Fields.Free;
    end;
  finally
    HTTP.Free;
  end;
end;

{*
  Downloads a file from KT.

  @param Url Http-url to download
  @param LocalPath Path to save to
  @param FileName FileName to save as
  @return true if success
  @throws EKTWSAPI_Exception Could not create local file
*}
function TKTWSAPI_FolderItem._DownloadFile(Url, LocalPath,
  FileName: WideString): Boolean;
var
  Stream: TMemoryStream;
  LocalFileName: WideString;
  FP: File;
  HTTP: TIdHTTP;
begin
  LocalFileName := LocalPath + '/' + FileName;

  AssignFile(FP, LocalFileName);
  {$I-}
  Rewrite(FP,1);
  {$I+}
  if (IOResult <> 0) then
    raise EKTWSAPI_Exception.Create('Could not create local file');
  CloseFile(FP);
  HTTP := TIdHttp.Create(Nil);
  try
    Stream := TMemoryStream.Create;
    try
      HTTP.Get(Url, Stream);
      Stream.SaveToFile(LocalFileName);
      Result := true;
    finally
      Stream.Free;
    end;
  finally
    HTTP.Free;
  end;
end;
        
{*
  Returns a reference to the parent folder.

  @return Handle to parent folder
*}
function TKTWSAPI_FolderItem.GetParentFolder: TKTWSAPI_Folder;
begin
  Result := FKTAPI.GetFolderById(FParentId);
end;


 { TKTWSAPI_Folder }

{*
  Constructor

  @param KTAPI Handle to KTAPI object
  @param FolderDetail Handle to kt_folder_detail
*}
constructor TKTWSAPI_Folder.Create(KTAPI: TKTWSAPI;
  FolderDetail: kt_folder_detail);
begin
  FKTAPI := KTAPI;
  FFolderId := FolderDetail.id;
  FFolderName := FolderDetail.folder_name;
  FParentId := FolderDetail.parent_id;
  FFullPath := FolderDetail.full_path;
end;

{*
  Returns a reference to a TKTWSAPI_Folder

  @param KTAPI Handle to KTAPI object
  @param FolderId Id of folder to fetch
  @return folder handle
  @throws EKTWSAPI_Exception Response message  
*}
class function TKTWSAPI_Folder.Get(KTAPI: TKTWSAPI;
  FolderId: Integer): TKTWSAPI_Folder;
var
  FolderDetail: kt_folder_detail;
begin
  Assert(Assigned(KTAPI));
  Assert(KTAPI.ClassNameIs('TKTWSAPI'));
  Result := nil;
  
  FolderDetail := KTAPI.SoapClient.get_folder_detail(KTAPI.Session, FolderId);
  try
    if (FolderDetail.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(FolderDetail.message_);

    Result := TKTWSAPI_Folder.Create(KTAPI, FolderDetail);

  finally
    FolderDetail.Free;
  end;
end;

{*
  Returns the parent folder id.

  @return parent folder id
*}
function TKTWSAPI_Folder.GetParentFolderId: Integer;
begin
  Result := FParentId;
end;

{*
  Returns the folder name.

  @return folder name
*}
function TKTWSAPI_Folder.GetFolderName: WideString;
begin
  Result := FFolderName;
end;

{*
  Returns the current folder id.

  @return current folder id
*}
function TKTWSAPI_Folder.GetFolderId: Integer;
begin
  Result := FFolderId;
end;

{*
  Returns the folder based on foldername.

  @param FolderName Name of folder
  @return folder handle
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.GetFolderByName(FolderName: WideString): TKTWSAPI_Folder;
var
  Path: WideString;
  FolderDetail: kt_folder_detail;
begin
  Path := FFullPath + '/' + FolderName;
  if (System.Copy(Path, 0, 13) = '/Root Folder/') then
    Path := System.Copy(Path, 13, Length(Path)-1);
  if (System.Copy(Path, 0, 12) = 'Root Folder/') then
    Path := System.Copy(Path, 12, Length(Path)-1);

  FolderDetail := FKTAPI.SoapClient.get_folder_detail_by_name(FKTAPI.Session,
    Path);

  if (FolderDetail.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(FolderDetail.message_);

  Result := TKTWSAPI_Folder.Create(FKTAPI, FolderDetail);
end;

{*
  Returns the full folder path.

  @return Full folder path
*}
function TKTWSAPI_Folder.GetFullPath: WideString;
begin
  Result := FFullPath;
end;

{*
  Returns the contents of a folder.

  @param Depth How many sub-folders to fetch
  @param What to fetch (F=Folders, D=Documents, FD=Both)
  @return folder contents handle
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.GetListing(Depth: Integer;
  What: WideString): kt_folder_contents;
begin
  Result := FKTAPI.SoapClient.get_folder_contents(
    FKTAPI.Session, FFolderId, Depth, What);

  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Returns a document based on title.

  @param Title Title of document
  @return document handle
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.GetDocumentByName(Title: WideString): TKTWSAPI_Document;
var
  Path: WideString;
  DocumentDetail: kt_document_detail;
begin
  Path := FFullPath + '/' + Title;
  if (System.Copy(Path, 0, 13) = '/Root Folder/') then
    Path := System.Copy(Path, 13, Length(Path)-1);
  if (System.Copy(Path, 0, 12) = 'Root Folder/') then
    Path := System.Copy(Path, 12, Length(Path)-1);

  DocumentDetail := FKTAPI.SoapClient.get_document_detail_by_name(FKTAPI.Session,
    Path, 'T');

  if (DocumentDetail.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(DocumentDetail.message_);

  Result := TKTWSAPI_Document.Create(FKTAPI, DocumentDetail);
end;

{*
  Returns a document based on filename.

  @param FileName Name of file
  @return document handle
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.GetDocumentByFileName(
  FileName: WideString): TKTWSAPI_Document;
var
  Path: WideString;
  DocumentDetail: kt_document_detail;
begin
  Result := nil;
  Path := FFullPath + '/' + FileName;
  if (System.Copy(Path, 0, 13) = '/Root Folder/') then
    Path := System.Copy(Path, 13, Length(Path)-1);
  if (System.Copy(Path, 0, 12) = 'Root Folder/') then
    Path := System.Copy(Path, 12, Length(Path)-1);

  DocumentDetail := FKTAPI.SoapClient.get_document_detail_by_name(FKTAPI.Session,
      Path, 'F');
  try
    if (DocumentDetail.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(DocumentDetail.message_);
    Result := TKTWSAPI_Document.Create(FKTAPI, DocumentDetail);
  finally
    DocumentDetail.Free;
  end;
end;

{*
  Adds a sub folder.

  @param FolderName Name of folder
  @return new folder handle
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.AddFolder(FolderName: WideString): TKTWSAPI_Folder;
var
  FolderDetail: kt_folder_detail;
begin
  Result := nil;
  FolderDetail := FKTAPI.SoapClient.create_folder(FKTAPI.Session, FFolderId, FolderName);
  try
    if (FolderDetail.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(FolderDetail.message_);

     Result := TKTWSAPI_Folder.Create(FKTAPI, FolderDetail);
  finally
    FolderDetail.Free;
  end;
end;

{*
  Deletes the current folder.

  @param Reason Reason for deletetion
  @return true
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.Delete(Reason: WideString): Boolean;
var
  response: kt_response;
begin
  // TODO: check why no transaction in folder_transactions
  Result := System.False;
  response := FKTAPI.SoapClient.delete_folder(FKTAPI.Session,
    FFolderId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Renames the current folder.

  @param NewName New folder name
  @return true
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Folder.Rename(NewName: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.rename_folder(FKTAPI.Session,
    FFolderId, NewName);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Moves a folder to another location.

  @param TargetFolder Handle to target folder
  @param Reason Reason for move
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Folder.Move(TargetFolder: TKTWSAPI_Folder;
  Reason: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  Assert(Assigned(TargetFolder));
  Assert(TargetFolder.ClassNameIs('TKTWSAPI_Folder'));

  response := FKTAPI.SoapClient.move_folder(FKTAPI.Session,
    FFolderId, TargetFolder.GetFolderId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Copies a folder to another location

  @param TargetFolder Handle to target folder
  @param Reason Reason for copy
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Folder.Copy(TargetFolder: TKTWSAPI_Folder;
  Reason: WideString): Boolean;
var
  TargetId: Integer;
  response: kt_response;
begin
  Result := System.False;
  Assert(Assigned(TargetFolder));
  Assert(TargetFolder.ClassNameIs('TKTWSAPI_Folder'));

  TargetId := TargetFolder.GetFolderId;
  response := FKTAPI.SoapClient.copy_folder(FKTAPI.Session,
    FFolderId, TargetId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Adds a document to the current folder.

  @param FileName FileName to upload
  @param Title Title to give document
  @param DocumentType Documenttype of document (Default is 'Default')
  @return handle to new document
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Folder.AddDocument(FileName, Title,
  DocumentType: WideString): TKTWSAPI_Document;
var
  BaseName, TempFileName: WideString;
  DocumentDetail: kt_document_detail;
begin
  Result := nil;
  BaseName := ExtractFileName(FileName);
  if (Title = '') then
    Title := BaseName;
  if (DocumentType = '') then
    DocumentType := 'Default';

  TempFileName := _UploadFile(FileName, 'A');
  DocumentDetail := FKTAPI.FSoapClient.add_document(FKTAPI.Session, FFolderId,
    Title, BaseName, DocumentType, TempFileName);
  try
    if (DocumentDetail.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(DocumentDetail.message_);

     Result := TKTWSAPI_Document.Create(FKTAPI, DocumentDetail);
  finally
    DocumentDetail.Free;
  end;
end;

{*
  Adds a document to the current folder through web service.

  @param FileName FileName to upload
  @param Title Title to give document
  @param DocumentType Documenttype of document (Default is 'Default')
  @return handle to new document
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Folder.AddDocumentBase64(FileName, Title,
  DocumentType: WideString): TKTWSAPI_Document;
begin
  raise EKTWSAPI_Exception.Create('Not implemented yet!');
end;

{ TKTWSAPI_Document }

{*
  Constructor

  @param KTAPI Handle to KTAPI object
  @param DocumentDetail handle to kt_document_detail
*}
constructor TKTWSAPI_Document.Create(KTAPI: TKTWSAPI;
  DocumentDetail: kt_document_detail);
begin
  FKTAPI := KTAPI;
  FDocumentId := DocumentDetail.document_id;
  FTitle := DocumentDetail.title;
  FDocumentType := DocumentDetail.document_type;
  FVersion := DocumentDetail.version;
  FFilename := DocumentDetail.filename;
  FCreatedDate := DocumentDetail.created_date;
  FCreatedBy := DocumentDetail.created_by;
  FUpdatedDate := DocumentDetail.updated_date;
  FUpdatedBy := DocumentDetail.updated_by;
  FParentId := DocumentDetail.folder_id;
  FWorkflow := DocumentDetail.workflow;
  FWorkflowState := DocumentDetail.workflow_state;
  FCheckoutBy := DocumentDetail.checkout_by;
  FFullPath := DocumentDetail.full_path;
end;

{*
  Returns a reference to a document.

  @param KTAPI Handle to KTAPI object
  @param DocumentId Id of document
  @param LoadInfo Call web service to load document details
  @return handle to document
  @throws EKTWSAPI_Exception Response message
*}
class function TKTWSAPI_Document.Get(KTAPI: TKTWSAPI; DocumentId: Integer;
  LoadInfo: Boolean): TKTWSAPI_Document;
var
  DocumentDetail:kt_document_detail;
begin
  Assert(Assigned(KTAPI));
  Assert(KTAPI.ClassNameIs('TKTWSAPI'));
  if LoadInfo then
  begin
    DocumentDetail := KTAPI.SoapClient.get_document_detail(KTAPI.Session, DocumentId);

    if (DocumentDetail.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(DocumentDetail.message_);

  end else
  begin
    DocumentDetail := kt_document_detail.Create;
    DocumentDetail.document_id := DocumentId;
  end;
  try
    Result := TKTWSAPI_Document.Create(KTAPI, DocumentDetail);
  finally
    DocumentDetail.Free;
  end;
end;

{*
  Checks in a document.

  @param FileName Name of file to checkin
  @param Reason Reason for checkin
  @param MajorUpdate Checkin as a major update (bumps major version number)
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Checkin(FileName, Reason: WideString;
  MajorUpdate: Boolean): Boolean;
var
  BaseName, TempFileName: WideString;
  response: kt_response;
begin
  Result := System.False;
  BaseName := ExtractFileName(FileName);

  TempFileName := _UploadFile(FileName, 'C', FDocumentId);
  response := FKTAPI.SoapClient.checkin_document(FKTAPI.Session, FDocumentId, BaseName, Reason, TempFileName, MajorUpdate);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
      Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Checks out a document.

  @param Reason Reason for checkout
  @param LocalPath to save downloaded file to
  @param DownloadFile if false then checkout will happen without download
  @return true
  @throws EKTWSAPI_Exception local path does not exist
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Checkout(Reason, LocalPath: WideString;
  DownloadFile: Boolean): Boolean;
var
  response: kt_response;
  Url: WideString;
begin
  Result := System.False;
  if (LocalPath = '') then LocalPath := FKTAPI.GetDownloadPath;

  if not DirectoryExists(LocalPath) then
    raise EKTWSAPI_Exception.Create('local path does not exist');

  // TODO check if Directory is writable

  response := FKTAPI.SoapClient.checkout_document(FKTAPI.Session, FDocumentId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Url := response.message_;

    if DownloadFile then
      _DownloadFile(KTWebServerURL+Url, LocalPath, FFileName);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Undo a document checkout

  @param Reason Reason for undoing the checkout
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.UndoCheckout(Reason: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.undo_document_checkout(FKTAPI.Session, FDocumentId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Download a version of the document

  @param Version Which version of document to download
  @param LocalPath Optional path to save file to
  @param FileName Optional filename to save file as
  @return true
  @throws EKTWSAPI_Exception local path does not exist
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Download(Version, LocalPath, FileName: WideString): Boolean;
var
  response: kt_response;
  Url: WideString;
begin
  Result := System.False;
  if (LocalPath = '') then  LocalPath := FKTAPI.GetDownloadPath;
  if (FileName = '') then FileName := FFileName;

  if (not DirectoryExists(LocalPath)) then
    raise EKTWSAPI_Exception.Create('local path does not exist');

  // TODO: Check if local path is writable

  response := FKTAPI.SoapClient.download_document(FKTAPI.Session, FDocumentId);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Url := response.message_;
    Result := _DownloadFile(KTWebServerURL+Url, LocalPath, FileName);
  finally
    response.Free;
  end;
end;


{*
  Download a version of the document through webservice

  @param Version Which version of document to download
  @param LocalPath Path to save file to
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.DownloadBase64(Version,
  LocalPath: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  if (LocalPath = '') then  LocalPath := FKTAPI.GetDownloadPath;
  if (FileName = '') then FileName := FFileName;

  if (not DirectoryExists(LocalPath)) then
    raise EKTWSAPI_Exception.Create('local path does not exist');

  // TODO: Check if local path is writable

  response := FKTAPI.SoapClient.download_base64_document(FKTAPI.Session, FDocumentId);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := _SaveBase64StringAsFile(response.message_, LocalPath, FileName);
  finally
    response.Free;
  end;
end;

{*
  Deletes the current document.

  @param Reason Reason for delete
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Delete(Reason: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.delete_document(FKTAPI.Session, FDocumentId, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Changes the owner of the document.

  @param UserName Username of new owner
  @param Reason Reason for the owner change
  @return true
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Document.ChangeOwner(UserName, Reason: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.change_document_owner(FKTAPI.Session, FDocumentId, UserName, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Copies the current document to the specified folder.

  @param Folder Handle to target folder
  @param Reason Reason for copy
  @param NewTitle New title of the file
  @param NewFileName New filename of the file
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Copy(Folder: TKTWSAPI_Folder; Reason, NewTitle,
  NewFileName: WideString): Boolean;
var
  response: kt_response;
  FolderId: Integer;
begin
  Result := System.False;
  Assert(Assigned(Folder));
  Assert(Folder.ClassNameIs('TKTWSAPI_Folder'));
  FolderId := Folder.GetFolderId;

  response := FKTAPI.SoapClient.copy_document(FKTAPI.Session, FDocumentId, FolderId, Reason, NewTitle, NewFileName);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Moves the current document to the specified folder.

  @param Folder Handle to target folder
  @param Reason Reason for move
  @param NewTitle New title of the file
  @param NewFileName New filename of the file
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Move(Folder: TKTWSAPI_Folder; Reason, NewTitle,
  NewFileName: WideString): Boolean;
var
  response: kt_response;
  FolderId: Integer;
begin
  Result := System.False;
  Assert(Assigned(Folder));
  Assert(Folder.ClassNameIs('TKTWSAPI_Folder'));
  FolderId := Folder.GetFolderId;

  response := FKTAPI.SoapClient.move_document(FKTAPI.Session, FDocumentId, FolderId, Reason, NewTitle, NewFileName);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Changes the document type.

  @param DocumentType DocumentType to change to
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.ChangeDocumentType(DocumentType: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.change_document_type(FKTAPI.Session, FDocumentId, DocumentType);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Renames the title of the current document.

  @param NewTitle New title of the document
  @return true
  @throws EKTWSAPI_Exception Response message  
*}
function TKTWSAPI_Document.RenameTitle(NewTitle: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.rename_document_title(FKTAPI.Session, FDocumentId, NewTitle);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Renames the filename of the current document.

  @param NewFilename New filename of the document
  @return true
*}
function TKTWSAPI_Document.RenameFilename(NewFilename: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.rename_document_filename(FKTAPI.Session, FDocumentId, NewFilename);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Starts a workflow on the current document.

  @param WorkFlow Workflow
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.StartWorkflow(WorkFlow: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.start_document_workflow(FKTAPI.Session, FDocumentId, WorkFlow);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Removes the workflow process from the current document.

  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.DeleteWorkflow: Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.delete_document_workflow(FKTAPI.Session, FDocumentId);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Performs a transition on the current document.

  @param Transition Transition
  @param Reason Reason for transition
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.PeformWorkflowTransition(Transition,
  Reason: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.perform_document_workflow_transition(FKTAPI.Session, FDocumentId, Transition, Reason);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Returns metadata on the document.

  @return handle to metadata
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetMetadata: kt_metadata_response;
begin
  Result := FKTAPI.SoapClient.get_document_metadata(FKTAPI.Session, FDocumentId);

  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Updates the metadata on the current document.

  @param Metadata Handle to metadata
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.UpdateMetadata(
  Metadata: kt_metadata_fieldsets): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.update_document_metadata(FKTAPI.Session, FDocumentId, MetaData);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);

    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Returns the transaction history on the current document.

  @return handle to transaction history
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetTransactionHistory: kt_document_transaction_history_response;
begin
  Result := FKTAPI.SoapClient.get_document_transaction_history(FKTAPI.Session, FDocumentId);
  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Returns the version history on the current document.

  @return handle to document version history
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetVersionHistory: kt_document_version_history_response;
begin
  Result := FKTAPI.SoapClient.get_document_version_history(FKTAPI.Session, FDocumentId);
  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Returns the links of the current document

  @return handle to document version history
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetLinks: kt_linked_document_response;
begin
  Result := FKTAPI.SoapClient.get_document_links(FKTAPI.Session, FDocumentId);
  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Links the current document to a DocumentId

  @param DocumentId DocumentId to link to
  @param LinkType Type of link
  @return true  
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Link(DocumentId: Integer;
  const LinkType: WideString): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.link_documents(FKTAPI.Session, FDocumentId,
    DocumentId, LinkType);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Unlinks the current document from a DocumentId

  @param DocumentId DocumentId to unlink to
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.Unlink(DocumentId: Integer): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  response := FKTAPI.SoapClient.unlink_documents(FKTAPI.Session, FDocumentId,
    DocumentId);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Checks out a document and downloads document through webservice

  @param Reason Reason for checkout
  @param LocalPath to save downloaded file to
  @return true
  @throws EKTWSAPI_Exception local path does not exist
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.CheckoutBase64(Reason,
  LocalPath: WideString; DownloadFile: Boolean): Boolean;
var
  response: kt_response;
begin
  Result := System.False;
  if (LocalPath = '') then LocalPath := FKTAPI.GetDownloadPath;

  if not DirectoryExists(LocalPath) then
    raise EKTWSAPI_Exception.Create('local path does not exist');

  // TODO check if Directory is writable

  response := FKTAPI.SoapClient.checkout_base64_document(FKTAPI.Session, FDocumentId, Reason, DownloadFile);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    Result := True;
    if DownloadFile then
      Result := _SaveBase64StringAsFile(response.message_, LocalPath, FFileName);
  finally
    response.Free;
  end;
end;

{*
  Gets list of document types

  @return handle to document types response
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetTypes: kt_document_types_response;
begin
  Result := FKTAPI.SoapClient.get_document_types(FKTAPI.Session);
  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Get list of document link types
  @return handle to document types response
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.GetLinkTypes: kt_document_types_response;
begin
  Result := FKTAPI.SoapClient.get_document_link_types(FKTAPI.Session);
  if (Result.status_code <> 0) then
    raise EKTWSAPI_Exception.Create(Result.message_);
end;

{*
  Checks in a document and uploads through webservice

  @param FileName Name of file to checkin
  @param Reason Reason for checkin
  @param MajorUpdate Checkin as a major update (bumps major version number)
  @return true
  @throws EKTWSAPI_Exception Response message
*}
function TKTWSAPI_Document.CheckinBase64(FileName, Reason: WideString;
  MajorUpdate: Boolean): Boolean;
var
  Base64String, BaseName: WideString;
  response: kt_response;
begin
  Result := System.False;
  Base64String := _LoadFileAsBase64String(FileName);
  BaseName := ExtractFileName(FileName);

  response := FKTAPI.SoapClient.checkin_base64_document(FKTAPI.Session,
    FDocumentId, BaseName, Reason, Base64String, MajorUpdate);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
      Result := System.True;
  finally
    response.Free;
  end;
end;

{ TKTWSAPI }

{*
  Constructor
*}
constructor Tktwsapi.Create();
begin
  FSoapClient := GetKnowledgeTreePort(False, KTWebServiceUrl);
  FDownloadPath := '';
end;

{*
  This returns the default location where documents are downloaded in download() and checkout().

  @return string
*}
function TKTWSAPI.GetDownloadPath: WideString;
begin
  Result := FDownloadPath;
end;

{*
  Allows the default location for downloaded documents to be changed.

  @param DownloadPath Path to writable folder
  @return true
*}
function TKTWSAPI.SetDownloadPath(DownloadPath: WideString): Boolean;
begin
  if (not DirectoryExists(DownloadPath)) then
    raise EKTWSAPI_Exception.Create('local path is not writable');
  // TODO : Check if folder is writable

  FDownloadPath := DownloadPath;
  Result := System.True;
end;

{*
  Starts an anonymous session.

  @param Ip Users Ip-adress
  @return Active session id
*}
function TKTWSAPI.StartAnonymousSession(Ip: WideString): WideString;
begin
  Result := StartSession('anonymous', '', Ip);
end;

{*
  Starts a user session.

  @param Username Users username
  @param Password Users password
  @param Ip Users Ip-adress
  @return Active session id
  @throws EKTWSAPI_Exception KTWSAPI_ERR_SESSION_IN_USE
  @throws EKTWSAPI_Exception Response message   
*}
function TKTWSAPI.StartSession(Username, Password, Ip: WideString): WideString;
var
  response: kt_response;
begin
  if (FSession <> '') then
    raise EKTWSAPI_Exception.Create(KTWSAPI_ERR_SESSION_IN_USE);
  response := FSoapClient.login(Username, Password, Ip);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    FSession := response.message_;
    Result := FSession;
  finally
    response.Free;
  end;
end;

{*
  Sets an active session.

  @param Session Session id to activate
  @param Ip Users Ip-adress
  @return Active session id
  @throws EKTWSAPI_Exception KTWSAPI_ERR_SESSION_IN_USE
*}
function TKTWSAPI.ActiveSession(Session, Ip: WideString): WideString;
begin
  if (FSession <> '') then
    raise EKTWSAPI_Exception.Create(KTWSAPI_ERR_SESSION_IN_USE);
  FSession := Session;
  Result := FSession;
end;

{*
  Closes an active session.

  @return true
*}
function TKTWSAPI.Logout: Boolean;
var
  response: kt_response;
begin
  Result := System.False;

  if (FSession = '') then
    raise EKTWSAPI_Exception.Create(KTWSAPI_ERR_SESSION_NOT_STARTED);
  response := FSoapClient.logout(FSession);
  try
    if (response.status_code <> 0) then
      raise EKTWSAPI_Exception.Create(response.message_);
    FSession := '';
    Result := System.True;
  finally
    response.Free;
  end;
end;

{*
  Returns a reference to the root folder.

  @return handle to folder
*}
function TKTWSAPI.GetRootFolder: TKTWSAPI_Folder;
begin
  Result := GetFolderById(1);
end;

{*
  Returns a reference to a folder based on id.

  @param FolderId Id of folder
  @return handle to folder
*}
function TKTWSAPI.GetFolderById(FolderId: Integer): TKTWSAPI_Folder;
begin
  if FSession = '' then
    raise EKTWSAPI_Exception.Create('A session is not active');
  Result := TKTWSAPI_Folder.Get(Self, FolderId);
end;

{*
  Returns a reference to a document based on id.

  @param DocumentId Id of document
  @return handle to document
*}
function TKTWSAPI.GetDocumentById(DocumentId: Integer): TKTWSAPI_Document;
begin
  if FSession = '' then
    raise EKTWSAPI_Exception.Create('A session is not active');
  Result := TKTWSAPI_Document.Get(Self, DocumentId)
end;

end.
