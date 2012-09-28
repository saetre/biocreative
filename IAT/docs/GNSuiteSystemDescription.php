<?php

$self = $_SERVER{'PHP_SELF'};

if (preg_match ("/docs/", $self)){
	printDocument("GNSuiteSystemDescription.php");
}//If shown as an independent document

function printHeader(){
	$text = '
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
	<meta http-equiv=Content-Type content="text/html; charset=shift_jis">
	<meta name=ProgId content=Word.Document>
	<meta name=Generator content="Microsoft Word 12">
	<meta name=Originator content="Microsoft Word 12">
	<title>GNSuiteSystemDescription</title>
</head>

<body lang=EN-US link=blue vlink=purple style="tab-interval:36.0pt">
';
	return $text;
}//function printHeader

function printFooter(){
	$text = "";
	$text .= "</body>\n";
	$text .= "</html>\n";

	return $text;
}//END function printFooter

function printContents($manual){
	#print "MY MANUAL IS $manual<BR>\n";
	$text = "";
	$text .= "<A HREF='$manual'>DOCUMENTATION</A><BR>\n";
	$text .= "<OL><LI>\n";
	$text .= "<A HREF='$manual#general'>General</A>\n";
	$text .= "</LI><LI>\n";
	$text .= "<A HREF='$manual#installation'>Installation</A>\n";
	$text .= "</LI></OL>";

	#$result = array();
	#$result =  get_directory_files($results_path, "");
	#print "Files: \n";	#.count($result);
	#ort ($result);
	#foreach ($result as $file){
	#	print "<div class='resultFile' id='file$file'>$file</div>\n";
	#}
	
	return $text;
}//END function printContents


function printDocument($manual){
	printHeader();
?>

<h1>The GN<span style='font-size:12.5pt;line-height:115%'>Suite</span> System</h1>

<p class=MsoPlainText><span style='font-family:"Courier New"'>URL:
 <a href="http://www.idi.ntnu.no/~satre/biocreative/IAT/">
	http://www.idi.ntnu.no/~satre/biocreative/IAT/</a>
</span>
</p>

<p class=MsoPlainText><span style='font-family:"Courier New"'>BioCreative Team#:
93, Team leader: Rune S&aelig;tre, University of Tokyo<br>
<o:p>&nbsp;</o:p></span></p>

<h2>DOCUMENTATION</h2>
<A name="general">
<h3>General description</h3>

<p class=MsoNormal>Our GNSuite system addresses the Gene Normalization, and
therefore also the Gene Indexing, tasks.<br>
The front page of the system (Figure 1) lets the user enter or chose from a list
of PubMed Central (PMC) or PubMed identifiers (PMID) for all the provided full
text articles.<br>
The two lists contain the number of normalized gene mentions for each given
paper.<br>
The user can click on one of the PMC-IDs to view that paper, and any recognized
gene name will be highlighted in the text.<br>
At the top of each paper's visualization page is a summary of all the genes in
the paper. There is one tab for the genes recognized by MEDIE in the abstract,
and one tab for all the genes recognized by NERSuite and GNSuite in the full
text.</p>

<p class=MsoNormal>Finally there is a species-tab with taxonomy information
recognized by LINNAEUS.<br>
The user can click on a gene symbol to look up the corresponding gene entry in
Entrez Gene. The naming information is shown in the right column (Figure 2).<br>
Behind each gene symbol is the number of mentions in the current paper. The
user can see each mention in the text, by clicking on the gene-count number to
jump to the first occurrence, and then clicking that occurrence to jump to the
next occurrences, and so on.<br>
Summary:</p>

<p class=MsoNormal>18.000 articles are available online, integrated with the
Gene Normalization (GN) results from the MEDIE system.<br>
(<A HREF='http://www.nactem.ac.uk/medie/'>
http://www.nactem.ac.uk/medie/</A>)<br>
MEDIE uses the GENA dictionary, since the entries are normalized using Entrez-Gene,
Swiss-Prot, TREMBL, Fly-base and several other major Gene/Protein
databases.<br>
The MEDIE system contains only the genes found in the abstract, so we processed
the full papers with our NER- and GN-Suite systems. The normalized gene entries
from GNSuite are visible from the "gnsuite" tab for each individual paper.<br>
The names for a specific gene entry are mapped to the text. For this we use a
fast web service providing cached information from Entrez Gene: <a
href="http://entrezajax.appspot.com/">http://entrezajax.appspot.com/</a>. The
same web-service is also used to find alternative names for the species for
each gene, and to highlight these species names in the text as well.</p>

<p class=MsoNormal>We are still missing the following feature:<br>
1) Make the list of all the genes for each paper editable.<br>
This will be added silently in the very near future...</p>


<A name="installation"><h3>Installation</h3>
<P>
The GN<span style='font-size:10.5pt;line-height:115%'>Suite</span> system is developed and tested using the <B>FireFox</B> Web Browser. In case you are using a different browser like MS-IE, Conqueror, Opera, Chrome etc. there is a chance that the system will not work as expected. It is recommended to download the latest version of FireFox from here <A HREF='http://www.getfirefox.net/'>http://www.getfirefox.net/</A><BR>
Once FireFox is installed, you can just launch the URL below in the FireFox Web-Browser, and you will see the front page of the system. Please note that Java-Scripts must be enabled in the browser for all the functionality to work.
</P>

<A name="development"><h3>Development</h3>
<P>
If you want to contribute to this project, you need access to Tsujii-labs SVN-server. You can check out the code from SVN (for example from mason):<BR>
 svn co file:///home/svn/satre/biocreative/IAT IAT
</P>
<HR>
Front Page: <a href="http://http://www.idi.ntnu.no/~satre/biocreative/IAT/">
	http://http://www.idi.ntnu.no/~satre/biocreative/IAT/</a>
<HR>
<?php
	#foreach ($_SERVER as $key => $value){
	#	print "$key: $value<BR>\n";
	#}
	$ip = $_SERVER{"HTTP_X_FORWARDED_FOR"};
	$counts
	 = file_get_contents("http://www.idi.ntnu.no/~satre/php/count_guest.php?ip=$ip");
	list ($count, $visit_count, $name, $menu_joke) = preg_split ("/\t/", $counts);
	print "<B>$menu_joke\n</B>\n<HR>\n";
	printFooter();
}//END function printDocument

?>

