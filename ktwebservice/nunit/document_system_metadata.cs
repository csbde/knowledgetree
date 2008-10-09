using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentSystemMetadataTest : KTTest
    	{
		private int 			_folderId;
		private Document		_doc1;
		private Document		_doc2;


		[SetUp]
		public void SetUp()
		{
			this._folderId = 1;


			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(this._folderId);
			this._doc2 = new Document(2, this._session, this._kt, this._verbose,true);
		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();
			this._doc2.deleteFile();
		}

		[Test]
		public void UpdateDocumentMetadataTest()
		{

			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Technical";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[3];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";
			sysdata[2] = new kt_sysdata_item();
			sysdata[2].name = "modified_by";
			sysdata[2].value = "admin";


			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._doc1.docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
			Assert.AreEqual("Administrator", update_resp.modified_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
	    	}

		[Test]
		public void AddSmallDocumentWithMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Technical";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[2];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";

			this._doc2.local=false;
			kt_document_detail update_resp = this._doc2.createFileWithMetadata(this._folderId, fs, sysdata);

			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
	    	}

	    [Test]
	    public void TestBadCharsInDocType()
	    {
	    	kt_metadata_response resp = this._kt.get_document_type_metadata(this._session, "'''´`\"\"\\/:&;!.~,$%()|<>#=[]*?");
	    	Assert.AreEqual(26, resp.status_code);
	    }

		[Test]
		public void CheckinSmallDocumentWithMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Technical";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[2];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";

			kt_document_detail  resp = this._kt.checkout_base64_document(this._session, this._doc1.docId, "test checkin", false);
			Assert.AreEqual(0, resp.status_code);



			kt_document_detail update_resp = this._doc1.checkinFileWithMetadata(this._folderId, fs, sysdata);

			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
	    	}

		//[Test]
		public void AddDocumentWithMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Technical";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[3];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";


			sysdata[2] = new kt_sysdata_item();
			sysdata[2].name = "index_content";
			sysdata[2].value = "happy happy. this is a test with hippos and rhinos under the sun. unbrellas are required to create shade when trees are not abound.";



			this._doc2.local = true;
			this._doc2.createFile(this._folderId);



			 for (int i =0;i<1;i++)
			{
			FileUploader uploader = new FileUploader( );

			uploader.upload(this._session, this._doc2.filename);

			 System.Console.WriteLine("uploaded: " + uploader.filename);

			kt_document_detail response1 = this._kt.add_document_with_metadata(this._session, this._folderId, this._doc2.title+i, this._doc2.filename+i, "Default", uploader.filename,fs, sysdata);

			Assert.AreEqual(0, response1.status_code);
			}
	    	}

		[Test]
		public void CheckinDocumentWithMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Technical";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[2];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";

			kt_document_detail  resp = this._kt.checkout_base64_document(this._session, this._doc1.docId, "test checkin", false);
			Assert.AreEqual(0, resp.status_code);

			FileUploader uploader = new FileUploader( );

			uploader.upload(this._session, this._doc1.filename);

			kt_document_detail update_resp = this._kt.checkin_document(this._session, this._doc1.docId, this._doc1.filename, "unit test - doing checkin", uploader.filename, false);
			Assert.AreEqual(0, update_resp.status_code);
	    	}

	}
}
