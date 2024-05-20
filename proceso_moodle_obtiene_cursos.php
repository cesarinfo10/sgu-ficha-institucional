<?php
//CAMBIO
//if ($argv[1]=="ejecutar") {
    include("funciones.php");
//}

function moodle_obtenerCursosJSON($host, $token) {
    //$myCurl = "http://190.54.4.77/dumc0521/webservice/rest/server.php?wstoken=ee7ea7adb69bb42b8d1f313ca4d4362f&wsfunction=core_course_get_courses&moodlewsrestformat=json";
    $funcion = "core_course_get_courses";
    $arrLocal = array();
    //$arrLocal = null;
    $myCurl = $host."/webservice/rest/server.php?wstoken=".$token."&wsfunction=".$funcion."&moodlewsrestformat=json";
    //echo("curl '$myCurl'");
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $myCurl);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $output = curl_exec($ch);

///////////////////////////////////////////////////////////////////////////////////////////////////////
        $decoded_json = json_decode($output, true);
        //$indice = 0;
        $SS = "[";
        if ($decoded_json != null) {
            $indice = 0;
            foreach($decoded_json as $key => $value) {
                
//                if ($indice>1) {
//                    $SS .= ",";
//                }                
                $id = $decoded_json[$key]["id"];
                $seguir = true;
                if ($seguir) {
                    $displayname = $decoded_json[$key]["displayname"];
                    $shortname = $decoded_json[$key]["shortname"];
                    $categoryid = $decoded_json[$key]["categoryid"];
                    
                    //echo $id." - ".$shortname." - ".$displayname."\n";
                    if ($categoryid != "0") {
                        $indice++;
                        $SS .= "{";
                        $SS .= "\"id_curso\":"."\"".$id."\"".",";
                        $SS .= "\"nombre\":"."\"".$displayname."\"";
                        $SS .= "}";
                    }
                    $SS .= ",";
                }
            }
        }
        $SS .= "]";
        $SS = str_replace(",]", "]", $SS);
        $SS = str_replace("[,", "[", $SS);
        curl_close($ch);
    return $SS;
}
//$json_salida = "[{\"id_curso\":\"001\",\"nombre\":\"SISTEMAS001\"},{\"id_curso\":\"002\",\"nombre\":\"SISTEMAS002\"}]";
$host = $argv[1];
$token = $argv[2];
//echo("host = ".$host);
//echo("token = ".$token);
//$json_salida = moodle_obtenerCursosJSON("http://190.54.4.78/mumc1219cb", "c164c40a1653c8af8dfe139a21485b42");
$json_salida = moodle_obtenerCursosJSON($host, $token);
echo($json_salida);
