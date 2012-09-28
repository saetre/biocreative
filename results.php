<?php
/*************************************************************
 *    function that reads directory content and
 *    returns the result as links to every file in the folder
 *************************************************************
 + http://www.w3schools.com/jsref/default.asp
 */

#Include menu($menufile), $basedir, $logfile, $from-referer etc
require "../php/utilities.php";
#re-define

$DEBUG=0;

#Get directory name
$name = $PHP_SELF;
$name = dirname($name);

$letter = substr (strrchr ($name, "/"), 1, 5); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);


##############################
# HEADERS
##############################
?>
<HTML>
	<HEAD>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<TITLE>Store GN results for <?=$name?></TITLE>

<link rel="stylesheet" href="3column.css">
</HEAD>

<BODY>
<H1>BioCreative Results Store</H1>

<div id="leftbox">
MENU
</div>

<div id="rightbox">    
Status
</div>

<div id="middlebox">

<?php
##############################
# BODY
##############################

#Import from viewer.php
#$xml_path = "/satre/biocreative/xml";
include_once "viewer.php";

print "$name/index.php, folder: <B>$xml_path</B>\n";

$result = array();
#$result = get_directory_folders("../../biocreative/data/BC3GNTraining/xmls", $exclude);
#print "<p>Folders:<UL>\n";
#foreach ($result as $line){
#  print "$line	";
#}#foreach $file
#print "</UL>\n";
$result =  get_directory_files("xmls", "");

print "<p>".count($result)."Files:<BR>\n";
print "Duplicates: 2265441 2481299 2515315 2536679 2632753, (+DevTest: 2631505 2770568)<BR>\n";

#print "<UL>\n";
#foreach ($result as $line){
#	$name = str_replace(".nxml", "", $line);
#  print "<A HREF=\"viewer.php?file=$name\">$name</A>	";
#}#foreach $file
#print "</UL>\n";

printFileSet(1);
printFileSet(2);

function printFileSet($nr){
	$file = file("TrainingSet$nr.txt");
	$filter = file("TrainingSet$nr.txt.train0");
	foreach ($filter as $i => $pmid){	$filter[$i] = rtrim($pmid); }
	#print_r ($filter);
	$set = array();
	foreach ($file as $line){
		list($pmid,$gene) = explode("\t", $line);
		#print "---$pmid---<BR>";
		if (in_array("$pmid", $filter)){
			#print "Skip DevTest PMID $pmid\n";
		}else{
			if ( array_key_exists($pmid, $set) ){
				$set[$pmid] .= " $gene";
			}else{
				$set[$pmid] = "$gene";
			}
		}#if not dev-test pmid
	}#foreach pmid-gene pair
	print "Set $nr (".count($set)." +".count($filter)."devTest files), ".count($file)." genes<BR>\n";
	foreach ($set as $pmid => $genes ){
		print "<SPAN class='count'><A HREF=\"viewer.php?file=$pmid&set=$nr\">$pmid</A>\n";
		print count(explode(" ", $genes))."</SPAN>\n";
	}
	print "<BR>\n";
}#function printFileSet

?>

<!--div id="searchForm">Loading Single Search...</div-->

<B>Queries: </B>
<UL ID = oList>
</UL>

<INPUT id=count value=0 align=right>
<span id=results>Results...</span>

<!--INPUT TYPE="button"  VALUE="Append Child" onclick="fnAppend()"-->
<!--BR>
39->&#39<-
<BR-->

<!--?php
	$url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=p53+activates";
	
	// sendRequest
	// note how referer is set manually
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, 'http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/');
	$body = curl_exec($ch);
	curl_close($ch);
	// now, process the JSON string
	//phpinfo(); //Need at least 5.2 for json!
	//$json = json_decode($body);
	// now have some fun with the results...
	//print "$body<BR>\n";
-->
	
<?php
	if ($DEBUG>2){
		error_log(__FILE__ ."~". __LINE__ .":".strftime('%c'));
	}
	if ($DEBUG){
		include("http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/404.php");
	}
	

function directory($result){
	$handle=opendir(".");
	$folder = array();
	while ($file = readdir($handle)) {
		if (!($file == "." || $file == "..")) { }
		else{
			$url = rawurlencode($file);
			$size = round(filesize($file) / 1000);
			$folder[] = "<LI><a href=\"$url\">$file</a> ($size kB)<br>\n";
			$result[] = $file;
		}
	}#while more files
	closedir($handle);
	natcasesort($folder);
	return $folder;
}#function directory()

?>

</DIV>
<h2>Footer</h2>

</BODY>
</HTML>

