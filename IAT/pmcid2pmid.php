<?php
#Include menu($menufile), $basedir, $logfile, $from-referer etc
include_once "../../php/utilities.php";

##############################
# HEADERS
##############################
$DEBUG=0;

#Get directory name
$name = dirname($PHP_SELF);

$letter = substr (strrchr ($name, "/"), 1, 5); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);

#include_once "viewer.php"; #include $text_path, goldfile, mappingfile, etc.
include_once "configuration.php"; #include $text_path, goldfile, mappingfile, etc.

################################
### INCLUDE ############## BIG
################################
//Fill $pmcid2pmid-hash: PMCID->PMID
#$pmcid2pmid = Array();
#$pmcids = Array();
#getMapping($mappingfile, $pmcid2pmid, $prefix, $output);


################################
#Get Variables
################################
if ( array_key_exists('pmcid', $_POST) ){
	$pmcid = $_POST['pmcid'];
}else if ( array_key_exists('pmcid', $_GET) ){
	$pmcid = $_GET['pmcid'];
}else{
	#print "pmcid NOT provided!<BR>\n";
	return "pmcid NOT Provided!\n Skipping rest of the file!!\n\n";
}#If not given a filename, could be used to include...?
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<TITLE>Get PubMedID for PubMedCentralID<?=$pmcid?></TITLE>
</HEAD>

<BODY>
<?php
##############################
# BODY
##############################

if ($pmcid){
	$pmcid2pmid = Array();
	getMapping($mappingfile, $pmcid2pmid, "", "pmcid");
	print $pmcid2pmid{$pmcid};
}
#else{
#	print $pmcid2pmid{$pmid};
#}


##############################
# Functions
##############################

//Fills $pmcid2pmid-hash: PMCID->PMID, only keep pmid/pmcid matching prefix
function getMapping($mappingfile, &$pmcid2pmid, $find_prefix, $output){
	if ( !isset($find_prefix) ){ $find_prefix=""; }
	$handle = @fopen("$mappingfile", "r");
	if ($handle){
		while (!feof($handle)) {
			$line = fgets($handle, 1024);
			if ( trim($line) ){
				#print "$line<BR>\n";
				list($pmid,$pmcid,$rest) = explode(",", $line);
				$pmcid = str_replace("PMC", "", $pmcid);
				if ( !$find_prefix ){
					$pmcid2pmid{$pmcid}=$pmid;
				}else{
					if ($output=="pmid"){
						$pos = strpos($pmid, $find_prefix); //Match prefix at beginning
					}else{
						$pos = strpos($pmcid, $find_prefix); //Match prefix at beginning
					}
					if ($pos !== false && $pos==0){
						$pmcid2pmid{$pmcid}=$pmid;
					}
				}
			}#If not empty line
		}#foreach pmid-gene pair
		fclose($handle);
	}#if handle ok
	return "97~DONE!\n";
}#function getMapping

//Fills $pmid2pmcid-hash: PMID->PMCID
function getPmidMapping($mappingfile, &$pmid2pmcid){
	$handle = @fopen("$mappingfile", "r");
	if ($handle){
		while (!feof($handle)) {
			$line = fgets($handle, 256);
			if ( trim($line) ){
				list($pmid,$pmcid,$rest) = explode(",", $line);
				$pmcid = str_replace("PMC", "", $pmcid);
				$pmid2pmcid{$pmid}=$pmcid;
			}#If not empty line
		}#foreach pmid-gene pair
		fclose($handle);
	}#if handle ok
	return "106~DONE!\n";
}#function getPmidMapping

//Returns PMC-ID for given PMID
function getPMCID($mappingfile, $find_pmid){
	#print "find ---$find_pmid--- in $mappingfile<BR>\n";
	$handle = @fopen("$mappingfile", "r");
	if ($handle){
		while (!feof($handle)) {
			$line = fgets($handle, 256);
			if ( trim($line) ){
				#print "$line<BR>\n";
				list($pmid,$pmcid,$rest) = explode(",", $line);
				$pmcid = str_replace("PMC", "", $pmcid);
				#print "---$pmid---<BR>";
				if ($pmid == $find_pmid){
					return $pmcid;
				}#if matched pmid
			}#If not empty line
		}#foreach pmid-gene pair
		fclose($handle);
	}#if handle ok
	return false;
	return "PMID $find_pmid is NOT in BioCreative!<BR>\n";
}#function getPMCID

//Finds PMCID in "PMCID,PMID" mappingfile, and return PMID
function getPmid($mappingfile, $find_pmcid){
	$handle = @fopen("$mappingfile", "r");
	if ($handle){
		while (!feof($handle)) {
			$line = fgets($handle, 1024);
			if ( trim($line) ){
				#print "$line<BR>\n";
				list($pmid,$pmcid,$rest) = explode(",", $line);
				$pmcid = str_replace("PMC", "", $pmcid);
				#print "---$pmid---<BR>";
				if ($pmcid == $find_pmcid){
					return $pmid;
				}#if matched pmid
				#$pmcid2pmid{$pmcid}=$pmid;
				#$pmcids{$pmid}=$pmcid;
			}#If not empty line
		}#foreach pmid-gene pair
		fclose($handle);
	}#if handle ok
	#return false;
	return "NO PMC $find_pmcid in BioCreativeIII!!!\n";
}#function getPmid

if ($DEBUG>2){
	error_log(__FILE__ ."~". __LINE__ .":".strftime('%c'));
}
if ($DEBUG>2){
	include("http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/404.php");
}
?>

</BODY>
</HTML>

