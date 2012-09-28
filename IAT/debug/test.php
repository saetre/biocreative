<?php
#TEST=SCRIPT

include_once("configuration.php");
$ip="1.2.3.4";
include_once("pmcid2pmid.php");
include_once "viewer.php"; #include $text_path, goldfile, mappingfile, etc.

$pmid2pmcid = Array();
getPmidMapping($mappingfile, $pmid2pmcid);
ksort($pmid2pmcid);
#$pmid = "15061869";

foreach ($pmid2pmcid as $pmid => $pmcid){
#if ($pmid>15000111 and $pmid<15111222 and $pmid<20111222){
		$abstract = getAbstract($pmid, Array(), Array() ); 
		if ( strpos($abstract, "&lt;")>0 or strpos($abstract, "&gt;")>0 
		 or strpos($abstract, "<")>0 or strpos($abstract, ">")>0 ){
			print "\n$pmid:\n$abstract\n\n";
		}else{
			print "$pmid...\t";
		}
	#}else{
	#	print "$pmid...\t";
	#}
}//each pmid

print "Last Abstract: \n $abstract\n\n";

?>

