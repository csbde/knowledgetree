using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class AuthenticationTest
    	{

		private String 			_session;
		private KnowledgeTreeService 	_kt;

		[SetUp]
		public void SetUp()
		{
			this._kt = new KTWebService();
		}

		[TearDown]
		public void TearDown()
		{
		}

		[Test]
		public void Login()
		{
			kt_response response = this._kt.login("admin","admin","127.0.0.1");

			Assert.AreEqual(0,response.status_code);
			Assert.IsFalse(response.message == null);
			Assert.IsFalse(response.message == "");

			this._session = response.message;
    		}

		[Test]
		public void Logout()
		{
	    		kt_response response = this._kt.logout(this._session);
			Assert.AreEqual(0,response.status_code);
	    	}
	}
}
