using System;
using System.Text;
using System.Net;
using System.IO;
using System.Collections;
using System.Data;
using System.Data.Odbc;
using System.Runtime.Remoting;
using System.Runtime.Remoting.Channels;
using System.Runtime.Remoting.Messaging;
using System.Runtime.Remoting.Activation;
using System.Runtime.Remoting.Services;
using System.Runtime.Serialization;
using System.Text.RegularExpressions;
using System.Web.Services.Protocols;
using System.Reflection;
using System.Web;
using System.Xml;
using System.Web.Services;
using System.Diagnostics;
using System.Runtime.CompilerServices;
using System.Web.Services.Description;
using System.Web.Services.Discovery;
using System.Xml.Serialization;
using System.Xml.Schema;
using System.Threading;
using System.Web.Services.Protocols;

namespace MonoTests.KnowledgeTree
{



	[System.Web.Services.WebServiceBinding(Name="KnowledgeTreePort", Namespace="urn:KnowledgeTree")]
	public class KTWebService : KnowledgeTreeService
 	{
		public KTWebService() : base()
		{
			this.Url = Environment.GetEnvironmentVariable("KT_ROOT_URL") + "/ktwebservice/webservice.php";
		}
 	}

	public class MySoapHttpClientProtocol : SoapHttpClientProtocol
	{
		public MySoapHttpClientProtocol() : base() {}

		public  object [] ReceiveResponse (WebResponse response, SoapClientMessage message, SoapExtension[] extensions)
		{

			StreamReader sr = new StreamReader(response.GetResponseStream());
			String content = sr.ReadToEnd();
			System.Console.WriteLine(content);

			return null;
		}
	}

	public class KTTest
    	{
		protected KTWebService 	_kt;
		protected String 			_session;
		protected bool	_verbose;


		public KTTest()
		{
			this._kt = new KTWebService();
			kt_response response = this._kt.login("admin","admin","127.0.0.1");
			this._session = response.message;
			this._verbose = false;
			this.setupDb();

			//System.Web.Services.Protocols.SoapHttpClientProtocol.ReceiveResponse
		}

		void setupDb()
		{

			String connectionString = "DSN=ktdms;" + "UID=root;" + "PWD=";
			try
	  		{
       				IDbConnection dbcon = new OdbcConnection(connectionString);
       				if (dbcon == null)
				{
					System.Console.WriteLine("Cannot create connection");
       				}
				dbcon.Open();
       				IDbCommand dbcmd = dbcon.CreateCommand();
         			if (dbcmd == null)
				{
					System.Console.WriteLine("Cannot create command");
				}
       				dbcmd.CommandText = "DELETE FROM folders WHERE id > 1";
       				dbcmd.CommandType = CommandType.Text;
        			dbcmd.ExecuteNonQuery();
       				dbcmd.CommandText = "DELETE FROM documents";
       				dbcmd.CommandType = CommandType.Text;
        			dbcmd.ExecuteNonQuery();
				dbcmd.CommandText = "DELETE FROM document_types_lookup WHERE name = 'NewType'";
        			dbcmd.ExecuteNonQuery();
				dbcmd.CommandText = "INSERT INTO document_types_lookup(id,name) VALUES(2,'NewType')";
        			dbcmd.ExecuteNonQuery();
				dbcmd.Dispose();
       				dbcmd = null;
       				dbcon.Close();
       				dbcon = null;
       			}
       			catch(Exception ex)
       			{
       				System.Console.WriteLine(ex.Message);
       			}
		}

		~KTTest()
		{
	   		this._kt.logout(this._session);
		}
	}



	public class FileUploader
	{
		private String boundary;
		private String uri;
		public String filename;


		public FileUploader(String uri)
		{
			this.uri = uri;
			System.Console.WriteLine("Using upload URL: " + uri);
			this.boundary = "----" + DateTime.Now.Ticks.ToString("x");
		}

		public FileUploader() : this(Environment.GetEnvironmentVariable("KT_ROOT_URL") + "/ktwebservice/upload.php")
		{
		}


		public String getFilename()
		{
			return this.filename;
		}


		public void upload(String sessionid, String filename)
		{
			String displayname = Path.GetFileName(filename);
			StringBuilder header = new StringBuilder();

			header.Append("--" + boundary + "\r\n" + "Content-Disposition: form-data; name=\"session_id\"\r\n\r\n" + sessionid + "\r\n");
			header.Append("--" + boundary + "\r\n" + "Content-Disposition: form-data; name=\"action\"\r\n\r\nA\r\n");
			header.Append("--" + boundary + "\r\n" + "Content-Disposition: form-data; name=\"output\"\r\n\r\nxml\r\n");

			header.Append("--" + boundary + "\r\n");
			header.Append("Content-Disposition: form-data; name=\"name\";");
			header.Append("filename=\"" + displayname + "\"\r\nContent-Type: application/octet-stream\r\n\r\n");


			HttpWebRequest webrequest = (HttpWebRequest)WebRequest.Create(this.uri);
			webrequest.ContentType = "multipart/form-data; boundary=" + boundary;
			webrequest.Method = "POST";


			byte[] headerArray = Encoding.UTF8.GetBytes(header.ToString());
			byte[] boundaryArray = Encoding.ASCII.GetBytes("\r\n--" + boundary + "\r\n");

			FileStream file = new FileStream(filename, System.IO.FileMode.Open, System.IO.FileAccess.Read);

			long filesize = file.Length;
			webrequest.ContentLength = headerArray.Length + filesize + boundaryArray.Length;

			Stream requestStream = webrequest.GetRequestStream();
			requestStream.Write(headerArray, 0, headerArray.Length);

			byte[] buffer = new byte[10240];

			int read = 0;

			while ((read = file.Read(buffer, 0, buffer.Length)) > 0)
			{

				requestStream.Write(buffer, 0, read);
			}

			requestStream.Write(boundaryArray, 0, boundaryArray.Length);


			WebResponse response = webrequest.GetResponse();
			StreamReader sr = new StreamReader(response.GetResponseStream());
			String xml = sr.ReadToEnd();
			//System.Console.WriteLine("xml: " + xml);



			if (xml.IndexOf("<status_code>0</status_code>") != -1)
			{
				long tmp = this.tokenInt("filesize",xml);
				if (tmp != filesize)
				{
					throw new Exception("Filesize should be " + filesize + " but appears to be "+ tmp);
				}

				tmp = this.tokenInt("error",xml);
				if (tmp > 0)
				{
					throw new Exception("The server reported error code " + tmp + " for the file upload");
				}
				this.filename = this.tokenString("filename",xml);
				// yay, all is good!
				return;
			}

			String msg = this.tokenString("msg", xml);
			long error = this.tokenInt("error", xml);
			if (error > 0)
			{
				throw new Exception("The server reported error code " + error + " for the file upload");
			}

			throw new Exception("Upload error: " + msg);

		}

		private String tokenString(String token, String xml)
		{
			int tokStart = xml.IndexOf("<"+token+">") + token.Length+2;
			int tokEnd = xml.IndexOf("</"+token+">");
			if (tokEnd == -1) return "";

			String value = xml.Substring(tokStart, tokEnd-tokStart);
			//System.Console.WriteLine(token + ": " + value);
			return value;
		}

		private long tokenInt(String token, String xml)
		{
			String value = this.tokenString(token, xml);
			if (value.Equals(""))
			{
				return -1;
			}
			return long.Parse(value);
		}

	}



	public class Document
	{
		public String title;
		public String filename;
		public String realFilename;
		public String content;
		public int docId;
		public String 			session;
		public KnowledgeTreeService 	kt;
		public long filesize;
		public bool verbose;
		public bool local;

		public Document(int offset, String session, KnowledgeTreeService kt, bool verbose, bool local)
		{
			this.title = "kt unit test" + offset;
			this.realFilename =  "kt_unit_test" + offset + ".txt";
			this.filename = (Helper.isUnix()?("/tmp/"):("c:\\")) + this.realFilename;
			this.content = "Hello World!\nThis is a test! And more!\n\n\r\n";
			this.docId = 0;
			this.session = session;
			this.kt = kt;
			this.verbose =verbose;
			this.local = local;
		}

		public kt_document_detail createFile(int folderId)
		{
			Helper.writeFile(this.filename, this.content);
			this.filesize = this.content.Length;

			if (this.local)
			{
				return null;
			}
			kt_document_detail d1 = this.kt.get_document_detail_by_title(this.session, folderId, this.title, "");
			if (d1.status_code == 1)
			{
				this.docId = d1.document_id;
				this.deleteFile();
			}

			kt_document_detail response1 = this.kt.add_base64_document(this.session, folderId, this.title, this.filename, "Default", Helper.ConvertFileToBase64Encoding(this.filename));

			if (response1.status_code == 0)
			{
				this.docId = response1.document_id;
			}

			if (this.verbose)
			{
				if (response1.status_code == 0)
				{
					System.Console.WriteLine("docid: " + this.docId + " filename: " + this.filename);
				}
				else
				{
					System.Console.WriteLine("Could not create file: " + this.filename);
				}
			}

			return response1;

		}

		public kt_document_detail createFileWithMetadata(int folderId, kt_metadata_fieldset[] metadata, kt_sysdata_item[] sysdata)
		{
			Helper.writeFile(this.filename, this.content);

			this.filesize = this.content.Length;

			if (this.local)
			{
				return null;
			}

			kt_document_detail d1 = this.kt.get_document_detail_by_title(this.session, folderId, this.title, "");
			if (d1.status_code == 1)
			{
				this.docId = d1.document_id;
				this.deleteFile();
			}

			kt_document_detail response1 = this.kt.add_base64_document_with_metadata(this.session, folderId, this.title, this.filename, "Default", Helper.ConvertFileToBase64Encoding(this.filename), metadata, sysdata);

			if (response1.status_code == 0)
			{
				this.docId = response1.document_id;
			}

			if (this.verbose)
			{
				if (response1.status_code == 0)
				{
					System.Console.WriteLine("docid: " + this.docId + " filename: " + this.filename);
				}
				else
				{
					System.Console.WriteLine("Could not create file: " + this.filename);
				}
			}

			return 	response1;
		}

		public kt_document_detail checkinFileWithMetadata(int folderId, kt_metadata_fieldset[] metadata, kt_sysdata_item[] sysdata)
		{
			Helper.writeFile(this.filename, this.content);
			this.filesize = this.content.Length;

			if (this.local)
			{
				return null;
			}

			kt_document_detail d1 = this.kt.get_document_detail_by_title(this.session, folderId, this.title, "");
			if (d1.status_code == 1)
			{
				this.docId = d1.document_id;
				this.deleteFile();
			}

			kt_document_detail response1 = this.kt.checkin_base64_document_with_metadata(this.session, this.docId, this.filename, "checkin reason", Helper.ConvertFileToBase64Encoding(this.filename), false, metadata, sysdata);

			if (response1.status_code == 0)
			{
				this.docId = response1.document_id;
			}

			if (this.verbose)
			{
				if (response1.status_code == 0)
				{
					System.Console.WriteLine("docid: " + this.docId + " filename: " + this.filename);
				}
				else
				{
					System.Console.WriteLine("Could not create file: " + this.filename);
				}
			}

			return 	response1;
		}



		public void deleteFile()
		{
			Helper.deleteFile(this.filename);

			if (this.local)
			{
				return;
			 }

			if (this.docId > 0)
			{

				kt_response response = this.kt.delete_document(this.session, this.docId, "Delete - cleaning up");
				if (this.verbose && response.status_code != 0)
				{
					System.Console.WriteLine("Could not delete file: " + this.filename);
				}
			}
		}
	}


	public class Helper
    	{
		public static bool isUnix()
		{
			// found reference on: http://www.mono-project.com/FAQ:_Technical
			int platform = (int) Environment.OSVersion.Platform;
			return (platform == 4) || (platform == 128);
		}

		public static void writeFile(String filename, String text)
		{
			try
			{
		 		TextWriter tw = new StreamWriter(filename);
			 	tw.WriteLine(text );
			 	tw.Close();
			}
			catch (System.Exception exp)
			{
				System.Console.WriteLine("{0}", exp.Message);
				throw;
			}
		}

		public static String readFile(String filename)
		{
			String text = null;
			try
			{
				FileStream inFile = new FileStream(filename, System.IO.FileMode.Open, System.IO.FileAccess.Read);
				StreamReader sr = new StreamReader(inFile);
				text = sr.ReadToEnd();
				inFile.Close();
			}
			catch (System.Exception exp)
			{
				System.Console.WriteLine("{0}", exp.Message);
				throw;
			}

			return text;
		}

		public static void deleteFile(string filename)
		{
			try
			{
				File.Delete(filename);
			}
			catch(System.Exception)
			{
				// we are using this to cleanup, so don't handle
			}
		}

		public static string ConvertFileToBase64Encoding(string filename)
		{
			System.IO.FileStream inFile;

			byte[] binaryData;
			string base64String = "";

			try
			{
				inFile = new System.IO.FileStream(filename, System.IO.FileMode.Open, System.IO.FileAccess.Read);
				binaryData = new Byte[inFile.Length];
				inFile.Read(binaryData, 0, (int)inFile.Length);
				inFile.Close();

				base64String = System.Convert.ToBase64String(binaryData, 0, binaryData.Length);
			}
			catch (System.Exception exp)
			{
				System.Console.WriteLine("{0}", exp.Message);
				throw;
			}

			return base64String;
		}

		public static long ConvertBase64EncodingToFile(String encoding, string filename)
		{
			System.IO.FileStream inFile;

			byte[] binaryData;

			try
			{
				binaryData = Convert.FromBase64String (encoding);

				inFile = new System.IO.FileStream(filename, System.IO.FileMode.Create);

				inFile.Write(binaryData, 0, (int)binaryData.Length);
				inFile.Close();
			}
			catch (System.Exception exp)
			{
				System.Console.WriteLine("{0}", exp.Message);
				throw;
			}
			return binaryData.Length;
		}

	}
}
