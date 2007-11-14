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
