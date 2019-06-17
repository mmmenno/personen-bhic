<?php

$output = "csv";                // 'csv' or 'rdf'

include("functions.php");

if($output=="rdf"){
    $prefixes = "
    @prefix schema: <http://schema.org/> .
    @prefix pnv: <https://w3id.org/pnv#> .
    @prefix bio: <http://purl.org/vocab/bio/0.1/> .
    @prefix roar: <https://w3id.org/roar#> .
    @prefix akte: <https://data.bhic.nl/deed/> .
    @prefix po: <https://data.bhic.nl/personobservation/> .
    ";
    echo $prefixes . "\n\n";
}


if($output=="csv"){
    $fp = fopen('names.csv', 'w');
    $fields = array("akte","literalName","prefix","givenName","patronym","surname","surnamePrefix","baseSurname");
    fputcsv($fp, $fields);
    fclose($fp);
}



// loop through data 
if (($handle = fopen("../aangeleverd/Schepenakten_voor1800.txt", "r")) !== FALSE) {
    $i = 0;
    $x = 0;
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
        if($i>62){
            //break;
        }
        $i++;

        $data[1] = str_replace("  ", " ", $data[7]);            // double space in text, sometimes :-(
        
        $result = findNames($data[1]);                          // find names in text

        $x += count($result['names']);

        //echo "\n" . $i . " - " . $result['marked'] . " [" . $x . " namen tot dusver]\n";      // show marked text

        //print_r($result['names']);                            // show found names


        $names = array();
        foreach ($result['names'] as $name) {
            $names[$name] = splitName($name);                   // split names to PNV parts
        }

        if($output=="csv"){                                     // write csv
            foreach ($names as $k => $v) {
                writecsv($data[0],$v);
            }
            echo ". ";
            continue;                                           // skip the rest, if we want only csv
        }

        $relations = findRelations($result['marked']);          // find relations

        if(count($relations)){
            //print_r($relations);
        }

        if(!$daterange = properDates($data[2])){                // fix messy dates
            // notations lik 1-8-[1457], 1653 maart 3, 20-NOV-17
            //echo $data[2] . "\n";  
        }


        if($output=="rdf"){ 
            $rdf = createPersonObservations(                    // create rdf 
                                        $data[0],
                                        $daterange,
                                        $names,
                                        $relations
                                    );

            echo $rdf;                                          // show personobservation as rdf
        }

    }
    fclose($handle);
}


function writecsv($deedid,$nameparts){
    $fp = fopen('names.csv', 'a');
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