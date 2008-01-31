select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'word', 'Word Document');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'dotx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'word', 'Word Document');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'potx', 'application/vnd.openxmlformats-officedocument.presentationml.template', 'office', 'Powerpoint Presentation');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'ppsx', 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'office', 'Powerpoint Presentation');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'office', 'Powerpoint Presentation');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'excel', 'Excel Spreadsheet');
select @id:=max(id)+1 from mime_types;
insert into mime_types(id, filetypes, mimetypes, icon_path, friendly_name) values
(@id, 'xltx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'excel', 'Excel Spreadsheet');

update zseq_mime_types set id=@id