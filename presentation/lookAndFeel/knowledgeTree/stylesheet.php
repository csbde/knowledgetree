<?php
require("../../../config/dmsDefaults.php");
header("Content-type: text/css");
?>
BODY {
    background: #ffffff;
}

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
	font-size : 16pt;
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
    left: <?php echo $default->upArrowLeft ?>px; 
    top: <?php echo $default->upArrowTop ?>px;
}

#divDown {
    position:absolute; 
    left: <?php echo $default->downArrowLeft ?>px; 
    top: <?php echo $default->downArrowTop ?>px;
}

#divScrollTextCont {
    position:absolute; 
    left: <?php echo $default->textBoxLeft ?>px; 
    top: <?php echo $default->textBoxTop ?>px; 
    width: <?php echo $default->textBoxWidth ?>px; 
    height: <?php echo $default->textBoxHeight ?>px; 
    clip:rect(0px <?php echo $default->textBoxWidth ?>px <?php echo $default->textBoxHeight ?>px 0px); 
    overflow:hidden; 
    visibility:hidden;
}

#divText {
    position:absolute; 
    left:0px; 
    top:0px;
}

span.button {
        cursor: hand;
        font-size: 6pt;
        width: 100%;
        /* height: 20px; */
        border: 1px solid #000;
        text-align: center;
        white-space: nowrap;
        display: block;
        margin-top: 4px;
        text-decoration: none;
}
span.disabledbutton {
        font-size: 6pt;
        background-color: #CCCCCC;
        color: #333333;
        width: 100%;
        /* height: 20px; */
        border: 1px solid #333;
        text-align: center;
        white-space: nowrap;
        display: block;
        margin-top: 4px;
}

span.dash {
        font-size: 7pt;
        font-weight: 600;
        /* height: 20px; */
        /* border: 1px solid #FFF; */
        border-left: 2px solid #FFF;
        border-top: 2px solid #FFF;
        background-color: #9D9D7F;
        text-align: center;
        white-space: nowrap;
        display: block;
        /* margin: 2px; */
        /* padding: 1px; */
        padding-left: 5px;
        padding-right: 5px;
        padding-top: 2px;
        padding-bottom: 1px;
        text-decoration: none;
    font-style : normal;
    color : #FFFFFF;
}

span.dash:Hover {
        /* background-color: #EEE; */
        text-decoration: none;
}

span.dashactive {
        font-size: 7pt;
        font-weight: 600;
        /* height: 20px; */
        /* border: 1px solid #FFF; */
        border-left: 2px solid #FFF;
        border-top: 2px solid #FFF;
        background-color: #1A4383;
        text-align: center;
        white-space: nowrap;
        display: block;
        /* margin: 2px; */
        padding-left: 5px;
        padding-right: 5px;
        padding-top: 2px;
        padding-bottom: 1px;
        text-decoration: none;
    font-style : normal;
    color : #FFFFFF;
}

span.dashactive:Hover {
        /* background-color: #EEE; */
        text-decoration: none;
}

td.colour_dashboard {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #007A3F;
}

td.colour_browse {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #57AFAE;
}

td.colour_subscriptions {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #FFC602;
}

td.colour_asearch {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #A1571B;
}

td.colour_admin {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #056DCE;
}

td.colour_prefs {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #F87308;
}

td.colour_help {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #80CE05;
}

td.colour_logout {
    border-left: 2px solid #FFF;
    border-top: 2px solid #FFF;
    height: 10px;
    width: 100%;
    background-color: #CE0505;
}

table.pretty {
    margin: 0;
    padding: 0;
    border: 0;
    border-top: 1px solid #cccccc;
    border-left: 1px solid #cccccc;
}

table.pretty > thead > tr {
    border: 0;
    margin: 0;
    padding: 0;
    background-color: #feeeee;
}

table.pretty > thead > tr > th {
    border-right: 1px solid #cccccc;
    border-bottom: 2px solid #000000;
    border-left: 0;
    border-right: 0;
    padding-left: 5px;
    padding-right: 5px;
    padding-top: 2px;
    padding-bottom: 2px;
}

table.pretty > tbody > tr {
    border: 0;
    margin: 0;
    padding: 0;
    background-color: #eeeefe;
}

table.pretty > tbody > tr.odd {
    background-color: #eeeefe;
}
table.pretty > tbody > tr.odd {
    background-color: #fafafe;
}

table.pretty > tbody > tr > td {
    border: 0;
    border-right: 1px solid #cccccc;
    border-bottom: 1px solid #cccccc;
    margin: 0;
    padding-top: 2px;
    padding-bottom: 2px;
    padding-left: 3px;
    padding-right: 3px;
}

table.prettysw {
    margin: 0;
    padding: 0;
    border: 0;
    border-top: 1px solid #cccccc;
    border-left: 1px solid #cccccc;
}

table.prettysw > tbody > tr > th {
    border: 0;
    border-right: 2px solid #000000;
    margin: 0;
    padding: 0;
    padding-right: 1em;
    background-color: #feeeee;
}

table.prettysw > tbody > tr > td {
    border: 0;
    border-bottom: 1px solid #cccccc;
    border-right: 1px solid #cccccc;
    margin: 0;
    padding: 0;
    background-color: #eeeefe;
    padding-right: 1em;
    padding-left: 0.5em;
    text-align: right;
    padding-bottom: 3px;
    padding-top: 3px;
}

table.prettysw > tbody > tr > td.odd {
    background-color: #fafafe;
}

table.prettysw > tbody > tr > td.compare {
    color: red;
}

p.breadcrumbs {
    margin-top: 2px;
    margin-left: 2px;
    background-color: #88aacc;
}

