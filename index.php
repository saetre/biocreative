<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<?php
/*************************************************************
 *    function that reads directory content and
 *    returns the result as links to every file in the folder
 *************************************************************
 + http://www.w3schools.com/jsref/default.asp
 */

#Include menu($menufile), $basedir, $logfile, $from-referer etc
include_once "../php/utilities.php";

$DEBUG=0;

#Get directory name
$name = $PHP_SELF;
$name = dirname($name);

$letter = substr (strrchr ($name, "/"), 1, 3); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);


##############################
# HEADERS
##############################
?>
<HTML>
 <HEAD>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<TITLE>Index of <?=$name?></TITLE>

	<link rel="stylesheet" href="css/3column.css" type="text/css">
	<link rel="stylesheet" href="css/common.css" type="text/css">
</HEAD>


<BODY>
<?php include_once("../googleanalytics.php") ?>

<h1>BioCreative</h1>

<div id="leftbox">
MENU<BR>
<BR>
<A HREF="http://www.biocreative.org/">BioCreative.Org</A>
</div>


<div id="rightbox">    
Status
</div>


<div id="middlebox">

<?php
if ($DEBUG){
	print "$name/index.php\n";
	$result = array();
	$result =  get_directory_files(".", "");
	print "Files: \n";	#.count($result);
	sort ($result);
	print "<UL>\n";
	foreach ($result as $file){
		print "<LI>$file\n";
	}
	print "</UL>\n";
}
?>
<UL>
<!--
	<LI>	<A HREF="GN">Gene Normalization Task (GN) Early Training</A></LI>
	<LI>
	<LI>	<A HREF="GNTrain">Gene Normalization Task (GN) Train Species (with gold prot)</A></LI>
	<LI>	<A HREF="GNTest">Gene Normalization Task (GN) Test Species</A></LI>
	<LI>
	<LI>	<A HREF="MT">Experimental Methods Task (IMT)</A></LI>
	<LI>
-->
	<LI>	<A HREF="IAT">Inter-Active (Indexing/Retrieval demo) Task (IAT)</A></LI>
</UL>


</DIV>
<h3><?=print_menu($linkfile);?></h3>

</BODY>
</HTML>

