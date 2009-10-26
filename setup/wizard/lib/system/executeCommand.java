import java.io.*;
import java.lang.System;
import java.util.Properties;

public class executeCommand {

	public static void main(String args[]) throws Exception {
		String command = args[0];
		try {
        	// Execute a command without arguments
        	Process child = Runtime.getRuntime().exec(command);
    	} catch (IOException e) {
    		System.err.println("Error: " + e.getMessage());
    	}
	}
}
