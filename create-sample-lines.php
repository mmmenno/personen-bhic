<?php

$i = 0;

if (($handle = fopen("aangeleverd/Schepenakten_voor1800.txt", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
    	if($i%1000==0){
    		echo $data[0] . "#" . $data[7] . "\n";
    	}
       	$i++;
    }
    fclose($handle);
}




?>