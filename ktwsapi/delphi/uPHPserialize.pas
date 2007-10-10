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
  A helper unit to unserialize a php serialized string

  Inspired by the various implementaions presented here
  http://www.phpguru.org/static/PHP_Unserialize.html

  @Author Bjarte Kalstveit Vebjørnsen <bjarte@macaos.com>
  @Version 1.0  BKV 24.09.2007  Initial revision
  @Todo A complementary serializing function would be nice
*}

unit uPHPSerialize;

interface

uses
  Classes, SysUtils, Variants, ComCtrls;

type
  TPHPValue = class;
  TPHPArray = class;
  TPHPSerialize = class;

  /// a single hash element
  TPHPHashElement = record
    Key: TPHPValue;       /// The Key part of the element
    Value: TPHPValue;     /// The Value part of the element
  end;

  /// an array of hash elements
  TPHPHashedElementArray = array of TPHPHashElement;

  ///  Tries to replicate a php array by accessing values through strings
  ///  @Todo: Add support for numeric keys
  TPHPArray = class
  private
    FElements: TPHPHashedElementArray;  /// array of hash elements
    function GetValue(Key: string): TPHPValue; overload;
    function GetValue(Key: TPHPValue): TPHPValue; overload;
    procedure SetValue(Key: TPHPValue; VValue: TPHPValue); overload;
    function GetCount: Integer;
    function Getempty: Boolean;
  public
    property Value[Key: string]: TPHPValue read GetValue; default;
    property Value[Key: TPHPValue]: TPHPValue read GetValue write SetValue; default;
    property Count: Integer read GetCount;
    property Empty: Boolean read Getempty;
    procedure Clear;
    constructor Create;
    destructor Destroy; override;
  end;

  /// Tries to represent a PHP value of any type
  TPHPValue = class
  private
    FObj: TObject; /// Holds value if it's an object or array
    FValue: String; /// Holds value if it's something else
    procedure Clear;
  public
    constructor Create(Value: TObject); overload;
    constructor Create(Value: String); overload;
    destructor Destroy; override;
    function AsString: String;
    function AsDouble: Double;
    function AsInteger: Integer;
    function AsObject: TObject;
    function AsArray: TPHPArray;
    function AsBoolean: Boolean;
  end;

  /// Class for unserializing a php serialized string
  TPHPSerialize = class
  private
    function GetLength(Data: String): Integer;
    function GetIntLength(Value: Integer): Integer;
  public
    class function Unserialize(Data: String): TPHPValue;
    function _Unserialize(var Data: String): TPHPValue;
  end;

implementation

{ TPHPSerialize }

{*
  Finds the length of they to the type

  @param string Data
  @return integer
*}
function TPHPSerialize.GetLength(Data: String): Integer;
begin
  Data := Copy(Data, 3, Length(Data));

  Result := StrToIntDef(Copy(Data, 0, Pos(':', Data)-1), 0);
end;

{*
  Finds the lenght of the character-space the value occupies

  @param integer Value
  @return Integer
*}
function TPHPSerialize.GetIntLength(Value: Integer): Integer;
begin
  Result :=  Length(IntToStr(Value));
end;

{*
  Helper function to use this class statically

  @param integer Value
  @return Integer
*}
class function TPHPSerialize.Unserialize(Data: String): TPHPValue;
var
  obj: TPHPSerialize;
begin
  obj := TPHPSerialize.Create;
  try
    Result := obj._Unserialize(Data);
  finally
    obj.Free;
  end;
end;

{*
  Recursing function for unserializing a string and creating a php value from it

  @see TPHPValue
  @param Data String
  @return TPHPValue
*}
function TPHPSerialize._Unserialize(var Data: String): TPHPValue;
var
  I, Len: Integer;
  Num: Double;
  C: String;
  Arr: TPHPArray;
  Key, Value: TPHPValue;
begin
  C := Copy(Data,0,1);

  if (C = 'a') then
  begin
    Len := GetLength(Data);
    Data := Copy(Data, GetIntLength(Len) + 5, Length(Data) );

    Arr := TPHPArray.Create;
    for I := 0 to Len-1 do
    begin
      Key := _Unserialize(Data);
      Value := _Unserialize(Data);

      Arr[Key] := Value;
    end;

    Data := Copy(Data, Length(Data));
    Result := TPHPValue.Create(Arr);

  end else if (C = 's') then
  begin
    Len := GetLength(Data);
    Result := TPHPValue.Create(Copy(Data, GetIntLength(Len) + 5, Len));
    Data := Copy(Data, GetIntLength(Len) + 7 + Len, Length(Data));
  end else if (C = 'i') or (C = 'd') then
  begin
    Num := StrToFloat(Copy(Data, 3, AnsiPos(';', Data)-3));
    Result := TPHPValue.Create(FloatToStr(Num));
    Data := Copy(Data, Length(FloatToStr(Num)) + 4, Length(Data));
  end else if (C = 'b') then
  begin
    Result :=  TPHPValue.Create(BoolToStr(Copy(Data, 3, 1) = '1'));
    Data := Copy(Data, 4, Length(Data));
  end else if (C = 'O') or (C = 'r') or (C = 'C') or (C = 'R')
    or (C = 'U') then
  begin
    raise Exception.Create('Unsupported PHP data type found!');
  end else if (C = 'N') then
  begin
    Result := TPHPValue.Create(nil);
    Data := Copy(Data, 2, Length(Data));
  end else
  begin
    Result := TPHPValue.Create(nil);
    Data := '';
  end;
end;


{ TPHPValue }

{*
  Returns value as boolan

  @return boolean value
*}
function TPHPValue.AsBoolean: Boolean;
begin
  Result := StrToBool(FValue);
end;

{*
  Returns value as double

  @return double value
*}
function TPHPValue.AsDouble: Double;
begin
  Result := StrToFloat(FValue);
end;

{*
  Returns value as an associative-array

  @return associative-array
*}
function TPHPValue.AsArray: TPHPArray;
begin
  Result := nil;
  Assert(Assigned(FObj));

  if (FObj.ClassType = TPHPArray) then
    Result := TPHPArray(FObj);
end;

{*
  Returns value as an integer

  @return integer value
*}
function TPHPValue.AsInteger: Integer;
begin
  Result := StrToInt(FValue);
end;

{*
  Returns value as an object

  @return object value
*}
function TPHPValue.AsObject: TObject;
begin
  Assert(Assigned(FObj));
  Result := FObj;
end;

{*
  Returns value as a string

  @return string value
*}
function TPHPValue.AsString: String;
begin
  Result := FValue;
end;

{*
  Constructor

  @param Value Value to store
*}
constructor TPHPValue.Create(Value: String);
begin
  Clear;
  FValue := Value;
end;

{*
  Constructor

  @param Value Value to store  
*}
constructor TPHPValue.Create(Value: TObject);
begin
  Clear;
  FObj := Value;
end;

{*
  Clears current value
*}
procedure TPHPValue.Clear;
begin
  FValue := '';
  if Assigned(FObj) then FObj.Free;
  FObj := nil;
end;

{*
  Destructor
*}
destructor TPHPValue.Destroy;
begin
  Clear;
  inherited;
end;

{ TPHPArray }


{*
  Clears whole array
*}
procedure TPHPArray.Clear;
var
  i: Integer;
begin
  for i := 0 to GetCount - 1 do
  begin
    FElements[i].Key.Free;
    FElements[i].Key := nil;
    FElements[i].Value.Free;
    FElements[i].Value := nil;
  end;
  SetLength(FElements, 0);
end;

{*
  Constructor
*}
constructor TPHPArray.Create;
begin
  inherited;
  Clear;
end;

{*
  Destructor
*}
destructor TPHPArray.Destroy;
begin
  Clear;
  inherited;
end;

{*
  Returns the number of items in the array

  @return number of items
*}
function TPHPArray.GetCount: Integer;
begin
  Result := Length(FElements);
end;

{*
  Checks if the array is empty

  @return true
*}
function TPHPArray.Getempty: Boolean;
begin
  Result := Length(FElements) = 0;
end;

{*
  Fetch a phpvalue from the array

  @param Key Handle to phpvalue
  @return handle to phpvalue
*}
function TPHPArray.GetValue(Key: TPHPValue): TPHPValue;
begin
  Result := GetValue(Key.FValue);
end;

{*
  Fetch a phpvalue from the array

  @param Key Index to element
  @return handle to phpvalue
*}
function TPHPArray.GetValue(Key: string): TPHPValue;
var 
  i: Integer;
  r: Boolean;
begin
  Result := nil;
  i      := 0; 
  r      := False;
  while (i < Length(FElements)) and (not r) do
  begin
    if AnsiUpperCase(FElements[i].key.AsString) = AnsiUpperCase(Key) then
    begin 
      Result := FElements[i].Value;
      r := True;
    end;
    i := i + 1;
  end;
end;

{*
  Insert a phpvalue into the array

  @param Key Index to element
  @return handle to phpvalue
*}
procedure TPHPArray.SetValue(Key, VValue: TPHPValue);
var 
  i, j: Integer;
  r: Boolean;
  E: TPHPHashedElementArray;
begin
  if VValue <> nil then
  begin
    i := 0; 
    r := False;
    while (i < Length(FElements)) and not r do
    begin
      if AnsiUpperCase(FElements[i].key.AsString) = AnsiUpperCase(Key.AsString) then
      begin 
        FElements[i].Value := VValue;
        r := True;
      end;
      i := i + 1;
    end;
    if not r then 
    begin 
      SetLength(FElements, Length(FElements) + 1);
      FElements[Length(FElements) - 1].Key   := Key;
      FElements[Length(FElements) - 1].Value := Vvalue;
    end;
  end;

  SetLength(E, Length(FElements));
  for i := 0 to Length(FElements) - 1 do E[i] := FElements[i];
  SetLength(FElements, 0);
  for i := 0 to Length(E) - 1 do if (E[i].Key.AsString <> '')
    and (E[i].Value <> nil) then
    begin
      j := Length(FElements);
      setlength(FElements, j + 1);
      FElements[j] := E[i];
    end;
end;

end.

