<?php

// There are over 300000 Schepenakten, get only every nth to test & develop

$i = 0;
$fp = fopen('sample-na-1800.csv', 'w');

if (($handle = fopen("../aangeleverd/Schepenakten_vanaf1800.txt", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
    	if($i%1955==0){
    		$fields = array( $data[0], $data[7], $data[6]);
    		fputcsv($fp, $fields);
    	}
       	$i++;
    }
    fclose($handle);
}

fclose($fp);


?>