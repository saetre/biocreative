<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
 <HEAD>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<TITLE>Index of <?=dirname($_SERVER['PHP_SELF'])?></TITLE>
<?php

$DEBUG=0;

ini_set("memory_limit","12M");

##############################
# HEADERS
##############################

/*************************************************************
 *    function that reads directory content and
 *    returns the result as links to every file in the folder
 *************************************************************/

#Import from configuration.php: $xml_path = "merged-species-ascii";
include_once "configuration.php"; #include $xml_path, goldfile, $predictfile...

#Include menu($menufile), $basedir, $logfile, $from-referer etc
include_once "../../php/utilities.php";

#include gene2pmcid.php:
# print-functions, $prefix, getFileIds(), getFileIdVarList()
include_once "gene2pmcid.php";
if ($DEBUG) print "32~prefix is -$prefix-\n";

include_once "docs/GNSuiteSystemDescription.php"; #printContents, printDocumentation

#Get directory name
$name = dirname($PHP_SELF);

$letter = substr (strrchr ($name, "/"), 1, 3); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);

#CONSTANTS
###########
print getCssScriptsAndConstants($GLOB); #From configuration.php
?>
<script type="text/javascript" src="http://www.google.com/jsapi?key=ABQIAAAAzn9dwlEghoD3GnQqtDPsCxRWGAOh1iOGoeuRrTOjo8H2ZVqzVxQpkLU3_gkzTbOyeQSfML9xY96N7A">
</script>

<?php
##########################################
### Javascript CONSTANTS and VARIABLES ###
##########################################
print "<script type='text/javascript'>\n";
print "<!-- Hide Javasctipt from older browsers\n";

$startTime = microtime(true);
$pmids = getFileIds($mappingfile, ",", 0);
print "var pmids=".json_encode($pmids)."\n";
$time = microtime(true) - $startTime;
print "<!-- Pmid time: {$time} seconds -->\n";

$startTime = microtime(true);
$pmcIds = getFileIds($mappingfile, ",", 1); #from gene2pmcid.php
print "var files=".json_encode($pmcIds)."\n\n";
$time = microtime(true) - $startTime;
print"<!-- Pmcid time: {$time} seconds -->\n";

#$startTime = microtime(true);
#getGenes($geneIndexFile, "", $genes);
#print "var geneids=".json_encode(array_keys($genes))."\n\n";
#$time = microtime(true) - $startTime;
#print"<!-- Pmcid time: {$time} seconds -->\n";


#Show GeneId selector, if nothing is selected yet...
if ($prefix){						print "var selectedTab=0\n";}
else if ($pmid_prefix){	print "var selectedTab=2\n";}
else{										print "var selectedTab=1\n";}
print "//END:Hide Javascript from old browser -->\n";
print "</script>\n";
?>

<script type="text/javascript" src="index.js"></script>

</HEAD>


<BODY>
<?php
	include_once("../../googleanalytics.php");
	#GLOBALS
	$GENES = $GLOB["GENES"];
	$IDS   = $GLOB["IDS"];
	$PM    = $GLOB["PM"];
	$PMC   = $GLOB["PMC"];
	$ENTREZ= $GLOB["ENTREZ"];
	#$ = $GLOB[""];
?>

<H1>BioCreative <A HREF="http://www.biocreative.org/tasks/biocreative-iii/iat/">IAT</A></H1>

<div id="leftbox">
<?=printContents($manual);?>
</div>


<div id="rightbox">    
Status
</div>


<div id="middlebox">

<div id="DEBUG">Debug: results in <B><?=$results_path?></B><BR>
Files: 17.780 in <?=$xml_path?><BR>
<?="Files (".count($pmcIds).") in $goldfile<BR>"?>
</div> <!--END DEBUG-DIV-->
<HR>

<?php
if ($DEBUG){
	print "$name/index.php, folder: <B>$xml_path</B>\n";
}
?>

<!--href="http://www.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=$pmid&retmode=xml&rettype=abstract"><span>Abstract</span>&amp;geneid=<?=$geneid?> below
-->

<div id="tabs">
	<ul>
		<li><a title="<?=$PMC.$IDS?>" href="gene2pmcid.php?mode=pmcid&amp;prefix=<?=$prefix?>">
			<span>PMC Articles</span></a></li>

		<li><a title="<?=$ENTREZ.$GENES?>" href="gene2pmcid.php?mode=geneid&amp;geneid=<?=$geneid?>">
			<span>Entrez Genes</span></a></li>

		<li><a title="<?=$PM.$IDS?>" href="gene2pmcid.php?mode=pmid&amp;pmid_prefix=<?=$pmid_prefix?>">
			<span>PMID Articles</span></a></li>
	</ul>

	<DIV id="<?=$PMC.$IDS?>">
	</DIV>

	<DIV id="<?=$ENTREZ.$GENES?>">
		<?=printGenesForm($geneid, $geneIndexFile, $GNgeneIndexFile)?>
		<!--?=printPmcGeneTable($gene,$geneIndexF,$GNgeneIndexF);//gene2pmcid.php?-->
	</DIV>

	<DIV id="<?=$PM.$IDS?>">
		<?=printPmidSelectForm()?>
	</DIV>
</div><!--END tabs-->


<?php
#####################
### PHP FUNCTIONS ###
#####################
?>

<!--
<div id="searchForm">Loading Single Search...</div>
<B>Queries: </B>
<INPUT id=count value=0 align=right>
<span id=results>Results...</span>
-->

<!--?php
	$url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=p53+activates";
	// now, process the JSON string
	//phpinfo(); //Need at least 5.2 for json!
	//$json = json_decode($body);
-->
	
<?php
if ($SHOWLOG>0){
	#error_log(__FILE__."~".__LINE__."~". strftime('%c') );
	print file_get_contents("http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/404.php");
}
?>

</DIV>
<h3><?=print_menu($menufile);?></h3>

</BODY>
</HTML>

