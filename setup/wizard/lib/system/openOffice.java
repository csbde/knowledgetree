import java.io.*;
import java.lang.System;
import java.util.Properties;

public class openOffice {

	public static void main(String args[]) throws Exception {
		try {
        	// Execute a command without arguments
        	String command = "nohup /usr/bin/soffice -nofirststartwizard -nologo -headless -accept=\"socket,host=localhost,port=8100;urp;StarOffice.ServiceManager\" > /dev/null 2>&1 & echo $!";
        	Process child = Runtime.getRuntime().exec(command);
    	} catch (IOException e) {
    		System.err.println("Error: " + e.getMessage());
    	}
	}
}