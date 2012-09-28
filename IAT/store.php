<?php
#Include menu($menufile), $basedir, $logfile, $from-referer etc...
include_once "../../php/utilities.php";
include_once "configuration.php"; #$results_path = "./results", etc...


##############################
# HEADERS
##############################
$DEBUG=0;

#Get directory name
#$name = $PHP_SELF;
$name = dirname($name);

$letter = substr (strrchr ($name, "/"), 1, 5); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);


################################
#Common Functions
################################

#Make a species tag that can be used in CLASS-names
#input:	species:ncbi:12302?0.693|species:ncbi:10295?0.020
#output:species_ncbi_12302x0x693-species_ncbi_10295x0x020
function getPlainSpeciesTag($rich){
	$plain = str_replace(":", "_", $rich);
	$plain = str_replace(".", "x", $plain);
	$plain = preg_replace("/[?x](\d)[x.](\d\d\d)\d+/", "x$1x$2", $plain);
	$plain = str_replace("|", "-", $plain);
	return $plain;
}#function getPlainSpeciesTag

function get_speciesfile($filename){
	#global $pmids;
	#print "pmids size is ---".count($pmids)."---<BR>\n";
	$allSpecies = array();
	print "first READ old speciesfile --$filename--<BR>\n";
	if (($handle = fopen($filename, "r")) !== FALSE) {
		while ( ( $line=fgetcsv($handle, 1000, "\t") ) !== FALSE ) {
			if (count($line)<2){
				print "MY EMPTY LINE ---$line[0]---<BR>\n";
			}else{
				list($pmid,$specie) = $line;
				if ( array_key_exists($pmid, $allSpecies)){
					if ( array_key_exists($specie, $allSpecies[$pmid]) ){
						$allSpecies[$pmid][$specie]++;
					}else{
						$allSpecies[$pmid][$specie] = 1;
					}
				}else{
					$allSpecies[$pmid] = array($specie => 1);
				}#if-else: first entry
			}#if-else: NOT EMPTY
		}#while foreach pair of pmid-species in old results
		fclose($handle);
	}#else: No SPECIES results-file
	return $allSpecies;
}#function get_speciesfile

function store_speciesfile($filename, $main_pmid, $species){
	global $DEBUG;
	$allSpecies = get_speciesfile($filename);
	$answer = "";
	if ( array_key_exists($main_pmid, $allSpecies) ){
		$answer = implode("<BR>",array_keys($allSpecies{$main_pmid}));
	}
	$answer = "OLD ANSWER:<BR>$answer<BR>\n";
	if ($DEBUG>1){
		print "filename is $filename<BR>\n";
		print "count allSpecies is ".count ($allSpecies);
		print "<BR>count species is ".count ($species);
	}
	#add new species
	#print "Species is $species<BR>\n";
	foreach (explode(" ", $species) as $specie){
		$specie = str_replace("_", ":", $specie);
		if ( array_key_exists($main_pmid, $allSpecies) ){
			if ( array_key_exists($specie, $allSpecies[$main_pmid]) ){
				$allSpecies[$main_pmid][$specie]++;
			}else{
				$allSpecies[$main_pmid][$specie]=1;
			}#if old specie, else add new
		}else{
			$allSpecies[$main_pmid] = array($specie => 1);			
		}#if old count, else add new pmid
	}#for each species-id
	#Update file contents with new answer
	#overwrite old file... HOPE nobody changed it in the meantime!!!
	$fh = fopen($filename, 'w') or die("<BR>\n\ncan't re-open outputfile -$filename-");
	foreach ($allSpecies as $pmid => $species){
		foreach ($species as $specie => $count){
			if ($specie){
				fwrite($fh, "$pmid\t$specie\n");
			}else{
				print "Missing specie in $pmid<BR>\n";
			}#If valid output
		}#foreach specie
	}#for each pmid
	fclose($fh);
	$answer .= "<BR>NEW ANSWER ".implode("<BR>",array_keys($allSpecies{$main_pmid}));
	return $answer;
}#function store_speciesfile


################################
#Get Variables
################################
if ( array_key_exists('pass', $_GET) ){
	$pass = $_GET['pass'];
}else{
	$pass="";
	#print "pass NOT Provided!\n Please provide a password to use this feature!\n\n";
#	return "pass NOT Provided!\n Please provide a password to use this feature!\n\n";
}#If not given a filename, could be used to include...?

if ( array_key_exists('pmcid', $_POST) ){
	$pmcid = $_POST['pmcid'];
}else if ( array_key_exists('pmcid', $_GET) ){
	$pmcid = $_GET['pmcid'];
}else{
	return "pmcid NOT Provided!\n Skipping rest of the file!!\n\n";
}#If not given a filename, could be used to include...?
	
if ( array_key_exists('file', $_POST) ){
	$filename = $_POST['file'];
}else if ( array_key_exists('file', $_GET) ){
	$filename = $_GET['file'];
}#If not given a filename, could be used to include...?
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<TITLE>View and Store GN results for <?=$name?></TITLE>
</HEAD>

<?php
$genes=""; $species="";
if ( array_key_exists('genes', $_POST) ){
	$genes = $_POST['genes'];
}else if ( array_key_exists('genes', $_GET) ){
	$genes = $_GET['genes'];
}

if ( array_key_exists('species', $_POST) ){
	$species = str_replace ("_", ":", $_POST['species']);
}else if ( array_key_exists('species', $_GET) ){
	$species = str_replace ("_", ":", $_GET['species']);
}

if (!$genes && !$species){
	print "NEITHER genes NOR species were Provided!\n";
	#exit(1);
	return "NEITHER genes NOR species were Provided!\n";
}
	
if ( array_key_exists('file', $_POST) ){
	$file = $_POST['file'];
}else if ( array_key_exists('file', $_GET) ){
	$file = $_GET['file'];
}else{
	print "file NOT Provided!\n";
	exit(1);
}
	
?>
<BODY>
<H1>Storing BioCreative Results</H1>

<?php
##############################
# BODY
##############################

#Import from viewer.php
#include_once "viewer.php";
#$xml_path = "/satre/biocreative/xml";

print "$name/index.php, storage folder: <B>$results_path</B><BR>\n";

if ($genes){
	//print "EXECUTE!<BR>\n";
	#$oldCounts = getOldCounts("$results_path/$file", $pmid);
	$oldCounts = get_speciesfile("$results_path/$file", $pmid);
	print "<BR>OLD ANSWER ".implode( " ",array_keys($oldCounts{$pmid}) );
	print ", count=".count($oldCounts{$pmid})."<BR>\n";

	#Replace file contents with new answer
	#if ( array_key_exists($pmid, $oldCounts) ){
	$oldCounts{$pmid} = array();
	#$oldCounts{$pmid} = explode(" ",$genes);
	foreach (explode(" ", $genes) as $gene){
		#print "gene is $gene<BR>\n";
		#print_r($oldCounts);
		$oldCounts{$pmid}{$gene}=1;
	}
	writeFile("$results_path/$file", $oldCounts);
	print "<BR>NEW ANSWER ".implode( " ",array_keys($oldCounts{$pmid}) );
}#If genes to be stored, called from the viewer.js javascript

#Species
if ($species){
	$answer = store_speciesfile($filename, $pmid, $species);
	print "$answer<BR>\n";
}#If genes, else species


print "<FORM action=$PHP_SELF>\n";
?>
	Filename:<INPUT size=30 name=file value=<?=$file?>>
	PMID: <INPUT name=pmid  value=<?=$pmid?>  align=right>
	Species:<INPUT size=30 name=species value="<?=$species?>">
	Genes:<INPUT size=40 name=genes value="<?=$genes?>">
	<INPUT type="submit">
</FORM>

<?php
function getOldCounts($file, $targetPmid){
	$file = file($file);
	$pmidGenes = array();
	foreach ($file as $line){
		//list($pmid,$genes) = explode("\t", rtrim($line));
		$splits = explode("\t", rtrim($line));
		$pmid = $splits[0];
		if ( count($splits) >1 ){
			$genes = $splits[1];
		}else{
			$genes="";
		}
		$pmidGenes[$pmid] = $genes;
		
		#print "$line, genes: ".count( explode(" ",$genes) )."<BR>\n";
	}#foreach pmid-genelist
	return $pmidGenes;
}#function getOldCount


function writeFile($file, $pmidGenes){
	$fh = fopen($file, 'w') or die("<BR>\n\ncan't open file $file");
	#print_r($pmidGenes);
	foreach ($pmidGenes as $pmid => $value){
		#print "GOOO "; print_r($value);
		foreach ($value as $gene => $count){
			fwrite($fh, "$pmid\t$gene\n");
		}#foreach gene
	}#foreach pmid
	fclose($fh);
}#function writeFile


if ($DEBUG>2){
	error_log(__FILE__ ."~". __LINE__ .":".strftime('%c'));
}
if ($DEBUG){
	include("http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/404.php");
}
?>

</BODY>
</HTML>

