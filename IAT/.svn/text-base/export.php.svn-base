<?php
	include_once "configuration.php"; #$results_path = "./results", etc...

	$filename = "pmc".$_GET["pmcid"]."_$date_time.csv";
	$filename = preg_replace("/[^a-zA-Z0-9.-]/", "_", $filename);

	header('Content-Type: text/csv');
	header('Content-disposition: attachment; filename='.$filename);
	print $_POST['exportdata'];

	$filename = "$results_path/$filename";
	#print( "filename is $filename\n" );

	$fh = fopen($filename, 'w') or die("can't open file $filename");
	fwrite( $fh, $_POST['exportdata']."\n" );
	fclose($fh);

	#fwrite( $fh, "results_path is $results_path\n" );
	#fwrite( $fh, "filename is $filename\n" );
?>
