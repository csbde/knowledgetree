using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class QueryTest : KTTest
    	{
 

		[SetUp]
		public void SetUp()
		{ 
		}

		[TearDown]
		public void TearDown()
		{ 
		}

		[Test]
		public void Query()
		{
			Document doc = new Document(0,this._session, this._kt, false, false);
			doc.createFile(1);
			kt_search_response response = this._kt.search(this._session, "Filesize = \"13\"", "");

			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(doc.content.Length + 1,response.hits[0].filesize); 
			Assert.AreEqual(doc.title,response.hits[0].title);
			Assert.AreEqual(doc.realFilename,response.hits[0].filename); 
			
			response = this._kt.search(this._session, "DocumentId = \""+ response.hits[0].document_id +"\"", "");

			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(doc.content.Length + 1,response.hits[0].filesize); 
			Assert.AreEqual(doc.title,response.hits[0].title);
			Assert.AreEqual(doc.realFilename,response.hits[0].filename); 	
			
			response = this._kt.search(this._session, "Title = \""+ response.hits[0].title +"\"", "");

			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(doc.content.Length + 1,response.hits[0].filesize); 
			Assert.AreEqual(doc.title,response.hits[0].title);
			Assert.AreEqual(doc.realFilename,response.hits[0].filename); 
					
			
			response = this._kt.search(this._session, "Filename = \""+ response.hits[0].filename +"\"", "");

			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(doc.content.Length + 1,response.hits[0].filesize); 
			Assert.AreEqual(doc.title,response.hits[0].title);
			Assert.AreEqual(doc.realFilename,response.hits[0].filename); 
			
			doc.deleteFile();
    		}
	}
}
