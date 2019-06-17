<?php



// load givennames just one time, and access it as global var from function
$givennames = array();

if (($handle = fopen("firstnames.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 5000, ";")) !== FALSE) {
        $givennames[] = $data[0];
    }
    fclose($handle);
}



/**
 * lowercase words that obviously aren't names
 */
function decapitate($str){

    $notnames = array("Voor","Wij","Testament","Compareert","Testes","Deling","Transport",
        "Actum","Aen","Aan","Ende","Door","Getuigen","Verzoek","Genoemde","Voornoemde","Rekest","Hertogenbosch",
        "Onse Lieve Vrouwe","Lieve Vrouwe","Lieve Vrouw","Hem","Bij","Onze","De","Philippus gulden","Hertog van Brabant");

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


/**
 * see if a string is a known given name
 */
function isGivenName($str){
    
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

    //$patterns[] = "/[A-Z][a-z]+ (dochtere?|zoon|soene?|sone|soon) van [A-Z][a-z]+( [A-Z][a-z]+)?(van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.)? [A-Z][a-z]+/";
    // Jenneke dochter van Willem Hendrik van den Heuvel

    $patterns[] = "/[A-Z][a-z]+( de)? (dochtere?|zoon|soene?|sone|soon)( van)?( w(ij|y)len)? [A-Z][a-z]+( [A-Z][a-z]+)?( (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.))? [A-Z][a-z]+/";
    // Agnes dochter van Jan Willem Buijs

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+(ss\.)? (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.) [A-Z][a-z]+/";
    // Cathalijn Willem Adriaans de Jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+(ss\.)? (van|van ?der|der|ter|van ?den|den|de|de la|van de|a|v\.d\.) [A-Z][a-z]+/";

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+/";
    // Jan Dirck Jan Tijsse Versantvoort

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+(ss\.)? [A-Z][a-z]+(ss\.)? [A-Z][a-z]+/";

    $patterns[] = "/([A-Z][a-z]+ )?(Corn\.|Henr\.|Hend\.|Jac\.|Fr\.) ((van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.) )?([A-Z][a-z]+ )?[A-Z][a-z]+/";
    // Corn. Van Dijck; Henr. Arts

    $patterns[] = "/(Mr\.|Mr|mr|mr\.|\.|Juffr\.) [A-Z][a-z]+ [A-Z][a-z]+/";
    //  Mr. Jan Drabbe

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+ [A-Z][a-z]+( (dochter|zoen))?/";

    $patterns[] = "/[A-Z][a-z]+ (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.) [A-Z][a-z]+/";
    
    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+(ss\.)? de (oude|jonge)/";
    // Willem Mans de jonge

    $patterns[] = "/[A-Z][a-z]+ [A-Z][a-z]+(ss\.)?/";

    $patterns[] = "/[A-Z]\.( ?[A-Z]\.)?( ?[A-Z]\.)?( (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.))? [A-Z][a-z]+/";

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
                if(count($parts)<5 && !isGivenName($parts[0]) && !isInitials($parts[0]) && !isPrefix($parts[0])){
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


    // now, maybe see if there's another name WITHIN a name
    // e.g. 'Agnes dochter van Jan Willem Buijs'
    foreach ($names as $key => $value) {
        $pattern = "/[A-Z][a-z]+( de)? (dochtere?|zoon|soene?|sone|soon)( van)?( w(ij|y)len)? ([A-Z][a-z]+( [A-Z][a-z]+)?( (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.))? [A-Z][a-z]+)/";
        if(preg_match($pattern, $value, $found)){
            $names[] = $found[6];
        }
    }


    return array("names" => $names, "marked" => $marked);
}



/**
 * create proper dates (daterange actually)
 */
function properDates($datestring){

    if(preg_match("/^([0-9]{1,2})(-|\.)([0-9]{1,2})(-|\.)([0-9]{4})/", $datestring,$found)){ 

        if(strlen($found[1])==1){ $found[1] = "0" . $found[1]; }
        if(strlen($found[3])==1){ $found[3] = "0" . $found[3]; }
        return array(
            $found[5] . "-" . $found[3] . "-" . $found[1],
            $found[5] . "-" . $found[3] . "-" . $found[1]
        ); 
    }

    if(preg_match("/^([A-Za-z]+ )?([0-9]{1,2}) ([A-Za-z]+) ([0-9]{4})$/", $datestring, $found)){ 
        $from = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
        $to = array("01","02","03","04","05","06","07","08","09","10","11","12");
        $maand = str_replace($from, $to, $found[3]);
        if(strlen($found[2])==1){ $found[2] = "0" . $found[2]; }
        return array($found[4] . "-" . $maand . "-" . $found[2],$found[4] . "-" . $maand . "-" . $found[2]); 
    }

    if(preg_match("/^[0-9]{4}$/", $datestring)){ 
        return array($datestring . "-01-01",$datestring . "-12-31"); 
    }

    if(preg_match("/^([0-9]{4})-([0-9]{4})$/", $datestring, $found)){ 
        return array($found[1] . "-01-01",$found[2] . "-12-31"); 
    }

    return false;

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
        $ttl .= "po:" . $deedid . "-" . $pkeys[$k] . "\n";
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
        $ttl .= "\t] ;\n";
        foreach ($relations as $rel) {
            if($rel['p1']==$k && $rel['relation']=="huwelijk"){
                $ttl .= "\tschema:spouse po:" . $deedid . "-" . $pkeys[$rel['p2']] . " ;\n";
            }
            if($rel['p1']==$k && $rel['relation']=="heeft ouder"){
                $ttl .= "\tschema:parent po:" . $deedid . "-" . $pkeys[$rel['p2']] . " ;\n";
            }
            if($rel['p1']==$k && $rel['relation']=="heeft kind"){
                $ttl .= "\tschema:children po:" . $deedid . "-" . $pkeys[$rel['p2']] . " ;\n";
            }
            if($rel['p1']==$k && isset($rel['p1gender'])){
                $ttl .= "\tschema:gender schema:" . $rel['p1gender'] . " ;\n";
            }
            if($rel['p1']==$k && isset($rel['p1death']) && $daterange){
                $ttl .= "\tbio:death [\n";
                $ttl .= "\t\ta bio:Death ;\n";
                $ttl .= "\t\tbio:principal po:" . $deedid . "-" . $pkeys[$rel['p1']] . " ;\n";
                $ttl .= "\t\tsem:hasLatestTimeStamp \"" . $daterange[1] . "\"^^xsd:date ;\n";
                $ttl .= "\t] ;\n";
            }
        }
        $ttl .= "\ta roar:PersonObservation .\n\n";
        
    }

    return $ttl;
}


/**
 * try to find relations (spouse, child, etc.) with regex
 */
function findRelations($txt){

    $relations = array();

    // weduwnaars, man -> vrouw
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\} als weduwnaar van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\},? weduwnaar van \{\{([^\}]+)\}\}/";
    
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
    $patterns[] = "/\{\{([^\}]+)\}\} als man end?e? momboir van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} man end?e? momboir van \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} getrouwd met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} gehuwd geweest met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} gehuwd met \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} man van \{\{([^\}]+)\}\}/";
    $patterns[] = "/de echtelieden \{\{([^\}]+)\}\} en \{\{([^\}]+)\}\}/";
    $patterns[] = "/\{\{([^\}]+)\}\} en \{\{([^\}]+)\}\},? echtelieden/";
    $patterns[] = "/\{\{([^\}]+)\}\} en \{\{([^\}]+)\}\},? zijn huisvrouw/";
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

    // kinderen
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\},? verwekt bij \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "heeft ouder";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "heeft kind";
                
                $relations[] = $rel;
            }
        }
        
    }

    // zonen
    $patterns = array();
    $patterns[] = "/\{\{([^\}]+)\}\},? mondige zoon van \{\{([^\}]+)\}\}/";
    
    foreach ($patterns as $pattern) {
        if(preg_match_all($pattern,$txt,$found)){
            for ($i=0; $i<count($found[0]); $i++) {
                $rel = array();
                $rel['p1'] = $found[1][$i];
                $rel['p2'] = $found[2][$i];
                $rel['relation'] = "heeft ouder";
                $rel['p1gender'] = "Male";
                
                $relations[] = $rel;

                $rel = array();
                $rel['p1'] = $found[2][$i];
                $rel['p2'] = $found[1][$i];
                $rel['relation'] = "heeft kind";
                
                $relations[] = $rel;
            }
        }
        
    }

    // now, maybe see if there's another relation WITHIN a name
    $pattern = "/[A-Z][a-z]+( de)? (dochtere?|zoon|soene?|sone|soon)( van)?( w(ij|y)len)? ([A-Z][a-z]+( [A-Z][a-z]+)?( (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.))? [A-Z][a-z]+)/";
    if(preg_match_all($pattern,$txt,$found)){
        for ($i=0; $i<count($found[0]); $i++) {
            $rel = array();
            $rel['p1'] = $found[0][$i];
            $rel['p2'] = $found[6][$i];
            $rel['relation'] = "heeft ouder";
            if(preg_match("/dochtere?/", $found[2][$i])){
                $rel['p1gender'] = "Female";
            }else{
                $rel['p1gender'] = "Male";
            }
            $relations[] = $rel;

            $rel = array();
            $rel['p1'] = $found[6][$i];
            $rel['p2'] = $found[0][$i];
            $rel['relation'] = "heeft kind";
            if(strlen($found[4][$i])){
                $rel['p1death'] = "before";
            }
            $relations[] = $rel;
        }
    }

    $relations = array_map("unserialize", array_unique(array_map("serialize", $relations)));
    return $relations;
}



/**
 * split name into parts described in https://lodewijkpetram.nl/vocab/pnv/doc/
 */
function splitName($name){

    $pnv = array("literalName"=>$name);

    $leftover = $name;

    $pattern = "/ ([A-Z][a-z]+ss)$/";
    $pattern = "/((dochtere?|zoon|soene?|sone|soon)( van)?( w(ij|y)len)? ([A-Z][a-z]+( [A-Z][a-z]+)?( (van|van ?der|der|ter|van ?den|de|de la|van de|a|v\.d\.))? [A-Z][a-z]+))/";
    if(preg_match($pattern,$name,$found)){
        $pnv['patronym'] = $found[1];
        $leftover = preg_replace("/" . $found[0] . "$/", "", $leftover);
    }

    if(!isset($pnv['patronym'])){
        $pattern = "/ ([A-Z][a-z]+(ss|sz|xz))$/";
        if(preg_match($pattern,$name,$found)){
            $pnv['patronym'] = $found[1];
            $leftover = preg_replace("/" . $found[0] . "$/", "", $leftover);
        }
    }

    if(!isset($pnv['patronym'])){
        $pattern = "/ (van|van ?der|der|ter|van ?den|den|de|de la|van de|a|v\.d\.) ([A-Z][a-z]+)$/";
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

    $prefixes = array("Mr.","Juffrouw","Monsr.","Juffr.","Joncker","Mejuffrouw","Eerwaarde");
    
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