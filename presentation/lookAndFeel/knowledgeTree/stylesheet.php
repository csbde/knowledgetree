<?php
header('mime_type', 'text/css');
require("../../../config/dmsDefaults.php");

$scroll = array();

$browser = $default->phpSniff->property("browser");
$version = $default->phpSniff->property("version");
echo $browser . ":" . $version;
if ( ($browser == "moz") && ($version <= 4.79)) {
    $scroll["upArrowLeft"] = "780";
    $scroll["upArrowTop"] = "190";
    $scroll["downArrowLeft"] = "780";
    $scroll["downArrowTop"] = "585";
    
    $scroll["textBoxLeft"] = "145";
    $scroll["textBoxTop"] = "190";
    $scroll["textBoxWidth"] = "620";
    $scroll["textBoxHeight"] = "395";  
} elseif ( ($browser == "moz") && ($version == "5.0")) {
    $scroll["upArrowLeft"] = "722";
    $scroll["upArrowTop"] = "107";
    $scroll["downArrowLeft"] = "722";
    $scroll["downArrowTop"] = "588";
    
    $scroll["textBoxLeft"] = "135";
    $scroll["textBoxTop"] = "120";
    $scroll["textBoxWidth"] = "600";
    $scroll["textBoxHeight"] = "458";
} elseif ($browser == "ie") {
    // MSIE 6.0
    $scroll["upArrowLeft"] = "725";
    $scroll["upArrowTop"] = "122";
    $scroll["downArrowLeft"] = "725";
    $scroll["downArrowTop"] = "604";
    
    $scroll["textBoxLeft"] = "138";
    $scroll["textBoxTop"] = "135";
    $scroll["textBoxWidth"] = "600";
    $scroll["textBoxHeight"] = "463";
} else {
    $scroll["upArrowLeft"] = "722";
    $scroll["upArrowTop"] = "107";
    $scroll["downArrowLeft"] = "722";
    $scroll["downArrowTop"] = "588";
    
    $scroll["textBoxLeft"] = "135";
    $scroll["textBoxTop"] = "120";
    $scroll["textBoxWidth"] = "600";
    $scroll["textBoxHeight"] = "458";	
}
?>
<pre>

P {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #000000;
text-decoration: none;
}

P.footer {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #999999;
text-decoration: none;
}

P.title {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : inherit;
font-style : normal;
color : #ffffff;
text-decoration: none;
}


A {
font-size : 8pt;
font-family : Verdana, sans-serif;
text-decoration: none;
color : #000000;
}


A:Visited  {
font-size : 8pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #000000;
}

A:Active  {
color : #000000;
font-size : 8pt;
font-family : Verdana, sans-serif;
font-style : normal;
}

A:hover {
color : #000000;
font-size : 8pt;
font-family : Verdana,sans-serif;
font-style : normal;
text-decoration: underline;
}

TABLE {
	font-size : 10pt;
	font-family : Verdana, sans-serif;
}

TD {
	font-size : 8pt ! important;
	font-family : Verdana, sans-serif;
}

CAPTION {
	font-size : 10pt;
}

TH {
	font-size : 9pt;
	font-family : Verdana, sans-serif;
}

TH.sectionHeading {
font-size : 10 pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #ffffff;
text-decoration: none;
}
  
TH.sectionColumns {
font-size : 8pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #000000;
text-decoration: none;
}

.browseTypeSelect {
    color : #000000;
    font-size : 8pt;
    font-family : Verdana,sans-serif;
}

.errorText {
	color : #FF0000;
	font-size : 8pt;
	text-align : center;
	font-family : Verdana,sans-serif;
}

INPUT {
	font-size : 8pt;
	font-family : Verdana,sans-serif;
}

SELECT {
	font-size : 8pt;
	font-family : Verdana,sans-serif;
}

#divUp   {
    position:absolute; 
    left: <?php echo $scroll["upArrowLeft"] ?>px; 
    top: <?php echo $scroll["upArrowTop"] ?>px;}

#divDown {
    position:absolute; 
    left: <?php echo $scroll["downArrowLeft"] ?>px; 
    top: <?php echo $scroll["downArrowTop"] ?>px;}

#divScrollTextCont {
    position:absolute; 
    left: <?php echo $scroll["textBoxLeft"] ?>px; 
    top: <?php echo $scroll["textBoxTop"] ?>px; 
    width: <?php echo $scroll["textBoxWidth"] ?>px; 
    height: <?php echo $scroll["textBoxHeight"] ?>px; 
    clip:rect(0px <?php echo $scroll["textBoxWidth"] ?>px <?php echo $scroll["textBoxHeight"] ?>px 0px); 
    overflow:hidden; 
    visibility:hidden;
}

#divText {
    position:absolute; 
    left:0px; 
    top:0px;
}
