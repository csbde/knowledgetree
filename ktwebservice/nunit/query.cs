using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class QueryTest
    	{

		private String 			_session;
		private KnowledgeTreeService 	_kt;

		[SetUp]
		public void SetUp()
		{
			this._kt = new KnowledgeTreeService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;

		}

		[TearDown]
		public void TearDown()
		{
			this._kt.logout(this._session);
		}

		[Test]
		public void Query()
		{
			kt_search_response response = this._kt.search(this._session, "Filesize >= \"0\"", "");

			Assert.AreEqual(0,response.status_code);


			if (response.status_code == 0)
			{

				for(int i=0;i<response.hits.Length;i++)
				{

				}
			}
    		}
	}
}
