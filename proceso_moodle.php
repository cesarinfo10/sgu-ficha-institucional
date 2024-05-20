<?php
//CAMBIO
//if ($argv[1]=="ejecutar") {
    include("funciones.php");
//}

class CursoMoodle
{
    public $id;
    public $displayname;
    public $shortname;
    public $categoryid;

}
class AlumnoCursoMoodle
{
    public $idCurso;
    public $idAlumno;
    public $username;
    public $firstname;
    public $lastname;
    public $fullname;
    public $rut;
}

$arrCursoMoodle = array();
$arrAlumnoCursoMoodle = array();


function sacaCommaFinalMoodle($s) {
    $ss = $s;
    $ult = "";
    $ult = substr($ss,strlen($ss)-1,1); 
    if ($ult == ",") {
      $ss = substr($ss,0,strlen($ss)-1);
    }
    return $ss;
  }
function existeRegistroProceso() {
    $bSalida = false;
    $SQL = "
    SELECT 
    count(*) as cuenta
    FROM moodle_proceso";
    $fPending = consulta_sql($SQL);	
    $existen = $fPending[0]['cuenta'];

    if ($existen > 0) {    
        $bSalida = true;
    };
    return $bSalida;

}  
function estaProcesando() {
    $bSalida = false;
    $SQL = "
    SELECT 
    count(*) as cuenta
    FROM moodle_proceso where procesando = 'S'";
    $fPending = consulta_sql($SQL);	
    $existen = $fPending[0]['cuenta'];

    if ($existen > 0) {    
        $bSalida = true;
    };
    return $bSalida;

}  
function deleteMoodleDesincronizado() {
    $SQL_actualizar = "delete from moodle_desincronizados";
    if (consulta_dml($SQL_actualizar) == 1) {        

    };    
}
function insertaMoodleDesincronizado($origen, $rut, $nombre, $nombreCarrera, $id_curso, $nombreCurso) {
    $SQL_insert = "insert into moodle_desincronizados(origen, rut, nombre, carrera, id_curso, nombre_curso) values('$origen','$rut','$nombre','$nombreCarrera',$id_curso,'$nombreCurso')";
    if (consulta_dml($SQL_insert) > 0) {
    }

}
function actualizaMoodleProceso($procesando) {
    if (existeRegistroProceso()) {
        $SQL_actualizar = "update moodle_proceso set procesando = '$procesando'";
        if (consulta_dml($SQL_actualizar) == 1) {        
    
        };    
    } else {
        $SQL_insert = "insert into moodle_proceso(procesando) values('$procesando')";
        if (consulta_dml($SQL_insert) > 0) {
        }
    }
    if ($procesando=='S') {
        deleteMoodleDesincronizado();        
    }
}  

function moodle_obtenerAlumnosCursos($host, $token, $myIdCurso) {
    //$myCurl = "http://190.54.4.77/dumc0521/webservice/rest/server.php?wstoken=ee7ea7adb69bb42b8d1f313ca4d4362f&wsfunction=core_enrol_get_enrolled_users&moodlewsrestformat=json&courseid=4";
    $funcion = "core_enrol_get_enrolled_users";
    $arrLocal = array();
    //$arrLocal = null;
    $myCurl = $host."/webservice/rest/server.php?wstoken=".$token."&wsfunction=".$funcion."&moodlewsrestformat=json&courseid=".$myIdCurso;
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $myCurl);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $output = curl_exec($ch);
///////////////////////////////////////////////////////////////////////////////////////////////////////
        $decoded_json = json_decode($output, true);
        $indice = 0;
        if ($decoded_json != null) {
            foreach($decoded_json as $key => $value) {
                $idAlumno = $decoded_json[$key]["id"];
                $username = $decoded_json[$key]["username"];
                $firstname = $decoded_json[$key]["firstname"];
                $lastname = $decoded_json[$key]["lastname"];
                $fullname = $decoded_json[$key]["fullname"];
$roll_shortname = $decoded_json[$key]["roles"][0]["shortname"];
//echo "\nroll_shortname=".$roll_shortname;
                //echo $id." - ".$shortname." - ".$displayname."\n";
                if ($roll_shortname == 'student') {
                    $arrLocal[$indice] = new AlumnoCursoMoodle();
                    $arrLocal[$indice]->idCurso = $myIdCurso;
                    $arrLocal[$indice]->idAlumno = $idAlumno;
                    $arrLocal[$indice]->username = $username;
                    $arrLocal[$indice]->rut = $username;
                    $arrLocal[$indice]->firstname = $firstname;
                    $arrLocal[$indice]->lastname = $lastname;
                    $arrLocal[$indice]->fullname = $fullname;
                    $indice++;
    
                }
            }
    
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////
        // close curl resource to free up system resources
        //echo($output);
        curl_close($ch);
    return $arrLocal;
}

function moodle_obtenerCursos($host, $token, $course_id_moodle) {
    //$myCurl = "http://190.54.4.77/dumc0521/webservice/rest/server.php?wstoken=ee7ea7adb69bb42b8d1f313ca4d4362f&wsfunction=core_course_get_courses&moodlewsrestformat=json";
    $funcion = "core_course_get_courses";
    $arrLocal = array();
    //$arrLocal = null;
    $myCurl = $host."/webservice/rest/server.php?wstoken=".$token."&wsfunction=".$funcion."&moodlewsrestformat=json";
    echo("curl '$myCurl'");
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $myCurl);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $output contains the output string
        $output = curl_exec($ch);

///////////////////////////////////////////////////////////////////////////////////////////////////////
        $decoded_json = json_decode($output, true);
        $indice = 0;
        
        if ($decoded_json != null) {
            foreach($decoded_json as $key => $value) {
                $id = $decoded_json[$key]["id"];
                $seguir = true;
                if ($course_id_moodle==null) {
                    $seguir=true;
                } else {
                    if (existeCourseMoodle($course_id_moodle, $id)=='1' ) {
                        echo("<br>debe tomar id_curso = ".$id);
                        $seguir=true;
                    } else {
                        $seguir=false;
                    }
                    echo("<br>");
                
                }
                if ($seguir) {
                    $displayname = $decoded_json[$key]["displayname"];
                    $shortname = $decoded_json[$key]["shortname"];
                    $categoryid = $decoded_json[$key]["categoryid"];
                    
                    //echo $id." - ".$shortname." - ".$displayname."\n";
                    if ($categoryid != "0") {
                        $arrLocal[$indice] = new CursoMoodle();
                        $arrLocal[$indice]->id = $id;
                        $arrLocal[$indice]->displayname = $displayname;
                        $arrLocal[$indice]->shortname = $shortname;
                        $indice++;    
                    }
    
                }
            }
    
        }
///////////////////////////////////////////////////////////////////////////////////////////////////////
        // close curl resource to free up system resources
        //echo($output);
        curl_close($ch);
    return $arrLocal;

}
function existeCourseMoodle($course_id_moodle, $id_curso) {
    $myArrayCourseMoodle = explode(',', $course_id_moodle);
    $count = count($myArrayCourseMoodle);

    $b = 0;
    echo("<br>retorna b...=".$bb);
    echo("<br>count =".$count);
    for ($i = 0; $i < $count; $i++) {
        $my_id_course_moodle = $myArrayCourseMoodle[$i];
        echo "<br>Revisando ".$my_id_course_moodle.", con ".$id_curso ;
        if ((string)$my_id_course_moodle == (string)$id_curso) {
            echo("<br>SIIIIIIIIIIIIIII");
            $b=1;

        }
    }    
    echo("<br>retorna b=".$b);
    return $b;
}
/*
function procesar2($cu, $nombre_universo, $host, $token, $course_id_moodle) {
    echo("<br>Estoy en procesar2 voy a procesar los sgtes cursos = ".$course_id_moodle);
    //$myArrayCourseMoodle = explode(',', $course_id_moodle);
    //print_r($myArrayCourseMoodle);
    //echo("<br>FUNCION = ".existeCourseMoodle($course_id_moodle, 3));
    if (existeCourseMoodle($course_id_moodle, '5')=='1' ) {
        echo("<br>EXISTE");
    } else {
        echo("<br>NO EXISTE");
    }
    echo("<br>");
}
*/
function obtieneNombreAlumnoMoodle($arrAlumnoCursoMoodle, $rutAlumno) {
    $nombreAlumno = "";
    $fullname = "";
//    echo("buscar : rut = ".$rutAlumno);
    foreach ($arrAlumnoCursoMoodle as $myArrAlumno) {
//        $idCurso =  $myArrAlumno->idCurso;
//        $idAlumno =  $myArrAlumno->idAlumno;
//        $username =  $myArrAlumno->username;
//        $firstname =  $myArrAlumno->firstname;
//        $lastname =  $myArrAlumno->lastname;
        $fullname =  $myArrAlumno->fullname;
        $rut =  $myArrAlumno->rut;
        //$ruts .= $rut.",";
        //echo(".....cursoID=".$idCurso.", alumnoID=".$idAlumno.", ".$username.", ".$firstname.", ".$lastname."\n");
        //$cuentaAlumnos++;
        //echo("...buscar=".$rut);
        if ($rutAlumno == $rut) {
            $nombreAlumno = $fullname;
            break;
        }
    }
    return $nombreAlumno;
}
function procesar($cu, $nombre_universo, $host, $token, $course_id_moodle) {
    echo("PROCESAR : ".$cu.".-".$nombre_universo.", ".$host.", ".$token."\n");
    //$arrCursoMoodle = new CursoMoodle();
    $arrCursoMoodle = moodle_obtenerCursos($host, $token, $course_id_moodle);
    if ($arrCursoMoodle != null) {
        //echo("* * * Datos para ".$host.", token : ".$token."\n");
        foreach ($arrCursoMoodle as $myArr) {
            
            $id = $myArr->id;
            $shortname = $myArr->shortname;
            $displayname = $myArr->displayname;
            echo("\n");
            echo($id.", ".$shortname.", ".$displayname."\n");

            //1era PARTE (moodle)
            //POR CADA CURSO SE RETORNA LA CANTIDAD DE ALUMNOS
            $arrAlumnoCursoMoodle = moodle_obtenerAlumnosCursos($host, $token, $id);
            $cuentaAlumnos = 0;
            $ruts = "";
            foreach ($arrAlumnoCursoMoodle as $myArrAlumno) {
                $idCurso =  $myArrAlumno->idCurso;
                $idAlumno =  $myArrAlumno->idAlumno;
                $username =  $myArrAlumno->username;
                $firstname =  $myArrAlumno->firstname;
                $lastname =  $myArrAlumno->lastname;
                $fullname =  $myArrAlumno->fullname;
                $rut =  $myArrAlumno->rut;
                $ruts .= $rut.",";
                //echo(".....cursoID=".$idCurso.", alumnoID=".$idAlumno.", ".$username.", ".$firstname.", ".$lastname."\n");
                $cuentaAlumnos++;
            }
            $ruts = sacaCommaFinalMoodle($ruts);
            $ruts_moodle = $ruts;
            //echo(".....cursoID moodle=".$id.",  total = ".$cuentaAlumnos." ruts moodle = ".$ruts_moodle."\n");
            echo(".....cursoID moodle=".$id.",  total = ".$cuentaAlumnos."\n");
            $courseId = $id;
            //2da parte leer datos de tabla
            //2.1.-universo CURSOS involucrados
            $SQL_cuentaCursos = "
            select count(*) as cuenta
            from cursos
            where id_moddle_servicio = (
            select id from moodle_servicios
            where url_servicio = '$host'	
            )
            and course_id_moodle = $id
            and not cerrado
            ;
            ";
            $fExistenCursos = consulta_sql($SQL_cuentaCursos);	
            $cuentaCursosSGU = $fExistenCursos[0]['cuenta'];
            if ($cuentaCursosSGU > 0) {
                $SQL_cursos = "
                    select id
                    from cursos
                    where id_moddle_servicio = (
                    select id from moodle_servicios
                    where url_servicio = '$host'	
                    )
                    and course_id_moodle = $id
                    and not cerrado
                    ;
                ";
                $fCursos = consulta_sql($SQL_cursos);	
                //$id_curso = $ff[0]['id'];  
                //$id_moodlenombre = $ff[0]['nombre'];
                //$id_moodleurl = $ff[0]['url_servicio'];
                //$id_moodletoken = $ff[0]['token'];
                
//                echo("<br>id_moodlenombre=".$id_moodlenombre);
//                echo("<br>id_moodleurl=".$id_moodleurl);
//                echo("<br>id_moodletoken=".$id_moodletoken);

                for ($x=0;$x<count($fCursos);$x++) {
                    $id_curso = $fCursos[$x]['id'];		                
                    //echo("..........curso BD : ".$id_curso."\n");
                    $SQL_alumnos = "

                    select substring(rut,1,length(rut)-2) as rut_alumno 
                        from alumnos where id in (
                                SELECT id_alumno
                                FROM cargas_academicas WHERE 
                                id_curso IN (select id from cursos where $id_curso IN (id,id_fusion))
                        );
                        ";
                    $ruts_alumnos = "";
                    $fAlumnos = consulta_sql($SQL_alumnos);
                    $cuenta = 0;	
                    for ($y=0;$y<count($fAlumnos);$y++) {
                        $rut_alumno = $fAlumnos[$y]['rut_alumno'];
                        $ruts_alumnos .= $rut_alumno.",";
                        $cuenta++;
                    }
                    $ruts_alumnos = sacaCommaFinalMoodle($ruts_alumnos);
                    $ruts_BD = $ruts_alumnos;
                    //echo("..........curso BD : ".$id_curso.",  total = ".$cuenta." ruts BD = ".$ruts_BD."\n");
                    echo("..........curso BD : ".$id_curso.",  total = ".$cuenta."\n");
                    //VERIFICACION DE LA SINCRONIXACION
                    $SQL_verifica = "
                    select count(*) as cuenta from (
                        select regexp_split_to_table('$ruts_moodle',',')
                        except
                        select regexp_split_to_table('$ruts_BD',',')
                        ) as a
                    ";
                    $fVerifica = consulta_sql($SQL_verifica);	
                    $cuentaVerifica = $fVerifica[0]['cuenta'];
                    if ($cuentaVerifica == 0) {
                        //SINCRONIZAR
                        echo(".......... :) EXCELENT!, SINCRONIZADO...curso = ".$id_curso."\n");
                        $SQL_update = "update cursos SET diferencias_sgu_moodle='OK', diferencias_sgu_moodle_fec=CURRENT_TIMESTAMP
                                    WHERE id=$id_curso OR id_fusion=$id_curso;
                                    ";
                        //echo("\n".$SQL_update."\n");
                        
                        if (consulta_dml($SQL_update) > 0) {
                            echo("..........Actualizado curso = ".$id_curso);
                        } else {
                            echo("..........Error Actualizando curso = ".$id_curso);
                        }

                    } else {
                        //EXISTEN DIFERENCIAS ENTRE LOS RUTS MOODLE y RUTS BD
                        echo(".......... :( CURSO NO SINCRONIZADO...curso = ".$id_curso."\n");
                        //SACAR MOTIVO 1
                        $SQL_diff = "
                        select regexp_split_to_table('$ruts_moodle',',') as rut
                        except
                        select regexp_split_to_table('$ruts_BD',',') as rut
                        ; 
                        ";       
                        $fDiff = consulta_sql($SQL_diff);
                        //$ruts_diff = "";
                        $ruts_diff = "<br>";
                        $nombreAlumno = "";
                        for ($z=0;$z<count($fDiff);$z++) {
                            //$rut_diff = str_pad($fDiff[$z]['rut'],  8, "&nbsp;", STR_PAD_LEFT)." ".obtieneNombreAlumnoMoodle($arrAlumnoCursoMoodle, $fDiff[$z]['rut']);
                            $nombreAlumno = obtieneNombreAlumnoMoodle($arrAlumnoCursoMoodle, $fDiff[$z]['rut']);                            
                            insertaMoodleDesincronizado('MOODLE', $fDiff[$z]['rut'], $nombreAlumno, $nombre_universo, $courseId, $displayname);
                            $rut_diff = $fDiff[$z]['rut']." ".$nombreAlumno;
                            //$ruts_diff .= $rut_diff.",";
                            $ruts_diff .= $rut_diff."<br>";
                        }
                        //$ruts_diff = sacaCommaFinalMoodle($ruts_diff);
                        $ruts_diff_moodle = $ruts_diff;
                        if (count($fDiff)>0) {
                            $diff_A = "Inscripciones no registradas en MOODLE :<br>".$ruts_diff_moodle; 
                        } else {
                            $diff_A = "";
                        }
                        
                        echo($diff_A."\n");
                        //SACAR MOTIVO 2
                        $SQL_diff = "
                        select regexp_split_to_table('$ruts_BD',',') as rut
                        except
                        select regexp_split_to_table('$ruts_moodle',',') as rut
                        ; 
                        ";       
                        $fDiff = consulta_sql($SQL_diff);
                        //$ruts_diff = "";
                        $ruts_diff = "<br>";
                        //$ruts_diff_BD = "";
                        for ($z=0;$z<count($fDiff);$z++) {
                            $rut_diff = $fDiff[$z]['rut'];

                            //------------------------------------------------------------------
                            //$ruts_diff_BD .= $rut_diff.",";
                            //OBTIENE CON DIGITO VERIFICADOR EL RUT
                            $SQL_aa = "
                            select rut, concat(nombres,' ', apellidos) nombre_completo 
                            from alumnos
                            where rut like '$rut_diff%'
                            ";
                            $faa = consulta_sql($SQL_aa);	
                            $rut_diff_sgu = $faa[0]['rut'];
                            $nombre_diff_sgu = $faa[0]['nombre_completo'];



                            insertaMoodleDesincronizado('SGU', $rut_diff_sgu, $nombre_diff_sgu, $nombre_universo, $id_curso, $displayname);                            
                            //------------------------------------------------------------------


                            //$ruts_diff .= $rut_diff."<br>"; //RUT SIN DIGITO VERIFICADOR
                            $ruts_diff .= $rut_diff_sgu." ".$nombre_diff_sgu."<br>";
                        }
                        //$ruts_diff_BD = sacaCommaFinalMoodle($ruts_diff_BD);
                        $ruts_diff_moodle = $ruts_diff;
                        if (count($fDiff)>0) {
                            $diff_B = "Inscripciones no registradas en SGU :<br>".$ruts_diff_moodle;
                        } else {
                            $diff_B = "";
                        }
                        
                        echo($diff_B."\n");
                        //ESCRIBIR RESULTADFO
                        $diff_A_B = $diff_A."\n".$diff_B;
                        $SQL_update = "update cursos SET diferencias_sgu_moodle='$diff_A_B', diferencias_sgu_moodle_fec=CURRENT_TIMESTAMP
                                    WHERE id=$id_curso OR id_fusion=$id_curso;
                                    ";
                        //echo("\n".$SQL_update."\n");
                        
                        if (consulta_dml($SQL_update) > 0) {
                            echo("..........Actualizado direfencias curso = ".$id_curso);
                        } else {
                            echo("..........Error Actualizando curso = ".$id_curso);
                        }
                    
                    }
                
                }


            } else {
                echo("..........No existen registros SGU, descartado!"."\n");
            }




        }
    } else {
        echo("* * * NO SE OBTUVIERON Datos para ".$host.", token : ".$token."\n");
    }    

}
//UBUNTU
//sudo apt-get install php-curl

//$host = "http://190.54.4.77/dumc0521"; //MAYO 2021
//$token = "ee7ea7adb69bb42b8d1f313ca4d4362f";

//$host = "http://190.54.4.77/dumc0821"; //AGOSTO 2021
//$token = "48800c7a615f39276848a605cc721cfd";

//$host = "HTTP://190.54.4.77/MUMC0521CA"; //AGOSTO 2021
//$host = "http://190.54.4.77/mumc0521ca"; //AGOSTO 2021
//$token = "c4873bdf6898c3585d49469e67d3e01f";


function exec_moodle_universo() {
    $SQL_UNIVERSO = "
    select 
    id, 
    nombre, 
    url_servicio as host, 
    token as token
    from moodle_servicios 
    where 
    url_servicio is not null 
    and length(token)>3;
    ";
    actualizaMoodleProceso('S');
    $fUNIVERSO = consulta_sql($SQL_UNIVERSO);
    $cu = 0;
    try {
        for ($uu=0;$uu<count($fUNIVERSO);$uu++) {
            $id_universo = $fUNIVERSO[$uu]['id'];    
            $nombre_universo = $fUNIVERSO[$uu]['nombre'];    
            $url_servicio_universo = $fUNIVERSO[$uu]['host'];    
            $token_universo = $fUNIVERSO[$uu]['token'];    
            $cu++;
            //echo("procesando : ".$cu.".-".$nombre_universo."\n");
            procesar($cu, $nombre_universo, $url_servicio_universo, $token_universo,null);
        }
    } catch (Exception $e) {
    }        
    actualizaMoodleProceso('N');
    //echo("resultado = ".$valor."\n");
    
}
function exec_moodle_parametro($lista_id_curso) {
    //se invoca desde gestion_curso
    $SQL_UNIVERSO = "
    select 
    s.id as id, 
    s.nombre as nombre, 
    s.url_servicio as host, 
    s.token as token
    from moodle_servicios s
    where 
    s.url_servicio is not null 
    and length(s.token)>3
    and s.id in (
        select 
        c.id_moddle_servicio
        from cursos c where c.id in ($lista_id_curso)
        and ((c.diferencias_sgu_moodle != 'OK') or (c.diferencias_sgu_moodle is null))
        and c.id_moddle_servicio is not null
        and c.course_id_moodle is not null
    );

    ";
    
    $fUNIVERSO = consulta_sql($SQL_UNIVERSO);
    actualizaMoodleProceso('S');
    $cu = 0;
    try {

        for ($uu=0;$uu<count($fUNIVERSO);$uu++) {
            $id_universo = $fUNIVERSO[$uu]['id'];    
            $nombre_universo = $fUNIVERSO[$uu]['nombre'];    
            $url_servicio_universo = $fUNIVERSO[$uu]['host'];    
            $token_universo = $fUNIVERSO[$uu]['token'];    
            $cu++;
            echo("<br>procesando : ".$cu.".-".$nombre_universo."\n");
            /*
            $SQL_id_courses = "
                        select 
                        c.course_id_moodle as course_id_moodle
                        from cursos c where c.id in ($lista_id_curso)
                        and id_moddle_servicio = $id_universo
                        ;
                        ";
                        */
                        $SQL_id_courses = "
                        select 
                        c.course_id_moodle as course_id_moodle
                        from cursos c where c.id in ($lista_id_curso)
                        and id_moddle_servicio  in ( 5, 24)
                        ;
                        ";

            $fcourses = consulta_sql($SQL_id_courses);
            $courses_moodle = "";
            for ($x=0;$x<count($fcourses);$x++) {
                $id_courses = $fcourses[$x]['course_id_moodle'];    
                $courses_moodle .= $id_courses.",";
            }
            $courses_moodle = sacaCommaFinalMoodle($courses_moodle);
            echo("<br>courses : ".$courses_moodle);
            procesar($cu, $nombre_universo, $url_servicio_universo, $token_universo, $courses_moodle);
        }
    } catch (Exception $e) {
    }        

    actualizaMoodleProceso('N');
    //echo("ESTOY EN proceso_moodle.php EJECUTADO DESDE gestion_cursos = ".$lista_id_curso);
    
}

if ($argv[1]=="todos") {
    //EJEMPLO
    //sudo php proceso_moodle.php todos
    exec_moodle_universo();
} else {
    $lista_id_curso = $argv[1];
    //EJEMPLO
    //sudo php proceso_moodle.php 15691,15779,15800,15278
    exec_moodle_parametro($lista_id_curso);
}
//echo("parametro= ".$argv[1]);

?>
