using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{


	[TestFixture]
	public class DocumentSystemMetadataTest
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


			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(this._folderId);
			this._doc2 = new Document(2, this._session, this._kt, this._verbose,true);


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

			kt_sysdata_item[] sysdata = new kt_sysdata_item[2];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "created_by";
			sysdata[0].value = "Anonymous";
			sysdata[1] = new kt_sysdata_item();
			sysdata[1].name = "created_date";
			sysdata[1].value = "2007-01-17";


			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._doc1.docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
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

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
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

			kt_response  resp = this._kt.checkout_base64_document(this._session, this._doc1.docId, "test checkin", false);
			Assert.AreEqual(0, resp.status_code);



			kt_document_detail update_resp = this._doc1.checkinFileWithMetadata(this._folderId, fs, sysdata);

			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);

			Assert.AreEqual("Anonymous", update_resp.created_by);
			Assert.AreEqual("2007-01-17 00:00:00", update_resp.created_date);
	    	}


	}
}
