import java.io.*;
import java.lang.System;
import java.util.Properties;

public class openOffice {

	public static void main(String args[]) throws Exception {
		String openoffice = args[0];
		try {
        	// Execute a command without arguments
        	String command = ""+openoffice+" -nofirststartwizard -nologo -headless -accept=\"socket,host=localhost,port=8100;urp;StarOffice.ServiceManager\"";
        	Process child = Runtime.getRuntime().exec(command);
        	System.out.println(command);
    	} catch (IOException e) {
    		System.err.println("Error: " + e.getMessage());
    	}
	}
}
