using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentLinkTest : KTTest
    	{
		private int 			_folderId;
		private Document		_doc1;
		private Document		_doc2;


		[SetUp]
		public void SetUp()
		{
			this._folderId = 1;

			this._doc1 = new Document(1, this._session, this._kt, this._verbose, false);
			this._doc1.createFile(this._folderId);
			this._doc2 = new Document(2, this._session, this._kt, this._verbose, false);
			this._doc2.createFile(this._folderId);
		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
			this._doc2.deleteFile();
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
			Assert.AreEqual(this._doc2.title, linkresp.links[0].title);
			Assert.AreEqual("Default", linkresp.links[0].document_type);
			Assert.AreEqual(this._doc2.filesize+1, linkresp.links[0].filesize);
			Assert.AreEqual(0.1, linkresp.links[0].version);
			Assert.AreEqual("n/a", linkresp.links[0].workflow);
			Assert.AreEqual("n/a", linkresp.links[0].workflow_state);
			Assert.AreEqual("Reference", linkresp.links[0].link_type);
			Assert.AreEqual("n/a", linkresp.links[0].custom_document_no);
			Assert.AreEqual("n/a", linkresp.links[0].oem_document_no);

			response = this._kt.unlink_documents(this._session, this._doc1.docId, this._doc2.docId);
			Assert.AreEqual(0, response.status_code);

			linkresp = this._kt.get_document_links(this._session, this._doc1.docId);
			Assert.AreEqual(0, linkresp.status_code);
			Assert.AreEqual(null, linkresp.links);

	    	}


	}
}
