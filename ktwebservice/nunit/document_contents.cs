using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentContentsTest : KTTest
    	{
 		private int 			_folderId;
		private Document		_doc1;


		[SetUp]
		public void SetUp()
		{

			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(1);

			kt_folder_detail response = this._kt.create_folder(this._session, 1, "kt_unit_testabc");


			this._folderId = response.id;

		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
		}

		[Test]
		public void Query()
		{

			kt_folder_contents response = this._kt.get_folder_contents(this._session, 1, 1, "DF");

			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(2, response.items.Length);

			Assert.AreEqual(this._doc1.docId, response.items[1].id);

			Assert.AreEqual("n/a", response.items[1].custom_document_no);
			Assert.AreEqual("n/a", response.items[1].oem_document_no);

			Assert.AreEqual("D", response.items[1].item_type);
			Assert.AreEqual(this._doc1.title, response.items[1].title);
			Assert.AreEqual(this._doc1.realFilename, response.items[1].filename);
			Assert.AreEqual("Default", response.items[1].document_type);
			Assert.AreEqual(this._doc1.filesize+1 + "", response.items[1].filesize);

			Assert.AreEqual("Administrator", response.items[1].created_by);
			Assert.AreEqual("Administrator", response.items[1].modified_by);
			Assert.AreEqual("n/a", response.items[1].checked_out_by);
			Assert.AreEqual("Administrator", response.items[1].owned_by);
			Assert.AreEqual("0.1", response.items[1].version);
			Assert.AreEqual("false", response.items[1].is_immutable);
			Assert.AreEqual("RW", response.items[1].permissions);
			Assert.AreEqual("n/a", response.items[1].workflow);
			Assert.AreEqual("n/a", response.items[1].workflow_state);
			Assert.AreEqual("text/plain", response.items[1].mime_type);
			Assert.AreEqual("text", response.items[1].mime_icon_path);
			Assert.AreEqual("Plain Text", response.items[1].mime_display);
			Assert.IsTrue("" != response.items[1].storage_path);

			Assert.AreEqual(this._folderId, response.items[0].id);
			Assert.AreEqual("F", response.items[0].item_type);
			Assert.AreEqual("kt_unit_testabc", response.items[0].title);

			Assert.AreEqual("kt_unit_testabc", response.items[0].filename);
			Assert.AreEqual("n/a", response.items[0].document_type);
			Assert.AreEqual("n/a", response.items[0].filesize);

			Assert.AreEqual("Administrator", response.items[0].created_by);
			Assert.AreEqual("n/a", response.items[0].modified_by);
			Assert.AreEqual("n/a", response.items[0].checked_out_by);
			Assert.AreEqual("n/a", response.items[0].owned_by);
			Assert.AreEqual("n/a", response.items[0].version);
			Assert.AreEqual("n/a", response.items[0].is_immutable);
			Assert.AreEqual("RWA", response.items[0].permissions);
			Assert.AreEqual("n/a", response.items[0].workflow);
			Assert.AreEqual("n/a", response.items[0].workflow_state);
			Assert.AreEqual("folder", response.items[0].mime_type);
			Assert.AreEqual("folder", response.items[0].mime_icon_path);
			Assert.AreEqual("Folder", response.items[0].mime_display);
			Assert.AreEqual("n/a",response.items[0].storage_path);
    		}
	}
}
