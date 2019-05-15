<?php



/**
 * lowercase words that obviously aren't names, mostly at the beginning of a sentence.
 */
function decapitate($str){
    $str = preg_replace("/Voor /", "voor ", $str);
    $str = preg_replace("/Wij /", "wij ", $str);
    $str = preg_replace("/Testament /", "testament ", $str);
    $str = preg_replace("/Testes /", "testes ", $str);
    $str = preg_replace("/Deling /", "deling ", $str);
    $str = preg_replace("/Transport /", "transport ", $str);
    $str = preg_replace("/Actum /", "actum ", $str);
    $str = preg_replace("/Aen /", "aen ", $str);
    $str = preg_replace("/Ende /", "ende ", $str);

    return $str;
}

/**
 * get rid of placenames
 */
function isPlaceName($str){
    $placenames = array("Den Dungen","Den Bosch","Sint Oedenrode");
    
    if(in_array($str, $placenames)){
        return true;
    }

    if(preg_match("/ (Veldt|Landt|Wegh|Camp)/",$str)){
        return true;
    }

    return false;
}

function findNames($txt){

    $unwantedfirstnames = array("Onze","De");

    $leftover = decapitate($txt);
    $marked = $txt;
    
    $names = array();
    $realnames = array();
    $patterns = array();

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ (van|van der|der|van den|de|van de|a|v\.d\.) [A-Z][a-z]+/";
    // Cathalijn Willem Adriaans de Jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ (van|van der|der|van den|den|de|van de|a|v\.d\.) [A-Z][a-z]+/";

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/(Corn\.|Henr\.|Jac\.) ((van|van der|der|van den|de|van de|a|v\.d\.) )?([A-Z][a-z]+ )?[A-Z][a-z]+/";
    // Corn. Van Dijck; Henr. Arts

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/(Mr\.|Mr|mr|mr\.|Monsr\.|Juffr\.) [A-Z][a-z]+ [A-Z][a-z]+/";
    //  Mr. Jan Drabbe

    $patterns[] = "/[A-Z][a-z]+ (van|van der|der|van den|de|van de|a|v\.d\.) [A-Z][a-z]+/";

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ de (oude|jonge)/";
    // Willem Mans de jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/[A-Z]\.( ?[A-Z]\.)?( ?[A-Z]\.)?( (van|van der|der|van den|de|van de|a|v\.d\.))? [A-Z][a-z]+/";
    
    

    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$leftover,$found)){
            foreach ($found[0] as $value) {
                if(isPlaceName($value)){
                    continue;
                }
                $parts = explode(" ", $value);
                if(in_array($parts[0],$unwantedfirstnames)){
                    continue;
                }
                if(preg_match("/dag$/",$value)){ // e.g. Onze Lieve Vrouwe Lichtmisdag
                    continue;
                }
                if(!in_array($value,$names)){
                    $names[] = $value;
                    $marked = str_replace($value, "{{" . $value . "}}", $marked);
                }
                $leftover = str_replace($value, "", $leftover);
            }
        }
    }
   


    // when a name is part of another name we need to clean up
    $from = array("{{{{","}}}}");
    $to = array("{{","}}");
    for($i = 0; $i<6; $i++){
        $marked = str_replace($from, $to, $marked);
    }
    if(preg_match("/({{[A-Za-z. ]+){{([A-Za-z. ]+}})/",$marked,$found)){
        $cleaned = $found[1] . $found[2];
        $marked = str_replace($found[0], $cleaned, $marked);
    }
    if(preg_match("/({{[A-Za-z. ]+)}}([A-Za-z. ]+}})/",$marked,$found)){
        $cleaned = $found[1] . $found[2];
        $marked = str_replace($found[0], $cleaned, $marked);
    }



    return array("names" => $names, "marked" => $marked);
}


if (($handle = fopen("sample.csv", "r")) !== FALSE) {
    $i = 0;
    while (($data = fgetcsv($handle, 5000, "#")) !== FALSE) {
        if($i>30){
            break;
        }
        $i++;
    	//echo "\n" . $data[1] . "\n\n";
    	
        $result = findNames($data[1]);

        echo "\n" . $result['marked'] . "\n";

    	print_r($result['names']);


    }
    fclose($handle);
}




?>