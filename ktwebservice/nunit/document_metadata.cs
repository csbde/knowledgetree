using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentMetadataTest : KTTest
    	{
		private int 			_docId;
		private int 			_folderId;
		private String			_filename;
		private String			_content;


		[SetUp]
		public void SetUp()
		{
			this._filename = Helper.isUnix()?"/tmp/kt_unit_test1.txt":"c:\\kt_unit_test1.txt";

			String filename = "kt unit test1";

			this._content = "hello world!";

			Helper.writeFile(this._filename, this._content);

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
		}

		[Test]
		public void GetDocumentTypesTest()
		{
			kt_document_types_response doc_types = this._kt.get_document_types(this._session);
			Assert.AreEqual(0, doc_types.status_code);
	    	}

		[Test]
		public void GetDocumentTypeMetadataTest()
		{

			kt_metadata_response metadata = this._kt.get_document_type_metadata(this._session, "Default");
			Assert.AreEqual(0, metadata.status_code);
	    	}

		[Test]
		public void GetDocumentMetadataTest()
		{

			kt_metadata_response metadata = this._kt.get_document_metadata(this._session, this._docId);
			Assert.AreEqual(0, metadata.status_code);
	    	}

		[Test]
		public void UpdateDocumentMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Category";
			fs[0].fields[0].value = "Technical";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Document Author";
			fs[0].fields[1].value = "Joe Soap";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[0];

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Technical", update_resp.metadata[1].fields[1].value);

			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe Soap", update_resp.metadata[1].fields[0].value);

			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text", update_resp.metadata[1].fields[2].value);


	    	}

		[Test]
		public void UpdateDocumentMetadataWithSpecialCharactersTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[3];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe \\Soap";
			fs[0].fields[1] = new kt_metadata_field();
			fs[0].fields[1].name = "Category";
			fs[0].fields[1].value = "Tec/hn\\ical/";
			fs[0].fields[2] = new kt_metadata_field();
			fs[0].fields[2].name = "Media Type";
			fs[0].fields[2].value = "Text'";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[0];

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
			Assert.AreEqual("General information", update_resp.metadata[1].fieldset);

			Assert.AreEqual("Category", update_resp.metadata[1].fields[1].name);
			Assert.AreEqual("Tec/hn\\ical/", update_resp.metadata[1].fields[1].value);


			Assert.AreEqual("Document Author", update_resp.metadata[1].fields[0].name);
			Assert.AreEqual("Joe \\Soap", update_resp.metadata[1].fields[0].value);


			Assert.AreEqual("Media Type", update_resp.metadata[1].fields[2].name);
			Assert.AreEqual("Text'", update_resp.metadata[1].fields[2].value);
	    }

		[Test]
		public void ProblemMetadataNoFieldSetTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "UnknownFieldset";
			fs[0].fields = new kt_metadata_field[1];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Author";
			fs[0].fields[0].value = "Joe \\Soap";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[0];

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
	    }

	    [Test]
		public void ProblemMetadataNoFieldTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[1];
			fs[0] = new kt_metadata_fieldset();
			fs[0].fieldset = "General information";
			fs[0].fields = new kt_metadata_field[1];
			fs[0].fields[0] = new kt_metadata_field();
			fs[0].fields[0].name = "Document Owner";
			fs[0].fields[0].value = "Joe \\Soap";

			kt_sysdata_item[] sysdata = new kt_sysdata_item[0];

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);
	    }

	}
}
