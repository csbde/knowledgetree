using NUnit.Framework;
using System;
using System.IO;
using System.Net;

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


		[SetUp]
		public void SetUp()
		{
			this._kt = new KnowledgeTreeService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

			this._folderId = 1;


			this._doc1 = new Document(1, this._session, this._kt, this._verbose,false);
			this._doc1.createFile(this._folderId);


			this._verbose = true;

		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile();

			this._kt.logout(this._session);

		}

		[Test]
		public void DownloadTest()
		{
			kt_response update_resp = this._kt.download_document(this._session, this._doc1.docId );
			Assert.AreEqual(0, update_resp.status_code);

			System.Console.WriteLine("Download...." + update_resp.message);

			String uri = update_resp.message;

			HttpWebRequest webrequest = (HttpWebRequest)WebRequest.Create(uri);

			WebResponse response = webrequest.GetResponse();
			StreamReader sr = new StreamReader(response.GetResponseStream());
			String content = sr.ReadToEnd();

			System.Console.WriteLine(content);


	    	}

		[Test]
		public void SmallDownloadTest()
		{
			kt_response update_resp = this._kt.download_small_document(this._session, this._doc1.docId );
			Assert.AreEqual(0, update_resp.status_code);

			String filename = Helper.isUnix()?("/tmp/kt_unit_test_tmp.txt"):("c:\\kt_unit_test_tmp.txt");



			long length = Helper.ConvertBase64EncodingToFile(update_resp.message, filename);
			//System.Console.WriteLine(Helper.readFile(filename));

			// TODO - why???
			//Assert.AreEqual(length, this._doc1.filesize);
	    	}
	}
}
