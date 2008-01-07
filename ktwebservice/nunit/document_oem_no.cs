using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class DocumentOemNoTest : KTTest
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
		public void UpdateOemNoMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[0];

			kt_sysdata_item[] sysdata = new kt_sysdata_item[1];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "oem_document_no";
			sysdata[0].value = "1234";

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);

			Assert.AreEqual("1234", update_resp.oem_document_no);
	    }

		[Test]
		public void UpdateUniqueOemNoMetadataTest()
		{
			kt_metadata_fieldset[] fs = new kt_metadata_fieldset[0];

			kt_sysdata_item[] sysdata = new kt_sysdata_item[1];
			sysdata[0] = new kt_sysdata_item();
			sysdata[0].name = "unique_oem_document_no";
			sysdata[0].value = "1234";

			kt_document_detail update_resp = this._kt.update_document_metadata(this._session, this._docId, fs, sysdata);
			Assert.AreEqual(0, update_resp.status_code);

			Assert.AreEqual("1234", update_resp.oem_document_no);


			kt_document_collection_response response = this._kt.get_documents_by_oem_no(this._session, "1234", "");

			Assert.AreEqual(1, response.collection.Length);
			Assert.AreEqual(this._docId, response.collection[0].document_id);
			Assert.AreEqual("1234", response.collection[0].oem_document_no);

	    }
	}
}
