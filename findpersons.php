<?php

include("functions.php");


// loop through data 
if (($handle = fopen("sample-na-1800.csv", "r")) !== FALSE) {
    $i = 0;
    $x = 0;
    while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
        if($i>62){
            //break;
        }
        $i++;

        $data[1] = str_replace("  ", " ", $data[1]);            // double space in text, sometimes :-(
        
        $result = findNames($data[1]);                          // find names in text

        $x += count($result['names']);

        echo "\n" . $i . " - " . $result['marked'] . " [" . $x . " namen tot dusver]\n";      // show marked text
        //echo "\n" . $data[0] . " - " . $result['marked'] . "\n";      // show marked text

        print_r($result['names']);                            // show found names

        foreach ($result['names'] as $name) {                   // check very uncommon names
            $parts = explode(" ", $name);
            if(isRareGivenName($parts[0])){
                //echo "RAAR " . $name . "\n";
            }                   
        }

        $names = array();
        foreach ($result['names'] as $name) {
            $names[$name] = splitName($name);                   // split names to PNV parts
        }

        //print_r($names);                                      // show splitted names


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

        //echo $rdf;                                              // show personobservation as rdf

    }
    fclose($handle);
}




?>