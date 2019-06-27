<?php

$fileToExtractNamesFrom = "../aangeleverd/Schepenakten_voor1800.txt";
$csvOutput = "names.csv";

/*
    You might need te set some variables in the script below:
    - delimiter in csv, now '#'
    - column that contains description, now '7'
    - column that contains deed id, now '0'

    You may want to see what the script is doing:
    - uncomment $result['marked'] to see description with {{names found}}
    - uncomment $result['names'] to see a list of names found in description

*/

include("functions.php");

$fp = fopen($csvOutput, 'w');
$fields = array("akte","literalName","prefix","givenName","patronym","surname","surnamePrefix","baseSurname");
fputcsv($fp, $fields);
fclose($fp);


// loop through data 
if (($handle = fopen($fileToExtractNamesFrom, "r")) !== FALSE) {
    
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
        
        $text = $data[7];                                       // which column contains description?
        $deedid = $data[0];                                     // which column contains deed id?

        $text = str_replace("  ", " ", $text);                  // double space in text, sometimes :-(
        
        $result = findNames($text);                             // find names in text

        $x += count($result['names']);

        //echo "\n - " . $result['marked'] . "\n";              // show marked text

        //print_r($result['names']);                            // show found names


        $names = array();
        foreach ($result['names'] as $name) {
            $names[$name] = splitName($name);                   // split names to PNV parts
        }

                                            
        foreach ($names as $k => $v) {                          // write csv
            writecsv($deedid,$v);
        }
        echo ". ";                                              // just to see progress
        

    }
    fclose($handle);
}


function writecsv($deedid,$nameparts){

    global $csvOutput;

    $fp = fopen($csvOutput, 'a');
    $fields = array();
    $wanted = array("literalName","prefix","givenName","patronym","surname","surnamePrefix","baseSurname");
    $fields['id'] = $deedid;
    foreach ($wanted as $k => $v) {
        if(isset($nameparts[$v])){
            $fields[$v] = $nameparts[$v];
        }else{
            $fields[$v] = "";
        }
    }

    fputcsv($fp, $fields);

    fclose($fp);
}


?>