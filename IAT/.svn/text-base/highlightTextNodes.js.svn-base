	first=1; html[0] != ">" ? html=">"+html : first=0
	last=1; html[html.length-1] != "<" ? html=html+"<" : last=0
	var regex = new RegExp(">([^<]*)("+searchTerm+")([^>]*)<","g"+iCase)
	if (DEBUG){
		//debug("<BR>NEXT: space is --"+space+"--") //+html)
		debug( "Last character is --"+html[html.length-1] +"--" )
	}
	
	html = html.replace(regex,
		function($0, $1, $2, $3){
			if (hits < 10 && DEBUG){ //Too verbose for many outputs...
				debug ("1:--"+$1+"--, 2:"+$2+", 3:--"+$3+"--<BR>")
			}
			if (hits<LIMIT){
				//if separators are needed
				//debug("$1.length is "+$1.length+", $3.length is "+ $3.length)
				if (space && ( $1 && !($1.substring($1.length-1,$1.length).match(space))
										|| $3 && !($3.substring(0,1).match(space)) ) ){
					//tag = ">"+$1+$2+$3+"<"
					//Insert "miss", info for recursive match to fake "<"
					tag = ">"+$1+"<m><\/m>"+$2+$3+"<"
					//debug ("<BR>$1["+($1.length-1)+"]-"+$1.substring($1.length-1,$1.length)+"-")
					//debug (", $3[0] -"+$3.substring(0,1)+"-")
				}else{ //proceed with ok/tight matches
					//debug ("<BR>USING SEPARATORS --"+$1[$1.length-1]+"-- and --"+$3[0]+"--")
					hits++;
					tag = ">"+$1+"<span name='"+term_id+"' onclick=\"goNext('"+term_id+"')\""
					+" class='"+color+" highlighted'>"+$2+"<\/span>"+$3+"<"
				}//if matched, or non-matched separators

				//RECURSIVE, to capture multiple occurrences between two tags...
				if($1){
					//debug ("<BR>tag is "+tag.substring(0,$1.length+2))
					var rest = tag.substring($1.length+2) //var resets variable, in case it's empty, avoids keep old values
					//debug ("<BR>rest is "+rest)
					values = highlightTextNodes(tag.substring(0,$1.length+2), searchTerm, color, term_id, hits)
					tag = values.html+rest
					hits = values.hits
				}
			}else{//if too many hits
				tag = $0
			}
			return tag
		}//replace-and-count call-back-function
	)//replace
	if (first){
		debug ("Remove ---"+html[0]+"---<BR>")
		//html = html.replace(/^(<|&lt;)(.*)(>|&gt;)$/, "REPLACED")
		html = html.replace(/^<(.*)$/, "REPLACED")
		debug("html is ---"+html+"---<BR>\n")
	}


//RECURSIVE FUNCTION
//RegEx replace: Insert span (class='highlighted term-X'), for google style highlighting
//input html must contain one ">" before one "<"
//Input:
// html to change, searchterm to highlight, color(termX/orgY), term_id(geneX/specY)
function highlightTextNodes_old(html, searchTerm, color, term_id, hits) {
	var regex = new RegExp(">([^<]*)("+searchTerm+")([^>]*)[$<]","g"+iCase)
	if (DEBUG){
		//debug("<BR>NEXT: space is --"+space+"--") //+html)
		//debug( "Last character is --"+html[html.length-1] +"--" )
		debug("html is ---"+html+"---<BR>\n")
	}
	
	html = html.replace(regex,
		function($0, $1, $2, $3){
			if (hits < 10 && DEBUG){ //Too verbose for many outputs...
				debug ("<BR>1:--"+$1+"--, 2:"+$2+", 3:--"+$3+"--")
			}
			if (hits<LIMIT){
				//if separators are needed
				//debug("$1.length is "+$1.length+", $3.length is "+ $3.length)
				if (space && ( $1 && !($1.substring($1.length-1,$1.length).match(space))
										|| $3 && !($3.substring(0,1).match(space)) ) ){
					//tag = ">"+$1+$2+$3+"<"
					//Insert "miss", info for recursive match to fake "<"
					tag = ">"+$1+"<m><\/m>"+$2+$3+"<"
					//debug ("<BR>$1["+($1.length-1)+"]-"+$1.substring($1.length-1,$1.length)+"-")
					//debug (", $3[0] -"+$3.substring(0,1)+"-")
				}else{ //proceed with ok/tight matches
					//debug ("<BR>USING SEPARATORS --"+$1[$1.length-1]+"-- and --"+$3[0]+"--")
					hits++;
					tag = ">"+$1+"<span name='"+term_id+"' onclick=\"goNext('"+term_id+"')\""
					+" class='"+color+" highlighted'>"+$2+"<\/span>"+$3+"<"
				}//if matched, or non-matched separators

				//RECURSIVE, to capture multiple occurrences between two tags...
				if($1){
					//debug ("<BR>tag is "+tag.substring(0,$1.length+2))
					var rest = tag.substring($1.length+2) //var resets variable, in case it's empty, avoids keep old values
					//debug ("<BR>rest is "+rest)
					values = highlightTextNodes(tag.substring(0,$1.length+2), searchTerm, color, term_id, hits)
					tag = values.html+rest
					hits = values.hits
				}
			}else{//if too many hits
				tag = $0
			}
			return tag
		}//replace-and-count call-back-function
	)//replace
	return new HtmlAndHits(html, hits)
}//function highlightTextNodes_old

