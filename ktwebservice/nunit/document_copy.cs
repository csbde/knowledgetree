using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentCopyTest
    	{
		private String 			_session;
		private KnowledgeTreeService 	_kt;
		private int 			_folderId;
		private bool			_verbose;
		private Document		_doc1; 


		[SetUp]
		public void SetUp()
		{
			this._kt = new KnowledgeTreeService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

			this._folderId = 1;


			this._doc1 = new Document(1, this._session, this._kt, this._verbose, false);
			this._doc1.createFile(this._folderId); 


			this._verbose = true;

		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile(); 

			this._kt.logout(this._session);

		}

		[Test]
		public void CopyTest()
		{
			kt_document_detail linkresp = this._kt.copy_document(this._session, this._doc1.docId, 1, "copy", "test123", "test123.txt");
			Assert.AreEqual(0, linkresp.status_code); 
			Assert.AreEqual("test123.txt", linkresp.filename); 
			Assert.AreEqual("test123", linkresp.title); 

			 

	    	}


	}
}
