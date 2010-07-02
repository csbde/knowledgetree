These are SASS (scss) scripts used to generate some of the css for KT

In order to use sass scripts, you can follow this process:

LINUX
-----
	* Install Ruby
	* Install RubyGems
	* "gem install haml"
	
WINDOWS
-------
	* Download CygWin & mark Ruby to be installed
	* Download latest rubygems, unzip and run "ruby setup.rb" in the folder where you extracted
	* "gem install haml"
	
Using SASS
----------
	"sass --help" will give you an indication of how to implement sass
	I use "sass -t compact --watch input_folder:output_folder"
		this will wach the input folder for any scss files that change and automatically putting the css files in the output folder.
