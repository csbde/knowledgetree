using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentDetailTest
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
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

			this._filename = Helper.isUnix()?"/tmp/kt_unit_test1.txt":"c:\\kt_unit_test1.txt";

			String filename = "kt unit test1";

			this._content = "hello world!";

			Helper.writeFile(this._filename, this._content);

			this._verbose = false;

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

			this._kt.logout(this._session);

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
			kt_document_detail response = this._kt.get_document_detail(this._session, this._docId,"");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(this._docId, response.document_id);
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
