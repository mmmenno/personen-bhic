<?php



/**
 * lowercase words that obviously aren't names
 */
function decapitate($str){

    $notnames = array("Voor","Wij","Testament","Testes","Deling","Transport",
        "Actum","Aen","Ende","Door","Genoemde","Voornoemde","Rekest","Hertogenbosch",
        "Onse Lieve Vrouwe","Hem","Bij","Onze","De");

    foreach ($notnames as $notname) {
        $str = str_replace($notname . " ", strtolower($str) . " ", $str);
    }

    return $str;
    
}

/**
 * get rid of placenames
 */
function isPlaceName($str){
    $placenames = array("Den Dungen","Den Bosch","Hertogenbosch","Sint Oedenrode","Aarle Rixtel","Maas");
    
    if(in_array($str, $placenames)){
        return true;
    }

    if(preg_match("/ (Veld|Veldt|Landt|Wegh|Camp)$/",$str)){
        return true;
    }

    return false;
}


// load givennames just one time, and name it as global var within function
$givennames = array();

if (($handle = fopen("../firstnames.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, ";")) !== FALSE) {
        $givennames[] = $data[0];
    }
    fclose($handle);
}

/**
 * see if a string is a known given name
 */
function isGivenName($str){
    //$names = array("Elisabeth","Gerardus","Lijsken","Jenneken"); // just testing, should query db or api here
    
    global $givennames;

    if(in_array($str, $givennames)){
        return true;
    }

    return false;
}

/**
 * see if a string is a set of initials
 */
function isInitials($str){
    
    if(preg_match("/[A-Z]\.( ?[A-Z]\.)?( ?[A-Z]\.)?( ?[A-Z]\.)?( ?[A-Z]\.)?( ?[A-Z]\.)?/",$str)){
        return true;
    }

    return false;
}

/**
 * extract names from deed and mark names in deed
 */
function findNames($txt){

    $leftover = decapitate($txt);
    $marked = $txt;
    
    $names = array();
    $realnames = array();
    $patterns = array();


    $patterns[] = "/[A-Z][a-z]+ (dochter|zoon|soene?) van [A-Z][a-z]+( [A-Z][a-z]+)?(van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.)? [A-Z][a-z]+/";
    // Jenneke dochter van Willem Hendrik van den Heuvel

    $patterns[] = "/[A-Z][a-z]+( de)? (dochtere?|zoon|soene?)( van)?( w(ij|y)len)? [A-Z][a-z]+( [A-Z][a-z]+)?( (van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.))? [A-Z][a-z]+/";
    // Agnes dochter van Jan Willem Buijs

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ (van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.) [A-Z][a-z]+/";
    // Cathalijn Willem Adriaans de Jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ (van|van ?der|der|ter|van ?den|den|de|van de|a|v\.d\.) [A-Z][a-z]+/";

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";
    // Jan Dirck Jan Tijsse Versantvoort

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/([A-Z][a-z]+ )?(Corn\.|Henr\.|Hend\.|Jac\.|Fr\.) ((van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.) )?([A-Z][a-z]+ )?[A-Z][a-z]+/";
    // Corn. Van Dijck; Henr. Arts

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/(Mr\.|Mr|mr|mr\.|Monsr\.|Juffr\.) [A-Z][a-z]+ [A-Z][a-z]+/";
    //  Mr. Jan Drabbe

    $patterns[] = "/[A-Z][a-z]+ (van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.) [A-Z][a-z]+/";
    // Jan van Helvoirt

    //$patterns[] = "/[A-Z][a-z]+ (van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.) [A-Z][a-z]+t/";
    // Jan van Helvoirt (snap niet waarom nodig met die t erachter, maar anders lukt het niet???)

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ de (oude|jonge)/";
    // Willem Mans de jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+/";

    $patterns[] = "/[A-Z]\.( ?[A-Z]\.)?( ?[A-Z]\.)?( (van|van ?der|der|ter|van ?den|de|van de|a|v\.d\.))? [A-Z][a-z]+/";

    $patterns[] = "/[A-Z][a-z]+/";
    
    

    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$leftover,$found)){
            foreach ($found[0] as $value) {
                if(isPlaceName($value)){
                    $leftover = str_replace($value, "", $leftover);
                    continue;
                }
                $parts = explode(" ", $value);
                if(preg_match("/dag$/",$value)){ // e.g. Onze Lieve Vrouwe Lichtmisdag
                    $leftover = str_replace($value, "", $leftover);
                    continue;
                }
                if(preg_match("/^Sint /",$value)){ // e.g. Sint Marten
                    $leftover = str_replace($value, "", $leftover);
                    continue;
                }
                if(count($parts)<4 && !isGivenName($parts[0]) && !isInitials($parts[0])){
                    continue;
                }
                if(!in_array($value,$names)){
                    $names[] = $value;
                    $marked = preg_replace("/(^|[^{}])" . $value . "([^{}a-z]|$)/", "$1{{" . $value . "}}$2", $marked);
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

    if(preg_match_all("/({{[A-Za-z. ]+){{([A-Za-z. ]+}})/",$marked,$found)){
        for($n = 0; $n<count($found[0]); $n++){
            $cleaned = $found[1][$n] . $found[2][$n];
            $marked = str_replace($found[0][$n], $cleaned, $marked);
        }
    }
    if(preg_match_all("/({{[A-Za-z. ]+)}}([A-Za-z. ]+}})/",$marked,$found)){
        for($n = 0; $n<count($found[0]); $n++){
            $cleaned = $found[1][$n] . $found[2][$n];
            $marked = str_replace($found[0][$n], $cleaned, $marked);
        }
    }



    return array("names" => $names, "marked" => $marked);
}


if (($handle = fopen("sample-voor-1800.csv", "r")) !== FALSE) {
    $i = 0;
    $x = 0;
    while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
        if($i>22){
            //break;
        }
        $i++;
    	//echo "\n" . $data[1] . "\n\n";
    	
        $result = findNames($data[1]);

        echo "\n" . $result['marked'] . "\n";

    	//print_r($result['names']);

        //echo $x . " + " . count($result['names']) . " = ";
        $x = $x + count($result['names']);
        //echo $x . "\n";

        $names = array();
        foreach ($result['names'] as $name) {
            $names[$name] = splitName($name);
        }
        //print_r($names);


        $relations = findRelations($result['marked']);

        if(count($relations)){
            //print_r($relations);
        }

        $daterange = properDates($data[2]);

        $rdf = createPersonObservations($data[0],$daterange,$names,$relations);

        echo $rdf;

    }
    fclose($handle);
}

/**
 * create proper dates
 */
function properDates($datestring){

    if(preg_match("/[0-9]{2}-[0-9]{2}-[0-9]{4}/", $datestring)){ 
        $dmy = explode("-", $datestring); 
        return array($dmy[2] . "-" . $dmy[1] . "-" . $dmy[0],$dmy[2] . "-" . $dmy[1] . "-" . $dmy[0]); 
    }

    return array($datestring,$datestring);

}


/**
 * create person observation in turtle rdf
 */
function createPersonObservations($deedid,$daterange,$names,$relations){

    
    $i = 1;
    $pkeys = array();
    foreach ($names as $k => $v) {
        $pkeys[$k] = $i;
        $i++;
    }

    $ttl = "\n";
    foreach ($names as $k => $v) {
        $ttl .= "personobs:" . $deedid . "-" . $pkeys[$k] . "\n";
        $ttl .= "\troar:documentedIn akte:" . $deedid . " ;\n";
        $ttl .= "\tpnv:hasName [\n";
        $ttl .= "\t\tpnv:literalName \"" . $v['literalName'] . "\" ;\n";
        if(isset($v['prefix'])){
            $ttl .= "\t\tpnv:givenName \"" . $v['prefix'] . "\" ;\n";
        }
        if(isset($v['givenName'])){
            $ttl .= "\t\tpnv:givenName \"" . $v['givenName'] . "\" ;\n";
        }
        if(isset($v['patronym'])){
            $ttl .= "\t\tpnv:patronym \"" . $v['patronym'] . "\" ;\n";
        }
        if(isset($v['surname'])){
            $ttl .= "\t\tpnv:surName \"" . $v['surname'] . "\" ;\n";
        }
        if(isset($v['baseSurname'])){
            $ttl .= "\t\tpnv:baseSurname \"" . $v['baseSurname'] . "\" ;\n";
        }
        if(isset($v['surnamePrefix'])){
            $ttl .= "\t\tpnv:surnamePrefix \"" . $v['surnamePrefix'] . "\" ;\n";
        }
        $ttl .= "\t]\n";
        foreach ($relations as $rel) {
            if($rel['p1']==$k && $rel['relation']=="huwelijk"){
                $ttl .= "\tschema:spouse personobs:" . $deedid . "-" . $pkeys[$rel['p2']] . " ;\n";
            }
            if($rel['p1']==$k && isset($rel['p1gender'])){
                $ttl .= "\tschema:gender schema:" . $rel['p1gender'] . " ;\n";
            }
            if($rel['p1']==$k && isset($rel['p1death'])){
                $ttl .= "\tbio:death [\n";
                $ttl .= "\t\ta bio:Death ;\n";
                $ttl .= "\t\tbio:principal personobs:" . $deedid . "-" . $pkeys[$rel['p1']] . " ;\n";
                $ttl .= "\t\tsem:hasLatestTimeStamp \"" . $daterange[1] . "\"^^xsd:date ;\n";
                $ttl .= "\t]\n";
            }
        }
        $ttl .= "\ta roar:PersonObservation .\n\n";
        
    }

    return $ttl;
}


/**
 * split name into parts described in https://lodewijkpetram.nl/vocab/pnv/doc/
 */
function findRelations($txt){

    $relations = array();

    // echtgenoten, man -> vrouw
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\} als weduwnaar van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} weduwnaar van \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Male";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Female";
                $rel['p1death'] = "before";
                
                $relations[] = $rel;
            }
        }
        
    }
    
    // echtgenoten, man -> vrouw
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\}, eerder man van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} en zijn vrouw \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} en diens vrouw \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} als man van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} getrouwd met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} gehuwd geweest met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} gehuwd met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} man van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} en \{\{([^\}]+)\}\},? echtelieden/";
    $patterns[] = "/\{\{([^\}]+)\}\} en \{\{([^\}]+)\}\},? e\.l\./";
    $patterns[] = "/\{\{([^\}]+)\}\} & \{\{([^\}]+)\}\},? e\.l\./";
    $patterns[] = "/\{\{([^\}]+)\}\} g\.m\. \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Male";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Female";
                
                $relations[] = $rel;
            }
        }
        
    }
    
    // weduwen, vrouw -> man
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\},? de weduwe van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? weduwe van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? als weduwe van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? weduwe van wijlen \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? weduwe wijlen \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? wed.? van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? weduwe \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Female";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Male";
                $rel['p1death'] = "before";
                
                $relations[] = $rel;
            }
        }
        
    }

    // echtgenoten, vrouw -> man
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\},? huisvrouw van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? echtgenote van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? vrouw van \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Female";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "huwelijk";
                $rel['p1gender'] = "Male";
                
                $relations[] = $rel;
            }
        }
        
    }
    return $relations;
}



/**
 * split name into parts described in https://lodewijkpetram.nl/vocab/pnv/doc/
 */
function splitName($name){

    $pnv = array("literalName"=>$name);

    $leftover = $name;

    $pattern = "/ ([A-Z][a-z]+ss)$/";
    if(preg_match($pattern,$name,$found)){
        $pnv['patronym'] = $found[1];
        $leftover = preg_replace("/" . $found[0] . "$/", "", $leftover);
    }

    if(!isset($pnv['patronym'])){
        $pattern = "/ (van|van ?der|der|ter|van ?den|den|de|van de|a|v\.d\.) ([A-Z][a-z]+)$/";
        if(preg_match($pattern,$name,$found)){
            $pnv['surnamePrefix'] = $found[1];
            $pnv['baseSurname'] = $found[2];
            $pnv['surname'] = trim($found[0]);
            $leftover = preg_replace("/" . $found[0] . "$/", "", $leftover);
        }
    }

    if(!isset($pnv['surname']) && !isset($pnv['patronym'])){
        $pattern = "/ ([A-Z][a-z]+)$/";
        if(preg_match($pattern,$name,$found)){
            $pnv['surname'] = $found[1];
            $leftover = preg_replace("/ [A-Z][a-z]+$/", "", $leftover);
        }
    }


    $parts = explode(" ", $name);

    if(!isPrefix($parts[0])){
        $pnv['givenName'] = $parts[0];
        $leftover = preg_replace("/^" . $parts[0] . "/", "", $leftover);
    }else{
        $pnv['prefix'] = $parts[0];
        $leftover = preg_replace("/^" . $parts[0] . "/", "", $leftover);
        if(isGivenName($parts[1])){
            $pnv['givenName'] = $parts[1];
            $leftover = preg_replace("/^ " . $parts[1] . "/", "", $leftover);
        }
    }

    if(strlen(trim($leftover))){
        if(isset($pnv['patronym'])){
            $pnv['patronym'] = trim($leftover) . " " . $pnv['patronym'];
        }else{
            $pnv['patronym'] = trim($leftover);
        }
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