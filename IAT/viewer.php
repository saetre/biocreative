<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
 <HEAD>
<?php
#print __FILE__."~".__LINE__."TEST<BR>\n";

#SAMPLE QUERY, implemented ine viewer.js:
#efetch= "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?apikey=c4ca4238a0b923820dcc509a6f75849b&"
#esummary= "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?apikey=c4ca4238a0b923820dcc509a6f75849b&"
#espell= "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/espell.fcgi?db=taxonomy&term=mus_muskulus"
#esearch= "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=taxonomy&term=mus_musculus" //Get ID
#esummary+"db=taxonomy&id=10090" //Get Names
#efetch+"db=gene&id=257632"
#esummary+"db=gene&id=257632"
#esummary+"retmode=xml&db=unigene&term=107607"

include_once "configuration.php"; #e.g: $results_file=$results_path;
include_once "pmcid2pmid.php"; #To map between PM-Central and PM-ID
include_once "store.php"; #e.g: $results_file=$results_path, overwrites config.
include_once "table.php"; #To manipulate the annotated genes
include_once "../../php/utilities.php";

include_once "docs/GNSuiteSystemDescription.php"; #printContent,printDocumentation

$DEBUG=0;
if ($DEBUG) $SHOWLOG=1;
//$SHOWLOG=1;

if ($DEBUG){
	print __FILE__.__LINE__."~Now reading configuration, DEBUG is $DEBUG<BR>\n";
}

if ( array_key_exists('file', $_POST) && $_POST['file'] ){
	$pmcid = $_POST['file'];
	$pmid = getPmid($mappingfile, $pmcid); #from pmcid2pmid.php
}else if ( array_key_exists('file', $_GET) && $_GET['file'] ){
	$pmcid = $_GET['file'];
	$pmid = getPmid($mappingfile, $pmcid); #from pmcid2pmid.php
}else if ( array_key_exists('pmid', $_POST) && $_POST['pmid'] ){
	$pmid = $_POST['pmid'];
	$pmcid = getPMCID($mappingfile, $pmid);
}else if ( array_key_exists('pmid', $_GET) && $_GET['pmid'] ){
	$pmid = $_GET['pmid'];
	$pmcid = getPMCID($mappingfile, $pmid);
}else{
	if ($DEBUG){
		error_log(__FILE__."~".__LINE__.": No 'file' or 'pmid' was given..."
		 . "...Skipping the rest (Included file?");
	}
	print "<!--viewer.php~33: No 'file' or 'pmid' was given, Skipping the rest... (Included file?)-->";
	return "Skipping rest of the file!!\n\n";
}#If not given a filename, could be used to include...?


$xml_filename     = "$xml_path/$pmcid.nxml";
$species_filename = "$species_path/$pmcid.nxml.txt";
$results_filename = "$results_path/SPECIES";
#error_log(__FILE__."~".__LINE__."[$date_time][/satre/biocreative/viewer.php] see errors above");


#print "</HEAD><BODY>\n"; #For easy debugging output... Don't need to "view source"

#GLOBALS
$ABSTRACT = $GLOB["ABSTRACT"];
$COLORS   = $GLOB["COLORS"];
$COLORS2  = $GLOB["COLORS2"];
$FULLTEXT = $GLOB["FULLTEXT"];
$FOUND    = $GLOB["FOUND"];
$GENE     = $GLOB["GENE"];
$GENES    = $GLOB["GENES"];
$GNSUITE  = $GLOB["GNSUITE"];
$HIDE     = $GLOB["HIDE"];
$HIGHLIGHT= $GLOB["HIGHLIGHT"];
$IDS      = $GLOB["IDS"];
$MEDIE    = $GLOB["MEDIE"];
$TAB      = $GLOB["TAB"];
$TABLE    = $GLOB["TABLE"];
$TAXONOMY = $GLOB["TAXONOMY"];
$TOTAL    = $GLOB["TOTAL"];
$WEB      = $GLOB["WEB"];
#$ = $GLOB[""];

list($title, $textFragments, $allHTML, $species)
 = getTextFragmentsAndSpeciesTags($species_filename);
list($css, $species_div) = getSpeciesDIV($pmcid, $COLORS2, $species, $pass);

#getGenefile($File-ID, $goldfile, $index, $filter, $fileformat, $textFragments )
#--> Return: new index, max Confidence, newly added genes (ID -> Conf off-to ...)
#$genes: ID -> Conf Offset-To ... Offset-To
list($maxConfMedie, $genes)
 = getGenefile($pmcid, $goldfile, array(), $MEDIE, $textFragments);

list ($maxConfGnsuite,$predicted)
 = getGenefile($pmcid, $predictfile, array(), $GLOB["GNSUITE"], $textFragments);
if ($DEBUG){
  print __FILE__."~".__LINE__.":".__FUNCTION__.": DEBUG is $DEBUG, \n";
	print "Gene count is ".count($genes);
	print ", predicted count is ".count($predicted)."<BR>\n";
}

#For the record	#$species_file .= "$pmcid\t$specie\n";
#print "species is $species<BR>\n";
#print_r(array_keys($species));

#store_speciesfile($results_filename, $pmcid, implode(" ", array_keys($species)));
$abstract = getAbstract($pmid, $genes, $COLORS);

$allGenes = array();
$medieGeneLinks   = printGeneLinks( $genes,
	$COLORS, $maxConfMedie*2, $total, $allGenes, $MEDIE );
$gnsuiteGeneLinks = printGeneLinks( $predicted,
	$COLORS, $maxConfGnsuite, $total, $allGenes, $GNSUITE );

if ($DEBUG>1){
	print __FILE__.__LINE__."~allGenes is "; print_r($allGenes); print "<BR>\n";
	print __LINE__."~medieGeneLinks is "; print_r($medieGeneLinks); print "<BR>\n";
}//if DEBUG

##############################
# HEADERS
##############################
?>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<TITLE>GN Results for PMC<?=$pmcid?>: <?=$title?></TITLE>
<?php
print getCssScriptsAndConstants($GLOB); #From configuration.php

#CSS STYLING DYNAMICALLY ADDED
#http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css'
print "<style type='text/css'>\n";
print $css; #For species...
#Make the css layout for each gene-span (color0-4)
print makeGeneCSS($COLORS); #For genes...
print "</style>\n";
?>

<!--
<script type="text/javascript" src="http://www-tsujii.is.s.u-tokyo.ac.jp/satre/javascript/all64.js"></script>

src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"-->

<script type="text/javascript">
<!--
//Common post and get values
var filename	= "<?=$xml_filename?>"
var pass	=	"<?=$pass?>"
var pmcid	= "<?=$pmcid?>"

//ALLGENES(hash) is { GeneID:
// {COLOR=>color, CONF=>conf,
//  OFFSETS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] }
var ALLGENES = <?=json_encode($allGenes)?>

// -->
</script>
<script type="text/javascript" src="viewer.js"></script>
<script type="text/javascript" src="table.js"></script>

</head>



<!--HTML Skeleton-->
<BODY>
<?php
	include_once("../../googleanalytics.php");
?>

<H1><A HREF='.'>Index</A>,
	File: PMCID=<?=$pmcid?>,
	PMID=<?=$pmid?>
<!--[<A id=tFile HREF='./pdfs/<?=$pmcid?>.pdf'>PDF</A>] -->
<?php
	if ($pmcid){
		print "[<A id=xFile HREF='show.php?file=$xml_filename'>XML</A>]";
	}
?>
</H1>


<div id="leftbox">
	LOG IN<HR>
<?=printContents($manual)?>
</div>


<div id="middlebox">
	<div id="DEBUG"></div>
	<div id="toptabs">
		<ul>
			<li><a href="#<?=$MEDIE.$TAB?>"><?=$MEDIE?></a></li>
			<li><a href="#<?=$GNSUITE.$TAB?>"><?=$GNSUITE?></a></li>
			<li><a href="#<?=$TABLE.$TAB?>"><?=$TABLE?></a></li>
			<li><a href="#<?=$TAXONOMY.$TAB?>"><?=$TAXONOMY?></a></li>
			<li><a href="#<?=$HIGHLIGHT.$TAB?>"><?=$HIGHLIGHT?></a></li>
			<li><a href="#<?=$HIDE.$TAB?>"><?=$HIDE?></a></li>
		</ul>


		<div id="<?=$MEDIE.$TAB?>">
			<div id="<?=$MEDIE?><?=$GENES?>">
				Genes Found:
				<span id="<?=$MEDIE?><?=$IDS?>">
<?=implode("\n", $medieGeneLinks)?>
				</span>
				<span id=<?="$MEDIE$FOUND"?>>0</span>
				/<span id=<?=$MEDIE.$TOTAL?>><?=$total?></span>
			</div><!--END #<?=$MEDIE?><?=$GENES?>-->
		</div><!--END #medietab-->


		<div id="<?=$GNSUITE?><?=$TAB?>">
			<div id="<?=$GNSUITE?><?=$GENES?>">
				Recognized:
				<span id="<?=$GNSUITE?><?=$IDS?>">
<?=implode("\n", $gnsuiteGeneLinks)?>
				</span>
				<span id=<?="$GNSUITE$FOUND"?>>0</span>/<span id=<?=$GNSUITE.$TOTAL?>><?=$total?></span>
			</div><!--END #<?=$GNSUITE?><?=$GENES?>-->
		</div><!--END #gnsuitetab-->

<?php
	#PRINT TABLES
	###############

	print "<div id='$TABLE$TAB'>\n";
	print "	<!--Genes in Current Article, PMCID: $pmcid<HR-->\n";
	print printGeneTable( $GENE.$TABLE, $allGenes, $COLORS,
	 $maxConfMedie, $maxConfGnsuite )."\n";
	print printRemovedTable( $GENE.$TABLE )."\n";
	print "</div><!--END #tabletab-->\n";
?>

		<div id="<?=$TAXONOMY.$TAB?>">
<?=$species_div?>
		</div><!--END #taxonomy-->

		<div id="<?=$HIGHLIGHT.$TAB?>">
			<?=$HIGHLIGHT?><HR>
			<INPUT TYPE=CHECKBOX CLASS="master" ID="AllSpeciesButton"
				onClick="showHideHighlight(this)"  CHECKED="CHECKED">
				Species<BR>
			<INPUT TYPE=CHECKBOX CLASS="geneButton" ID="AllGenes"
				onClick="showHideHighlight(this)" CHECKED="CHECKED">
				Genes
			<HR>
			<div id="settings">
				<INPUT TYPE=CHECKBOX CLASS="settings" ID="caseButton"
					onClick="switchCase(this)">Case Sensitive
				<INPUT TYPE=CHECKBOX CLASS="settings" ID="spaceButton"
					onClick="switchSeparated(this)" CHECKED="CHECKED">
					Space/Separators
			</div>
		</div><!--END #highlight settings-->

		<div id="<?=$HIDE.$TAB?>">&nbsp;</div><!--END #hidetab-->

	</div><!--END toptabs-->

<!--<li><a href="http://www.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=<?=$pmid?>&retmode=xml&rettype=abstract"><span><?=$ABSTRACT?></span></a></li>-->


	<div id="tabs">
		<ul>
			<li><a href="#<?=$ABSTRACT.$TAB?>"><?=$ABSTRACT?></a></li>
			<li><a href="#<?=$FULLTEXT.$TAB?>"><?=$FULLTEXT?></a></li>
			<li><a href="#<?=$WEB.$TAB?>">Web</a></li>
		</ul>

		<div id="<?=$ABSTRACT.$TAB?>">
			<div id="<?=$ABSTRACT?>">
				<?=$abstract?>
			</div><!--END #<?=$ABSTRACT?>-->
		</div><!--END #abstracttab-->

		<div id="<?=$FULLTEXT.$TAB?>">
			<div id="<?=$FULLTEXT?>">
				<?=$allHTML?>
			</div><!--END #fulltext, created in getTextFragmentsAndSpeciesTags() above-->
		</div><!--END #fulltexttab-->

		<div id="<?=$WEB.$TAB?>">
			<?php $title_escaped = str_replace(" ", "%20", $title);?>
			Search <A HREF="http://google.com/#hl=en&amp;q=intitle:<?=$title_escaped?>">Google: "<?=$title?>"</A><BR><BR>
		Search <A HREF="http://www.ncbi.nlm.nih.gov/pubmed/<?=$pmid?>">PubMed: <?=$pmid?></A><BR>
		</div><!--END #$webtab-->

	</div><!--END tabs-->

	<div id="result"></div>
	<div id=showme class=details></div>
</div><!--END #middlebox-->

<div id="rightbox">
<FORM action=''>
	Current GENE (or PROTEIN)
	<HR>
	<div id="currentGene">Click a Colored Gene
	</div>
	<div id="names">Names
	</div>
	<img id="loading" SRC="../css/images/loading.gif" ALT="Loading...">
	<hr>
	<INPUT id="annotate" Type="submit" value="Search!"><BR>
<HR>
	<textarea id="newname" rows=3 cols=20>Double-Click a missing gene word, and click Search!</textarea>
	<DIV id=annotationresults></DIV>
</FORM>
</div>




<?php
	if ($SHOWLOG>0){
		#error_log(__FILE__."~".__LINE__."~". strftime('%c') );
		print file_get_contents(
			"http://www-tsujii.is.s.u-tokyo.ac.jp/satre/php/404.php"
		);
	}
	$counts = file_get_contents(
		"http://www.idi.ntnu.no/~satre/php/count_guest.php?ip=$ip&page=$pmcid"); #slow
	list ($count, $visit_count, $name, $menu_joke) = preg_split ("/\t/", $counts);
?>

<h3><?=$menu_joke?></h3>

</body>
</html>



<?php
//////////////////
//php functions///
//////////////////

#cmpR (compare, reverse): In C++
# bool Offset::operator<(const Offset& offset2) const{
#	return (this->from < offset2.from ||
#	 (this->from == offset2.from && this->to > offset2.to));
#}
#Reverse sort begin tags: smallest "from" is last, outside after inside: biggest "to" is last
#Reverse sort end tags: smallest "to" is last, inside after outside: biggest "from" is last
#..."Begin tags" after "end tags" in same position
#Array: From,To,"begin",Tag, or To,From,"end",Tag
function cmpR($a, $b){
	return ( $a[0] < $b[0] ||
	 ($a[0]==$b[0] && $a[2] < $a[2] ) ||
	 ($a[0]==$b[0] && $a[2]==$b[2] && $a[1] > $b[1] ) ||
	 ($a[0]==$b[0] && $a[2]==$b[2] && $a[1]==$b[1] && $a[2]=="b" && $a[3]<$b[3] ) ||
	 ($a[0]==$b[0] && $a[2]==$b[2] && $a[1]==$b[1] && $a[2]=="e" && $b[3]<$a[3] ) ) ? 1 : -1;
}#function cmpR (Reverse Offset-comparator)

function escapeString($text){
	$text = str_replace("'", "\'", $text);
	$text = str_replace('"', '\"', $text);
	return $text;
}#function escapeString

//Make XML inline text from the merged text/species file,
// make the species-hash ( ID->[content] )
// ...ID: species_ncbi_12302x0x693-...-species_ncbi_10295x0x020
// ...content: text-in-the-species-tag
//Return:
// title-text, textFragments-array (without tags) and allHTML (with tags)
//... <FRAGMENT id="fragmentX"> is inserted in allHTML
#Filename contains
	#	###xml 1119 1129 <span type="species:ncbi:6239">C. elegans</span>
  #	or plain text (without leading ###)
function getTextFragmentsAndSpeciesTags($filename){
	global $DEBUG, $GLOB, $pmcid, $pmid;
	$FRAGMENT=$GLOB["FRAGMENT"];
	$myDEBUG=$DEBUG+0;
	$title=""; $textFragments=array(); $allHTML=""; $species=array();
	$current_text = ""; $tags = array();

  if ($myDEBUG) print __FILE__."~".__LINE__.": Reading file -$filename-<BR>\n";
	if ( preg_match("/^\d+$/", $pmcid) ){ // If pre-processed file is available?
		$handle = fopen($filename, "r"); 	// open file
		if ($handle){
		  if ($myDEBUG>2) print __FILE__."~".__LINE__.": Open ok!\n";
			while( ($line=fgets($handle)) !== FALSE ){
				//$line = substitute_html_escapes($line);
				if ($myDEBUG>2) print __FILE__."~".__LINE__.": $line\n";
				if (substr($line, 0, 3) == "###"){
					#content is the plain-text-inside-the-tag
					list($type, $from,$to,$tag,$index,$attrib, $content)=match_tag($line);

					if ($type == "xml"){
						#tags: [ [from,to,"b",<tag>] OR [to,from,"e",</tag>] ]
						$tags=storeXmlTag($species, $tags, $from,$to,$tag,$attrib, $content);
					}else if ($type == "begin"){
						#Reset tag-collection
						$tags=array();
						if ($tag == "article-title"){
							$allHTML .= "<span class='article-title' id='$FRAGMENT$index'>\n";
						}else	if ($tag == "title"){
							$allHTML .= "<span class='title' id='$FRAGMENT$index'>\n";
						}else	if ($tag == "p"){
							$allHTML .= "<$tag id=$FRAGMENT$index>\n";
						}else{
							$allHTML .= "$tag $index: <BR>\n";
						}
					}else if ($type == "end"){
						$textFragments[$index] = $current_text;
						$allHTML .= put_tags($current_text, $tags);
						if ($tag == "p"){
							$allHTML .= "</$tag>\n";
						}else if ($tag == "article-title"){
							if (!$title){ $title = trim($current_text); }
							$allHTML .= "</span>\n";
						}else if ($tag == "title"){
							$allHTML .= "</span>\n";
						}
					}else{ $allHTML .= "<BR>PHAILPHAIL $type<BR>\n"; }
				}else{ #line is not a tag -> remember the text
					$current_text = $line;
				}#if comment, else print
			}#while foreach result
			fclose($handle);
		}else{
			print __FILE__."~".__LINE__.": CANNOT OPEN file $filename<BR>\n\n";
		}#If file open ok
	}else{// If pre-processed file is available?
		$allHTML = "PMID $pmid is not available in BioCreative<BR>\n";
	}
	if ($myDEBUG>2) print __FILE__."~".__LINE__.": allhtml is -----$allHTML-----";
	return array($title, $textFragments, $allHTML, $species);
}#function getTextFragmentsAndSpeciesTags

###
# OLD
#$plain = preg_replace("/<[^>]+>/", "", $abstract); //Remove Tags
#$plain = preg_replace("/\s+/", " ", $plain);//Merge joint Spaces to " "
#$plain = html_entity_decode($plain);
#$adjust=strpos($plain, substr($title,0) )+13;
#print "adjust=$adjust";
###
//Get the online abstract from PubMed E-Utils
//Return: abstract
function getAbstract($pmid, $genes, $colors){
	global $DEBUG; $myDEBUG = $DEBUG+0;
	$abstract = file_get_contents(
"http://www.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=$pmid&retmode=xml&rettype=abstract");
	if (!$abstract){
		print "<!--".__FILE__."~".__LINE__
		 . ": FAILED TO LOAD ABSTRACT $abstract...-->\n";
		$abstract="";
	}#If abstract successfully loaded...

	if ( preg_match("/<ArticleTitle>(.*)<\/ArticleTitle>/", $abstract, $matches) ){
		$title = html_entity_decode($matches[1]);
	}else{
		$title="";
	}
	if ( preg_match("/<MedlinePgn>(.*)<\/MedlinePgn>/", $abstract, $matches) ){
		$pages = $matches[1];
		#print "pages: ---$pages---";
		if ($myDEBUG) print "pages size = ".strlen($pages)."<BR>\n";
	}
	#if ( preg_match("/<AbstractText[^>]+>(.*)<\/AbstractText>/", $abstract, $matches) ){
	#	$padding = 12;
	#}
	#if ( preg_match("/<AbstractText>(.*)<\/AbstractText>/", $abstract, $matches) ){
	if ( preg_match_all('/<AbstractText(.*Label="([^"]+)[^>]+)?>(.*)<\/AbstractText>/', $abstract, $matches, PREG_SET_ORDER) ){
		#$abstract = "$title<BR>";
		$abstract = "<B>$title</B><BR>";
		$abstract = "$title<BR>";
		for ($i=0; $i <= strlen($pages); $i++){ $abstract .= " "; }
		#for ($i=0; $i <= strlen($pages)+$padding; $i++){ $abstract .= " "; }
		foreach ( $matches as $val ){
			if ($val[2]){
				$abstract .= htmlentities($val[2].": ");
			}
			$abstract .= htmlentities($val[3]." ");
		}
		$abstractTags = makeAbstractTags($genes, $colors, 0);
		$abstract = put_tags($abstract, $abstractTags);
	}else{
		print __FILE__."~".__LINE__.": No abstract?<BR>\n";
		$abstract = $title; //Maybe not matched?
		#print "$abstract...<BR>\n";
	}#If abstract successfully downloaded from PubMed
	return $abstract;
}#getAbstract


#getGenefile($pmcid, $goldfile, $filter, $fileformat, &$textFragments  )
#...$filter is hash? of already added genes
#--> Return: max Confidence, and newly added genes (ID -> Conf off-to ...)
#$genes: ID -> Conf Offset-To ... Offset-To
function getGenefile($pmcid, $goldfile, $filter, $fileformat, &$textFragments ){
	global $DEBUG, $GLOB;
	$myDEBUG = $DEBUG+0;
	$MEDIE=$GLOB["MEDIE"]; $GNSUITE=$GLOB["GNSUITE"];
	$genes=array(); $hitFilter=array(); $predicted=array();
	if ($fileformat == $MEDIE){
		$maxConf = getGenes($goldfile, $pmcid, $genes);
	}else if ($fileformat == $GNSUITE){
		$maxConf = getGNSuiteGenes($goldfile, $pmcid, $genes, $textFragments);
	}else{
		print __FILE__."~".__LINE__.": Unregonized fileformat --$fileformat--<BR>\n";
	}
	#print "filter is "; print_r ($filter);
	#print "file is $goldfile<BR>\n";
	foreach ($genes as $gene => $confOffset){
		if ($myDEBUG>2){
			print __FILE__."~".__LINE__.": confOffset is ";
			print_r($confOffset);print"<BR>\n";
		}
		if (count($filter) && array_key_exists($gene, $filter) ){
			$hitFilter{$gene}=$confOffset;
		}else{
			$predicted{$gene}=$confOffset;
		}#if tp, else add
	}#foreach gene
	return array($maxConf, $predicted);
}#function getGenefile


#Get Genes from $file, for key=pmcid, store in $genes
#MEDIE-file: index.txt
# 13920	EntrezGene:17979|MGI:1276535|SWISS-PROT:O09000	145-149 449-453 555-559
# 13920	EntrezGene:8202|HUGO:7670|OMIM:601937|PIR:T03851	289-293
# 15024	EntrezGene:380794|MGI:2144967|TrEMBL:Q8R3H6	880-885
#or GNSUITE FILE?
#1999495	2705	7.415273	68:971-985 68:1059-1073 69:438-452 68:929-948
#1999495	5376	0.984507	12:873-878
#
#--> Returns max Confidence and adds new entries to genes
#$genes: hash of {ID -> array of [0: Conf, 1: [off-to, ..., off-to] )}
function getGenes($file, $key, &$genes){
	global $DEBUG, $GLOB, $REMOVE;
	$ABSTRACT=$GLOB["ABSTRACT"]; $CONF=$GLOB["CONF"];
	$FRAGMENTS=$GLOB["FRAGMENTS"];
	$myDEBUG=$DEBUG+0;
	$maxConf=0; $found=false;
	if ($myDEBUG>1){
		print __FILE__."~".__LINE__.": FILE IS $file, key is $key<BR>\n";
	}
	#$lines = file($file);
	#foreach ($lines as $line){
	$handle = fopen($file, "r");  // open file
	if ($handle){
		// loop through results with fgetcsv()
		while( ($line=fgets($handle)) !== FALSE ){
			$values = explode("\t", rtrim($line));
			$pmid = $values[0];
			if ($pmid==$key){
				$found=true;
				if (count($values)>1){
					$gene = $values[1];
					#Add all genes for given pmid, EXCEPT "cell" and "gene" -> TOO general
					if ( !in_array($gene, $REMOVE) ){
						if ( count($values)>2 ){
							$offsets = explode(" ",$values[2]);
							$conf = count( $offsets );
						}else{
							$offsets = array();
							$conf = 1;
						}
						if ($conf>$maxConf){ $maxConf=$conf; }
						if ($myDEBUG>2){
							print __FILE__."~".__LINE__.": pmid is $pmid, gene is $gene, ";
							print "conf is $conf, offsets are $offsets<BR>\n";
						}
						#Push $gene to the $genes array, with confidence score
						$genes[$gene] = array(
						 "$CONF"=>$conf, "$FRAGMENTS"=>array($ABSTRACT => $offsets)
						);
						#print "Added $gene: conf-off-set is $genes[$gene]<BR>\n";
					}#If not super-ambiguos gene
				}else{
					#print __FILE__."~".__LINE__.": No gene for pmcid -$key- in file -$file-<BR>\n";
				}#No gene found
			}else if ($found){//if right pmcid was found, and whole sorted cluster processed
				fclose($handle);
				return $maxConf;
			}
		}#while foreach result
		fclose($handle);
	}else{
		print __FILE__."~".__LINE__.": Cannot open file --$file--\n";
	}
	return $maxConf;
}#getGenes

#file: pmcid, entrezgeneId, conf, occurrences (fragment:off-set)
#1999495	2705	7.415273	68:971-985 68:1059-1073 69:438-452 68:929-948 114:94-114
#1999495	5376	0.984507	12:873-878
#1998882	19127	96.740437	5:96-103 24:394-401 52:1391-1398 52:1818-1825 52:2197-2204 52:2589-2596 52:2755-2762 52:2785-2792 52:3044
#--> Returns max Confidence and adds new entries to genes(ID -> Conf off-to ...)
function getGNSuiteGenes($file, $key, &$genes, &$textFragments){
	global $DEBUG, $GLOB;
	$CONF=$GLOB["CONF"]; $ENTREZGENE = $GLOB["ENTREZ"].$GLOB["GENE"]; $FRAGMENTS=$GLOB["FRAGMENTS"];
	$myDEBUG=$DEBUG+0;
	$maxConf=0; $found=false;
	if ($myDEBUG){ print __FILE__."~".__LINE__.":".__FUNCTION__.": ";
		print "FILE IS $file<BR>\n";
	}
	$handle = fopen($file, "r");  // open file
	if ($handle){
		// loop through results with fgetcsv()
		while( ($line=fgets($handle)) !== FALSE ){
			$values = explode("\t", rtrim($line));
			#if (count($values)){
			$pmid = $values[0];
			if ($pmid==$key){
				$found=true;
				$gene = $values[1];
				if ( count($values)>2 ){
					$conf = $values[2];
					if ($myDEBUG>2){print __FILE__."~".__LINE__.": conf is $conf<BR>\n";}
				}else{
					$conf = 1;
				}
				if ($conf>$maxConf){ $maxConf=$conf; }

				if ( count($values)>3 ){
					$terms = replaceOffSetsWithText( $values[3], $textFragments );
				}else{
					if ($myDEBUG>1) print __FILE__."~".__LINE__." MISSING!<BR>\n";
				}
				if ($myDEBUG>1){ print __FILE__."~".__LINE__.":".__FUNCTION__.": ";
					print "Add $gene, Conf is $conf, pmid $pmid<BR>\n";
					if ($myDEBUG>2){print "...Terms is "; print_r($terms); print "<BR>\n";}
				}
				#Push $gene to the $genes array, with confidence score and fragment:terms
				$genes["$ENTREZGENE:$gene"]
				 = array(	"$CONF"=>$conf, "$FRAGMENTS"=>$terms );
			}else if ($found){//right pmcid found, and whole sorted cluster processed
				fclose($handle);
				return $maxConf;
			}
		}#while foreach result
		fclose($handle);
	}else{
		print __FILE__."~".__LINE__.": Could not open file $file<BR>\n\n";
	}
}#getGNSuiteGenes (...&$genes, &$textFragments)

#Filename contains ###xml tags, including species
#... and text, merged per fragment (paragraph/title/etc)
#Modifies Species: a hash of array of content ( ID -> [content] )
# ID = species_ncbi_12302x0x693-species_ncbi_10295x0x020
//Return: css for species and the species DIV
function getSpeciesDIV($pmcid, $colors, &$species, $pass){
	global $GLOB;
	$GNSUITE=$GLOB["GNSUITE"]; $LINNAEUS=$GLOB["LINNAEUS"]; $MEDIE=$GLOB["MEDIE"];
	$ORG=$GLOB["ORG"]; $SPECIESNCBI=$GLOB["SPECIESNCBI"];
	$css = "";
	$species_div = "<DIV id='species'>\n";
	$species_div .= "<HR>$GNSUITE:<BR><DIV id='$GNSUITE$ORG'></DIV>\n";
	$species_div .= "<HR>$MEDIE:<BR>"
	 . "<DIV id='$MEDIE$ORG'></DIV>\n";

	$species_div .= "<HR>Others predicted by $LINNAEUS:<BR><DIV id='$LINNAEUS$ORG'>\n";
	$color = 0;
	uasort($species, "sizeCmp");
	foreach ($species as $specie => $names){
		#for css
		$css .= "span.$specie{ background-color: $colors[$color]; }\n";
		$css .= "span.$specie:hover{ text-decoration:underline; color:$colors[$color]; background-color:white; }\n";

		#for the menu
		preg_match("/^$SPECIESNCBI(\d+)/", $specie, $matches);
		if ($matches){
			$myId = $matches[1];
		}else{
			print "No match for $specie<BR>\n";
			$myId = $specie;
		}
		$species_div .= "<SPAN class='$specie' title='$myId'>";
		if ($pass){
			$species_div .= "<A HREF='store.php?pmcid=$pmcid&species=$specie&file=results/TP'>$ABSTRACT</A>\n";
			$species_div .= "<A HREF='store.php?pmcid=$pmcid&species=$specie&file=results/FP'>Predicted</A>\n";
		}
		$species_div .= "$names[0] ";
		$species_div .= "<A href=\"javascript:goNext('$specie')\">";
		$species_div .= "(".count($names).")</A> \n";
		$ids = explode("-", $specie);
		foreach ($ids as $one){
			$id = preg_replace("/^$SPECIESNCBI(\d+)/", "$1", $one);
			$species_div .= "$id";
		}
		$species_div .= "</SPAN> \n";
		$color = ($color+1) % count($colors);
	}
	$species_div .= "</DIV><!--END $LINNAEUS-tax-->\n";
	$species_div .= "<HR>NOT predicted by LINNAEUS:<BR><DIV id='FN'></DIV><HR>\n";
	$species_div .= "</DIV><!--END species-->\n";

	return array($css, $species_div);
}#function getSpeciesDIV


#tags: [ [from,to,"b",<tag>] OR [to,from,"e",</tag>] ]
function makeAbstractTags($genes, $colors, $adjust){
	global $DEBUG, $GLOB;
	$ABSTRACT=$GLOB["ABSTRACT"]; $COLOR=$GLOB["COLOR"]; $CONF=$GLOB["CONF"];
	$FRAGMENTS=$GLOB["FRAGMENTS"]; $ENTREZGENE = $GLOB["ENTREZ"].$GLOB["GENE"];
	$myDEBUG = $DEBUG+0;
	$tags = array();
	$color=0;
	foreach ($genes as $geneId => $confidenceOffsets){
		if ( preg_match("/($ENTREZGENE):(\d+)/i", $geneId, $matches) ){
			#$geneId = $matches[1].$matches[2]; #Standardize!
			$geneId = "$ENTREZGENE$matches[2]";
		}
		if ($myDEBUG>1){
			print __FILE__."~".__LINE__.": $geneId===>>><BR>\n";
			print "...: confidenceOffsets:"; print_r($confidenceOffsets);print "<BR>";
		}
		$conf = $confidenceOffsets[$CONF];
		$offsets = $confidenceOffsets[$FRAGMENTS];
		foreach ($offsets[$ABSTRACT] as $offset){
			if ($myDEBUG>1) print __FILE__."~".__LINE__.": ===>>>$offset<BR>\n";
			list ($from, $to) = explode("-", $offset);
			#array_push ($tags, array ($from-$adjust, $to-$adjust, "b", "<span class='color$color highlighted $geneId' title='$geneId' onclick=\"goNext('gene$geneId')\">") );
			#array_push ($tags, array ($to-$adjust, $from-$adjust, "e", "</span>") );
			array_push ($tags, array ($from-$adjust, $to-$adjust, "b", "<span class='$geneId'>") );
			array_push ($tags, array ($to-$adjust, $from-$adjust, "e", "</span>") );
		}#foreach offset
		#print "geneId is $geneId, color is $color<BR>\n";
		$ALLGENES[$geneId][$COLOR] = $color;
		$color = ($color+1) % count($colors);
	}#foreach gene
	return $tags;
}#function makeAbstractTags

###begin article-title 0
###xml 109 136 109 136 <italic xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:mml="http://www.w3.org/1998/Math/MathML">Gracilaria vermiculophylla </italic>
###xml 109 135 <span type="species:ncbi:257814">Gracilaria vermiculophylla</span>
###xml 137 141 <span type="species:ncbi:257814">Ohmi</span>
# Variations in morphology and PSII photosynthetic capabilities during the early development of tetraspores of Gracilaria vermiculophylla (Ohmi) Papenfuss (Gracilariales, Rhodophyta)
###end article-title 0
###begin title 1
function match_tag($line){
	$from=0; $to=0; $tag=""; $index=""; $attrib=""; $content="";
	#begin or end-tags
	if (preg_match('/^###(begin|end) (\S+) (\d+)$/', $line, $matches) ){
		#1:begin/end (Content), 2:tagname, 3:index
		$type = $matches[1];
		$tag = $matches[2];
		$index = $matches[3];

		###xml 374 375 368 369 <italic>n</italic> 374 is text-offset, 368 is unicode-o
	}else if (preg_match('/^###xml (\d+) (\d+) \d+ \d+ <(\S+) ([^>]+)>(.+)<\/(\3)>$/', $line, $matches) ){
		#1:from, 2:to, 3:tagname, 4:content, 5:tagname
		$type = "xml";
		$from = $matches[1];
		$to = $matches[2];
		$tag = $matches[3];
		$attrib = $matches[4];
		$content = $matches[5];
		#print "Matched: $matches[3]<BR>\n";
	}else if (preg_match('/^###xml (\d+) (\d+) <span type="([^>]+)">([^<]+)<\/span>$/', $line, $matches) ){
		#1:from, 2:to, 3:spantype, 4:content, 5:tagname
		$type = "xml";
		$from = $matches[1];
		$to = $matches[2];
		$tag = "span";
		$attrib = $matches[3];
		$content = $matches[4];
	}else{
		$line = str_replace("<", "&lt;", $line);
		$type="MINOR PHAIL: $line<BR>\n!";
	}#if begin/end, else if original tag, else if span-tag
	return array($type, $from, $to, $tag, $index, $attrib, $content);
}#function match_tag


#$genes: ID -> Conf Offset-To ... Offset-To
# ID: EntrezGene:17979|MGI:1276535|SWISS-PROT:O09000
# Conf: Frequency count (or sum/avg Confidence
# Off-to ...:	145-149 449-453 555-559
#OUTPUT:
# $output: <SPAN>s for the MEDIE/GNSUITE-tabs
# MODFIES and ADDS to $allGenes
//ALLGENES: to be imported and shown in table.js
	//ALLGENES(hash) is { GeneID:
	// {COLOR=>color, CONF=>conf,
	//  OFFSETS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] }
function printGeneLinks($genes, $colors, $maxConf, &$total, &$allGenes, $source){
	global $DEBUG, $GLOB, $pmcid, $pmid;
	$myDEBUG=$DEBUG+0;
	$COLOR=$GLOB["COLOR"]; $CONF=$GLOB["CONF"];
	$ENTREZGENE=$GLOB["ENTREZ"].$GLOB["GENE"];
	$FRAGMENTS=$GLOB["FRAGMENTS"];
	$SWISSPROT=$GLOB["SWISSPROT"];

	$problemReport=true;
	#$color=0;
	$output="";
	if ($myDEBUG>1){ print __FILE__."~".__LINE__.": ConfOffsets is ";
		print __FILE__."~".__LINE__.": Size is ".count($genes);	print_r($genes);
	}
	foreach ($genes as $gene => $confOffsets){
		if ( preg_match("/($ENTREZGENE):(\d+)/i", $gene, $matches) ){
			#$geneId = "$matches[1]$matches[2]";
			$geneId = $ENTREZGENE.$matches[2];
			#Set correct COLOR in mergeGene, table.php
			$total = mergeGene( $geneId, $confOffsets, $maxConf, $allGenes );
			$color = $allGenes[$geneId][$COLOR];
			if ($myDEBUG>0){ print __FILE__."~".__LINE__.":".__FUNCTION__.": ";
				print "color is $color<BR>\n";
			}
			$output.= "<SPAN id='$geneId$source' class='color$color geneswitch' ";
			$output.= " title='$geneId' ";
			$output.= " onclick='getGene(\"$geneId\")'>$geneId</SPAN>\n";
		}else if ( preg_match("/$SWISSPROT:([A-Z0-9]+)/i", $gene, $matches) ){
			$geneId = "$SWISSPROT$matches[1]";
		}else if ( preg_match("/([^:]+):([A-Z0-9]+)/i", $gene, $matches) ){
			#print "MyID is "$matches[1]$matches[2]";
			#$geneId = "MISSINGDB_$matches[1]$matches[2]_";#.count($offsets);
			$geneId = "$matches[1]";#.$matches[2];
		}else{
			$geneId = "MISSING".str_replace( ":","_", $gene."_"); #count($offsets));
		}#If Entrez identifier exists
		#print "geneId is $geneId<BR>\n";

		if ($myDEBUG>0){
			print __FILE__."~".__LINE__."~".__FUNCTION__.": ";
			print "count is ".count($genes).", gene is $geneId<BR>\n";
			if ($myDEBUG>1){
				print "maxConf is $maxConf, total is $total<BR>\n";
				print "JSON OFFSETS=".json_encode($offsets)."<BR>\n";
			}
		}#if DEBUG
	}#foreach gene

	if ($myDEBUG>1){
		print "TOTAL is $total<BR>\n";
	}
	return array($output);
}#function printGeneLinks


#Insert $tags in $text, from back to beginning, to preserve offsets for the tags
#tags: [ [from,to,"b",<tag>] OR [to,from,"e",</tag>] ]
function put_tags($text, $tags){
	$text = html_entity_decode($text);
	if ($tags){
		usort($tags, "cmpR"); 	#DON'T Break internal ordering
		foreach ($tags as $tag){
			list ($offset, $fromOrTo, $type, $tagstr) = array($tag[0], $tag[1], $tag[2], $tag[3]);
			#TEMPORARY BUG-FIX (for nested xml-tags in xml-tags)
			#if opening before closing tag!
			if (substr($tagstr, 0, 2) != "</"  && substr($text, $offset, 2) == "</"){
				$offset = strpos($text, ">", $offset+1)+1;
				#$text .= "ILLEGAL<BR>\n".substr($tagstr, 1)." (".substr($tagstr, 1, 1)
				# ." before off=$offset, ".substr($text, $offset, 5)."<BR>\n";
			}
			$text=substr_replace($text, $tagstr, $offset, 0);
			#print "text is $text<BR>\n";
		}#foreach tag
	}#If any tags
	return $text;
}#function put_tags

//Replace the Off-Sets with JUST ONE 'term' in each fragment
//Input: $textFragments-array has one entry for each fragment:off-set
//Return a JSON representation of $fragmentTerms: Hash{fragment} => arr[terms]
//... one entry for each UNIQUE term in each fragment
function replaceOffSetsWithText($offsets, &$textFragments){
	global $DEBUG;
	$myDEBUG=$DEBUG+1;
	$fragmentTerms = array();
	foreach (explode(" ", $offsets) as $offset){
		list($index,$offset) = explode(":", $offset);
		if ( array_key_exists($index, $textFragments) ){
			list($from,$to) = explode("-", $offset);
			if ( strlen($textFragments[$index]) > $to ){
				$term=escapeString( substr($textFragments[$index], $from, $to-$from) );

				//if ($from<30){ print "textFragments[$index]:$from-$to is $term<BR>\n"; }
				if ( array_key_exists($index, $fragmentTerms) ){
					if ( !in_array("'$term'", $fragmentTerms[$index]) ){
						array_push($fragmentTerms[$index], "'$term'");
						if ($myDEBUG>1) print __FILE__."~".__LINE__.": add \"$index:'$term'\"<BR>\n";
						//print json_encode("HEI!"); #Need PHP >5.2
					}else{
						if ($myDEBUG>1) print __FILE__."~".__LINE__.": Already added $term<BR>\n";
					}#If not already added
				}else{
					$fragmentTerms[$index] = array("'$term'");
					if ($myDEBUG>2){
						print __FILE__."~".__LINE__.": created $index:$term in ";
					 	print_r($fragmentTerms); print "<BR>\n";
					}
				}#If new/existing index
			}else{
				print "OUT OF BOUNDS! text=$textFragments[$index], to is $to<BR>\n";
			}#If offset is in text
		}else if ($myDEBUG>2){
			print "Missing text fragment $index<BR>\n";
		}#If text fragment present
	}#foreach fragment:off-set

	$json = json_encode($fragmentTerms);
	if ($myDEBUG>1) print __FILE__."~".__LINE__.": json is $json<BR><BR>\n\n";
	return $fragmentTerms;
}#function replaceOffSetsWithText

function sizeCmp($a, $b){
	return (count($a) <  count($b)) ? 1 : -1;
}#function sizeCmp


#Species-hash: ID -> array[content]
# ID = species_ncbi_12302x0x693-species_ncbi_10295x0x020
# content = text-inside-the-tag
#tags: [ [from,to,"b",<tag>] OR [to,from,"e",</tag>] ]
#side-effect: Add species (names) to (the right) species hash
function storeXmlTag(&$species, $tags, $from, $to, $tag, $attrib, $content){
	$tag = str_replace("bold", "b", $tag);
	$tag = str_replace("italic", "i", $tag);
	if ($tag=="span"){
		$attrib = getPlainSpeciesTag($attrib); #From store.php
		array_push ($tags, array ($from, $to, "b", "<$tag class='$attrib' title='$attrib' onclick=\"goNext('$attrib')\">") );
		array_push ($tags, array ($to, $from, "e", "</$tag>") );
		if ( array_key_exists($attrib, $species) ){
			array_push( $species[$attrib], "$content" );
		}else{
			$species[$attrib] = array("$content");
		}
	}else if($tag != 'xref'){
		#array_push ($tags, array ($from, $to, "b", "<$tag $attrib>") );
		array_push ($tags, array ($from, $to, "b", "<$tag>") );
		array_push ($tags, array ($to, $from, "e", "</$tag>") );
	}#If span, else original xml
	return $tags;
}#function storeXmlTag

function substitute_html_escapes($text){
	$text = str_replace("&#8722;", "-", $text);
	return $text;
}#substitute_html_escapes

#print __FILE__."~".__LINE__.": BAD Numbering?<BR>\n\n";
?>

