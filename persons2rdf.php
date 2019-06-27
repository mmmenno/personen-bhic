<?php

$fileToExtractNamesFrom = "../aangeleverd/Schepenakten_voor1800.txt";

/*

    This script shows rdf in your console.
    To save the output as a file, instead of:

    > php persons2rdf.php

    type:

    > php persons2rdf.php > filenameOfChoice.ttl

*/


include("functions.php");


$prefixes = "
@prefix schema: <http://schema.org/> .
@prefix pnv: <https://w3id.org/pnv#> .
@prefix bio: <http://purl.org/vocab/bio/0.1/> .
@prefix roar: <https://w3id.org/roar#> .
@prefix akte: <https://data.bhic.nl/deed/> .
@prefix po: <https://data.bhic.nl/personobservation/> .
";
echo $prefixes . "\n\n";


// loop through data 
if (($handle = fopen($fileToExtractNamesFrom, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {

        $text = $data[7];                                       // which column contains description?
        $deedid = $data[0];                                     // which column contains deed id?

        $text = str_replace("  ", " ", $text);                  // double space in text, sometimes :-(
        
        
        $result = findNames($text);                             // find names in text

        //echo "\n - " . $result['marked'] . "\n";              // show marked text

        //print_r($result['names']);                            // show found names


        $names = array();
        foreach ($result['names'] as $name) {
            $names[$name] = splitName($name);                   // split names to PNV parts
        }

        $relations = findRelations($result['marked']);          // find relations

        if(count($relations)){
            //print_r($relations);
        }

        if(!$daterange = properDates($data[2])){                // fix messy dates
            // notations lik 1-8-[1457], 1653 maart 3, 20-NOV-17
            //echo $data[2] . "\n";  
        }


        
        $rdf = createPersonObservations(                        // create rdf 
                                    $data[0],
                                    $daterange,
                                    $names,
                                    $relations
                                );

        echo $rdf;                                              // show personobservation as rdf
    

    }
    fclose($handle);
}



?>