using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentOwnerTest : KTTest
    	{
		private int 			_folderId;
		private Document		_doc1;

		[SetUp]
		public void SetUp()
		{
			this._folderId = 1;

			this._doc1 = new Document(1, this._session, this._kt, this._verbose, false);
			this._doc1.createFile(this._folderId);
		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
		}

		[Test]
		public void ChangeOwnerTest()
		{
			kt_document_detail response = this._kt.change_document_owner(this._session, this._doc1.docId, "anonymous", "just trying to change owner");
			Assert.AreEqual(0, response.status_code);

			// test to non existant user
			response = this._kt.change_document_owner(this._session, this._doc1.docId, "blah", "just trying to change owner");
			Assert.IsFalse(0 == response.status_code);
	    	}
	}
}
