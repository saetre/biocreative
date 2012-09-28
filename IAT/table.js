function Table(tableId){
 	if ( !(this instanceof Table) )
	return new Table();

	this.tableId = tableId
	/* Store our reference to the DOM element we're working on (the 'context') */
	_context : false
}//Table-CONSTRUCTOR


/* Export this table as CSV */
Table.prototype.export2CSV = function() {
	this.getContext().table2CSV( { separator : '|',
		header:['PMCID', 'Gene', 'EntrezID', 'ranking', 'Conf', 'tax', 'abs', 'text']
	} )
}//function export2CSV

/* A simple getter function to find the position of a named header column */
Table.prototype.getColNr = function( colName ) {
	//var DEBUG=1
	var colNr;
	$('th',this.getContext()).each( function(column){
		if ( $(this).is('.'+colName+HEADER) ) colNr=column-1
	} )
	if (DEBUG) debug( "table.js25~getColNr "+colName+" is col nr:"+colNr )
	return colNr
}//function getColNr

/* A simple getter function for this object's context */
Table.prototype.getContext = function() {
	if (!this._context) {
		this._context = $('#'+this.tableId)
	}
	//debug( "table.js23~ Now context is "+this._context.selector )
	//debug( "tableId is "+this.tableId )
	//debug( "$('#'+this.tableId).length is "+ $('#'+this.tableId).length )

	return this._context
}//function getContext

Table.prototype.getGeneIds = function() {
	//var DEBUG=1
	var idCol = this.getColNr(GENE+ID)
	var ids = Array()
	$('TBODY TR', this.getContext()).each( function(){
		ids.push( $(this).children('td').eq(idCol).text() )
	} )
	if(DEBUG)debug( "table.js48~getGeneIds ids is "+dump(ids) )
	return ids
}//function getGeneIds

Table.prototype.initSortable = function() {
	var options = { headers:{ 0:{sorter:false},9:{sorter:false} } }
	if ( !this.tableId.match(REMOVED) ){
		options = {sortList: [[5,1]], widgets: ['zebra']}
	}
	this.getContext().tablesorter( options );
}//function initSortable

//Insert Gene in Table
Table.prototype.insertGene = function(span_thisGene){
	//var DEBUG=1
	var id = span_thisGene.attr("title")
	if (DEBUG){
		debug( "table.js~30: context is "+this.getContext().selector+", id is "+id)
	}
	var name = ALLGENES[id][NAME]
	var taxid = ALLGENES[id][ORG+ID]
	var taxname = ALLGENES[id][ORG+NAME]
	var color = ALLGENES[id][COLOR]
	if (DEBUG>1){
		debug( "~ gene is "+id+" ("+name+")--"+taxname+"<BR>" )
		debug( "looking for"+ '#'+TABLE+id +" in "+ this.getContext() )
		debug( "ALLGENES id is "+dump(ALLGENES[id]) )
	}
	var namecol, taxcol;
	$('th',this.getContext()).each( function(column){
		if ( $(this).is('.'+NAME+HEADER) ) {
			namecol=column-1
		}else if ( $(this).is('.'+TAX+HEADER) ) {
			taxcol=column-1
		}
	} )
	if (DEBUG) debug( "namecol is "+namecol+", taxcol is "+taxcol )
	var generow = $('#'+TABLE+id, this.getContext())
	generow.children('td').eq(namecol).html(
		"<SPAN class=color"+color+" onclick=showNames('"+id+"')>"+name+"</SPAN>" )
	generow.children('td').eq(taxcol).html(
		"<SPAN class="+SPECIESNCBI+taxid+">"+taxname+"</SPAN>" )
	this.getContext().trigger("update");
}//function insertGeneInSpecies

Table.prototype.removeGene = function( geneId, tabId, add ){
	//var DEBUG=1
	if (DEBUG) debug ( "table.js93~removeGene -"+geneId+"-, add is "+add )
	if (add){
		var removeToTableId = tabId
		var removeFromTableId = tabId+REMOVED
		var newEvent="GENETABLE.removeGene(\""+geneId+"\", \""+tabId+"\", false)"
		var button="<IMG src='../css/images/delButton.gif' alt='del' title='Remove'"
		+" height='15' onclick='javascript:"+newEvent+"'>";
	}else{
		var removeToTableId = tabId+REMOVED
		var removeFromTableId = tabId
		var newEvent
		 = "GENETABLEREMOVED.removeGene(\""+geneId+"\", \""+tabId+"\",true)"
		var button="<IMG src='../css/images/addButton.gif' alt='add' title='AddGene'"
		 + " height='15' onclick='javascript:"+newEvent+"'>";
	}
	var removeToTable = $("#"+removeToTableId)
	$("#"+removeToTableId+DIV).show('fast');

	$("#"+TABLE+geneId).appendTo( removeToTable.find("TBODY") )
	$("#"+TABLE+geneId+" img").replaceWith(button);
	this.getContext().trigger("update");
	removeToTable.trigger("update");
	
	removeGeneHighlights(ABSTRACT, geneId)
	removeGeneHighlights(FULLTEXT, geneId)

	return false //Hijack the clicking event, avoid form submission
}//function removeGene

Table.prototype.rerank = function(){
	//var DEBUG=1
	var rankCol = this.getColNr( RANK )
	//$('th',this.getContext()).each( function(column){
	//	if ( $(this).is('.'+RANK+HEADER) ) rankCol=column-1
	//} )
	if (DEBUG) debug( "rankCol is "+rankCol )
	//var generow = $('TR', this.getContext())
	$('TR', this.getContext()).each( function(row){
		if(DEBUG>1)debug("row is "+row)
		$(this).children('td').eq(rankCol).html( row )
	} )
	this.getContext().trigger("update");
}//function rerank

Table.prototype.zebraStripes = function(){
	debug("Table.js is here!")
	$('table.sortable tbody tr:nth-child(odd)', this._context).removeClass("alt");
  $('table.sortable tbody tr:nth-child(even)', this._context).addClass('alt');
}//function zebraStripes()



