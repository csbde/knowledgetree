using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{    
	[TestFixture]
	public class AddDocumentTest  
    	{
	
		private String 			_session;
		private KnowledgeTreeService 	_kt;		
		private int 			_docId;
		private int 			_folderId; 
		private String			_filename;
		private String			_content;
		private bool			_verbose;
		
		
		[SetUp]
		public void SetUp() 
		{
			this._kt = new KnowledgeTreeService();	
			//this._kt.Url = "http://ktdms.trunk/ktwebservice/webservice.php";
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;   
			
			this._filename = Helper.isUnix()?"/tmp/kt_unit_test1.txt":"c:\\kt_unit_test1.txt"; 
			this._content = "hello world!";

			Helper.writeFile(this._filename, this._content);
			
			this._verbose = false;
						
			this._folderId = 1;			
		}

		[TearDown]
		public void TearDown() 
		{
			this._kt.logout(this._session);			
			
			Helper.deleteFile(this._filename);					
		}
		
		
		
		 		
		[Test]
		public void FindDocumentBeforeAdd() 
		{
			String filename = "Root Folder/kt test folder/kt unit test1";
			if (this._verbose) System.Console.WriteLine("Finding document before add: " + filename);
			kt_document_detail documentDetail = this._kt.get_document_detail_by_name(this._session, filename, "T");
			if (0 == documentDetail.status_code)
			{
				if (this._verbose) System.Console.WriteLine("Found document - deleting");
				kt_response response = this._kt.delete_document(this._session, documentDetail.document_id, "Delete - cleaning up before add");
				Assert.AreEqual(0, response.status_code);
			}
			else if (this._verbose) 
			{
				System.Console.WriteLine("document not found. that is ok!");
			}
		}	
		
		
		[Test]
		public void FindFolderBeforeAdd() 
		{
			String folder = "Root Folder/kt test folder";
			if (this._verbose) System.Console.WriteLine("Finding folder before add: " + folder);
			kt_folder_detail folderDetail = this._kt.get_folder_detail_by_name(this._session, folder);
			if (0 == folderDetail.status_code)
			{
				if (this._verbose) System.Console.WriteLine("Found folder - deleting");
				kt_response response = this._kt.delete_folder(this._session, folderDetail.id, "Delete - cleaning up before add");
				Assert.AreEqual(0, response.status_code);
			}
			else
			{
				if (this._verbose) System.Console.WriteLine("folder not found. that is ok!");
			}
		}			
				
				 
		[Test]
		public void AddDocument() 
		{				
			String folder = "kt test folder";
		
			if (this._verbose) System.Console.WriteLine("Creating folder : " + folder);
			kt_folder_detail folderDetail = this._kt.create_folder(this._session, 1, folder);
			this._folderId = folderDetail.id;
			if (this._verbose) System.Console.WriteLine("Got folder id : " + this._folderId);
			
			String filename = "kt unit test1";
			
			if (this._verbose) System.Console.WriteLine("Adding document : " + filename);
			 
		
			kt_document_detail response1 = this._kt.add_base64_document(this._session, this._folderId, filename, this._filename, "Default", Helper.ConvertFileToBase64Encoding(this._filename));
						
			Assert.AreEqual(0, response1.status_code);
			Assert.AreEqual("kt unit test1", response1.title);
			Assert.AreEqual("Default", response1.document_type);
			Assert.AreEqual("0.1", response1.version);
			Assert.AreEqual("kt_unit_test1.txt", response1.filename);
			
			Assert.IsFalse(response1.created_date == null);
			Assert.IsFalse(response1.created_date == "");
			
			Assert.AreEqual("Administrator", response1.created_by);
			
			//Assert.IsTrue(response1.updated_date == null);
			Assert.IsTrue("" != response1.updated_date);			

			Assert.AreEqual("Administrator", response1.updated_by);	

			Assert.IsTrue(response1.document_id > 0);	
								
			Assert.AreEqual(this._folderId, response1.folder_id);
			
			 
			Assert.AreEqual("n/a",response1.workflow);			
			
			 
			Assert.AreEqual("n/a",response1.workflow_state);			
			
			Assert.AreEqual("Root Folder/" + folder + "/kt unit test1", response1.full_path);	
			
			this._docId = response1.document_id;
	    	}
		 
		[Test]
		public void FindDocumentBeforeDelete() 
		{
			if (this._verbose) System.Console.WriteLine("Find document before delete");
			kt_document_detail documentDetail = this._kt.get_document_detail_by_name(this._session, "Root Folder/kt test folder/kt unit test1", "T");
			Assert.AreEqual(0, documentDetail.status_code);
			Assert.AreEqual(this._docId, documentDetail.document_id);
			
			if (this._verbose) System.Console.WriteLine("Find document before delete without the Root Folder's explicit naming");
			documentDetail = this._kt.get_document_detail_by_name(this._session, "/kt test folder/kt unit test1", "T");
			Assert.AreEqual(0, documentDetail.status_code);
			Assert.AreEqual(this._docId, documentDetail.document_id);
		}
		
		[Test]
		public void DeleteDocument() 
		{
			if (this._verbose) System.Console.WriteLine("Deleting document");
			kt_response response = this._kt.delete_document(this._session, this._docId, "Delete - cleaning up after add");	
			Assert.AreEqual(0, response.status_code);
		}
		
		[Test]
		public void FindDocumentAfterDelete() 
		{
			if (this._verbose) System.Console.WriteLine("Checking that document is gone!");
			
			kt_document_detail documentDetail = this._kt.get_document_detail_by_name(this._session, "Root Folder/kt test folder/kt unit test1", "T");
			Assert.IsTrue(0 != documentDetail.status_code);
		} 
		
	}
}
