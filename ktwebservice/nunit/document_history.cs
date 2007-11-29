using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentHistoryTest : KTTest
    	{
		private int 			_folderId;
		private Document		_doc1;

		[SetUp]
		public void SetUp()
		{
			this._folderId = 1;

			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(this._folderId);
		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
		}

		[Test]
		public void ChangeTypeTest()
		{
			kt_document_version_history_response version_resp = this._kt.get_document_version_history(this._session, this._doc1.docId);
			Assert.AreEqual(0, version_resp.status_code);

			kt_document_transaction_history_response history_resp = this._kt.get_document_transaction_history(this._session, this._doc1.docId);
			Assert.AreEqual(0, history_resp.status_code);
	    	}
	}
}
