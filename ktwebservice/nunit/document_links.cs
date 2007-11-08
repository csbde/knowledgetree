using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentLinkTest
    	{
		private String 			_session;
		private KnowledgeTreeService 	_kt;
		private int 			_folderId;
		private bool			_verbose;
		private Document		_doc1;
		private Document		_doc2;


		[SetUp]
		public void SetUp()
		{
			this._kt = new KnowledgeTreeService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

			this._folderId = 1;


			this._doc1 = new Document(1, this._session, this._kt, this._verbose, false);
			this._doc1.createFile(this._folderId);
			this._doc2 = new Document(2, this._session, this._kt, this._verbose, false);
			this._doc2.createFile(this._folderId);


			this._verbose = true;

		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
			this._doc2.deleteFile();

			this._kt.logout(this._session);

		}

		[Test]
		public void LinkingTest()
		{
			kt_linked_document_response linkresp = this._kt.get_document_links(this._session, this._doc1.docId);
			Assert.AreEqual(0, linkresp.status_code);
			Assert.AreEqual(null, linkresp.links);

			kt_response response = this._kt.link_documents(this._session, this._doc1.docId, this._doc2.docId, "Reference");
			Assert.AreEqual(0, response.status_code);

			linkresp = this._kt.get_document_links(this._session, this._doc1.docId);
			Assert.AreEqual(0, linkresp.status_code);
			Assert.AreEqual(this._doc1.docId, linkresp.parent_document_id);
			Assert.AreEqual(1, linkresp.links.Length);
			Assert.AreEqual(this._doc2.docId, linkresp.links[0].document_id);

			response = this._kt.unlink_documents(this._session, this._doc1.docId, this._doc2.docId);
			Assert.AreEqual(0, response.status_code);

			linkresp = this._kt.get_document_links(this._session, this._doc1.docId);
			Assert.AreEqual(0, linkresp.status_code);
			Assert.AreEqual(null, linkresp.links);

	    	}


	}
}
