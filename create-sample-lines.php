<?php

$i = 0;
$fp = fopen('sample-voor-1800.csv', 'w');

if (($handle = fopen("../aangeleverd/Schepenakten_voor1800.txt", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
    	if($i%1003==0){
    		$fields = array( $data[0], $data[7], $data[6]);
    		fputcsv($fp, $fields);
    	}
       	$i++;
    }
    fclose($handle);
}

fclose($fp);


?>