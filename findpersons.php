<?php



/**
 * lowercase words that obviously aren't names
 */
function decapitate($str){

    $notnames = array("Voor","Wij","Testament","Testes","Deling","Transport",
        "Actum","Aen","Ende","Door","Genoemde");

    foreach ($notnames as $notname) {
        $str = str_replace($notname . " ", strtolower($str) . " ", $str);
    }

    return $str;
    
}

/**
 * get rid of placenames
 */
function isPlaceName($str){
    $placenames = array("Den Dungen","Den Bosch","Sint Oedenrode","Aarle Rixtel");
    
    if(in_array($str, $placenames)){
        return true;
    }

    if(preg_match("/ (Veld|Veldt|Landt|Wegh|Camp)/",$str)){
        return true;
    }

    return false;
}

/**
 * see if a string is a known given name
 */
function isGivenName($str){
    $names = array("Elisabeth","Gerardus","Lijsken","Jenneken"); // just testing, should query db or api here
    
    if(in_array($str, $names)){
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

    $patterns[] = "/[A-Z][a-z]+/";
    
    

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
                if(strpos($value," ") === false && !isGivenName($value)){
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


if (($handle = fopen("sample-voor-1800.csv", "r")) !== FALSE) {
    $i = 0;
    while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
        if($i>50){
            //break;
        }
        $i++;
    	//echo "\n" . $data[1] . "\n\n";
    	
        $result = findNames($data[1]);

        echo "\n" . $result['marked'] . "\n";

    	print_r($result['names']);

        foreach ($result['names'] as $name) {
            $splitted = splitName($name);
            print_r($splitted);
        }
        


    }
    fclose($handle);
}




/**
 * split name into parts described in https://lodewijkpetram.nl/vocab/pnv/doc/
 */
function splitName($name){

    $pnv = array("literalName"=>$name);

    $leftover = $name;

    $pattern = "/ (van|van der|der|van den|de|van de|a|v\.d\.) ([A-Z][a-z]+)$/";
    if(preg_match($pattern,$leftover,$found)){
        $pnv['surnamePrefix'] = $found[1];
        $pnv['baseSurname'] = $found[2];
        $pnv['surname'] = trim($found[0]);
        $leftover = str_replace($found[0], "", $leftover);
    }


    $pattern = "/ ([A-Z][a-z]+)$/";
    if(preg_match($pattern,$leftover,$found)){
        
        if(isSurname($found[1])){
            $pnv['surname'] = $found[1];
            $leftover = str_replace($found[0], "", $leftover);
        }
    }


    $parts = explode(" ", $leftover);

    if(!isPrefix($parts[0])){
        $pnv['givenName'] = $parts[0];
        $leftover = str_replace($parts[0], "", $leftover);
    }else{
        $pnv['prefix'] = $parts[0];
        $leftover = str_replace($parts[0], "", $leftover);
    }

    if(strlen(trim($leftover))){
        $pnv['leftover'] = trim($leftover);
    }

    return $pnv;

}



/**
 * check if namepart is prefix like 'Mr.', 'Juffrouw', etc.
 */
function isPrefix($str){

    $prefixes = array("Mr.","Juffrouw");
    
    if(in_array($str, $prefixes)){
        return true;
    }

    return false;

}



/**
 * check if namepart is surname like 'Rovers', 'Jongedijk', etc.
 */
function isSurname($str){

    // we need a list of surnames, for now just check givenName
    
    if(isGivenName($str)){
        return false;
    }

    return true;

}




?>