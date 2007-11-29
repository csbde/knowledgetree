using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class WorkflowTest : KTTest
    	{ 
		private int 			_folderId; 
		private Document		_doc1;

		[SetUp]
		public void SetUp()
		{ 
			this._folderId = 1; 
			
			this._doc1 = new Document(1, this._session, this._kt, this._verbose, false);
			this._doc1.createFile(this._folderId); 
		}

		[TearDown]
		public void TearDown()
		{
			this._doc1.deleteFile(); 
		}

		[Test]
		public void NonExistantWorkflowTest()
		{
			kt_document_detail response = this._kt.start_document_workflow(this._session, this._doc1.docId, "Non Existant Workflow");
			Assert.IsTrue(0 != response.status_code);
		}

		[Test]
		public void StartWorkflowTest()
		{
			kt_document_detail response = this._kt.start_document_workflow(this._session, this._doc1.docId, "Review Process");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);
		}

		[Test]
		public void StopWorkflowTest()
		{
			kt_document_detail response = this._kt.start_document_workflow(this._session, this._doc1.docId, "Review Process");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);

			response = this._kt.delete_document_workflow(this._session, this._doc1.docId);
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("n/a", response.workflow);
	    	}

		[Test]
		public void GetTransitionsTest()
		{
			kt_document_detail response = this._kt.start_document_workflow(this._session, this._doc1.docId, "Review Process");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);

			kt_workflow_transitions_response trans_resp = this._kt.get_document_workflow_transitions(this._session, this._doc1.docId);
			Assert.AreEqual(0, trans_resp.status_code);
			Assert.AreEqual(1, trans_resp.transitions.Length);
			Assert.AreEqual("Request Approval", trans_resp.transitions[0]);
	    	}

		[Test]
		public void WorkflowTransitionTest()
		{
			kt_document_detail response = this._kt.start_document_workflow(this._session, this._doc1.docId, "Review Process");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);
			Assert.AreEqual("Draft", response.workflow_state);

			response = this._kt.perform_document_workflow_transition(this._session, this._doc1.docId, "Request Approval", "Please approve me");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);
			Assert.AreEqual("Approval", response.workflow_state);

			response = this._kt.perform_document_workflow_transition(this._session, this._doc1.docId, "Approve", "Ok!");
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual("Review Process", response.workflow);
			Assert.AreEqual("Published", response.workflow_state);
	    	}
	}
}
