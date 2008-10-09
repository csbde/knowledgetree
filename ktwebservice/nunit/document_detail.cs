using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentDetailTest : KTTest
    	{
		private int 			_docId;
		private int 			_folderId;
		private String			_filename;
		private String			_content;


		[SetUp]
		public void SetUp()
		{

			this._filename = Helper.isUnix()?"/tmp/kt_unit_test1.txt":"c:\\kt_unit_test1.txt";

			String filename = "kt unit test1";

			this._content = "hello world!";

			Helper.writeFile(this._filename, this._content);
			this._folderId = 1;

			kt_document_detail response1 = this._kt.add_base64_document(this._session, this._folderId, filename, this._filename, "Default", Helper.ConvertFileToBase64Encoding(this._filename));

			if (this._verbose && response1.status_code != 0)
			{
				System.Console.WriteLine("Could not create file: " + this._filename);
			}
			this._docId = response1.document_id;


		}

		[TearDown]
		public void TearDown()
		{

			Helper.deleteFile(this._filename);

			kt_response response = this._kt.delete_document(this._session, this._docId, "Delete - cleaning up");
			if (this._verbose && response.status_code != 0)
			{
				System.Console.WriteLine("Could not delete file: " + this._filename);
			}

		}

		[Test]
		public void NonExistantDocumentTest()
		{
			kt_document_detail response = this._kt.get_document_detail(this._session, -1,"");
			Assert.IsFalse(response.status_code == 0);
	    	}

		[Test]
		public void DocumentExistanceTest()
		{
			kt_document_detail response = this._kt.get_document_detail(this._session, this._docId,"MLTVH");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
			Assert.AreEqual("kt unit test1", response.title);
			Assert.AreEqual("n/a", response.custom_document_no);
			Assert.AreEqual("n/a", response.oem_document_no);
			Assert.AreEqual("Default", response.document_type);
			Assert.AreEqual("/kt unit test1", response.full_path);
			Assert.AreEqual("kt_unit_test1.txt", response.filename);
			Assert.AreEqual(this._content.Length + 1, response.filesize);
			Assert.AreEqual(this._folderId, response.folder_id);
			Assert.AreEqual("Administrator", response.created_by);
			Assert.IsTrue("" != response.created_date);
			Assert.AreEqual("n/a", response.checked_out_by);
			Assert.IsTrue("" != response.checked_out_date);
			Assert.AreEqual("Administrator", response.modified_by);
			Assert.IsTrue("" != response.modified_date);
			Assert.AreEqual("Administrator", response.owned_by);
			Assert.AreEqual(0.1, response.version);
			Assert.AreEqual(false, response.is_immutable);
			Assert.AreEqual("RW", response.permissions);
			Assert.AreEqual("n/a", response.workflow);
			Assert.AreEqual("n/a", response.workflow_state);
			Assert.AreEqual("text/plain", response.mime_type);
			Assert.AreEqual("text", response.mime_icon_path);
			Assert.AreEqual("Plain Text", response.mime_display);
			Assert.IsTrue("" != response.storage_path);
			Assert.AreEqual(2, response.metadata.Length);
			Assert.AreEqual(null, response.links);

			Assert.AreEqual(1, response.transaction_history.Length);
			Assert.AreEqual("Create", response.transaction_history[0].transaction_name);
			Assert.AreEqual("Administrator", response.transaction_history[0].username);
			Assert.AreEqual(0.1, response.transaction_history[0].version);
			Assert.AreEqual("Document created", response.transaction_history[0].comment);
			Assert.IsTrue("" != response.transaction_history[0].datetime);

			Assert.AreEqual(1, response.version_history.Length);
			Assert.AreEqual("Administrator", response.version_history[0].user);
			Assert.AreEqual(0, response.version_history[0].metadata_version);
			Assert.AreEqual(0.1, response.version_history[0].content_version);



			Assert.AreEqual(null, response.transitions);
	    	}

		[Test]
		public void GetDetailByTitleTest()
		{
			kt_document_detail response = this._kt.get_document_detail_by_name(this._session, 1, "Root Folder/kt unit test1", "T","");

			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
	    	}


		[Test]
		public void GetDetailByTitle2Test()
		{
			kt_document_detail response = this._kt.get_document_detail_by_title(this._session, 1, "Root Folder/kt unit test1", "");

			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
	    	}

		[Test]
		public void GetDetailByFileTest()
		{
			kt_document_detail response = this._kt.get_document_detail_by_name(this._session, 1, "Root Folder/kt_unit_test1.txt", "F","");

			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
	    	}
		[Test]
		public void GetDetailByFile2Test()
		{
			kt_document_detail response = this._kt.get_document_detail_by_filename(this._session, 1, "Root Folder/kt_unit_test1.txt", "");

			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
	    	}

		[Test]
		public void GetDetailByUnknownNameTest()
		{
			kt_document_detail response = this._kt.get_document_detail_by_name(this._session, 1, "Root Folder/kt_unit_test1.ssssdasdasd", "F","");
			Assert.IsFalse(response.status_code == 0);
	    	}
	}
}
