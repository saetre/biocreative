<?php

$DEBUG=0;

include_once "configuration.php"; #include $text_path, goldfile, mappingfile, geneIndexFile, GNgeneIndexFile, pmidIndexFile, etc.
$GNSUITE = $GLOB["GNSUITE"];
$MEDIE = $GLOB["MEDIE"];

#Increase the memorysize when there are too many articles!
#ini_set("memory_limit","12M");

if ( array_key_exists('prefix', $_GET) ){
	$prefix = $_GET['prefix'];
}else{
	$prefix="";
}#if prefix (for pmcid)
if ($DEBUG){ print "24~prefix is $prefix\n"; }

if ( array_key_exists('geneid', $_POST) ){
	$geneid = $_POST['geneid'];
}else if ( array_key_exists('geneid', $_GET) ){
	$geneid = $_GET['geneid'];
}else{
	$geneid = "";
	if ($DEBUG){
		print "gene2pmcid~27: NO geneid Given! Showing all articles<BR>\n";
	}
}#if geneid (for filtering file-ids?)

if ( array_key_exists('pmid_prefix', $_GET) ){
	$pmid_prefix = $_GET['pmid_prefix'];
}else{
	$pmid_prefix="";
	if ($DEBUG && !$geneid && !$prefix){
		print "gene2pmcid~43: NO prefix or GeneId Given! Make prefixes<BR>\n";
	}
}#if prefix (for pmid)

################################
#Get Last Variables, or skip
################################
if ( array_key_exists('mode', $_GET) ){
	$mode = $_GET['mode'];
}else{
	if ($DEBUG>2){ print "gene2pmcid~9: mode NOT provided! INCLUDED!\n"; }
	print "<!--gene2pmcid~10: mode NOT provided!-->\n";
	return "mode NOT Provided!\n Skipping rest of the file (for include)";
}#If not given a filename, could be used to include...?

if ($mode == "json"){
	$term=$_GET['term'];
	$genes = Array();
	#print '[ {"label":22, "value":2}, {"label":44, "value":4} ]';
	getGenes($geneIndexFile, $term, $genes);
	$geneids = array();
	foreach ($genes as $geneid => $count){
		array_push($geneids, array("label"=>"$geneid => $count text(s)", "value"=>$geneid) );
	}
	print json_encode($geneids);
	return;
}//If return json object

#Include menu($menufile), $basedir, $logfile, $from-referer etc
include_once "../../php/utilities.php";

##############################
# HEADERS
##############################

#Get directory name
$name = dirname($PHP_SELF);

$letter = substr (strrchr ($name, "/"), 1, 5); #Skip /, take 3 letters
log_to_ip_file($logdir, $ip, $from, $letter);

include_once "pmcid2pmid.php";#getMapping($mappingfile,$pmids, $prefix,$output) etc

##############################
# Includable Functions
##############################

//Read all PMC-IDs and corresponding GeneIds from $goldfile
//Return: Hash of (PMCID => "gene1ids gene2ids ... geneNids")
//Changed: Hash of (PMCID => "1 1 ... 1") // Too memory intensive above
//GOLDFILE: PMCID<tab>GeneID // $goldfile separator is "\t"
//GOLDFILE: PMCID,PMID,OPT //
//Return? 
// $pm(c)id => $geneCount?
function getFileIds($mappingfile, $separator, $index){
	global $DEBUG;
	$myDEBUG = $DEBUG+0;
	$set = array();
	if ($myDEBUG){
		print "// ".__FILE__.__LINE__."~mappingfile is -$mappingfile-, ";
		print ",  sep is -$separator-, i is -$index-<BR>\n";
	}
	$handle = @fopen("$mappingfile", "r");
	if ($handle){
		while (!feof($handle)) {
			// loop through results with fgetcsv()
			$line = fgets($handle, 192);
			if (trim($line)){
				if ($myDEBUG>2) print "$line<BR>\n";
				#list($pmid,$id,$rest) = explode(",", $line); //mappingfile
				$info = explode($separator, $line);
				if ( count($info) >$index ){
					$id = $info[$index];
					$id = str_replace("PMC", "", $id);
					array_push($set, $id);
					
					if (!$id){ print "FOUND IT!"; }

				}else{
					print "MISSING index=$index in line -$line-<BR>\n";
				}
			}#if line not empty
		}#while, foreach pmid-gene pair
		fclose($handle);
	}else{#if handle ok
		print "//gene2pmcid.php:121~getFileIds:NOT read file -$mappingfile-\n";
	}
	if ($myDEBUG>1){ print_r($set); print "<BR>\n"; }
	return $set;
}#function getFileIds

//Read all PMC-IDs and corresponding GeneIds from $goldfile (MEDIE)
//Changed: Hash of (PMID => #genes) // Memory intensive, many pmids
function getPmids($pmidIndexFile){
	global $DEBUG;
	$myDEBUG = $DEBUG+0;
	$pmids = array();
	if ($myDEBUG) print "pmidIndexFile is $pmidIndexFile<BR>\n";
	$handle = @fopen("$pmidIndexFile", "r");
	if ($handle){
		while (!feof($handle)) {
			$line = fgets($handle, 256);
			if (trim($line) && $line[0] != '#'){
				if ($myDEBUG) print "$line<BR>\n";
				#list($pmid,$pmcid,$rest) = explode(",", $line);
				$info = explode("\t", $line);
				$pmid = $info[0];
				$genecount = 0;
				if (count($info)>1){
					$genecount = $info[1];
				}else{
					print "Missing Genes in PMID $pmid<BR>\n";
				}
				$pmids[$pmid] = $genecount;
			}#if line not empty
		}#while, foreach pmid-gene pair
		fclose($handle);
	}#if handle ok
	return $pmids;
}#function getpmids


###########################
### MAIN                ###
###########################

#if ($DEBUG){ print "mode is -$mode-<br>\n"; }
if ($mode == "geneid"){
	printGenesForm($geneid, $geneIndexFile, $GNgeneIndexFile);
	if ($geneid){
		if ($DEBUG) print "My geneid is -$geneid-<BR>\n";
		print printPmcGeneTable($geneid, $geneIndexFile, $GNgeneIndexFile);
	}
}else if ($mode == "pmid"){
	if ($pmid_prefix){
		print "<HR>COUNTS ARE (#Mentions in ABSTRACT + #Mentions in FULLTEXT\n";
		printFilesPmid($pmids, $pmidIndexFile, $pmid_prefix, $MEDIE); #From MEDIE
		print "<HR>IN FULL-TEXT\n";
	}
	$pmids = getPmids($pmidIndexFile);
	printFilesPmid($pmids, $predictfile, $pmid_prefix, $GNSUITE); #From GNSuite
}else{ #mode=pmcid
	if ($prefix){
		print "<HR>COUNTS ARE (#Mentions in ABSTRACT + #Mentions in FULLTEXT\n";
		printFilesPmcid($goldfile, $mappingfile, $prefix, $MEDIE); #From MEDIE
		print "<HR>IN FULL-TEXT\n";
	}
	printFilesPmcid($predictfile, $mappingfile, $prefix, $GNSUITE); #From GNSuite
}#If geneid is given, return filtered list



#################
### Functions ###
#################

//Input [a,b,c]
function getFileIdVarList(&$pmcIds){
	print "//My count is ".count($pmcIds)." pmcids<BR>\n";
	#print '[{"id":"123", "value":"1234", "label":"12345"}]'; # OR
	#print '["123456", "123457", "123458"]';
	$text = "['";
	sort($pmcIds);
	foreach ($pmcIds as $pmcid){
		$text .= "$pmcid', '";
	}
	$text .= "']\n";
	return $text;
}#function getFileIdVarList

function getPmidVarList(&$pmids){
	#print "My count is ".count($pmids)." pmids<BR>\n";
	#print '[{"id":"123456", "value":"123456", "label":"123456"}]'; # OR
	#print '["123456", "123457", "123458"]';
	$text = "['";
	foreach ($pmids as $pmid => $geneCount){
		$text .= "$pmid', '";
	}
	$text .= "']\n";
	return $text;
}#function getPmidVarList

#file
# EntrezGene-ID	PMCID(freq)	PMCID(freq)	...
# 1							2364664(4)	2528014(6)
#--> Returns () or the found gene hash ( PMCID -> #abstract-mentions )
function getGene($file, $search_gene){
	global $DEBUG;
	$myDEBUG=$DEBUG+0;
	$found_gene = Array();
	if ($myDEBUG){
		print "FILE IS $file<BR>\n";
		print "My search_gene is $search_gene<BR>\n";
	}
	$handle = fopen($file, "r");  // open file
	if ($handle){
		// loop through results with fgetcsv()
		while( ($line=fgets($handle)) !== FALSE && !$found_gene){
			if ($line[0] != '#'){
				$values = explode("\t", rtrim($line));
				$gene = array_shift($values);
				$pmids = $values;
				$freq = count($values);
				#	if ( !in_array($info[1],$REMOVE) )
				if ($search_gene == $gene){
					#print "Found $gene...<BR>\n";
					foreach ($pmids as $pmid){
						if ($DEBUG>1){ print "PMID is $pmid<BR>\n"; }
						#Push gene's abstracts to $found_gene array, with freq counts
						if ( preg_match("/(\d+)\((\d+),(\d+)\)/", $pmid, $matches) ){
							$pmid = $matches[1];
							$freq = $matches[2];
							$titleFreq = $matches[3];
							$found_gene[$pmid] = Array($freq, $titleFreq);
						}else if ( preg_match("/(\d+)\((\d+)\)/", $pmid, $matches) ){
							$pmid = $matches[1];
							$freq = $matches[2];
							$found_gene[$pmid] = Array($freq,0);
						}else{
							print "gene2pmcid.php245~getGene mismatch $pmid<BR>\n";
						}
					}#foreach pmid
				}#if matching filter
			}#if not comment
		}#while foreach result
		fclose($handle);
	}else{
		error_log("gene2pmcid~259:Could not open file $file");
	}
	return $found_gene;
}#function getGene

#file
# EntrezGene-ID	PMCID(freq)	PMCID(freq)	...
# 1							2364664(4)	2528014(6)
#--> Returns new entries added to genes(ID -> #abstracts)
function getGenes($file, $filter, &$genes){
	global $DEBUG;
	#print "FILE IS $file<BR>\n";
	#print "My term_filter is $filter<BR>\n";
	$handle = fopen($file, "r");  // open file
	// loop through results with fgetcsv()
	while( ($line=fgets($handle)) !== FALSE ){
		if ($line[0] != '#'){
			$values = explode("\t", rtrim($line));
			$gene = array_shift($values);
			$pmids = $values;
			$freq = count($values);
			if ($DEBUG>1){
				print "pmid is $pmid, gene is $gene, conf is $f<BR>\n";
			}
			#if ($gene !="EntrezGene:330286|MGI:2669829|TrEMBL:Q8C3K4")
			if ($filter == ""){
				$pos=0;  #simulate match at beginning of string, if NO FILTER
			}else{
				$pos = strpos($gene, $filter); #Check filter
			}
			if ($pos !== false and $pos==0){
				if ($DEBUG>1) print "Adding $gene...<BR>\n";
				#Push $gene to the $genes array, with confidence score
				$genes[$gene] = $freq;
			}#if matching filter
		}#if not comment
	}#while foreach result
	fclose($handle);
	return count($genes);
}#function getGenes

#Find only matching pmid/pmcids
#Called by printFiles (get pmcid->#genes) and printFilesPmid (get pmid->#genes)
#if source==GNSUITE, Find only matching pmcids with pmid matching the prefix...
function getFilteredPrefix2Genecount($goldfile, $find_prefix, $output, $source){
	global $DEBUG, $GLOB, $mappingfile;
	$REMOVE=$GLOB["REMOVE"]; $GNSUITE=$GLOB["GNSUITE"]; $MEDIE=$GLOB["MEDIE"];
	$pmcids = Array();
	$DEBUG=0;
	if ($DEBUG){ print "output is --$output--, Source is --$source--<BR>\n"; }

	#if ($output=="pmid" and $source != $MEDIE){
	if ($find_prefix and $source == $GNSUITE){
		$pmcid2pmid = Array();
		getMapping($mappingfile, $pmcid2pmid, $find_prefix, $output);
		if ($DEBUG){
			print "302~ count(pmcid2pmid) is ".count($pmcid2pmid)."<BR>\n";
		}
		foreach ($pmcid2pmid as $pmcid => $pmid ){
			if ($output=="pmid"){
				$pmcids{$pmid}=0;
			}else{
				$pmcids{$pmcid}=0;
			}#Reset (potentially without pmcid genes) IDs
			#print "Initialized $pmid: 0<BR>\n";
		}#foreach (potentially without pmcid genes) pmid
	}#if output pmids instead of pmcids
	$limit=10;
	$handle = @fopen("$goldfile", "r");
	if ($DEBUG){ print "336~goldfile is --$goldfile--<BR>\n"; }
	if ($handle){
		while ( !feof($handle) ){ // loop through results with fgetcsv()
			$line = fgets($handle, 8192);
			if (trim($line)){
				#print "$line<BR>\n";
				#list($pmid,$pmcid,$rest) = explode(",", $line);
				$info = explode("\t", $line);
				$pmcid = $info[0];
				$pmcid = str_replace("PMC", "", $pmcid);

				if ($output=="pmid" and $source != $MEDIE){
					if ( array_key_exists($pmcid, $pmcid2pmid) ){
						$pmid = $pmcid2pmid{$pmcid};
					}else{
						#print "Missing pmcid $pmcid in getMapping\n";
						$pmid=0;
					}
					if ($pmid !=0 and $limit-- >0){
						#print "321~pmcid is $pmcid and pmid is $pmid<BR>\n";
					}
					$pmcid=$pmid;
				}
				$pos = strpos($pmcid, $find_prefix); //Match prefix at beginning of pmcid
				if ( $pos !== false && $pos==0){
					if ($output=="pmid" and $source==$MEDIE){
						$gene = $info[1]; #TODO: Update this small index sometimes
					}else{
						if (count($info)>1 and !in_array($info[1], $REMOVE) ){
							$gene=1;
						}else{
							$gene=0;
						}
					}
					if ( array_key_exists($pmcid, $pmcids) ){
						$pmcids[$pmcid] += $gene;
						#print "346~Now set $pmcid is $pmcids[$pmcid]<BR>\n";
					}else{
						$pmcids[$pmcid] = $gene;
					}
				}else{
					#print "Skipped $output ID --$pmcid--<BR>\n";
				}#if current pmcid matches the given prefix
			}#if line not empty
		}#while, foreach pmid-gene pair
		fclose($handle);
	}else{
		print "cannot open file $goldfile\n";
	}#if handle ok
	return $pmcids;
}#function getFilteredPrefix2Genecount

#function printFilesPmcid($goldfileCounts, $mappingPmid, &$pmcIds, $find_prefix){
function printFilesPmcid($goldfile, $mappingfile, $find_prefix, $format){
	$DEBUG=0;
	if ($DEBUG){
		print "394~Goldfile is $goldfile\n";
		print "count(pmcids) is ---".count($pmcIds)."---<BR>\n";
		print "PMCIDS are ---"; print_r($pmcIds); print "---";
	}
	print "<FORM id=\"fileselect\" action=\"viewer.php\">Select PMC-ID:
		<input id=\"autocomplete\" name=\"file\"/>
		<input type=\"submit\" value=\"Annotate!\"/>
		</FORM>
	";
	if ($find_prefix){
		#Find only matching pmcids
		$pmcIds
		 = getFilteredPrefix2Genecount($goldfile, $find_prefix, "pmcid", $format);
		if ($DEBUG){ print "371~pmcids is ".count($pmcIds); }
		if ($DEBUG>1){ print_r($pmcIds); }
		if ($DEBUG) { print "<BR>\n"; }

		ksort($pmcIds);
		foreach ($pmcIds as $pmcid => $geneCount ){
			print "<SPAN class='count'>";
			print "	<A HREF='viewer.php?file=$pmcid'>$pmcid</A>\n";
			print "	<SPAN class='found'>$geneCount genes</SPAN>\n";
			print "</SPAN>\n";
		}//for each possible pmcid

	}else{ #No prefix given
		#print around 100 prefixes for all the files
		$prefixCounter = Array();
		$pmcIds = getFileIds($mappingfile, ",", 1);
		#foreach ($pmcIds as $pmcid => $geneCount ){
		foreach ( $pmcIds as $pmcid ){
			$prefix = substr($pmcid, 0, 2);
			if ( array_key_exists($prefix,$prefixCounter) ){
				$prefixCounter{$prefix}++;
			}else{
				if (!$prefix){ print "prefix=$prefix????\t"; }
				$prefixCounter{$prefix}=1;
			}#Count all prefixes and their number of files
		}#foreach possible pmcid
		ksort ($prefixCounter);
		foreach ($prefixCounter as $prefix => $fileCount){
			print "<SPAN class='count'>";
			print "	<A HREF='index.php?prefix=$prefix'>$prefix...</A>\n";
			print "	<SPAN class='found'>$fileCount files</SPAN>\n";
			print "</SPAN>\n";
		}//for each possible prefix
	}#if prefix - else make prefix
	print "<BR>\n";
}#function printFiles

//Input: pmids-hash-ref (pmid -> gene-count)
//Input: find_prefix: To be matched
function printFilesPmid(&$pmids, $goldfile, $find_prefix, $source){
	global $DEBUG, $mappingfile;
	$myDEBUG=$DEBUG+0;
	if ($DEBUG){ print __LINE__."~Goldfile is $goldfile\n"; }
	printPmidSelectForm();
	if ($find_prefix){
		#Find only matching pmcids, return PMID => $genes
		$pmids = getFilteredPrefix2Genecount($goldfile, $find_prefix, "pmid", $source);
		if ($DEBUG){
			print __LINE__."count(pmids) is ---".count($pmids)."---<BR>\n";
			print "PMIDS are ---"; print_r($pmids); print "---";
			print "~pmids is ".count($pmids);
			if ($DEBUG>1) print_r($pmids);
			print "<BR>\n";
		}#DEBUG
		ksort($pmids);
		foreach ($pmids as $pmid => $geneCount ){
			$pos = strpos($pmid, $find_prefix);
			if ( $pos !== false && $pos==0){
				print "<SPAN class='count'>";
				print "	<A HREF='viewer.php?pmid=$pmid'>$pmid</A>\n";
				print "	<SPAN class='found'>$geneCount gene</SPAN>\n";
				print "</SPAN>\n";
			}#if current pmid matches the given prefix
		}//for each possible pmid
	}else{
		#print around 100 prefixes for all the files
		$prefixCounter = Array();
		foreach ($pmids as $pmid => $count ){
			$prefix = substr($pmid, 0, 2);
			if ( array_key_exists($prefix,$prefixCounter) ){
				$prefixCounter{$prefix}++;
			}else{
				#print "pref=$prefix\t";
				$prefixCounter{$prefix}=1;
			}#Count all prefixes and their number of files
		}#foreach possible pmid
		ksort ($prefixCounter);
		foreach ($prefixCounter as $prefix => $fileCount){
			print "<SPAN class='count'>";
			print "	<A HREF='index.php?pmid_prefix=$prefix'>$prefix...</A>\n";
			print "	<SPAN class='found'>$fileCount files</SPAN>\n";
			print "</SPAN>\n";
		}//for each possible prefix
	}#if prefix - else make prefix
	print "<BR>\n";
}#function printFilesPmid

//<img src='../css/images/tablesortup.gif'>
//Input: $gene is hash ( PMID -> #Mentions )
function printPmcGeneTable($geneid, $geneIndexFile, $GNgeneIndexFile){
	global $DEBUG, $GLOB, $mappingfile;
	$myDEBUG = $DEBUG+0;
	
	$ABSTRACT = $GLOB["ABSTRACT"]; $FULLTEXT = $GLOB["FULLTEXT"];
	$GENE = $GLOB["GENE"]; $HEADER = $GLOB["HEADER"]; $ID = $GLOB["ID"];
	$PMC = $GLOB["PMC"]; $TABLE = $GLOB["TABLE"]; $TITLE = $GLOB["TITLE"];

	$startTime = microtime(true);
	if ($myDEBUG){
		print "MY GENEID IS -$geneid-, and GLOB is "; print_r($GLOB); print "<BR>\n\n";
	}
	// normal page code here
	$gene = getGene($geneIndexFile, $geneid);
	$GNgene = getGene($GNgeneIndexFile, $geneid);
	$time = microtime(true) - $startTime;
	echo "<!-- Page generation time: {$time} seconds -->";

	$text="";
	$text .= "EntrezGene $geneid<HR>";
	$text .= "<TABLE id='$PMC$GENE$TABLE' class='tablesorter'>\n";
	$text .= "  <THEAD>\n";
	$text .= "    <TR>\n";
	$text .= "<TH scope='col' class='nobackground'></TH>\n";
	$text .= "<TH scope='col' class='$HEADER'>PMC-ID</TH>\n";
	$text .= "<TH scope='col' class='$HEADER'>PMID</TH>\n";
	$text .= "<TH scope='col' class='$HEADER'>In GNSUITE-Title</TH>\n";
	$text .= "<TH scope='col' class='$HEADER'>In MEDIE-Abstract#</TH>\n";
	$text .= "<TH scope='col' class='$HEADER'>In GNSUITE-Text#</TH>\n";
	$text .= "    </TR>\n";
	$text .= "  </THEAD>\n";
	$text .= "  <TBODY>\n";

	$pmcid2pmid = Array();
	getMapping($mappingfile, $pmcid2pmid, "", "pmcid");
	if ($myDEBUG){ print "// ".__FILE__.__LINE__."~mappingfile -$mappingfile-, "; }
	#print_r($pmcid2pmid);

	foreach ($GNgene as $pmcid => $count){
		$all = $count[0];
		$title = $count[1];
		$text .= "<TR id='$ABSTRACT$pmcid'>\n";
		$text .= "  <TH id='$pmcid' scope='row' class='alt'></TH>\n";
		$text .= "	<TD><A HREF='viewer.php?file=$pmcid'>$pmcid</A></TD>\n";
		$text .= "  <TD><A HREF='viewer.php?file=$pmcid'>"
		 .$pmcid2pmid{$pmcid}."</A></TD>\n";
		$text .= "	<TD>$title</SPAN>\n";
		$text .= "	<TD>";
		if ( array_key_exists($pmcid, $gene) ){
			$text .= $gene{$pmcid}[0];
			unset($gene[$pmcid]);
		}
		$text .= "</TD>\n";
		$text .= "	<TD>$all</TD>\n";
		$text .= "</TR>\n";
	}
	foreach ($gene as $pmcid => $count){
		$text .= "<TR id='$ABSTRACT$pmcid'>\n";
		$text .= "  <TH id='$pmcid' scope='row' class='alt'></TH>\n";
		$text .= "	<TD><A HREF='viewer.php?file=$pmcid'>$pmcid</A></TD>\n";
		$text .= "  <TD><A HREF='viewer.php?file=$pmcid'>"
		 .$pmcid2pmid{$pmcid}."</A></TD>\n";
		$text .= "	<TD></TD>\n";
		$text .= "	<TD>$count[0]</TD>\n";
		$text .= "	<TD>";
		if ( array_key_exists($pmcid, $GNgene) ){
			$text .= $GNgene{$pmcid}[0];
			print "gene2pmcid.php540~printPmcGeneTable NEVER HAPPENS!\n";
		}
		$text .= "</TD>\n";
		$text .= "</TR>\n";
	}
	$text .= "  </TBODY>\n";
	$text .= "</TABLE>\n";

	return $text;
}//function printPmcGeneTable

function printPmidSelectForm(){
	global $DEBUG;
	print "<FORM id=\"pmidselect\" action=\"viewer.php\">Select PMID:
		<input id=\"autocomplete_PMID\" name=\"pmid\"/>
		<input type=\"submit\" value=\"Annotate!\"/>
		</FORM>\n";
	if ($DEBUG){ print "count(pmids) is ---".count($pmids)."---<BR>\n"; }
	if ($DEBUG){ print "PMIDS are ---"; print_r($pmids); print "---"; }
}#function printPmidSelectForm

function printGenesForm($geneid, $geneIndexFile, $GNgeneIndexFile){
	global $DEBUG;
	$myDEBUG = $DEBUG+0;
	print "<FORM id=\"geneselect\" action=\"index.php\">";
	#print "<FORM id=\"geneselect\" action=\"javascript:showGeneArticles()\">";
	print "Select EntrezGene-ID:
		<input id=\"autocomplete_geneid\" name=\"geneid\" size='25'/>
		<input type=\"submit\" value=\"Find Articles!\"/>";
	print "</FORM>\n";
}#function printGenesForm


?>

