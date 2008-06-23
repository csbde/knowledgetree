update mime_types SET mimetypes='application/ai', friendly_name = 'Adobe Illustrator Vector Graphic', extractor_id = null where filetypes = 'ai';
update mime_types SET mimetypes='application/eps', extractor_id = null  where filetypes = 'eps';
update mime_types SET mimetypes='application/x-msi', extractor_id = null  where filetypes = 'msi';

select @id:=ifnull(max(id),0)+1 from mime_types;
insert into mime_types(id,filetypes, mimetypes , friendly_name, icon_path) values (@id,'db','application/db','Misc DB file', '');
select @id:=ifnull(max(id),0)+1 from mime_types;
insert into mime_types(id,filetypes, mimetypes , friendly_name, icon_path) values (@id,'msg','application/vnd.ms-outlook','Outlook Item', 'office');
select @id:=ifnull(max(id),0)+1 from mime_types;
insert into mime_types(id,filetypes, mimetypes , friendly_name, icon_path) values (@id,'pps','application/vnd.ms-powerpoint','Powerpoint Presentation', 'office');
update zseq_mime_types set id=@id;