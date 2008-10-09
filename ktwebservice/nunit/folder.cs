using NUnit.Framework;
using System;
using System.IO;

namespace MonoTests.KnowledgeTree
{
	[TestFixture]
	public class FolderTest : KTTest
    	{

		private int			_folder_id;
		private int			_subfolder_id;

		[SetUp]
		public void SetUp()
		{
		}

		[TearDown]
		public void TearDown()
		{
		}

		[Test]
		public void GetFolderDetail()
		{

			kt_folder_detail response = this._kt.get_folder_detail(this._session, 1);
			Assert.AreEqual(0, response.status_code);
			Assert.AreEqual(1, response.id);
			Assert.AreEqual("Root Folder", response.folder_name);
			Assert.AreEqual(0, response.parent_id);
			Assert.AreEqual("/", response.full_path);
    		}

		[Test]
		public void AddFolder()
		{

	    		kt_folder_detail response = this._kt.create_folder(this._session, 1, "kt_unit_test");
		       	Assert.AreEqual(0,response.status_code);

			this._folder_id = response.id;

	    		response = this._kt.create_folder(this._session, this._folder_id, "subfolder");
		       	Assert.AreEqual(0,response.status_code);

			this._subfolder_id = response.id;

	    	}




		[Test]
		public void GetFolderByName()
		{

			kt_folder_detail response = this._kt.get_folder_detail_by_name(this._session, "/kt_unit_test");
			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(this._folder_id, response.id);

			response = this._kt.get_folder_detail_by_name(this._session, "kt_unit_test");
			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(this._folder_id, response.id);

			response = this._kt.get_folder_detail_by_name(this._session, "kt_unit_test/subfolder");
			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(this._subfolder_id,response.id);

			response = this._kt.get_folder_detail_by_name(this._session, "kt_unit_test/subfolder2");
			Assert.IsFalse(response.status_code == 0);


    		}

		[Test]
		public void GetFolderContents()
		{
	    	kt_folder_contents response = this._kt.get_folder_contents(this._session, this._folder_id, 1, "DF");
			Assert.AreEqual(0,response.status_code);
			Assert.AreEqual(this._folder_id,response.folder_id);
			Assert.AreEqual("kt_unit_test", response.folder_name);
			Assert.AreEqual("kt_unit_test", response.full_path);

	    		kt_folder_contents response2 = this._kt.get_folder_contents(this._session, this._subfolder_id, 1, "DF");
			Assert.AreEqual(0, response2.status_code);
			Assert.AreEqual(this._subfolder_id, response2.folder_id);
			Assert.AreEqual("subfolder", response2.folder_name);
			Assert.AreEqual("kt_unit_test/subfolder", response2.full_path);
	    }

		[Test]
		public void RenameFolder()
		{
			kt_response response = this._kt.rename_folder(this._session, this._subfolder_id, "subfolde'r2");
			Assert.AreEqual(0, response.status_code);

			kt_folder_detail response2 = this._kt.get_folder_detail(this._session, this._subfolder_id);
			Assert.AreEqual(0, response2.status_code);
			Assert.AreEqual(this._subfolder_id, response2.id);
			Assert.AreEqual("subfolde-r2", response2.folder_name);
			Assert.AreEqual(this._folder_id, response2.parent_id);
			Assert.AreEqual("kt_unit_test/subfolde-r2", response2.full_path);
	    }

		[Test]
		public void RemoveFolder()
		{
	    	kt_response response = this._kt.delete_folder(this._session, this._folder_id, "unit testing remove");
			Assert.AreEqual(0, response.status_code);
	    }

		[Test]
		public void AddFolderWithSpecialCharacters()
		{
	    		kt_folder_detail response = this._kt.create_folder(this._session, 1, "kt.unit.test");
		       	Assert.AreEqual(0,response.status_code);
		       	Assert.AreEqual("kt.unit.test",response.folder_name);

	    		response = this._kt.create_folder(this._session, 1, "kt ' unit \" test");
		       	Assert.AreEqual(0,response.status_code);
		       	Assert.AreEqual("kt - unit - test",response.folder_name);

		       	// this fails because the previous folder makes a folder with the same name because of invalid character substitution
	    		response = this._kt.create_folder(this._session, 1, "kt - unit - test");
		       	Assert.AreEqual(22,response.status_code);
//		       	Assert.AreEqual("kt - unit - test",response.folder_name);

		       	response = this._kt.get_folder_detail_by_name(this._session, "/kt ' unit \" test");
		       	Assert.AreEqual(0,response.status_code);
		       	Assert.AreEqual("kt - unit - test",response.folder_name);
		}

		[Test]
		public void CopyFolder()
		{
	    		kt_folder_detail response = this._kt.create_folder(this._session, 1, "kt_unit_test2");
		       	Assert.AreEqual(0,response.status_code);

			this._folder_id = response.id;

	    		response = this._kt.create_folder(this._session, 1, "subfolder");
		       	Assert.AreEqual(0,response.status_code);

				this._subfolder_id = response.id;


				response = this._kt.copy_folder(this._session, this._folder_id, this._subfolder_id, "copy reason");
		       	Assert.AreEqual(0,response.status_code);
		       	Assert.AreEqual(this._subfolder_id,response.parent_id);
		       	Assert.AreEqual("kt_unit_test2",response.folder_name);

	    	}

	    [Test]
		public void MoveFolder()
		{

	    		kt_folder_detail response = this._kt.create_folder(this._session, 1, "kt_unit_test3");
		       	Assert.AreEqual(0,response.status_code);

				this._folder_id = response.id;

	    		response = this._kt.create_folder(this._session, 1, "subfolder3");
		       	Assert.AreEqual(0,response.status_code);

				this._subfolder_id = response.id;

				response = this._kt.move_folder(this._session, this._folder_id, this._subfolder_id, "move reason");
		       	Assert.AreEqual(0,response.status_code);
		       	Assert.AreEqual(this._folder_id,response.id);
		       	Assert.AreEqual(this._subfolder_id,response.parent_id);
		       	Assert.AreEqual("kt_unit_test3",response.folder_name);
	    }


	}
}
