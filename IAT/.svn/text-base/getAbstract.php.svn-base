<?php
header ("Content-Type:text/xml");
$xml =  join( "<BR>\n", file($_GET{"file"}) );
$xml = preg_replace("/<\/?mml:[^>]+>/", "", $xml); //REMOVING "STYLE" INFORMATION!
print $xml;
print "<P>PMID: ".$_GET{"pmid"}."</P>\n";
?>
