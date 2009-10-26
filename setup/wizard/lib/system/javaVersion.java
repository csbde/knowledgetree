import java.io.*;
import java.lang.System;
import java.util.Properties;

public class javaVersion {
	public static void main(String args[]) throws Exception {
		String outJV = args[0];
		String outJVHome = args[1];
		Properties sysProps = new Properties();
		sysProps = System.getProperties();
		String sysVersion = sysProps.getProperty("java.version");
		String javaHome = sysProps.getProperty("java.home");
		try{
	    	// Create file
	    	FileWriter fstream = new FileWriter(outJV);
	        BufferedWriter out = new BufferedWriter(fstream);
	    	out.write(sysVersion);
	    	//Close the output stream
	    	out.close();
	    	fstream = new FileWriter(outJVHome);
	        out = new BufferedWriter(fstream);
	    	out.write(javaHome);
	    	//Close the output stream
	    	out.close();
    	} catch (Exception e){//Catch exception if any
      		System.err.println("Error: " + e.getMessage());
    	}
	}
}