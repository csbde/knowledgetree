using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{    
	[TestFixture]
	public class DocumentTest  
    	{
	
		private String 			_session;
		private KnowledgeTreeService 	_kt;
		
		private String FILE1_NAME 		= "/tmp/kt_unit_test1.txt";
		private String FILE2_NAME 		= "/tmp/kt_unit_test2.txt";
		private String FILE3_NAME 		= "/tmp/kt_unit_test3.txt";
		private String FILE1_CONTENT 		= "hello world";
		private String FILE2_CONTENT 		= "this is a test";
		private String FILE3_CONTENT 		= "we do like unit tests!";
		private String NEW_DOCUMENT_TYPE 	= "Default";
		private String NEW_OWNER 		= "admin";
		private String NEW_DOCUMENT_FILENAME 	= "kt_unit_test-1.txt";
		private String NEW_DOCUMENT_TITLE 	= "unit test 1";
		private String NEW_WORKFLOW 		= "leave";
		private String NEW_WORKFLOW_STATE 	= "approved";
		private String NEW_WORKFLOW_START 	= "approved";		
		private String NEW_TRANSITION 		= "approve";
		
		private int[] 			_doc_id;
		private int 			_folder_id;
		private bool			_skip;
		
		
		[SetUp]
		public void SetUp() 
		{
			this._skip = true; 
			if (this._skip) return;
			this._kt = new KnowledgeTreeService();	
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;   
			
			writeFile(FILE1_NAME, FILE1_CONTENT);
			writeFile(FILE2_NAME, FILE2_CONTENT);
			writeFile(FILE3_NAME, FILE3_CONTENT);
			
			this._folder_id = 1;
			
		}

		[TearDown]
		public void TearDown() 
		{
			if (this._skip) return;
			this._kt.logout(this._session);
			
			
			deleteFile(FILE1_NAME);
			deleteFile(FILE2_NAME);
			deleteFile(FILE3_NAME);
			
			for(int i=0;i<3;i++)
			{
				this._kt.delete_document(this._session, this._doc_id[i], "TearDown");
			}
			
		}
		 
		private void validateDocumentDetail(kt_document_detail response1)
		{
			 
			Assert.AreEqual(0, response1.status_code);
			Assert.AreEqual("kt unit test1", response1.title);
			Assert.AreEqual("Default", response1.document_type);
			Assert.AreEqual("0.1", response1.version);
			Assert.AreEqual("kt_unit_test1.txt", response1.filename);
			
			Assert.IsFalse(response1.created_date == null);
			Assert.IsFalse(response1.created_date == "");
			
			Assert.AreEqual("admin", response1.created_by);
			
			Assert.IsTrue(response1.updated_date == null);
			Assert.IsTrue(response1.updated_date == "");			

			Assert.IsTrue(response1.updated_by == null);
			Assert.IsTrue(response1.updated_by == "");			

			Assert.IsTrue(response1.document_id > 0);	
								
			Assert.AreEqual(this._folder_id, response1.folder_id);
			
			Assert.IsTrue(response1.workflow == null);
			Assert.IsTrue(response1.workflow == "");			
			
			Assert.IsTrue(response1.workflow_state == null);
			Assert.IsTrue(response1.workflow_state == "");			
			
			Assert.AreEqual("Root Folder/kt_unit_test1.txt", response1.full_path);	
		} 
		 
		[Test]
		public void AddDocument() 
		{
			if (this._skip) return;
			 this._doc_id = new int[3];
		
			// document a
			
			kt_document_detail response1 = this._kt.add_base64_document(this._session, this._folder_id, "kt unit test1", "kt_unit_test1.txt", "Default", ConvertFileToBase64Encoding(FILE1_NAME));
			
			
			validateDocumentDetail(response1);	
	    		
			// document b
			
			kt_document_detail response2 = this._kt.add_base64_document(this._session, this._folder_id, "kt unit test2", "kt_unit_test2.txt", "Default", ConvertFileToBase64Encoding(FILE2_NAME));
			Assert.AreEqual(0, response2.status_code);
			Assert.IsTrue(response2.document_id > 0);
			Assert.AreEqual("Root Folder/kt_unit_test2.txt", response2.full_path);
						
			// document c
			kt_document_detail response3 = this._kt.add_base64_document(this._session, this._folder_id, "kt unit test3", "kt_unit_test3.txt", "Default", ConvertFileToBase64Encoding(FILE3_NAME));
			Assert.AreEqual(0, response3.status_code);
			Assert.IsTrue(response3.document_id > 0);
			Assert.AreEqual("Root Folder/kt_unit_test3.txt", response3.full_path);
			
			this._doc_id[0] = response1.document_id;
			this._doc_id[1] = response2.document_id;
			this._doc_id[2] = response3.document_id;
			
			
			
	    	}		

		[Test]
		public void GetDocumentDetail() 
		{
			if (this._skip) return;
			// test referencing a non existant object - should fail
			kt_document_detail response = this._kt.get_document_detail(this._session, -1);	
			Assert.IsFalse(response.status_code == 0);
		
			// get document we added based on id
			response = this._kt.get_document_detail(this._session, this._doc_id[0]);			
			validateDocumentDetail(response);
			Assert.AreEqual(this._doc_id[0], response.document_id);
			
			// get document based on title
			response = this._kt.get_document_detail_by_name(this._session, "Root Folder/kt unit test1", "T");
			validateDocumentDetail(response);
			Assert.AreEqual(this._doc_id[0], response.document_id);

			// get document based on file
			response = this._kt.get_document_detail_by_name(this._session, "Root Folder/kt_unit_test1.txt", "F");
			validateDocumentDetail(response);
			Assert.AreEqual(this._doc_id[0], response.document_id);

			// test accessing file by filename that does not exist - should fail
			response = this._kt.get_document_detail_by_name(this._session, "Root Folder/kt_unit_test1.ssssdasdasd", "F"); 
			Assert.IsFalse(response.status_code == 0);
	    	}		


		[Test]
		public void LinkDocuments() 
		{
			if (this._skip) return;
			// get document link types
			kt_linked_document_response linkresp = this._kt.get_document_links(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, linkresp.status_code);
			
			
			// TODO: length test
			//Assert.IsTrue(linkresp.links == 0);
			
	    		// link a to b
			kt_response response = this._kt.link_documents(this._session, this._doc_id[0], this._doc_id[1], "Reference");	
			Assert.AreEqual(0, response.status_code);
			
			// link a to c
			response = this._kt.link_documents(this._session, this._doc_id[0], this._doc_id[1], "Reference");	
			Assert.AreEqual(0, response.status_code);
			
			// get list on a
			linkresp = this._kt.get_document_links(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, linkresp.status_code);
			// TODO: length test
			//Assert.IsTrue(linkresp.links.length == 2);

			Assert.AreEqual("kt unit test2", linkresp.links[0].title);
			Assert.IsTrue(linkresp.links[0].document_id == this._doc_id[0]);
			Assert.AreEqual("kt unit test3", linkresp.links[1].title);
			Assert.IsTrue(linkresp.links[1].document_id == this._doc_id[1]);			
			
			// unlink c from a
			response = this._kt.unlink_documents(this._session, this._doc_id[0], this._doc_id[1]);	
			Assert.AreEqual(0, response.status_code);			
			
			// get list on a
			linkresp = this._kt.get_document_links(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, linkresp.status_code);
			// TODO: length test
			//Assert.IsTrue(linkresp.links.length == 1);

			Assert.AreEqual("kt unit test3", linkresp.links[0].title);
			Assert.IsTrue(linkresp.links[0].document_id == this._doc_id[0]);			
	    	}		

		[Test]
		public void CheckoutBase64Document() 
		{
			if (this._skip) return;
		
		
	    		// checkout a
			kt_response response = this._kt.checkout_base64_document(this._session, this._doc_id[0], "unit test - going to undo", false);	
			Assert.AreEqual(0, response.status_code);			
			
			// undocheckout
			response = this._kt.undo_document_checkout(this._session, this._doc_id[0], "unit test - doing undo");	
			Assert.AreEqual(0, response.status_code);
			
			// todo: download and compare with original
			

			// checkout a
			response = this._kt.checkout_base64_document(this._session, this._doc_id[0], "unit test - going to checkin", false);	
			Assert.AreEqual(0, response.status_code);

			// todo: change

			// checkin a
			kt_document_detail checkin = this._kt.checkin_base64_document(this._session, this._doc_id[0], "kt_unit_test1", "unit test - doing checkin", ConvertFileToBase64Encoding(FILE1_NAME), false);	
			Assert.AreEqual(0, checkin.status_code);			
			
			// todo: download and compare with original
						
	    	}
		
		[Test]
		public void CheckoutDocument() 
		{
			if (this._skip) return;
			// TODO - must deal with the more complex scenario
		}
		

		[Test]
		public void ChangeDocumentOwner() 
		{
			if (this._skip) return;
			kt_response response = this._kt.change_document_owner(this._session, this._doc_id[0], NEW_OWNER, "just trying");	
			Assert.AreEqual(0, response.status_code);
		
		
			// get document info - validate
			kt_document_detail detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, detail.status_code);
			// TODO: if we had the owner field, we could validate it
			//Assert.AreEqual(NEW_OWNER, detail.owner);			
	    	}
		
		[Test]
		public void ChangeDocumentType() 
		{
			if (this._skip) return;
			kt_response response = this._kt.change_document_type(this._session, this._doc_id[0], NEW_DOCUMENT_TYPE);	
			Assert.AreEqual(0, response.status_code);
		
		
			// get document info - validate
			kt_document_detail detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(NEW_DOCUMENT_TYPE, detail.document_type);
		}

		[Test]
		public void CopyDocument() 
		{
			if (this._skip) return;
			// TODO copy document
			// get document info by name - validate
	    	}
		
		[Test]
		public void MoveDocument() 
		{
			if (this._skip) return;
			// TODO move document
			// get document info by name - validate
	    	}		
		
		[Test]
		public void DownloadDocument() 
		{
			if (this._skip) return;
			// TODO download document
			// get document info by name - validate
	    	}
		
		
		[Test]
		public void Workflow() 
		{
			if (this._skip) return;
			// start workflow
			kt_response response = this._kt.start_document_workflow(this._session, this._doc_id[0], NEW_WORKFLOW);	
			Assert.AreEqual(0, response.status_code);			
			
			// get document info - validate
			kt_document_detail detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, detail.status_code);
			Assert.AreEqual(NEW_WORKFLOW, detail.workflow);			
			
			// stop workflow
			response = this._kt.delete_document_workflow(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, response.status_code);		
			
			// get document info - validate
			detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, detail.status_code);
			Assert.AreEqual("", detail.workflow);	
			
			
			// get workflow state 	    -
			response = this._kt.get_document_workflow_state(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, detail.status_code);
			Assert.AreEqual(NEW_WORKFLOW, detail.workflow);			
			Assert.AreEqual(NEW_WORKFLOW_START, detail.workflow_state);
			
			
			// get workflow transitions - maybe we should merge the two functions
			kt_workflow_transitions_response trans_resp = this._kt.get_document_workflow_transitions(this._session, this._doc_id[0]);
			Assert.AreEqual(0, trans_resp.status_code);
			
			
			
			// start workflow
			response = this._kt.start_document_workflow(this._session, this._doc_id[0], NEW_WORKFLOW);	
			Assert.AreEqual(0, response.status_code);
			
			// do transition
			response = this._kt.perform_document_workflow_transition(this._session, this._doc_id[0], NEW_TRANSITION, "unit test - transition 1");	
			Assert.AreEqual(0, response.status_code);
			
			detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, detail.status_code);
			Assert.AreEqual(NEW_WORKFLOW, detail.workflow);	
			Assert.AreEqual(NEW_WORKFLOW_STATE, detail.workflow_state);	
			
						
	    	}	
		
		[Test]
		public void Metadata() 
		{
			if (this._skip) return;
			// get document types
			kt_document_types_response doc_types = this._kt.get_document_types(this._session);	
			Assert.AreEqual(0, doc_types.status_code);		
			
			// get document type metadata
			kt_metadata_response metadata = this._kt.get_document_type_metadata(this._session, "Default");	
			Assert.AreEqual(0, metadata.status_code);
					
			// get document metadata
			metadata = this._kt.get_document_metadata(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, metadata.status_code);
			
			// update document metadata

			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0].fieldset = "Invoice";
			fs[0].fields = new kt_metadata_field[2];
			fs[0].fields[0].name = "Invoice No";
			fs[0].fields[0].value = "000010";
			fs[0].fields[1].name = "Invoice Date";
			fs[0].fields[1].value = "2007-10-12";

			kt_response update_resp = this._kt.update_document_metadata(this._session, this._doc_id[0], fs);
			Assert.AreEqual(0, update_resp.status_code);



			// get document metadata
			metadata = this._kt.get_document_metadata(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, metadata.status_code);

	    	}	
			
		[Test]
		public void History() 
		{
			if (this._skip) return;
			kt_document_version_history_response version_resp = this._kt.get_document_version_history(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, version_resp.status_code);
			
			kt_document_transaction_history_response history_resp = this._kt.get_document_transaction_history(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, history_resp.status_code);
	    	}
		
		[Test]
		public void Rename() 
		{
			if (this._skip) return;
			kt_response response = this._kt.rename_document_filename(this._session, this._doc_id[0], NEW_DOCUMENT_FILENAME);	
			Assert.AreEqual(0, response.status_code);
				
			// get document info - validate
			kt_document_detail detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(NEW_DOCUMENT_FILENAME, detail.filename);


			response = this._kt.rename_document_filename(this._session, this._doc_id[0], NEW_DOCUMENT_TITLE);	
			Assert.AreEqual(0, response.status_code);
				
			// get document info - validate
			detail = this._kt.get_document_detail(this._session, this._doc_id[0]);	
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(NEW_DOCUMENT_TITLE, detail.filename);
	    	}	
		
	 
	
	public static void writeFile(String filename, String text)
	{
		try 
		{
		 	TextWriter tw = new StreamWriter(filename);
		 	tw.WriteLine(text );
		 	tw.Close();
		}
		catch (System.Exception exp) 
		{
			System.Console.WriteLine("{0}", exp.Message);
			throw;
		}	
	}
	
	public static String readFile(String filename)
	{
		String text = null;
		try 
		{
			FileStream inFile = new FileStream("path.txt", System.IO.FileMode.Open, System.IO.FileAccess.Read);
			StreamReader sr = new StreamReader(inFile);
			text = sr.ReadToEnd();
			inFile.Close();
		}
		catch (System.Exception exp) 
		{
			System.Console.WriteLine("{0}", exp.Message);
			throw;
		}
		
		return text;
	}	
	
	public static void deleteFile(string filename)
	{
		try
		{
			File.Delete(filename);
		}
		catch(System.Exception)
		{
			// we are using this to cleanup, so don't handle
		}
	}
	
	public static string ConvertFileToBase64Encoding(string filename)
	{
		System.IO.FileStream inFile;  
 
		byte[] binaryData;
		string base64String = "";

		try 
		{
			inFile = new System.IO.FileStream(filename, System.IO.FileMode.Open, System.IO.FileAccess.Read);
			binaryData = new Byte[inFile.Length];
			inFile.Read(binaryData, 0, (int)inFile.Length);
			inFile.Close();

			base64String = System.Convert.ToBase64String(binaryData, 0, binaryData.Length);
		}
		catch (System.Exception exp) 
		{
			System.Console.WriteLine("{0}", exp.Message);
			throw;
		}

		return base64String;
	} 	
	
	}
	
}
