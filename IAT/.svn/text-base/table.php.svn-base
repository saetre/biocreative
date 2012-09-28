<link type='text/css' rel='stylesheet' href='../css/table.css'>

<?php

#Script: table.php
#
#The list of genes/proteins should be ranked for their importance or centrality
# to the article;
#Such ranking could take into consideration the frequency of gene/protein mention,
# the sections of the article where the gene is mentioned,
# the mention of the gene in figures or experimental results,
# etc. 
#
#Provide the HTML layout for JQuery to perform sorting by column etc.

#########################################################################
#From viewer.php
#include_once "viewer.php"; #For separate testing
#... usually this table.php is INCLUDED BY viewer.php

if ( array_key_exists('pmcid', $_GET) ){
	$pmcid = $_GET['pmcid'];
}else{
	$pmcid=0;
}
$pmid = getPmid($mappingfile, $pmcid); #from pmcid2pmid.php



##############################################
### FUNCTIONS
##############################################

#Calculate the sum of GNSuite confidence and MEDIE confidence
function calculateConfidence( $oldConf, $newConf, &$maxConf){
	if ($maxConf){
		$newConf = $newConf/$maxConf;
	}#if (report missing) maxConf
	$newConf = $newConf + $oldConf;
	return round($newConf,3);
}#function calculateConfidence

//GET METHODS
function getAbstractCount($offsets){
	global $DEBUG, $GLOB;
	$ABSTRACT=$GLOB["ABSTRACT"]; 
	$myDEBUG=$DEBUG+0;
	
	$termCount="";
	if ( array_key_exists($ABSTRACT, $offsets) ){
		$termCount = count($offsets[$ABSTRACT]);
		if ($myDEBUG){ print __FILE__."~".__LINE__.": offsets[$ABSTRACT] is\n";
			print_r($offsets[$ABSTRACT]); print "<BR><BR>\n";
		}#myDEBUG
	}else{
		if ($myDEBUG>1){ print __FILE__."~".__LINE__.":".__FUNCTION__.": ";
			print "MISSING ABSTRACT in offsets: \n";
			print_r($offsets); print "<BR><BR>\n";
		}#myDEBUG
	}#if no ABSTRACT in offsets
	if ($myDEBUG){
		print __FILE__."~".__LINE__.": count is $termCount, offsets was\n";
		print_r($offsets); print "<BR><BR>\n";
	}#myDEBUG
	#$abstractCount = ($abstractCount ? $abstractCount : "");
	return $termCount;
}#function getAbstractCount

function getFulltextCount($fragments){
	global $DEBUG, $GLOB;
	$ABSTRACT=$GLOB["ABSTRACT"]; $FULLTEXT=$GLOB["FULLTEXT"];$TITLE=$GLOB["TITLE"];
	$myDEBUG=$DEBUG+0;

	$termCount=0;	$titleCount=0;
	if ($myDEBUG>1){ print __FILE__."~".__LINE__.": fragments is ";
		print_r($fragments);print "<BR><BR>\n\n";
	}
	foreach ($fragments as $fragment_i => $terms){
		//print "68~fragment_i is $fragment_i and terms is "; print_r($terms);
		if ($fragment_i != $ABSTRACT && $fragment_i != $TITLE){
			$termCount += count($terms);
			if ($fragment_i == "0"){ $titleCount++; }
		}else{#if fragment_i belongs to FULLTEXT
			if ($fragment_i == $TITLE){ $titleCount++; }
		}//If fragment is classified as TITLE in MEDIE
	}#foreach fragment_i
	$termCount = ($termCount ? $termCount : "");
	$titleCount = ($titleCount ? $titleCount : "");
	return Array($termCount, $titleCount);
}#function getFulltextCount

#	if ($myDEBUG>1){ print __FILE__."~".__LINE__.": ConfOffsets is ";
#		print_r($confOffsets); print"<BR>\n";
#	}

//Merge a new (medie?) gene with the other genes //from viewer.php:printGeneLinks
#... GeneID-hash(GeneId) => {COLOR=>$color, CONF=>$conf,
#...... FRAGMENTS => hash:{"segment" => array[Off-Set, ...]} ] }
//$allGenes[$gene]= {COLOR=>color, CONF=>conf,
//  FRAGMENTS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] }
#Adds a color for new genes, ($newConf, $newOffsets), to &$allGenes-reference
function mergeGene( $geneId, $confOffsets, $maxConf, &$allGenes ){
	global $DEBUG, $GLOB;
	$FRAGMENTS=$GLOB["FRAGMENTS"]; $CONF=$GLOB["CONF"]; $COLOR=$GLOB["COLOR"];
	$COLORS=$GLOB["COLORS"]; 
	$myDEBUG = $DEBUG+0;
	$newConf = calculateConfidence( 0, $confOffsets[$CONF], $maxConf );
	$newOffsets = $confOffsets[$FRAGMENTS];
	if ($myDEBUG>0){
		print __FILE__."~".__LINE__;
		print " newGene is $geneId, newConf is $newConf<BR>";
		if ($myDEBUG>1){
			print " newOffsets is "; print_r($newOffsets);print "<BR>\n";
			#print "confOFfsets: "; print_r($confOffsets); print"<BR><BR>\n\n";
		}
	}
	if ( array_key_exists($geneId, $allGenes) ){ //If existing gene
		$newConf = calculateConfidence(
			$allGenes[$geneId][$CONF], $confOffsets[$CONF], $maxConf );
		$color = $allGenes[$geneId][$COLOR];
		$allGenes[$geneId][$CONF] = $newConf;
		foreach ($newOffsets as $fragment => $offsets){
			#Merge offsets!
			if ( array_key_exists($fragment, $allGenes[$geneId][$FRAGMENTS]) ){
				array_push ( $allGenes[$geneId][$FRAGMENTS][$fragment], $offsets );
			}else{
				$allGenes[$geneId][$FRAGMENTS][$fragment] = $offsets;
			} //If existing gene
		}//for each new fragment with offsets.
	}else{//New GENE
		$color = count($allGenes) % count($COLORS);
		$allGenes[$geneId] = array( "$COLOR" => $color, "$CONF" => $newConf,
		"$FRAGMENTS" => $newOffsets);
	}
	if ($myDEBUG>1){ print __FILE__."~".__LINE__."~".__FUNCTION__." ";
		print "size is ".count($allGenes).", color is $color<BR>\n";
		#print "ALLGENES: "; print_r($allGenes); print"<BR><BR>\n\n";
	}
	return count($allGenes);
}#function mergeGenes
#extra: array("$ABSTRACT", "$FULLTEXT");


#From viewer.php
#getGenefile($File-ID, $goldfile, $index, $filter, $fileformat, $textFragments )
#--> Return: new index, max Confidence, newly added genes (ID -> Conf off-to ...)
#$genes: ID -> Conf Offset-To ... Offset-To
#list($maxConfMedie, $genes)
# = getGenefile($pmcid, $goldfile, array(), $MEDIE=$GLOB["MEDIE"];$textFragments);

#list ($maxConfGnsuite,$predicted)
# = getGenefile($pmcid, $predictfile, array(), $GNSUITE=$GLOB["GNSUITE"];$textFragments);
#if ($DEBUG) print "Gene count is ".count($genes).", predicted count is ".count($predicted);

############################################################################
function printGeneTable($tableId, $allgenes, $colors, $maxConfMedie, $maxConfGnsuite){
	global $DEBUG, $GLOB, $pmcid;
	$DIV = $GLOB["DIV"];
	$text = "<DIV id=$tableId$DIV>\n";
	$text .= printGeneTableHead($tableId, "Genes in PMC$pmcid");

	$text .= "  <TBODY>\n";
	$text .= printGeneRows(
		$tableId, $allgenes, $colors, $maxConfMedie, $maxConfGnsuite );
	$text .= "  </TBODY>\n";
	$text .= "</TABLE>\n";
	
	$text .= "<FORM action=''>\n";
	$text .= "<INPUT type='submit' value='export results' title='export' ";
	$text .= " onclick='javascript:GENETABLE.export2CSV(); return false'>\n";
	$text .= "</FORM>";
	$text .= "</DIV> <!--END $tableId$DIV-->\n";
	
	return $text;
}//function printGeneTable()

function printRemovedTable( $tableId ){
	global $DEBUG, $GLOB; $DIV = $GLOB["DIV"]; $REMOVED = $GLOB["REMOVED"];
	$tableId .= "$REMOVED";
	$text = "<DIV id=$tableId$DIV style='display:none'>\n";
	#$text .= "<A HREF='.' onClick='javascript:hideHijack(\"$tableId$DIV\");0'>\n";
	$text .= "<A HREF='javascript:hideHijack(\"$tableId$DIV\")'>\n";
	#$text .= "<A HREF='javascript:$(\"#$tableId$DIV\").hide();0'>";
	$text .= "Hide!</A>\n";
	$text .= printGeneTableHead($tableId, "Excluded Genes listed below...");
	$text .= "  <TBODY><TR><TD>\n";
	$text .= "  </TD></TR></TBODY>\n";
	$text .= "</TABLE>\n";
	$text .= "</DIV> <!--END $tableId$DIV-->\n";
	return $text;
}//function printRemovedTable

function printGeneTableHead($tableId, $heading){
	global $DEBUG, $GLOB, $pmcid;
	$ABSTRACT=$GLOB["ABSTRACT"]; $CONF=$GLOB["CONF"]; $FULLTEXT=$GLOB["FULLTEXT"];
	$GENE=$GLOB["GENE"]; $HEADER=$GLOB["HEADER"]; $ID=$GLOB["ID"];
	$NAME=$GLOB["NAME"]; $PMC=$GLOB["PMC"]; $RANK=$GLOB["RANK"];
	$TAX=$GLOB["TAX"]; $TITLE=$GLOB["TITLE"]; 

	$text = "";
	$text .= "";
	$text .= "<DIV class='left'>$heading</DIV>\n";
	$text .= "<DIV class='right'>Number of mentions in:\n";
	$text .= " T=Title, A=Abstract, F=Fulltext</DIV>\n";
	$text .= "<BR>\n";
	$text .= "<TABLE id='$tableId' class='tablesorter'>\n";
	$text .= "  <THEAD>\n";
	$text .= "    <TR>\n";
	$text .= "      <TH class='nobg'></TH>\n";
	$text .= "      <TH scope='col' class='$PMC$ID$HEADER'>$PMC$ID</TH>\n";
	$text .= "      <TH scope='col' class='$NAME$HEADER'>$NAME</TH>\n";
	$text .= "      <TH scope='col' class='$GENE$ID$HEADER'>$GENE$ID</TH>\n";
	$text .= "      <TH scope='col' class='$RANK$HEADER'>#</TH>\n";
	$text .= "      <TH scope='col' class='$CONF$HEADER'>$CONF</TH>\n";
	$text .= "      <TH scope='col' class='$TAX$HEADER'>$TAX</TH>\n";
	$text .= "      <TH scope='col' class='$TITLE$HEADER'>T</TH>\n";
	$text .= "      <TH scope='col' class='$ABSTRACT$HEADER'>A</TH>\n";
	$text .= "      <TH scope='col' class='$FULLTEXT$HEADER'>F</TH>\n";
	$text .= "      <TH scope='col'><IMG src='../css/images/delButton.gif' ";
	$text .= "        alt='del' title='delete genes' height='15'>\n";
	$text .= "        <IMG src='../css/images/addButton.gif' ";
	$text .= "        alt='add' title='Add Gene' height='15'></TH>\n";
	$text .= "    </TR>\n";
	$text .= "  </THEAD>\n";
	return $text;
}//function printGeneTableHead

//ALLGENES(hash): {GeneID: {COLOR=>color, CONF=>conf,
//  FRAGMENTS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] } }
# GeneID: entrezgene17979 or swissprotO09000 or MGI:1276535|SWISS-PROT:O09000
# Conf: Frequency count (or sum/avg Confidence
# Off-to ...:	145-149 449-453 555-559
function printGeneRows($tabId,$allgenes, $colors, $maxConfMedie, $maxConfGnsuite){
	global $pmcid, $pmid;
	global $DEBUG, $GLOB;
	$CONF=$GLOB["CONF"]; $ENTREZGENE = $GLOB["ENTREZ"].$GLOB["GENE"];
	$FRAGMENTS=$GLOB["FRAGMENTS"]; $REMOVED=$GLOB["REMOVED"];
	$SWISSPROT=$GLOB["SWISSPROT"]; $TABLE=$GLOB["TABLE"]; 
	$myDEBUG = $DEBUG+0;
	
	$text="";
	$total=0; $rank=1;
	if ($myDEBUG){
		print __FILE__."~".__LINE__.": count(allGenes)=".count($allgenes)."<BR>\n";
		if ($myDEBUG >0){
			print "allgenes is "; print_r($allgenes)."<BR>\n";
		}#more DEBUG
	}#DEBUG
	foreach ($allgenes as $gene => $colorConfOffsets){
		$offsets = $colorConfOffsets[$FRAGMENTS];
		$abstractCount = getAbstractCount($offsets);
		list ($fulltextCount, $titleCount) = getFulltextCount($offsets);
		$conf = $colorConfOffsets[$CONF] + $titleCount;
		if ($myDEBUG){
			print __FILE__."~".__LINE__.": Conf is $conf<BR>\n";
			print __FILE__."~".__LINE__.": Conf is $conf<BR>\n";
			print __FILE__."~".__LINE__.":Offsets is ";print_r($offsets);print "<BR>\n";
			print __FILE__."~".__LINE__.": MyID is $gene<BR>\n";
		}
		#$addButton  = "<IMG src='../css/images/addButton.gif' "
		# ." alt='add' title='Add Gene' height='15'>";
		#$addEvent = "GENETABLEREMOVED.removeGene('$gene', '$tabId$', 'true')";
		$delButton  = "<IMG src='../css/images/delButton.gif' alt='del' "
		 ."title='delete gene' height='15'\n"
		 ."onclick='javascript:GENETABLE.removeGene(\"$gene\", \"$tabId\", false)'>";

		#print "ENTREZGENE is $ENTREZGENE<BR>\n";
		if ( preg_match("/^$ENTREZGENE(\d+)/i", $gene, $matches) ){
			$geneNr = "$matches[1]";
		}else	if ( preg_match("/^$SWISSPROT([A-Z0-9]+)/i", $gene, $matches) ){
			$geneNr = "$matches[1]";
		}else if ( preg_match("/([^:]+):([A-Z0-9]+)/i", $gene, $matches) ){
			$geneNr = "$matches[1]$matches[2]_";
			$geneNr = "$matches[1]";
		}else{
			$geneNr = "MISSING".str_replace( ":","_", $gene);
		}#If Entrez identifier exists

		if ($myDEBUG) print __FILE__."~".__LINE__.": MyID is $geneNr<BR>\n";

		$text .= "<TR id='$TABLE$gene'>\n"
		 ."<TH scope='row' class='alt'></TH>\n"
		 ."<TD>$pmcid</TD>\n"
		 ."<TD></TD>\n"
		 ."<TD>$geneNr</TD>"
		 ."<TD>$rank</TD>\n"
		 ."<TD>$conf</TD>\n"
		 ."<TD></TD>\n"
		 ."<TD>$titleCount</TD>\n"
		 ."<TD>$abstractCount</TD>\n"
		 ."<TD>$fulltextCount</TD>\n"
		 ."<TD scope='row' class='alt'>$delButton</TD>\n"
		 ."</TR>\n";
		
		if ($myDEBUG) print __FILE__."~".__LINE__.": gene was $gene, conf is $conf<BR>\n";
		$rank++;
		$total++;
		//}else{
		//}#else, not ENTREZGENE
	}#foreach gene
	if ($myDEBUG) print "TOTAL is $total<BR>\n";
	return $text;
}#function printGeneRows

?>

