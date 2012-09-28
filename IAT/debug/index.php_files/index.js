var DEBUG=0

function init(){
	//var DEBUG=1
	if(DEBUG)debug("selected is "+selectedTab)
	$("#tabs").tabs({
		selected: selectedTab,
		load: function(e, ui){ //debug("Tabs loaded now!")
			setupAutocomplete()
			if (DEBUG){
				debug( "$(#tabs).length is "+ $("#tabs").length )
				debug( " $(#"+PMC+GENE+TABLE+").length is "
				 + $("#"+PMC+GENE+TABLE).length )
			}//DEBUG
			$("#"+PMC+GENE+TABLE).trigger("update");
			$("#"+PMC+GENE+TABLE).tablesorter( {
				headers:{ 0:{sorter:false},9:{sorter:false} },
				sortList: [[3,1], [5,1], [4,1]] } )
		}//loaded-function
	});//.tabs()
}//function init, called onload

//window.onload = init()
window.onload = setTimeout('init()', 1000)

// DEBUGGING //
function debug(msg){
	$("#DEBUG").append(msg+"<BR>\n")
}//function debug


function setupAutocomplete(){
	//Setup PMCID autocomplete
	//debug("Files size is "+files.length+"...")
	$("input#autocomplete").autocomplete({
		minLength: 2,
//	source: ["c++", "java", "php", "coldfusion", "javascript", "ruby"],
//		source: files,
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( files, function(item,index){
			    return matcher.test(item);
			});
			responseFn( a );
		},//source
		select: function(event, ui) {
			$("input#autocomplete").val(ui.item.value)
			$("form#fileselect").submit();
		}
	});//automcomplete init

	//Setup PMID autocomplete
	//debug("PMIDs size is "+files.length+"...")
	$("input#autocomplete_PMID").autocomplete({
		minLength: 3,
//	source: ["c++", "java", "php", "coldfusion", "javascript", "ruby"],
//	source: files,
		source: function(req, responseFn) {
			var re = $.ui.autocomplete.escapeRegex(req.term);
			var matcher = new RegExp( "^" + re, "i" );
			var a = $.grep( pmids, function(item,index){
			    return matcher.test(item);
			});
			responseFn( a );
		},//source
		select: function(event, ui) {
			$("input#autocomplete_PMID").val(ui.item.value)
			$("form#pmidselect").submit();
		}
	});//automcomplete init

	//Setup GeneID autocomplete
	$("input#autocomplete_geneid").autocomplete({
		minLength: 2,
		source: "gene2pmcid.php?mode=json",
		select: function(event, ui) {
			$("input#autocomplete_geneid").val(ui.item.value)
			$("form#geneselect").submit();
		}
	});//automcomplete init
}//function setupAutocomplete

function showGeneArticles(){
	geneid = $("input#autocomplete_geneid")[0].value
	tabs = $("div#tabs")
	//debug("Selected gene was "+JSON.stringify(geneid))
	debug("Selected gene was "+geneid)
	debug( "Number of tabs are "+tabs.tabs("length") )
	tabs.tabs("url",1, "gene2pmcid.php?mode=pmcid&geneid="+geneid)
	tabs.tabs("select",1)
}//function showGeneArticles

