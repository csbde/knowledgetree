using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentCopyTest : KTTest
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
		public void FindDocumentBeforeCopy()
		{
			String filename = "Root Folder/test123";
			if (this._verbose) System.Console.WriteLine("Finding document before add: " + filename);
			kt_document_detail documentDetail = this._kt.get_document_detail_by_title(this._session, 1, filename, "");
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
		public void CopyTest()
		{
		kt_folder_detail response2 = this._kt.create_folder(this._session, 1, "kt_unit_test_move");
		       	Assert.AreEqual(0,response2.status_code);
		        int folderId =    response2.id;

			kt_document_detail linkresp = this._kt.copy_document(this._session, this._doc1.docId, folderId, "copy", "");
			Assert.AreEqual(0, linkresp.status_code);
			Assert.AreEqual("kt_unit_test1.txt", linkresp.filename);
			Assert.AreEqual("kt unit test1", linkresp.title);



	    	}

		[Test]
		public void FindDocumentAfterCopy()
		{
			String filename = "Root Folder/kt unit test1";
			if (this._verbose) System.Console.WriteLine("Finding document before add: " + filename);
			kt_document_detail documentDetail = this._kt.get_document_detail_by_title(this._session, 1, filename, "");
			Assert.AreEqual(0, documentDetail.status_code);



		}



	}
}
