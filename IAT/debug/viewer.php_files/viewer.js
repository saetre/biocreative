//GLOBALS
var separators = "[ ,.*/()s\n-]"
var space = separators //or space = "[^<>]" (any separator)
//space =""

var iCase=""	//iCase = "i" or ""
var iCase="i"	//iCase = "i" or ""

//URLs
entrezURL = "http://www.ncbi.nlm.nih.gov/gene/"
efetch = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?apikey="+ENTREZKEY
esearch = 'http://entrezajax.appspot.com/esearch?callback=?'
esummary = 'http://entrezajax.appspot.com/esummary?callback=?'
taxonomy = "http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?lvl=0&name="

// DEBUGGING //
var DEBUG=0
var DUMP=0

function debug(msg){
	$("#DEBUG").append(msg+"<BR>\n")
}//function debug


function loadText(){
	$("#toptabs").tabs(); $("#tabs").tabs()
	cleanTextXML()

	initAnnotationHandlers()
	var gold = $("#"+MEDIE+"ids").find("span")
	var predicted= $("#"+GNSUITE+"ids").find("span")

	var MEDIEtarget=ABSTRACT; var GNSUITEtarget=FULLTEXT

	GENETABLE = new Table(GENE+TABLE) 	//GENE TABLE //GLOBAL!
	GENETABLE.initSortable()
	GENETABLE.rerank()
	var geneIds = GENETABLE.getGeneIds()

	GENETABLEREMOVED = new Table(GENE+TABLE+REMOVED) 	//GENE TABLE //GLOBAL!
	GENETABLEREMOVED.initSortable()

	$("#toptabs").tabs('select', 2) //2: Select GeneTable
	if (predicted.length){ //If some genes found by GNSUITE
		//$("#toptabs").tabs('select', 2)
		predicted.each( function(){ getGene(this.id, GNSUITE, GNSUITEtarget) } )
	}else{ //No GNSuite genes, use MEDIE for fulltext too
		gold.each( function(){ getGene(this.id, MEDIE, MEDIEtarget) } )
		MEDIEtarget=FULLTEXT //Annotate FULLTEXT with MEDIE genes
	}//If GNSuite Genes in FullText, else use MEDIE genes in FullText

	gold.each( function(){ getGene(this.id, MEDIE, MEDIEtarget) } )

	$("#tabs").tabs('select', 1) //1: Select Full-Text

	if (DEBUG>0){
		debug( "js:Gene count is "+MEDIE+":"+ gold.length+" "
		+GNSUITE+":"+ jQuery("#"+GNSUITE+"ids").find("span").length)
	}
}//function loadText

//window.onload = loadText;
//execute after skeleton HTML DOM is fully loaded
window.onload = setTimeout('loadText()', 1000)


//HELPER Object.functions 

//...can take any other obj as parameter
Object.size = function(obj) {
	var size = 0, key;
	for (key in obj) {
	    if (obj.hasOwnProperty(key)) size++;
	}
	return size;
}//Object.size method general definition

//JQuery.Utility Functions
//Can take any object as input
//Google: Prototype vs JQuery?
$.extend({
	keys: function(obj){
		var a = [];
		$.each(obj, function(k){ a.push(k) });
		return a;
	}
})//$.keys(obj) Utility Function



// HIGHLIGHTING //
var colorCounter=0

//CLASSES (constructor functions) and general Object methods

//Class to keep from and to tags together
function Offset(from, to){
	if (!to){ //Try to construct Offset by splitting the from-value
		//debug("from is "+from)
		temp = from.split( /[\s-]/ )
		from = temp[0]
		to = temp[1]
		//alert( printStackTrace().join("<BR><BR>\n\n") )
	}//Iff offset created from single string with <Space> or <-> separator only
	if (!to){
		debug("new offset went bad: "+from)
	}
	this.from = from
	this.to = to
	this.toString = function(){
		return this.from+"-"+this.to
	}//Constructor for Tag.toString
}//CLASS Offset, Constructor


///////////////////////////////////////////////////////////
//CLASS StandOff, keep both html, plain text and tags (Stand-Off Manager)
//... used in function highlightTextNodes
///////////////////////////////////////////////////////////
//text: string
//so:		Array of Tag
//html: string
function StandOff(text, so, html){
	// if user accidentally omits the new keyword, this will 
	// silently correct the problem...
 	if ( !(this instanceof StandOff) )
		return new StandOff(text, so);
  // constructor logic follows...
	this.text = text;
	this.so = so;
	this.html = html
}//StandOff-CONSTRUCTOR

//Methods

//Remove added duplicate tags from so, decrease hits accordingly
StandOff.prototype.getUniqueTags = function(hits){
	//var DEBUG=1
	if (DEBUG){
		debug("StandOff.prototype.getUniqueTags~191 so.size is"+Object.size(this.so))
	}
	//Remove duplicates, and decrease hits!
	hits = this.removeDuplicateTags(hits)
	if (DEBUG) debug( "~so.size is " + Object.size(this.so) )
	return hits
}//StandOff.getUniqueTags

/*
StandOff.prototype.debug = function(property){
	if (this.DEBUG){
		debug( "StandOff.prototype.debug~"+property ) //Called from where?
		if (!property || property=="text") debug( "StandOff.text is "+this.text)
	 	if (!property || property=="so") debug( "StandOff.tags are "+dump(this.so) )
		if (!property || property=="html") debug( "StandOff.html is "+this.html )
	}
}//StandOff.debug
*/

//2: Match remaining plain text to get positions for new tags in plain the text
// Tag(name, end, pos, partnerPos, attr)
//May Contain duplicatea
StandOff.prototype.findTags = function(oSearch, insertTag, hits){
	//var DEBUG=1
	//var tags = new Array() //Store all matched tags
	//this.so = newArray()
	//if (oSearch instanceof String){
	if (typeof(oSearch) === 'string'){
		//Protect special characters
		oSearch = oSearch.replace(/^\'/,"")
		oSearch = oSearch.replace(/\'$/,"")
		oSearch = oSearch.replace(/([\[\]\/\'\".+*!^$()|])/g, "\\$1")

		term = oSearch
		if (DEBUG) debug("findTags~177 oSearch is "+oSearch)
		if (DEBUG>1) debug("findTags~this.text is "+this.text)
		var termRE = new RegExp(oSearch,"g")
		var match = termRE.exec(this.text)

		while ( match && hits++ <400 ){
			if(DEBUG){
				debug("findTags~186 match at "+match.index+", match was "+dump(match) )
			}
			this.so.push( new Tag(insertTag.name, "",match.index, 
				match.index+oSearch.length, insertTag.attr) )
			this.so.push( new Tag(insertTag.name,"/",
				match.index+oSearch.length, match.index, "") )
			match = termRE.exec(this.text)
		}//WHILE

		//debug ("findTags~190 so.length is "+this.so.length+", hits is "+ hits)
	}else{
		if (DEBUG) debug( "184~Making new tags from "+dump(oSearch ))
		this.so.push(new Tag(insertTag.name,"", oSearch.from,oSearch.to, insertTag.attr))
		this.so.push( new Tag(insertTag.name,"/", oSearch.to, oSearch.from, "") )
	}//Else: Just this one tag
	return hits
}//StandOff.findTags


//Insert span (class='highlighted termGeneX'), for google style highlighting
//1: Pull-out all proper <TAGs>...</TAGs>, make open-close pairs, to get offsets in plaintext. From beginning to end, use hash of stacks, for each tagtype
	//tags: array of [Tag: [name, from,to,"b",<tag...>] OR [to,from,"e",</tag>] ]
	//b is begin, e is end
//2: Match remaining plain text to get positions for new tags in plain text
//3: Merge old tags and new tags, sort in reverse
//4: Insert all tags into plain-text, from end to beginning
//Input:
// html to change, searchterm to highlight, with general tag to insert
//Return:
// Updated HTML, with total number of hits updated
StandOff.prototype.highlightTextNodes = function(oSearch, insertTag, hits) {
	//var DEBUG=3
	//1:
	this.pullTags() //Make text and so from html
	if (DEBUG) debug(this.so)
	if (DEBUG>1) debug ("StandOff.highlightTextNodes:insertTag.attr is "+insertTag.attr)
	//2:
	hits = this.findTags(oSearch, insertTag, hits)
	if (DEBUG){
		debug ("224~all (with new) tags are "+this.so.length+"(/2)" )
		debug ("New hits total is "+ hits)
	}
	//3:
	if (DEBUG>1) debug ("224~old hits is "+hits)
	hits = this.getUniqueTags(hits)
	if (DEBUG) debug ("226~unique hits is "+hits)
	//4:
	if (DEBUG>1) debug ("228~Search for "+dump(oSearch) )
	this.mergeTextAndTags()
	if (DEBUG>1) debug ("227~new this.html is:<BR>"+this.html+"<BR>\n")
	if (DEBUG>3) debug ("228~new this.html is:<BR>"+dump(this.html)+"<BR>\n")
	if (DEBUG>1) debug ("highlightTextNodes~229 out hits is "+hits)
printStackTrace().join("<BR><BR>\n")
	return hits
}//StandOff.highlightTextNodes


//Make XML inline with Tags
//Return: Merged all-text, with <SPAN id="ContextDbId" class"..."> inserted
//Mergedfile: ###xml 1119 1129 <span type="species:ncbi:6239">C. elegans</span>
//# tags: [ [from,to,"b",<tag>] OR [to,from,"e",</tag>] ]
//Build the html-string from the last index and forward, insert all so on the fly
StandOff.prototype.mergeTextAndTags = function(){
	//var DEBUG=1
	var lastIndex= this.text.length
	this.so.sort(cmpR)
	if (DEBUG) debug("StandOff.mergeTextAndTags~281")
	this.html=""
	if (DEBUG) debug( "so.size is "+Object.size(this.so) )
	for each(myTag in this.so){
		if (DEBUG>1) debug("240~myTag is "+dump(myTag) )//+"size="+Object.size(myTag))
		this.html = myTag.tag()+this.text.substring(myTag.pos, lastIndex)+this.html
		lastIndex = myTag.pos
		if(DEBUG>2)debug("243~now index="+lastIndex+", this.html is "+this.html+"<BR>")
	}//for each myTag
	this.html = this.text.substring(0, lastIndex)+this.html
	return this
}//StandOff.mergeTextAndTags

//1: Pull-out all proper <TAGs>...</TAGs>, create link from open to close to get offsets in plaintext. From beginning to end, use hash of stacks, for each tagtype
	//tags is array of [ [from,to,"b",<tag attr>] OR [to,from,"e",</tag>] ]
	//b is begin, e is end
StandOff.prototype.pullTags = function(){
	//var DEBUG=1
	this.so = new Array() //Store all matched tags
	this.text = this.html //Store remaining plain text
	var fromTags= new Object() //Hash of Tag -> Stack
	//var openCloseTagRE= new RegExp("^[^<]*<(/)?([^ >]+)(\s+[^>]+)?>")
	var openCloseTagRE= new RegExp('<(/?)([^>\\s]+)(\\s[^>]+)?>')

	//debug("<BR>267~this.text is "+(this.text)+"<BR>\n")
	var match= openCloseTagRE.exec(this.text)
	//debug("899~Match.index is "+(match.index))
	var limit=400
	while (match && limit--){
		//debug("272~this.text.length is "+this.text.length)
		this.text = this.text.replace(match[0], "")
		//Tag(name,end,pos,partnerPos, attr), attr: "class='gene1'"
		var tag = new Tag(match[2], match[1], match.index, 0, match[3])
		//debug("276~tag is "+dump(tag))
		if (tag.end){ //Closing, match with opening tag on stack
			//debug ("278~end-tag? "+dump(tag))
			if ( fromTags[tag.name] && fromTags[tag.name].length ){
				var fromTag= fromTags[tag.name].pop()
				fromTag.partner = tag.pos
				tag.partner = fromTag.pos
				//debug("Tags are "+dump(fromTag)+" and "+dump(tag))
				this.so.push(fromTag) //Ensure only pairs of tags are added, sort later!
				this.so.push(tag)
			}else{
				debug( "viewer.js287~Missing fromTag, fromTags is "+dump(fromTags) )
				debug( "tag was "+dump(tag) )
			}
		}else{
			if (!fromTags[tag.name]){
				fromTags[tag.name] = new Array() //Create stack for this tag name
			}
			fromTags[tag.name].push(tag)
			//debug ("Added tag "+dump(tag))
		}
		//debug ( "tag is "+dump(tag)+", tag.toString() is "+dump(tag.toString() ))
		//debug("298~<BR>this.text is "+(this.text))
		match = openCloseTagRE.exec(this.text)
	}//while more tags
	if (!limit) debug("283~limit EXCEEDED!")
	//debug("this.text is "+this.text)
	//debug ("303~this.so are "+dump(this.so))
	return this
}//StandOff.pullTags


StandOff.prototype.removeDuplicateTags = function(hits){
	if (DEBUG) debug("StandOff.removeDuplicateT~")
	var uniqueTags = new Array()
	this.so.sort(cmpR)
	var lastTag = {}
	for each (nextTag in this.so){
		if( !nextTag.equal(lastTag) ){
			uniqueTags.push(nextTag)
		}else{
			debug( "Removing gene "+lastTag.attr )
			hits--
			//$("#"+lastTag.gene)
		}//else: remove duplicate
	}//for each Tag
	this.so = uniqueTags
	return hits
}//StandOff.removeDuplicateTags()

////////////////////////////////////////////////////////
//END OF StandOff-Class
////////////////////////////////////////////////////////

////////////////////////////////
//CLASS FOR STANDOFF TAGS (Single tag w/pos, matching open/close: partner-pos
////////////////////////////////
//End is either ("" for opening-tag) or ("/" for ending-tag)
function Tag(name, end, pos, partnerPos, attr){
 	if ( !(this instanceof Tag) )
		return new Tag(name, end, pos, partnerPos, attr);
	//PROPERTIES
	this.DEBUG=1
	if (!attr){
		attr=""
	}else{
		if ( ! attr.match(/^\s/) ) attr = " "+attr
	}
	this.name = name
	this.end = end
	this.pos = pos
	this.partner = partnerPos
	this.attr = attr
	return this
}//Tag-Constructor

Tag.prototype.debug = function(msg){
	debug( msg+"~352~Tag("+this.size()+"): "+this.tag() )
}//Tag.debug

//METHODS
Tag.prototype.equal = function(that){
	return (
		this.name == that.name &&
		this.end == that.end &&
		this.pos == that.pos &&
		this.partner == that.partner &&
		this.attr == that.attr
	)
}//Tag.equal(that)

//Return a string that can be put in the right position to make text into html
Tag.prototype.tag = function(){
	return "<"+this.end+this.name+this.attr+">"
}//Tag.toString()

//Alternative that allows inheritance
Tag.prototype.toString = function(){
	if (this.DEBUG) return this.tag()+htmlEncode(this.tag())+"("+this.pos+")"
	return this.tag()
}//Tag.toString (Added to all old and new Tag-object instances

//////////////////////////////////////////////////
//END CLASS "Tag"
//////////////////////////////////////////////////



//Add highlighting CSS-classes for "thisGene" in targetId("haystack"),
//...useFragments (e.g: "false: abstract / true: fragment#":off-set)
//... and update "status"-counter
//Input: targetId (haystack) is ABSTRACT or FULLTEXT etc.
// span_Gene.attr("offsets") contains offsets to be highlighted in each fragment
//... If fragments==1: highlight frag:off-sets, else highlight normal off-sets, 
function addGeneFragmentsHighlight(span_thisGene, targetId, useFragments){
	//var DEBUG=1
	var geneId = span_thisGene.attr("id")
	if (DEBUG){
		debug( "addGeneFragmentsHighlight~396:, targetId="+targetId 
		 +", geneId is "+geneId )
	}
	if (DEBUG) debug( "Allgenes[id][color] is "+ ALLGENES[geneId][COLOR] )
	var color_id = ALLGENES[geneId][COLOR]
	if ( typeof(color_id) === 'undefined' ){
		color_id = (colorCounter+1)%COLORS.length
		span_thisGene.attr("color", color_id)
		debug (" WHY?!" )
	}
	var hits = ALLGENES[geneId][HITS]
	if (!hits){
		hits=0;
		ALLGENES[geneId][HITS] = 0
		debug (" WHY?!" )
	}
	if(DEBUG)debug( "410~span_thisGene is "+span_thisGene.selector )
	var limit=LIMIT
	if (DEBUG){
		debug( "viewer.js413~addGFH geneId is "+geneId
		+" ("+ALLGENES[geneId][NAME]+")" )
	}
	//var fragments = ALLGENES
	//ALLGENES(hash) is {GeneID: {COLOR=>color, CONF=>conf,
	//           FRAGMENTS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] } }
	if(DEBUG>1)debug( "407~Pick fragments from here: "+ JSON.stringify(ALLGENES) )
	$offsetKey = FRAGMENTS // or $offsetKey = OFFSETS?
	//if (useFragments) $offsetKey = FRAGMENTS
	hits = highlightFragments( span_thisGene, color_id, geneId, hits, targetId,
	 useFragments, !useFragments, ALLGENES[geneId][$offsetKey] )
	if (DEBUG) debug( "398~hits is "+hits )
	return hits
}//function addGeneFragmentsHighlight

//ALLGENES(hash) is {GeneID: {COLOR=>color, CONF=>conf,
//           FRAGMENTS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] } }

//Add highlighting CSS-classes for "span_thisGene"
// in "haystack" (e.g. ABSTRACT or FULLTEXT), update "status"-counter
//function addGeneHighlight(span_thisGene, haystackID, statusID){
//}//function addGeneHighlight

function addHighlightOrg(orgDiv){
	var DEBUG=1
	if (DEBUG) debug ("viewer.js442~addHO orgDiv is "+orgDiv.selector)
	if (!orgDiv.attr("color")){
		var color = colorCounter++ % COLORS2.length
		orgDiv.attr("color", color)
	}
	//terms = [ orgDiv.attr("name"), orgDiv.attr("commonName") ]
	var terms= orgDiv.attr("name").split(" ")
	if (orgDiv.attr("commonName")){
		terms = terms.concat( orgDiv.attr("commonName").split(" ") )
	}
	if (orgDiv.attr("division")){
		terms = terms.concat( orgDiv.attr("division").split(" ") )
	}
	var hitString=""
	var newText= orgDiv.html()
	for ( var i=0; i<terms.length; i++){
		debug( "424~ HERE haystack is "+haystack )
		var hits = highlightTerm(terms[i], ORG, color, FULLTEXT, 0 )
		if (hits){
			orgDiv.addClass( ORG+color )
			newText = newText.replace(terms[i], terms[i]
			+"<A HREF=\"javascript:goNext('"+ORG+color+"')\">"+hits+"<\/A>")
		}//if hits
	}//for each term in organism name
	orgDiv.html( newText )
}//function addHighlightOrg







function cleanTextXML(){
	if (DEBUG) $("#DEBUG").html("458~cleanTextXML Loaded file "+filename+"<BR>\n")
	//a hack to display title elements, which are usually moved to <HEAD> (FireFox)
	//debug("Title Count is "+ $(".article-title").length)
	//debug("---"+$(".article-title").html()+"---")

	//$("article-meta").replaceWith( $(ABSTRACT) )
	$(ABSTRACT).prepend("<BR>\n"+ABSTRACT).append("<HR>")

	//This list is reversed... (because of pre-pend)
	//$("#xml").prepend( $("article-title", "article-meta") )
	//$("article-title", "citation").each( function(){
		//$(this).parent().replaceWith( $(this) )
	//})

	//$("contrib-group").remove()
	//$('sec:contains("Authors\' contributions")').remove()
	//$("sec").filter(function() { return /Authors' contributions/.test( $(this).text() ); }).remove(); 
	//$("sec").filter(function() { return $(this).text() == "Authors' contributions" }).remove(); 
	$("ack").remove()
}//function cleanTextXML

/***
* Reverse sort begin tags: smallest "from" is last,
* ...outside after inside: biggest "to" is last
* Reverse sort end tags: smallest "to" is last,
* ... inside after outside: biggest "from" is last
* "Begin tags" after "end tags" in same position
*
* Array[Tag]: [ {pos:From,partner:To,"b",Tag} or {pos:To,partner:From,"e",Tag} ]
***/
function cmpR(a, b){
	return ( a.pos < b.pos ||
	 (a.pos==b.pos && a.type < a.type ) ||
	 (a.pos==b.pos && a.type==b.type && a.partner > b.partner ) ||
	 (a.pos==b.pos && a.type==b.type && a.partner==b.partner && a.type=="b" && a.name<b.name ) ||
	 (a.pos==b.pos && a.type==b.type && a.partner==b.partner && a.type=="e" && b.name<a.name ) ) ? 1 : -1;
}//function cmpR (Reverse Offset-comparator)

/**
 * Function : dump()
 * Arguments: The data - array,hash(associative array),object
 *    The level - OPTIONAL
 * Returns  : The textual representation of the array.
 * This function was inspired by the print_r function of PHP.
 * This will accept some data as the argument and return a
 * text that will be a more readable version of the
 * array/hash/object that is given.
 * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
 */
//Really just an advanced version of JSON.stringify
function dump(arr,level){
	//var DEBUG=1
	if (!DUMP){
		return "JSON:"+( JSON.stringify(arr) )
	}else{
		var dumped_text = "523~DUMP: ";
		var parentObject = arr
		if(!level) level=0
		if( level<10 ){
			if ( typeof(arr)=='object' ) { //Array/Hashes/Objects 
				dumped_text += "{" ;
				for(var item in arr) {
					var value = arr[item]
					if(typeof(value) == 'object') { //If it is an array, function?
						var parentObject = value
						dumped_text += "'"+item+"':"
						if (DEBUG>1) dumped_text += typeof(value)+"==>"
						dumped_text += dump(value,level+1) //RECURSIVE!
					//}else if(typeof(value)=='function'){
					//	dumped_text += "'"+item+"':"
					//	dumped_text += htmlEncode( parentObject.toString()+", <BR>\n" )
					}else{
						if (DEBUG){
							dumped_text += "'" + item + "':"
							if (DEBUG>1) dumped_text += "("+typeof(value)+")"
							dumped_text += value+", \n"
						}else{
							dumped_text = htmlEncode( arr.toString()+"\n" )
						}
					}//if array, else value
				}//for each index
				dumped_text += "}"
				if (level<2) dumped_text += "<BR>" ;
			}else{ //Stings/Chars/Numbers etc.
				if (DEBUG){
					//dumped_text += typeof(arr)+":"+ arr+", \n"
					dumped_text = "===>"+arr.toString()+"<===("+typeof(arr)+")"
				}else{
					//dumped_text = htmlEncode( arr.toString()+"\n" )
					dumped_text = arr.toString()
				}
			}
		}//If not TOO many levels
		if (level=0) dumped_text=htmlEncode( dumped_text )+"<BR>"
	}//If (DUMP=0, JSON), else (DUMP)
	return dumped_text
}//function dump

//SPECIES AND GENES HANDLING
//var textContainerNode = haystack // Parent to all search-nodes
function findAltName(span_thisGene, haystackID){
	//var DEBUG=1
	var geneId = span_thisGene.attr("id")
	var altNames = ALLGENES[geneId][ALTNAMES]
	var color_id = ALLGENES[geneId][COLOR]
	var hits=0
	var alt=0
	if ( altNames ){
		altNames = JSON.parse(altNames)
		if (DEBUG) debug("<BR>length is "+altNames.length+" altNames is "+altNames )
		while(!hits && alt<altNames.length){
			geneName = altNames[alt]
			if (DEBUG) debug( "ALT haystack is "+haystackID )
			if (DEBUG) debug( "geneName is "+altNames[alt] )
			hits = highlightTerm( geneName, color_id, geneId, haystackID, hits )
			if (hits){
				ALLGENES[geneId][NAME] = geneName
				//debug("<BR>FOUND searchTerm is ---"+geneName+"---")
			}//if altName was found
			alt++
		}//while no hits, try other names
	}else{//if altNames
		debug("NO altNames in ")
		debug("span_thisGene is "+JSON.stringify(span_thisGene))
	}//if altnames, else skip
	return hits
}//function findAltName


//GET-METHODS


//GENE NAME LOOKUP
//Get gene information online from EntrezGene or UniProt
//Store information in "context" (e.g. MEDIE/GNSUITE)
//Highlight matched terms in "target" (e.g. ABSTRACT/FULLTEXT)
//... if target is provided (Done by processGeneData)
function getGene(geneId, context, target){
	//var DEBUG=1
	var span_thisGene= $('#'+geneId)
	if (DEBUG) debug("getGene~598 geneId is "+ geneId)

	if ( ALLGENES[geneId][ALTNAMES] ){ //if already loaded:
		showNames(geneId)
		show("loading", false)
	}else{
		if ( geneId.match("^"+ENTREZ+GENE) ){
			geneId = geneId.replace(ENTREZ+GENE,"")
			//debug("getGene~geneId is "+ geneId)
			args ={ 'apikey' : ENTREZKEY,
			        'db' : 'gene',
			        'id' : geneId }
			//search with Callback-function
			if (DEBUG>2) debug( printStackTrace().join("<BR><BR>\n") )
			$.getJSON(esummary, args, function(data){
				processGeneData(data, context, target) } )
		}else if ( geneId.match("^"+SWISSPROT) ){
			//data.result[0].Id = geneId
			var data = {result: [{Id:geneId}]}
			processGeneData(data, context, target)
		}
	}//if already loaded, else load
}//getGene

// Get user selection text on page
function getSelectedText(){
	if (window.getSelection) {
		return window.getSelection();
	}else if (document.selection) {
		return document.selection.createRange().text;
	}
	return '';
}//getSelectedText

function getStorageFilename(pmid){
	var storageURL= "store.php?file="
	//YESdelimitYES.tsv&pmid=2040507&genes=291912"
	var useCase= "NO"
	if ($("#caseButton")[0].checked){
		useCase = "YES"
	}
	var delimit= "NO"
	if ($("#spaceButton")[0].checked){
		delimit = "YES"
	}
	storageURL = storageURL+"case"+useCase +"delimit"+delimit +".tsv&pmid="+pmid
	//debug ("store to "+storageURL)
	return storageURL
}//function getStorageFilename

function getTpFpFnFilename(xx, pmid){
	//storageURL = "http://www-tsujii.is.s.u-tokyo.ac.jp/satre/biocreative/store.php?file="
	//YESdelimitYES.tsv&pmid=2040507&genes=291912"
	var storageURL= "store.php?file="
	var useCase= "NO"
	if ($("#caseButton")[0].checked){
		useCase = "YES"
	}
	var delimit= "NO"
	if ($("#spaceButton")[0].checked){
		delimit = "YES"
	}
	storageURL = storageURL+"case"+useCase +"delimit"+delimit +".tsv&pmid="+pmid
	debug("Using storage url "+storageURL)
	return storageURL
}//function getStorageFilename

//SPECIES (Organism Taxonomy) NAME LOOKUP
//Old: return orgName.replace(/[#;&,.+*~:!^$()=>|\/\'\"\[\] ]/g,"_") //JQuery Special Characters
function getOrgId(orgName){
	//http://eutils.ncbi.nlm.nih.gov/entrez/eutils/espell.fcgi?db=taxonomy&term=mus_muskulus //SpellCheck	
	//http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=taxonomy&term=mus_musculus //Get ID
	//http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=taxonomy&id=10090 //Get Names	
	debug("orgName to ID is NOT implemented "+orgId)
	return "orgName to ID is NOT implemented!"
	//return orgName
}//function getOrgId

//Get "CommonName" and "ScientificName" OrgNames from this orgDiv
//var names=[] //"CommonName" and "ScientificName"
//Input, jQuery selector set
function getOrgNames(orgDiv, context){
	//orgDiv = $(orgDiv)
	//debug( "orgDiv.attr(id) is "+ orgDiv.attr("id") )
	if (orgDiv){
		//debug( JSON.stringify(orgDiv) )
		if ( !orgDiv.attr("commonName") ){
			var args={ 'apikey' : ENTREZKEY,
			        'db' : 'taxonomy',
			        'id' : orgDiv.attr("id").replace(context+ORG,"") }
			//search with Callback-function
			$.getJSON(esummary, args, function(data){
				//debug( "<BR>get names from "+JSON.stringify(data) )
				if (data.entrezajax.error){
					debug("getOrgNames: "+data.entrezajax.error_message)
				}
				if (data.result[0].CommonName){
					orgDiv.attr( {"commonName": data.result[0].CommonName} )
					orgDiv.html( orgDiv.html().replace( ":", " ("+data.result[0].CommonName+"):" ) )
				}
				if (data.result[0].Division){
					orgDiv.attr( {"division": data.result[0].Division} )
					orgDiv.html( orgDiv.html().replace( ":", " ("+data.result[0].Division+"):" ) )
				}
				//debug("<BR>...Got common name for "+orgDiv.attr("name") )
			} )
		}//if names are missing, asynchronyous(!) update
	}else{
		debug("<BR>missing orgDiv to read/write orgNames!")
	}
	return "The results are added asynchronously to the orgDiv!\n"
}//getOrgNames

//return (after possibly making it for the first time) the matching species-div
function getSpecies_coll(id, orgName, context){
	if (!id || !orgName){
		debug("->missing id is "+id+", orgName is "+orgName)
	}else{
		//If organism(in context)-div does not already exist
		if ( $('#'+context+ORG+id).length == 0 ){ //If NEW Organism-Div
			//Move Predicted Specie to TP or FP
			var specId = "species_ncbi_"+id
			var predicted = $("#species").find("."+specId)
			if (predicted.length){
				$("#"+context+ORG).append(predicted)
			}else{
				var specie= "<SPAN class='"+specId+"' name='"+specId+"' onclick=\"goNext('"+ORG+id+"')\">\n"
				if (pass){
					specie = specie+"<A HREF='store.php?pmid="+pmid+"&species="+specId+"&file=results/FN'>FN</A>\n"
				}
				specie = specie+id+"() \n"
				$("#FN").append(specie)
			}//If TP, else make FN
			
			//Make ONE "div" for each species
			var newDiv = $("<div><\/div>").attr( {'id':context+ORG+id, 'name':orgName} )
			newDiv.append("<INPUT TYPE=CHECKBOX CLASS=speciesButton ID=button"+id+" onClick=showHideHighlight(this)>")
			newDiv.append( orgName+"("+id+"): " )
			getOrgNames(newDiv, context) //Asynchronous! Adds the newDiv to the right DIV-tab
	
			//Count and insert sorted in existing organism-DIVs
			var correctPos = 0
			var next_coll = $('#'+context+GENES).find('div')
			//debug("<BR>length is "+ next_coll.length +" current is "+ newDiv.attr("name") )
			while ( correctPos<next_coll.length && newDiv.attr("name") > next_coll[correctPos].getAttribute("name") ){
				//debug(" next name is "+ next_coll[correctPos].getAttribute("name") )
				correctPos++
			}
			//Insert at the end
			if (correctPos == next_coll.length){
				$('#'+context+GENES).append(newDiv)
			}else{
				newDiv.insertBefore(next_coll[correctPos])
			}
		}//If NEW species
	}//If not missing SpecID
	return $('#'+context+ORG+id) //Return existing, or newly created, orgDiv
}//getSpecies_coll

function goNext(termClass){
	var DEBUG=1
	//var termClass= termClass+""
	if (DEBUG) debug ("<BR>viewer.js778~goNext term is "+termClass)
	var pos= $(window).scrollTop()
	var viewHeight= $(window).height()
	var htmlHeight= $(document).height()
	if (DEBUG){ debug( "<BR>position is "+pos+", view-height is "+viewHeight
		+", html height is "+htmlHeight )
	}

	//var terms= $("span[name="+termClass+"]")
	var terms = $("span."+termClass)
	if(DEBUG)debug(", Terms.length is "+terms.length)
	var next=0
	//Find next not visible item to scroll to (-20: almost hidden at the bottom...)
	while ( next<terms.length && $(terms[next]).offset().top<pos+viewHeight-20 ){
		if(DEBUG)debug (", "+Math.round($(terms[next]).offset().top))
		next++
	}
	if (next<terms.length){
		if(DEBUG)debug (", next is "+next+", pos is "+Math.round($(terms[next]).offset().top))
		$("html,body").animate({ scrollTop: $(terms[next]).offset().top })
	}else{
		$("html,body").animate( {scrollTop:0} )
	}//if-else: bottom or last element
	if(DEBUG)debug("PHAIL?")
}//function goNext

function hideHijack(divId){
	$("#"+divId).hide();
	return void(0);
}//function hideHijack


//ALLGENES(hash) is {GeneID: {COLOR=>color, CONF=>conf,
//           FRAGMENTS=>[segment(i/ABSTRACT), [off-set, ..., off-set] ] } }
//Highlight terms in targetId FULLTEXT(GNSUITE) or ABSTRACT(MEDIE)
function highlightFragments( span_thisGene, color_id, geneId, hits, targetId,
 useFragments, useOffsets, fragments ){
	//var DEBUG=1
	if (DEBUG){
		debug( "highlightFragments~818: targetId --"+targetId+"-, geneId -"+geneId
		 + "-, useFragments --"+ useFragments
		 + "--, useOffsets --"+useOffsets+"--" )
		if (DEBUG>1){
			debug( "highlightFragments~ fragments is "+JSON.stringify(fragments) )
 			debug( "highlightFragments~ ALLGENES.keys is "+$.keys(ALLGENES) )
 		}
 	}//DEBUG
	if (targetId==ABSTRACT){
		hits = highlightOneFragment( span_thisGene, color_id, geneId, hits,
		 ABSTRACT, fragments[ABSTRACT], useOffsets )
	}else if (useFragments){
		for (fragment_i in fragments){ //for ... in: returns keys
			if (fragment_i != ABSTRACT){
				hits = highlightOneFragment( span_thisGene, color_id, geneId, hits,
				 FRAGMENT+fragment_i, fragments[fragment_i], useOffsets )
				if (DEBUG){
					debug( "highlightFragments~844 fragment_i is "+fragment_i )
				}//DEBUG
			}//Skip Abstract in useFullTextMode
		}//for each fragment
	}else{
		debug( "840~ Skip fragment_i="+fragment_i+" i "
		+" fulltextFragments="+useFragments )
	}
	return hits
}//function highlightFragments







function highlightOneFragment( span_thisGene, color_id, geneId, hits, textFragmentId, textFragmentOffsets, useOffsets ){
	//var DEBUG=1
	if (DEBUG){
		debug( "viewer.js855~highlightOneFragment fragId is --"+textFragmentId+"--")
	}
	var context=$("#"+textFragmentId)
	if (DEBUG) debug("textFragmentOffsets is "+dump(textFragmentOffsets))
	//for each ... in: returns values. term is "term" or "off-set"
	for each (term in textFragmentOffsets){
		if (DEBUG) debug ("highlightOneFragment~862~Term is "+term)
	//for (term in textFragmentOffsets){
		if (useOffsets){
	 		if (DEBUG) debug( "highlightFragT~866 new Offset is "+dump(term) )
	 		term = new Offset(term)
	 		if (DEBUG) debug( "highlightOneFragment~868~new Offset is "+dump(term) )
		}else{ //Use array of terms
			if (DEBUG) debug( "highlightOneFragmenterms~870 term is "+dump(term) )
		}
		if (DEBUG>1) debug("872~geneId is "+geneId+", context is "+dump(context))
		if( $("span.term"+geneId, context).length ){
			if (DEBUG) debug( "877~Done"+$("span.term"+geneId, context).length )
		}else{ //Not already highlighted
			hits = highlightTerm( term, color_id, geneId, textFragmentId, hits )
			if (DEBUG) debug( "highlightOF~862: hits is "+hits )
			var matches = {}
			if ( span_thisGene.attr("title") ){
				matches = JSON.parse(span_thisGene.attr("title"))
			}
			matches[term] ? matches[term]++ : matches[term]=1
			span_thisGene.attr( "title", JSON.stringify(matches) )
			if (DEBUG) debug( "matches is "+JSON.stringify(matches) )
		
			span_thisGene.attr(HITS,hits)
			span_thisGene.addClass(geneId)
			updateHits(span_thisGene, hits)
			return hits
		}//if not already highlighted
		if (DEBUG) debug( "879~term is --"+term+"--hits is --"+hits+"--<BR>" )
	}//for each term in fragment_i
	return hits
}//function highlightOneFragment

//Highlight "oSearch" (String or Offset)
//... with "color", in "#textContainerID"
// ...with color(term:X/org:Y), geneId(EntrezGeneX/ncbi_species_Y/etc)
function highlightTerm(oSearch, color, termId, textContainerID, hits){
	//var DEBUG=1 //"var" in function means local
	//color: "term0", termId: "entrezgene5893" //Confusing!
	if (DEBUG) debug( "highlightTerm~891, old hits is "+hits )
	if (DEBUG>1){
		debug (printStackTrace().join("<BR><BR>\n\n"))
		debug( "highlightT~color is "+color+", and termId is "+termId )
	}
	var textContainerNode= $("#"+textContainerID)[0]
	if (DEBUG){
		debug( "highlightTerm~898 search for "+JSON.stringify(oSearch)
		 + " in "+textContainerID )
		//debug( "$ID.length is "+$("#"+textContainerID).length )
	}
	if (oSearch && textContainerNode){
		var standoff = new StandOff("", [], textContainerNode.innerHTML)
		if ( standoff.html ){
			//Tag(name,end,pos,partnerPos, attr), attr: "class='term1'"
			var tag = new Tag( "span", "", 0, 0, 
				" class='"+HIGHLIGHT+" "+COLOR+color+" "+termId+"'")
			//debug( "909~tag is "+tag )

			hits = standoff.highlightTextNodes(oSearch, tag, hits)
//printStackTrace().join("<BR><BR>\n\n")
			textContainerNode.innerHTML = standoff.html
			if (DEBUG) debug( "913~ new hits is --"+hits+"--" )
			if (DEBUG>1){ debug( "html:::"+standoff.html ) }
		}else{
			debug("textEl.innerHTML not found, NOT textEl is"+textContainerNode.id)
		}//if browser supports this...?
	}else{
		if (oSearch){
			debug("highlightTerm~914: Not found textContainerNode: "+textContainerID)
			//debug("highlightTerm~: textContainerNode is "+textContainerNode)
		}else{ debug("NO oSearch GIVEN!") }
	}//if(oSearch and container exist)
	if (DEBUG) debug( "highlightTerm~928 hits is --"+hits+"--" )
	return hits
}//function highlightTerm






function htmlDecode(value){ 
	//var DEBUG=1
  return $('<div/>').html(value).text(); 
}//function htmlDecode

function htmlEncode(value){ 
	//var DEBUG=1
	if (DEBUG>1) debug( "940~htmlEncode this: "+value)
	if (DEBUG>2) alert( printStackTrace().join("<BR><BR>\n\n") )
	if (!value) return "Undefined!"
	else return $('<div/>').text(value).html(); 
}//function htmlEncode

function initAnnotationHandlers(){
	if (DEBUG) debug("920~initAnnotationHandlers this is "+this)
	$('#text').dblclick(function() {
		$('textarea#newname').val( getSelectedText() );
		$("html,body").animate( {scrollTop:0} )
  });
  
	$('span').dblclick(function() {
		$('textarea#newname').val( $(this).attr('name') );
		$("html,body").animate( {scrollTop:0} )
  });
  
	// Bind the click handler of some button on your page
	$('#annotate').click(function(evt){
		$("#annotationresults").html("<BR> Hits: ")
		lookupGenes( $('textarea#newname').val() );
		evt.preventDefault();
	});
}//initAnnotationHandlers

//Insert Gene Sorted in Species
function insertGeneInSpecies(span_thisGene, orgDiv){
	//var DEBUG=1
	geneId = span_thisGene.attr("id")
	if (DEBUG){
		debug( "orgDiv is "+orgDiv.selector+", length is "+orgDiv.length )
		debug( "thisGene.attr(name) is "+ ALLGENES[geneId][NAME]+":<BR>" )
	}
	if ( orgDiv.find("#"+geneId).length ){
		if (DEBUG) debug("936~Don't re-insert!")
		if (DEBUG) alert( printStackTrace().join("<BR><BR>\n\n") )
	}else{
		var geneSpan_coll = orgDiv.find('span')
		if ( geneSpan_coll.length ){ //if EXISTING spans: insert sorted
			var correctPos = 0
			while ( correctPos < geneSpan_coll.length
			 && ALLGENES[geneId][NAME]
			  > geneSpan_coll[correctPos].getAttribute('name') ){
				correctPos++
			}
			if(correctPos>=geneSpan_coll.length
			 || geneSpan_coll[correctPos].length==0){
				orgDiv.append(" ").append(span_thisGene)
			}else{
				//debug( "FIXED: DISAPPEARING? when adding before SELF!!" )
				span_thisGene.insertBefore(geneSpan_coll[correctPos])
			}
		}else{//leave inserted single gene_span alone
			//debug( "First-Span: thisGene is "+span_thisGene.attr("id") )
			orgDiv.append(span_thisGene)
		}//if multiple genes: sort, else append
	}//if first time: insert
}//function insertGeneInSpecies


//XML LOADING 
function loadXMLDoc(myURL){
  var xhttp= getHTTPObjectType("xml")
	xhttp.open("GET",myURL,false);
	try{
		xhttp.send("");
	}catch(e){
		document.body.appendChild( document.createTextNode("ERROR: "+ myURL + e) );	
		alert("ERROR: "+ myURL+e+"<BR\>");
	}
	return xhttp.responseXML;
}//function loadXMLDoc

function getHTTPObjectType(type){
  var http_request= false;
  if (window.XMLHttpRequest){ //Mozilla, Safari, ...
		http_request = new XMLHttpRequest();

		if (type=="html" && http_request.overrideMimeType){
			http_request.overrideMimeType('text/html');                
		}
	}else if (window.ActiveXObject) { // IE
		try {
	  	http_request = new ActiveXObject("Msxml2.XMLHTTP");
		}catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			}catch (e) {}
		}
	}
	return http_request;
}//function getHTTPObjectType

var isBusy = false;
var gHttp = getHTTPObjectType("html");
function setResponseHtml(pUrl, pDiv) {
	var lHttp = getHTTPObjectType("html");
	var lUrl = pUrl;
	lUrl = lUrl.replace("+","%2b");
	if (isBusy) {
		lHttp.onreadystatechange = function () {}
		lHttp.abort();
	}
	lHttp.open("GET", lUrl , true);        
	lHttp.onreadystatechange = function(){
		getHttpResponseText(pDiv, lHttp);
	};

	if (window.XMLHttpRequest) { // Mozilla check
		if (!isBusy) { // getting Javascript errors (only in Mozilla) this check prevents error
			isBusy = true;            
			lHttp.send(null);
		}
	}else{ //just proceed IE does not have issue!
		isBusy = true;            
		lHttp.send(null);
	}
}//function setResponseHtml

function getHttpResponseText(pDiv, pHttp) {
	var lHttp = pHttp;
	if(lHttp == null){ // just in case!
		lHttp = gHttp;
	}
	if (lHttp.readyState == 4) {
		isBusy = false;
		var status = "";
		try{
			status = lHttp.statusText;
			if (lHttp.status == 200) {
			    var content = lHttp.responseText;
			    document.getElementById(pDiv).innerHTML = content;            
			}
		}catch(e) {
			status = "Trouble accessing it";
			document.getElementById(pDiv).innerHTML = e.message;
		}            
	}else{
		document.getElementById(pDiv).innerHTML = "<b>Updating Database...</b>";
		return;
	}
}//getHttpResponseText


function lookupGenes(text){
	var args={ 'apikey':ENTREZKEY, 'db':'gene', 'term':text }
	$.getJSON(esearch, args, function(data) {
		//$("#DEBUG").html( JSON.stringify(data) )
		if (data.entrezajax.error){
			debug("lookupGenes: "+data.entrezajax.error_message)
		}else{
			//$("#DEBUG).html( JSON.stringify(data)+"<BR><BR>" )
			$("#annotationresults").append(data.result.Count)
			for (i in data.result.IdList){
				args ={ 'apikey':ENTREZKEY, 'db':'gene', 'id':data.result.IdList[i] }
				$.getJSON(esummary, args, function(data) {
					var geneId = data.result[0].Id
					var geneName = data.result[0].Name
					var nomName = data.result[0].NomenclatureName
					var alias = data.result[0].OtherAliases
					var designation = data.result[0].OtherDesignations
					var descript = data.result[0].Description
					var orgName = data.result[0].Orgname
					var orgId = data.result[0].TaxID
					$("#annotationresults").append("<BR>"+orgName+"("+orgId+"):"+geneName)
					//.append("("+nomName+", "+alias+", "+designation+", "+descript)
				})
			}
		}//if no errors
	} )
}//lookupGenes

//Return an array of alternative name-strings
function makeAltNames( geneId, allGenes ){
	//var DEBUG=1
	if(DEBUG)debug("1101~makeAltNames for ---"+geneId+"---<BR>\n")
	var geneName = allGenes[geneId][NAME]
	var nomName = allGenes[geneId][NOMEN]
	var alias = allGenes[geneId][ALIAS]
	var designation = allGenes[geneId][DESIGNATE]
	var descript = allGenes[geneId][DESCRIPT]

	//Find less ambigious gene names
	if (geneName.length <2){ allGenes[geneId][NAME] = nomName }

	//All usefull fields: Multiple Designations, multiple Aliases, and the entiry Description
	var altNames = []
	if (designation){ altNames = altNames.concat( designation.split(/[|;] ?/) ) }
	if (alias){ altNames = altNames.concat( alias.split(", ") ) }
	if (descript){ altNames = altNames.concat(descript) }

	//filter
	//debug("<BR>altnames length is "+altNames.length)
	altNames = jQuery.grep( altNames, function(el, i){
		//debug(" el.length is "+el.length)
		return (el.length>1 && el.length<30)
	} )
	//debug("altnames length is "+altNames.length)
	altNames.sort(sortStringLength)
	//debug("<BR>Find names: "+altNames)
	allGenes[geneId][ALTNAMES] = JSON.stringify(altNames)
}//makeAltNames

/*
function onlyAbstract(onlyAbs){
	removeGeneHighlights()
	if (onlyAbs){
		$("#"+FULLTEXT).html( $(ABSTRACT) )
		jQuery('#'+context+GENES).find("span[id^=gene]").each( function(){
			debug ("<BR>FOUND finding "+this.id)
			addGeneFragmentsHighlight( $(this), ABSTRACT, FOUND )
		} )
	}else{
		loadText()
	}
}//function onlyAbstract
*/

//Callback-function, from getGene
//Initialize The Gene-DIV
//Add HIGHLIGHTING, if target is provided, count matches in "contextFOUND"
function processGeneData(data, context, target) {
	//var DEBUG=1
	if (DEBUG){
		debug( "processGeneData~1144: Context-"+context+"-, target-"+target+"-" )
		if (DEBUG>1) debug( ""+JSON.stringify(data)+"<BR>" )
	}
	if (data.entrezajax){
		if (data.entrezajax.error){
			debug("processGeneData: "+data.entrezajax.error_message)
		}else{
			var geneId = ENTREZ+GENE+data.result[0].Id
			var geneName = data.result[0].Name
			var orgName = data.result[0].Orgname
			var orgId = data.result[0].TaxID
			ALLGENES[geneId][NOMEN] = data.result[0].NomenclatureName
			ALLGENES[geneId][ALIAS] = data.result[0].OtherAliases
			ALLGENES[geneId][DESIGNATE] = data.result[0].OtherDesignations
			ALLGENES[geneId][DESCRIPT] = data.result[0].Description
	
			var span_thisGene = $('#'+geneId)
			if ( !ALLGENES[geneId][NAME] ){ //First time to process this gene-link
				var conf = ALLGENES[geneId][CONF]
				conf = Math.round(10*conf)/10
				if (DEBUG>1){
					debug( "processGeneData~ ALLGENES.length is "+ Object.size(ALLGENES) )
					debug(": "+$.keys(ALLGENES) )
					debug ("ALLGENES["+geneId+"] is "
					+dump(ALLGENES[geneId]))
				}//if debug
				//var hits = span_thisGene.attr("offsets").split(/[, ]+/).length
				var fragCount = Object.size(ALLGENES[geneId][FRAGMENTS])
				//debug ("conf is "+conf+", Fragcount is "+fragCount)
				ALLGENES[geneId][NAME]=geneName
				ALLGENES[geneId][OFFICIAL]=geneName
				ALLGENES[geneId][ORG+ID]=orgId
				ALLGENES[geneId][ORG+NAME]=orgName
				ALLGENES[geneId][CONF]=conf
				ALLGENES[geneId][HITS]=fragCount
				span_thisGene.attr("alt",geneId).text( geneName + "("+fragCount+") " )
				makeAltNames( geneId, ALLGENES )
				var statusSpan= $("#"+context+FOUND)
				statusSpan.html( parseInt(statusSpan.text()) +1 )
			}else{
				if(DEBUG)debug("viewer.js~1207 ID --"+geneId+"-- already has attr(name)")
			}
			//HIGHLIGHTING
			if (target && context==MEDIE){
				//addGeneFragmentsHighlight( span_thisGene, ABSTRACT, false )
			}else if (target){ // && context == GNSUITE
				addGeneFragmentsHighlight( span_thisGene, FULLTEXT, true )
			}else{
				debug( "viewer.js1190~ MISSING TARGET is "+target )
			}
			showNames(geneId)
		
			var orgDiv = getSpecies_coll(orgId, orgName, context)
			insertGeneInSpecies(span_thisGene, orgDiv)
			GENETABLE.insertGene(span_thisGene) //in table.js
		}//If EntrezGene.data was received
	}else{
		debug( "processGeneData~1199: Strange data was: "+dump(data) )
	}
	if (! $("#"+GNSUITE+"ids").find("span").length ){
		show("loading", false)
	}else{ //If all gene-ids have been processed already
		if (DEBUG) debug ("viewer.js1229~processGeneData Still Loading!")
	}
}//processGeneData: callback function on returned data

function processTable(id){
	show(id, "switch")
	var table= $('#'+id)
	if ( table.attr("processed") != "true" ){
		//show("loading", true)
		table.prepend("Showing all tables...<BR>")
		jQuery('#'+context+GENES).find("span[id^=gene]").each( function(){
			addGeneFragmentsHighlight( $(this), "table", true )
		} )
		table.attr("processed",true)
		//show("loading", false)
	}//if not already processed
}//function processTable

//Remove link color, hits-count, found, alt-GeneName(->official)
function removeGeneHighlights(targetId, geneId){
	var DEBUG=1
	if (!geneId){
		if (DEBUG) debug( "viewer.js1251~removeGHl REMOVE ALL" )
		//Remove for all colors!
		$("#found").text(0)
		//$("#foundTab").text(0)
		//remove span-tags, leave the text
		$("#"+targetId).find("SPAN."+HIGHLIGHT).each( function(){
			$(this).replaceWith($(this).text())
		} )
	}else{ //Remove specific geneId
		$("#"+targetId).find("SPAN."+geneId).each( function(){
			$(this).replaceWith($(this).text())
		} )
	}//If all, else specific Gene
//$('#'+context+GENES).find("span[class*=color]").attr(HITS, 0).each(function(){
//		$(this).removeClass(this.id).attr("name", this.getAttribute("official") )
//		.text(this.getAttribute("official")).find("A").remove()
//		//debug ("new (original) name is "+ $(this).attr("name") )
//	})
}//function removeGeneHighlights

//debug("Remove HIGHLIGHT terms: "+	$("."+HIGHLIGHT).length )
function showHideHighlight(button){
	var DEBUG=1
	if (DEBUG){ debug ("viewer.js1276~showHideHighlight ")
		debug( "button.id is "+button.id+ " className is "+button.className )
	}
	//If: Set All-SpeciesButtons at the same time
	if (button.id == "allSpeciesButton"){
		$("input.speciesButton").each( function(){
			this.checked=button.checked; showHideHighlight(this);
		} )
	//Else: Set one specified Species-button
	}else if (button.className == "speciesButton"){
		orgDiv = $("#"+ORG+button.id)
		//if ( button.checked ){
			showHideHighlightOrg(button)
		//}//if checked, else un-check
	//Else: flip allGenes color-buttons
	}else{
		showHideHighlightGene(button.checked, "")
	}//if species, else genes
}//function showHideHighlight

//Remove individual gene class-highlighters,
// or flip the background transparancy for all genes
function showHideHighlightGene(set, geneId){
	//var DEBUG=1
	var color = ALLGENES[geneId][COLOR]
	if (DEBUG){
		debug("viewer.js1263~set is "+set+", term is "+geneId+", color is "+color)
	}//if DEBUG
	if (geneId){
		if ( set ){
			//Add color-class to all spans
			$("SPAN."+geneId).addClass("color"+color+" "+HIGHLIGHT)
			.attr("title", geneId)
			if(DEBUG)debug( "SET term class='"+geneId+"', length is "
			 + $("SPAN."+geneId).length )
		}else{//un-check...
			$("SPAN[name='term"+geneId+"']").removeClass( geneId )
			if (DEBUG) debug (", UNSET SIZE name-term  is "
			 + $("SPAN[name='term"+geneId+"']").length)
		}//if checked, else un-checked
	}else{ //switch all terms
		if ( set ){
			$("SPAN[name^='term']").addClass( function(i,tag){
				debug( "Add "+$(this).attr("name") )
				return $(this).attr("name")
			} )
		}else{//un-check...
			$("SPAN[name^='term']").removeClass( function(i,tag){
				//debug("removing "+$(this).attr("name"))
				return $(this).attr("name") 
			})
		}//if checked, else un-checked
	}//if one class, else all terms
}//function showHideHighlightGene

function showHideHighlightOrg(button){
	var id= button.id.replace("button", "")
	var orgDiv= $("#"+ORG+id)
	//if first time
	if (!orgDiv.attr("color")){
		addHighlightOrg(orgDiv)
	}else{
		//un-check... Hide colored background
		var ourColor = orgDiv.attr("color")
		var spans= $("SPAN[name^='"+ORG+ourColor+"']")
		if (button.checked){ //re-color
			//debug(", adding is "+ORG+ourColor+", count is "+spans.length)
			spans.addClass( ORG+ourColor )
			orgDiv.addClass( ORG+ourColor )
		}else{ //un-color
			spans.removeClass( ORG+ourColor )
			orgDiv.removeClass( ORG+ourColor )
		}//if check, else un-check
	}//if new, else re-color
}//function showHideHighlightOrg

function showNames(geneId){
	//var DEBUG=1
	//What about Lineage-names? For highlighting more terms?
	if(DEBUG>1)debug( "viewer.js~1325 "+printStackTrace()[3] )
	if(DEBUG)debug("viewer.js~1326 geneId is "+geneId )
	var gene= $('#currentGene')
	gene.html( "\n<SPAN id='switch_term"+geneId
		+"' class='geneSwitch "+geneId+"'>"+ALLGENES[geneId][NAME]+"<\/SPAN>" )
	if(DEBUG>1)debug( "length is "+$("#switch_term"+geneId).length )
	gene.append(" (ID:"
		+"<A HREF="+entrezURL+geneId.match(/(\d+)/)[1]+">"+geneId+"<\/A>)<BR>"
		+"<A HREF='"+taxonomy+ALLGENES[geneId][ORG+NAME]+"'>"
		+ ALLGENES[geneId][ORG+NAME]+"<\/A>"
	)
	showHideHighlightGene(true, geneId)

	$("#switch_term"+geneId).toggle(
	 function(){ showHideHighlightGene(false, geneId)},
	 function(){ showHideHighlightGene(true, geneId) })
	//$('#names').html( "\nOTHERNAMES<BR>"+ ALLGENES[geneId][ALT]") )

	$('#names').html( "\n<UL>")
		.append( "<LI>\n<B>NOMENCLATURE-NAME</B><BR>"+ ALLGENES[geneId][NOMEN] )
		.append( "<LI>\n<B>OTHER-ALIAS</B><BR>" + ALLGENES[geneId][ALIAS] )
		.append( "<LI>\n<B>OTHER-DESIGNATIONS</B><BR>"+ALLGENES[geneId][DESIGNATE])
		.append( "<LI>\n<B>DESCRIPTION</B><BR>"+ALLGENES[geneId][DESCRIPT] )
		.append( "</UL>")
}//function showNames


function showNames_old(geneSpan){
	//var DEBUG=1
	//What about Lineage-names? For highlighting more terms?

	if(DEBUG)debug( "viewer1313~"+printStackTrace()[3] )
	var termId= geneSpan.attr("id")
	if(DEBUG)debug("viewer1311~geneSpan is "+geneSpan[0].id+", term is "+termId )
	var gene= $('#currentGene')
	gene.html( "\n<SPAN id='switch_term"+termId
		+"' class='geneSwitch "+termId+"'>"+geneSpan.attr("name")
		+"<\/SPAN>" )
	if(DEBUG>1)debug( "length is "+$("#switch_term"+termId).length )
	gene.append(" (ID:"
		+"<A HREF="+entrezURL+geneSpan.attr("alt")+">"
		+ geneSpan.attr("alt")+"<\/A>)<BR>"
		+"<A HREF='"+taxonomy+geneSpan.attr("orgName")+"'>"
		+ geneSpan.attr("orgName")+"<\/A>"
	)
	showHideHighlightGene(true, termId)

	$("#switch_term"+termId).toggle(
	 function(){ showHideHighlightGene(false, termId)},
	 function(){ showHideHighlightGene(true, termId) })
	//$('#names').html( "\nOTHERNAMES<BR>"+ geneSpan.attr("altNames") )

	$('#names').html( "\n<UL>")
		.append( "<LI>\n<B>NOMENCLATURE-NAME</B><BR>"+ geneSpan.attr("nomName") )
		.append( "<LI>\n<B>OTHER-ALIAS</B><BR>" + geneSpan.attr("alias") )
		.append( "<LI>\n<B>OTHER-DESIGNATIONS</B><BR>"+geneSpan.attr("designation") )
		.append( "<LI>\n<B>DESCRIPTION</B><BR>"+geneSpan.attr("descript") )
		.append( "</UL>")
}//function showNames_old


//MAGIC
//visible=true means show, visible=false means hide, visible="switch" means change visible/hidden
function show(id, visible, remove){
	//debug("SHOWTIME!<BR>")
	var which = $("#"+id)[0]
	if (which){
		if (visible=="switch"){
			//set on, if already off
			visible = (which.style.display == "none" || which.style.visibility == "hidden")
			//debug(" switching "+id+" to "+ visible)
		}
		//Turn on, or off
		if (visible){
			which.style.display="block"
			which.style.visibility="visible"
		}else{
			//debug("Turning off "+which)
			//if (remove){
				which.style.display="none"
			//}
			//which.style.visibility="hidden"
		}//if hide, else show
	}else{
		debug("viewer.js1371~show No such ID is '"+id+"'")
	}
}//function show


//SORTING: Return Longer Strings (b) first
function sortStringLength(a,b){
	return b.length - a.length;
}//function sortStringLength


function storeGenes(){
	var foundGenes = $("#"+GENES).find("span.geneswitch[hits!='0']")
	debug ( "storeGenes: <BR>PMID is "+$("#aFile").text().replace(".pdf","") )

	var geneIds = Array()
	foundGenes.each (function(){ geneIds.push ((this.id).replace("gene","")) })
	geneIds.sort( function(a,b){return a-b} )
	var myURL=getStorageFilename($("#aFile").text().replace(".pdf","")) +"&genes="+geneIds.join(" ")
	//debug( "<BR>Filename: "+myURL )
	setResponseHtml(myURL,'showme');
}//function storeGenes


//SETTINGS
function switchCase(button){
	$("#DEBUG").html("Switch Case-sensitivity ="+button.checked)
	if (button.checked){
		iCase=""
	}else{
		iCase="i" //Ignore Case
	}
	removeGeneHighlights()
	jQuery('#'+context+GENES).find("span[id^=gene]").each( function(){
		debug ("<BR>FOUND "+this.id)
		addGeneFragmentsHighlight( $(this), FULLTEXT, true )
	} )
}//function switchCase

function switchSeparated(button){
	//var DEBUG=1
	$("#DEBUG").html("Switch separation Delimiters ="+button.checked)
	//show("loading", true)
	if (button.checked){
		space = separators //Require separators before and after
	}else{
		space="" //any neigbor character
	}
	removeGeneHighlights()
	jQuery('#'+context+GENES).find("span[id^=gene]").each( function(){
		debug ("<BR>FOUND "+this.id)
		addGeneFragmentsHighlight( $(this), FULLTEXT, true )
	} )
	show("loading", false)
}//function switchSeparated

function updateHits(span_thisGene, hits){
	var geneId = span_thisGene.attr("id")
	if (DEBUG) debug( "1321~hits is "+hits+", geneId is "+geneId)
	var geneName = ALLGENES[geneId][NAME]
	var official = span_thisGene.attr("official")
	
	span_thisGene.find("SPAN[id^='hits_"+geneId+"']").remove()
	if (hits){
		statusSpan = $("#"+HITS+"_"+geneId)
		if( statusSpan.length ){
			//debug( "1178~statusSpan is #"+HITS+"_"+geneId )
			statusSpan.text( hits )
		}else{
			span_thisGene.append("(<A id='hits_"+geneId
			 + "' HREF=\"javascript:goNext('"+geneId+"')\">"+hits+"<\/A>) ")
		}
		//debug ( " official Name is "+official+", geneName is "+geneName )
		if (geneName != official){
			//debug ( ", new altName geneName is "+geneName )
			span_thisGene.find("#hits_"+geneId).prepend("::"+geneName)
		}//if alternative name being used
	}else{
		span_thisGene.append("<A id='hits_"+geneId+"'>(0) <\/A>")
	}//if hits, else 0

	$("#total").data("processed", $("#total").data("processed")+1 )
	if ( $("#total").text() == $("#total").data("processed") ){
		//debug( "Processed is "+$("#total").data("processed") )
		storeGenes()
		$("#total").data("processed", 0)
	}//Store genes to file... (via php)
}//function updateHits
/*
	if ( hits>0 && statusID ){
		var statusSpan= $("#"+statusID)
		statusSpan.html( parseInt(statusSpan.text()) +1 )
	}//if hits, by term stored in span.attr("name")
*/



/*
Querying UniProt

http://www.uniprot.org/uniprot/?query=accession:P33203+or+interpro+IPR014002&format=tab&columns=id,database%28interpro%29

http://www.ebi.ac.uk/cgi-bin/dbfetch?db=accession:P33203+or+interpro+IPR014002&format=tab&columns=id,database%28interpro%29



$('#divid').load('myphp.php',{'par1' : 'value1', 'par2' : 'value2'});

http://www.uniprot.org/uniprot/?format=tab&columns=id,entry%20name,genes,organism-id,protein%20names&query=accession:P33203


*/