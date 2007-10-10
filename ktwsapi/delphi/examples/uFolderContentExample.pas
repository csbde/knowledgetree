unit uFolderContentExample;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, uKtwsapi, uWebService, ImgList, ComCtrls, StdCtrls;

type
  TFolderContentExample = class(TForm)
    tvFolderList: TTreeView;
    ImageList4: TImageList;
    Button1: TButton;
    procedure Button1Click(Sender: TObject);
    procedure tvFolderListCollapsed(Sender: TObject; Node: TTreeNode);
    procedure tvFolderListExpanded(Sender: TObject; Node: TTreeNode);
  private
    { Private declarations }
    FIsPopulating: Boolean;
    procedure PopulateTreeView(items: kt_folder_items;
      parent: TTreeNode; tv: TTreeView);
  public
    { Public declarations }
  end;

var
  FolderContentExample: TFolderContentExample;
  UserName, Password: String;

implementation

{$R *.dfm}

procedure TFolderContentExample.Button1Click(Sender: TObject);
var
  ktapi: TKTWSAPI;
  folder: TKTWSAPI_Folder;
  contents: kt_folder_contents;
begin
  if FIsPopulating then Exit;
  
  Screen.Cursor := crHourglass;

  FIsPopulating := True;
  ktapi := TKTWSAPI.Create;
  try
    ktapi.SetDownloadPath(ExtractFileDir(Application.ExeName));
    ktapi.StartSession(UserName,Password);

    folder:= ktapi.GetRootFolder;
    try
      contents := folder.GetListing(10);
      try
        PopulateTreeView(contents.items, nil, tvFolderList);
      finally
        contents.Free;
      end;
    finally
      folder.Free;
    end;

    ktapi.Logout;
  finally
    ktapi.Free;
    Screen.Cursor := crDefault;
    FIsPopulating := False;
  end;
end;

procedure TFolderContentExample.PopulateTreeView(items: kt_folder_items; parent: TTreeNode;
  tv: TTreeView);
var
  I: Integer;
  node: TTreeNode;
  it: kt_folder_item;
begin
  for I := 0 to Length(items) - 1 do
  begin
    it := items[i];
    if it.item_type <> 'F' then Continue;
    node := tv.Items.AddChild(parent, it.title);
    node.ImageIndex := 0;
    node.Data := it;
    if (Length(it.items) > 0) then
      PopulateTreeView(it.items, node, tv);
  end;
end;

procedure TFolderContentExample.tvFolderListCollapsed(Sender: TObject;
  Node: TTreeNode);
begin
  Node.ImageIndex := 0;
end;

procedure TFolderContentExample.tvFolderListExpanded(Sender: TObject;
  Node: TTreeNode);
begin
  Node.ImageIndex := 1;  
end;

initialization
  UserName := 'xxxx';
  Password := 'xxxx';
  uktwsapi.KTWebServerUrl :=  'http://ktdms.trunk';
  uktwsapi.KTWebServiceUrl := uktwsapi.KTWebServerUrl+'/ktwebservice/webservice.php?wsdl';
  uktwsapi.KTUploadUrl := uktwsapi.KTWebServerUrl+'/ktwebservice/upload.php';

end.
