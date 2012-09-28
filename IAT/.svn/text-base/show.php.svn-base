<?php
header ("Content-Type:text/xml");
$lines = file($_GET{"file"});
$xml =  join( "<BR>\n", $lines );
$xml = preg_replace("/<\/?mml:[^>]+>/", "", $xml); //REMOVING "STYLE" INFORMATION
#... to ensure that Firefox prints the XML-tree!
print $xml;
#print "<P>HELLO</P>\n";
?>
