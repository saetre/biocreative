<?php

date_default_timezone_set('Asia/Tokyo');
#$date_time = date ('Y-m-d H:i:s');
$date_time = date ('Y-m-d_H');

#Increase the memorysize when there are too many articles!
#ini_set("memory_limit","12M");

# DEBUGGING
############
$DEBUG=0; $SHOWLOG=0;
if ($DEBUG) print "Now reading configuration, DEBUG is $DEBUG<BR>\n";

/*
	if (DEBUG){ return "StandOff: so.length is "+this.so.length+", text.length is "
	 + this.text.length+", html.length is "+html.length+"<BR><BR>\n\n"
	}else{ return "" }
*/


#GLOBAL CONSTANTS
#################
#Remove "cell", "gene" and "beta" genes
$REMOVE = Array("EntrezGene:330286|MGI:2669829|TrEMBL:Q8C3K4",
	"EntrezGene:43767|FlyBase:FBgn0004859|PIR:A38926|SWISS-PROT:P19538",
	"FlyBase:FBgn0016772"
);

#Google Highlighting Colors, etc.

$GLOB = array(
	'ABSTRACT'  => "abstract",
	'ALIAS'     => "alis",
	'ALTNAMES'  => "alt",
	'COLOR'     => "color",
	'COLORS'    => array("#ffff00", "#00ffff", "#ff00ff", "#00ff00", "#ff0000"),
	'COLORS2'   => array("#ff8800", "#00ffaa", "#ff0088", "#00aa00", "#888800", "#888888"),
	'CONF'      => "conf",
	'DESCRIPT'  => "description",
	'DESIGNATE' => "designation",
	'DIV'       => "DIV",
	'ENTREZ'    => "entrez",
	#'entrez'   => "http://www.ncbi.nlm.nih.gov/gene/",
	'ENTREZKEY' => "c4ca4238a0b923820dcc509a6f75849b",
	'FOUND'     => "found",
	'FRAGMENT'  => "fragment",
	'FRAGMENTS' => "fragments",
	'FULLTEXT'  => "fulltext",
	'GENE'      => "gene",
	'GENES'     => "genes",
	'GNSUITE'   => "gnsuite",
	'HEADER'    => "header",
	'HIGHLIGHT' => "highlight",
	'HIDE'      => "hide",
	'HITS'      => "hits",
	'ID'        => "id",
	'IDS'       => "ids",
	'LIMIT'     => 400,
	'LINNAEUS'  => "linnaeus",
	'MEDIE'     => "medie",
	'NAME'      => "name",
	'NOMEN'     => "nomen",
	'OFFICIAL'  => "official",
	'OFFSETS'   => "offsets",
	'ORG'       => "org",
	'PM'        => "pm",
	'PMC'       => "pmc",
	'RANK'      => "rank",
	'REMOVE'    => $REMOVE,
	'REMOVED'   => "removed",
	'SPECIESNCBI'=>"species_ncbi_",
	'SWISSPROT' => "swiss-prot",
	'TAB'       => "tab",
	'TAX'       => "tax",
	'TABLE'     => "genes",
	'TAXONOMY'  => "species",
	'TITLE'     => "title",
	'TOTAL'     => "total",
	'WEB'       => "web",
);

function getCssScriptsAndConstants($GLOB){
	$output = "
	<!--COMMON CSS and JavaScript INCLUDES-->
	<link type='text/css' rel='stylesheet' href='../css/jquery-ui-firefox.css'>
	<link type='text/css' rel='stylesheet' href='../css/3column.css'>
	<link type='text/css' rel='stylesheet' href='../css/common.css'>
	<link type='text/css' rel='stylesheet' href='../css/table.css'>

	<script type='text/javascript' src='../jquery.min.js'></script>
	<script type='text/javascript' src='../jquery-ui.min.js'></script>
	<script type='text/javascript' src='../stacktrace.min.js'></script>
	<script type='text/javascript' src='../jquery.tablesorter.min.js'></script>
	<script type='text/javascript' src='../table2CSV.js'></script>
	<!--script type='text/javascript' src='../jquery.detailsRow.js'></script-->

	<script type='text/javascript'>
		<!--
		//CONSTANT values, For index.js and viewer.js
";

	foreach ($GLOB as $key => $val){
		$output .= "var $key\t= ".json_encode($val)."\n";
	}
	$output .= "
		// -->
	</script>
	";
	return $output;
#If not given a filename, could be used to include...?
}#function getCssScriptsAndConstants


#Common:
########

#Path with folders for nXML, PDFs, results, etc.
$datapath = '../data/IAT';

#Documents Path
$DOCS = 'docs';
$manual = "$DOCS/GNSuiteSystemDescription.php";

#Folder with .nxml files
$xml_path = "$datapath/xml";

#Folder with merged Text and Species tags
$species_path = "merged-species-ascii";
#$species_path = ".";


#For index.php, viewer.php, store.php

#Folder for storing the results
$results_path = "results";

#file with PMC-IDS, Database:identifiers, and Off-sets (from MEDIE)
$goldfile = "$datapath/results/index.txt";

#file with PMC-IDS, EntrezIDs, confidence-score, and Paragraph:Off-sets (From GNSuite)
$predictfile = "GNSuite.txt";
$predictfile = "$datapath/results/GNSuite.complete.txt";

#same as goldfile, with PMIDs instead of PMCids
$pmidIndexFile = "$datapath/pmidIndex.txt";

#file with EntrezIDs, and corresponding PMCIDs, MEDIE
$geneIndexFile = "$datapath/geneIndex.txt";
#EntrezIDs to PMCIDs, but from GNsuite
$GNgeneIndexFile = "$datapath/geneIndex.GNSuite.txt";

#file with PMID to PMCID mapping
$mappingfile = "$datapath/annotations.txt";


#HELPER FUNCTIONS
function makeGeneCSS($colors){
	$genes_css="";
	for ($color=0; $color<count($colors); $color++){
		$genes_css .= "
.color$color { background-color: $colors[$color]; }
.color$color:hover { text-decoration: underline; color: red; background-color: white;}
	";
	}
	return $genes_css;
}#function makeGeneCSS



?>
