<?php
/*
function existenRegistros($ano, 
        //$id_asiscapac_origen, 
        $id_actividad, 
        $id_usuario_seleccionado) {

        try {
        $ss = "
        select count(*) as cuenta from asiscapac_actividades_obligatorias_funcionarios
        where
        ano = $ano 
        and id_asiscapac_actividades = $id_actividad
        and id_usuario = $id_usuario_seleccionado
        "; 
 


        $sqlCuenta     = consulta_sql($ss);

//        echo("<br>".$ss);


        extract($sqlCuenta[0]);
        } catch (Exception $e) {
        $cuenta = 0;
        }

        return $cuenta;

}
*/


if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}
 
include("validar_modulo.php");
$modulo_destino = "ver_alumno";

$id_profesores_seleccionados = $_REQUEST['id_profesores_seleccionados'];
//$convocar = $_REQUEST['convocar'];

$ids_carreras = $_SESSION['ids_carreras'];



$id_usuario = $_SESSION['id_usuario']; //1211; //$_SESSION['id_usuario']; //USER
//$id_usuario = 655; 




$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;
 
$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$modo = $_REQUEST['modo'];
$grabar      = $_REQUEST['grabar'];

$ano            = $_REQUEST['ano'];
$id_origen      = "2"; //$_REQUEST['id_origen']; //ACTIVIDADES_OBLIGATORIAS
$id_tipo      = $_REQUEST['id_tipo'];
$id_tipo_check      = $_REQUEST['id_tipo_check'];
$id_estado      = $_REQUEST['id_estado'];
$id_descripcion      = $_REQUEST['id_descripcion'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$duracion_minutos  = $_REQUEST['duracion_minutos'];
$id_recordar      = $_REQUEST['id_recordar'];
$id_link_zoom  = $_REQUEST['id_link_zoom'];

$id_campo_actividades  = $_REQUEST['id_campo_actividades'];  
$id_estado_actividades  = $_REQUEST['id_estado_actividades'];

$id_estado_check = $_REQUEST['id_estado_check'];

$SQL = "select min(ano) ano_min_db from asiscapac_actividades;"; 
$anitos = consulta_sql($SQL);
extract($anitos[0]);

if ($ano_min_db == "") {
  $ano_min_db = $ANO;
} 

$SQL = "select to_char(periodo_desde,'YYYY') ano_vigente  from periodo_eval where activo = 't';";  
$ano_vigente = consulta_sql($SQL);
extract($ano_vigente[0]);

if ($ano == "") {
  $ano = $ano_vigente;
}

/*
echo("<br>modo...$modo");
echo("<br>grabar...$grabar");
echo("<br>ano...$ano");
echo("<br>id_origen...$id_origen");
echo("<br>id_tipo...$id_tipo");
echo("<br>id_estado...$id_estado");
echo("<br>id_descripcion...$id_descripcion");
echo("<br>fec_ini_asist...$fec_ini_asist");
echo("<br>fec_fin_asist...$fec_fin_asist");
echo("<br>duracion_minutos...$duracion_minutos");
echo("<br>id_recordar...$id_recordar");
echo("<br>id_link_zoom...$id_link_zoom");
*/


$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$ano_egreso        = $_REQUEST['ano_egreso'];
$semestre_egreso   = $_REQUEST['semestre_egreso'];
$fec_ini_egreso    = $_REQUEST['fec_ini_egreso'];
$fec_fin_egreso    = $_REQUEST['fec_fin_egreso'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$aprob_ant         = $_REQUEST['aprob_ant'];
$matriculado       = $_REQUEST['matriculado'];

if (empty($_REQUEST['matriculado'])) { $matriculado = ""; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['ano_egreso'])) { $ano_egreso = $ANO; $semestre_egreso = -1; }
if (empty($_REQUEST['fec_ini_egreso'])) { $fec_ini_egreso = date("Y")."-01-01"; }
if (empty($_REQUEST['fec_fin_egreso'])) { $fec_fin_egreso = date("Y-m-d"); }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { $cond_base = "ae.nombre='Egresado'"; }


//VERIFICA SI id_actividad corresponde al año, sino entonces id_actividad = ''
/*
if ($id_campo_actividades <> "") {
  $SQL_actCorrige = "select count(*) as cuenta from asiscapac_actividades
  where id = $id_campo_actividades and ano = $ano";
  $actCorrige = consulta_sql($SQL_actCorrige);
  extract($actCorrige[0]);
  if ($cuenta == 0) {
    $id_campo_actividades = "";
  }
  //echo("<br>CUENTA = $cuenta");
}
*/
//////FIN CORRIGE


/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*
if ($convocar <> "") {
  if ($id_profesores_seleccionados == "") {
    echo(msje_js("No tiene convocados para enviár correo."));          
  } else {
//    echo("*** *** ***");

    $puedeSeguir = true;
    if ($puedeSeguir) {
//      echo("<br>id_profesores_seleccionados = $id_profesores_seleccionados");
      $usuarios = explode(",",trim($id_profesores_seleccionados));
//      echo("count usuarios = ".count($usuarios));
      for ($x=0;$x<count($usuarios);$x++) {
              $id_usuario_seleccionado 	= $usuarios[$x];
//              echo("<br>usuario seleccionado = ".$id_usuario_seleccionado);



              $cuenta = existenRegistros($ano, 
                                    //$id_asiscapac_origen, 
                                    $id_campo_actividades, 
                                    $id_usuario_seleccionado);
                //echo("<br>cuenta = $cuenta");
              if ($cuenta==0) {

                      //$fecha = date("Y-m-d");
                      $SQL = "
                      insert into asiscapac_actividades_obligatorias_funcionarios
                      (ano, 
                      id_asiscapac_actividades, 
                      id_usuario, 
                      convocado
                      ) 
                      (select 
                      $ano, 
                      $id_campo_actividades, 
                      id,
                      't'
                      from usuarios where id = $id_usuario_seleccionado
                      )
                      ;";
//                       echo("<br>$SQL");
                      if (consulta_dml($SQL) > 0) {

                      } else {
                      }                  
              } else {
                      $SQL = "
                      update asiscapac_actividades_obligatorias_funcionarios
                      set 
                      convocado = 't'
                      where 
                      ano = $ano
                      and id_asiscapac_actividades = $id_campo_actividades
                      and id_usuario = $id_usuario_seleccionado
                      ;"; 
//                        echo("<br>$SQL");
                      consulta_dml($SQL);      
              }


              //
              //BUSCAR  A LOS USUARIOS PARA ENVIAR CORREO DERIVACION DE AREA
              //
              //if ($id_area_derivacion <> "") {
                      $SQL_correo = "select email as email_usuario, 
                      nombre_usuario as nombre_usuario_operador, 
                      nombre as nombre_operador, 
                      apellido as apellido_operador  
                      from usuarios where id in ($id_profesores_seleccionados)";
                      $envio_correo = consulta_sql($SQL_correo);
                      $envioMensaje = false;
                      for ($y=0;$y<count($envio_correo);$y++) {
                              extract($envio_correo[$y]);
                              //AQUI DEBE ENVIAR CORREO
                              //chemp

                              $sql_act = "select descripcion act_descripcion, 
                                    to_char(fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_inicio, 
                                    to_char(fecha_termino,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_termino, 
                                      link_zoom act_link_zoom 
                                      from asiscapac_actividades 
                                      where id = $id_campo_actividades";
                              $my_act = consulta_sql($sql_act);
                              extract($my_act[0]);


                              $asunto = "SGU: Convocatoria para $act_fecha_inicio : $act_descripcion";
                              $cuerpo = "Sr(a) $nombre_operador $apellido_operador le informa que se ha creado una nueva convocatoria relacionada con con la actividad '$act_descripcion' \n";
                              $cuerpo .= "la cual comienza el $act_fecha_inicio \n";
                              $cuerpo .= "y termina el $act_fecha_termino \n";
                              $cuerpo .= "presione el siguiente enlace para unirse : $act_link_zoom \n";            
                              $cuerpo .= "\n\n\n";

                              $cuerpo .= "\n\n\n";
                              $cuerpo .= "\n\n";
                              $cuerpo .= "Este es un correo automático, favor no responder.";

                              $cabeceras = "From: SGU" . "\r\n"
                              . "Content-Type: text/plain;charset=utf-8" . "\r\n";

                              //                mail($email_usuario,$asunto,$cuerpo,$cabeceras);
                              if ($y == 0) {
                              //mail("rmazuela@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
                              mail("dcarreno@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
                              $envioMensaje = true;
                              }

                              //echo(msje_js("Correo para $email_usuario")); 
                      }
          

              //}
              }

              if ($envioMensaje) {
                        echo(msje_js("Se ha se han enviado correctamente los correos con la convocatoria."));
//                echo("VAMOOOO");
              }
                
      }


      
  }
}
*/



/*
if ($grabar == "grabar") {
  if ($modo=="NUEVO") {
    //se procede a almacenar registro.
    //verificaciones de los campos
    $puedeSeguir = true;
    if ($puedeSeguir) {
      if ($id_origen == "") {
        echo(msje_js("Falta Ingresar Origen"));
        $puedeSeguir = false;
      }  
    }

    if ($puedeSeguir) {
      if ($id_tipo == "") {
        echo(msje_js("Falta Ingresar Tipo"));
        $puedeSeguir = false;
      }  
    }
    if ($puedeSeguir) {
      if ($id_estado == "") {
        echo(msje_js("Falta Ingresar Estado"));
        $puedeSeguir = false;
      }  
    }    
    if ($puedeSeguir) {
      if ($id_descripcion == "") {
        echo(msje_js("Falta Ingresar Descripción"));
        $puedeSeguir = false;
      }  
    }
    if ($puedeSeguir) {
      if ($fec_ini_asist == "") {
        echo(msje_js("Falta Ingresar Fecha Desde"));
        $puedeSeguir = false;
      }  
    }
    if ($puedeSeguir) {
      if ($fec_fin_asist == "") {
        echo(msje_js("Falta Ingresar Fecha Hasta"));
        $puedeSeguir = false;
      }  
    }
    if ($puedeSeguir) {
      if ($duracion_minutos == "") {
        echo(msje_js("Falta Ingresar Duración (Horas)"));
        $puedeSeguir = false;
      }  
    }
    
      //    if ($puedeSeguir) {
      //      if ($id_recordar == "") {
      //        echo(msje_js("Falta Ingresar Recordar"));
      //        $puedeSeguir = false;
      //     }  
      //   }

    if ($puedeSeguir) {
      if ($id_link_zoom == "") {
        echo(msje_js("Falta Ingresar Link Zoom"));
        $puedeSeguir = false;
      }  
    }

    if ($id_recordar == "") {
      $campoRecordar = "null";
    } else {
      $campoRecordar = $id_recordar;
    }

    if ($puedeSeguir) {
            //$fecha = date("Y-m-d");
            $SQL = "
            insert into asiscapac_actividades(
            id_asiscapac_origen,
            id_asiscapac_tipo,
            descripcion,
            fecha_inicio,
            fecha_termino,
            duracion, 
            id_asiscapac_recordar,
            link_zoom,
            id_asiscapac_estado
          ) values
          (
            $id_origen, 
            $id_tipo, 
            '$id_descripcion',
            '$fec_ini_asist',
            '$fec_fin_asist',
            $duracion_minutos, 
            $campoRecordar,
            '$id_link_zoom',
            $id_estado      
          )
            ;";
      //echo($SQL);
            if (consulta_dml($SQL) > 0) {
                    echo(msje_js("Registro Exitoso"));
                    $id_tipo = "";
                    $id_estado = "";
                    $id_descripcion = "";
                    $fec_ini_asist = "";
                    $fec_fin_asist = "";
                    $duracion_minutos = "";
                    $id_recordar = "";
                    $id_link_zoom = "";
              
      //              echo(js("location='$enlbase=crear_moodle_servicio';"));
            } else {
                    echo(msje_js("Error : al momento de grabar."));          
            }                  



    }
  }
}
*/



/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */







//$sem_ant = $ano_ant = 0;
//if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
//elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

//$condicion = "WHERE $cond_base  ";
/*
if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
		            . " a.rut ~* '$cadena_buscada' OR "
		            . " text(a.id) ~* '$cadena_buscada' "
		            . ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {

	if ($cohorte > 0) {
		$condicion .= "AND (cohorte = '$cohorte') ";
	}

	if ($semestre_cohorte > 0) {
		$condicion .= "AND (semestre_cohorte = $semestre_cohorte) ";
	}

	if ($mes_cohorte > 0) {
		$condicion .= "AND (mes_cohorte = $mes_cohorte) ";
	}
	 
	if ($ano_egreso > 0) {
		$condicion .= "AND (a.ano_egreso = $ano_egreso) ";
		if ($semestre_egreso <> "-1") { $condicion .= "AND (semestre_egreso = $semestre_egreso) "; }
	} elseif ($ano_egreso == "-2") {
		if ($fec_ini_egreso <> "" && $fec_fin_egreso <> "") {
			$condicion .= " AND (a.fecha_egreso between '$fec_ini_egreso'::date AND '$fec_fin_egreso'::date) ";
		}
	}

	if ($moroso_financiero <> "-1") {
		$condicion .= "AND (moroso_financiero = '$moroso_financiero') ";
	}
	
	if ($id_carrera <> "") {
		$condicion .= "AND (carrera_actual = '$id_carrera') ";
	}

	if ($jornada <> "") {
		$condicion .= "AND (a.jornada = '$jornada') ";
	}

	if ($admision <> "") {
		$condicion .= "AND (a.admision = '$admision') ";
	}

	if ($regimen <> "" && $regimen <> "t") {
		$condicion .= "AND (c.regimen = '$regimen') ";
	}

	if ($matriculado == "t") {
		$condicion .= "AND (m.id_alumno IS NOT NULL) ";
	} elseif ($matriculado == "f") {
		$condicion .= "AND (m.id_alumno IS NULL) ";
	}
	
	switch ($aprob_ant) {
		case 1:
			$condicion .= " AND (($SQL_tasa_aprob_ant) = 0) ";
			break;
		case 2:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 1 AND 39.9) ";
			break;
		case 3:
			$condicion .= " AND (($SQL_tasa_aprob_ant) BETWEEN 40 AND 100) ";
			break;
	}
}
*/
//if (!empty($ids_carreras) && empty($id_carrera)) {
//	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
//}

//$limite_reg = "LIMIT $cant_reg";
//if ($cant_reg == -1) { $limite_reg = ""; }
/*
$SQL_al_presente = "SELECT count(periodo) AS semestre_presente
                    FROM (SELECT id_alumno,ano||'-'||semestre as periodo 
                          FROM vista_alumnos_cursos 
                          WHERE id_alumno=a.id AND semestre>0 
                          GROUP BY id_alumno,periodo) AS foo 
                    GROUP BY id_alumno";
*/                    
/*
$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||'/'||aet.nombre ELSE ae.nombre END AS estado,
                       CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       semestre_egreso||'-'||ano_egreso AS periodo_egreso,
                       (ano_egreso-cohorte+1)*2+CASE WHEN semestre_egreso <= semestre_cohorte THEN -1 ELSE 0 END AS duracion
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY nombre 
                $limite_reg
                OFFSET $reg_inicio;";
                */
//$alumnos = consulta_sql($SQL_alumnos);

/*
$SQL_consulta = "
select
id,                    
ano,                   
--id_asiscapac_origen,
(select glosa from asiscapac_origen where id = id_asiscapac_origen)   glosa_origen,
id_asiscapac_tipo   ,  
descripcion          , 
fecha_inicio          ,
fecha_termino         ,
duracion              ,
--id_asiscapac_recordar ,
(select glosa from asiscapac_recordar where id = id_asiscapac_recordar)   glosa_origen,
link_zoom             ,
--id_asiscapac_estado,
(select glosa from asiscapac_estado where id = id_asiscapac_estado)   glosa_estado
from asiscapac_actividades 
where 
ano = $ano
";
*/
/*
if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_origen = $id_origen";
}
*/
/*
if ($id_tipo != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_tipo = $id_tipo";
}
*/
/*
if ($id_estado != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_estado = $id_estado";
}
*/
/*
if ($duracion_minutos != "") {
  $SQL_consulta = $SQL_consulta." and duracion = $duracion_minutos";
}
*/
/*
if ($id_recordar != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_recordar = $id_recordar";
}
*/
/*
if ($fec_ini_egreso <> "" && $fec_fin_egreso <> "") {
  $condicion .= " AND (a.fecha_egreso between '$fec_ini_egreso'::date AND '$fec_fin_egreso'::date) ";
}
*/
//$SQL_consulta = $SQL_consulta." order by descripcion";











//$actividades = consulta_sql($SQL_consulta);
if ($id_origen != "") {
  //if ($id_campo_actividades != "") {
    $SQL_funcionarios = "
    select
        f.id id_actividad_obligatoria, 
        a.id id_actividades, 
        a.descripcion glosa_actividad,
        to_char(a.fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') fecha_inicio,
        (
            select chk.glosa glosa_estado from asiscapac_actividades_funcionarios_check chk where chk.id = f.id_asiscapac_actividades_funcionarios_check
        ) glosa_estado,
        a.duracion minutos_reunion,
        (
          select sum(duracion_minutos) from asiscapac_zoom z
          where upper(z.email) = (
            select upper(u.email) from usuarios u where u.id = f.id_usuario
            ) 
          and z.id_asiscapac_actividades = a.id
          
          ) sus_minutos,
            f.observacion observacion,
            a.link_zoom link_zoom,
    (select glosa from asiscapac_estado where id = a.id_asiscapac_estado) estado_actividad,
    a.porc_aprobacion porc_aprobacion       
--    , (
--      select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
--      ) sala     
    , (
      select s.nombre FROM salas s where s.codigo = a.sala
      ) sala     
  
    from asiscapac_actividades_obligatorias_funcionarios f, asiscapac_actividades a
    where f.id_usuario = $id_usuario
    and f.ano = $ano
    and f.convocado = 't'
    and a.id = f.id_asiscapac_actividades
    and a.id_asiscapac_origen = $id_origen
    ";
    if ($id_campo_actividades != "") {
      $SQL_funcionarios = $SQL_funcionarios." and f.id_asiscapac_actividades = $id_campo_actividades";
    }
    if ($id_estado_actividades != "") {
      $SQL_funcionarios = $SQL_funcionarios." and a.id_asiscapac_estado = $id_estado_actividades";
    }
    $SQL_funcionarios = $SQL_funcionarios." order by a.fecha_inicio";

        //echo("$SQL_funcionarios");
        $funcionarios = consulta_sql($SQL_funcionarios);
  //}
}
/*
$sql_porc_asistencia = "
select 
((
select count(*) from asiscapac_actividades_obligatorias_funcionarios
where ano = $ano
and id_usuario = $id_usuario
and id_asiscapac_actividades_funcionarios_check not in (5,7) --todos menos los inasistentes y sin correo
and 
)
/
(
select count(*) from asiscapac_actividades_obligatorias_funcionarios
where ano = $ano
and id_usuario = $id_usuario
)::decimal * 100
) as porc_asistencia
";
*/
$sql_porc_asistencia = "
select (
  select count(*) from asiscapac_actividades_obligatorias_funcionarios a, asiscapac_actividades b
  where a.ano = $ano
  and a.id_usuario = $id_usuario
  and a.id_asiscapac_actividades_funcionarios_check not in (5,7) --todos menos los inasistentes y sin correo
  and b.id = a.id_asiscapac_actividades
  and b.id_asiscapac_estado in (2,3) -- ejecutada, cerrada
  )
  /
  (
  select count(*) from asiscapac_actividades_obligatorias_funcionarios a, asiscapac_actividades b
  where a.ano = $ano
  and a.id_usuario = $id_usuario
  and b.id = a.id_asiscapac_actividades
  and b.id_asiscapac_estado in (2,3) -- ejecutada, cerrada
  ) 
  ::decimal*100 as porc_asistencia
  ";

$porcentajes_asistencia = consulta_sql($sql_porc_asistencia);
extract($porcentajes_asistencia[0]);
//$porc_asistencia = round(($porc_asistencia)*100,1);
$porc_asistencia = round(($porc_asistencia),0);


$sql_origen = "select id, glosa nombre from asiscapac_origen where id = $id_origen order by orden";
$origenes = consulta_sql($sql_origen);

$sql_tipo = "select id, glosa nombre from asiscapac_tipo order by orden";
$tipos = consulta_sql($sql_tipo);

$sql_estados = "select id, glosa nombre from asiscapac_estado order by orden";
$estados = consulta_sql($sql_estados);

$sql_recordar = "select id, glosa nombre from asiscapac_recordar order by orden";
$recordars = consulta_sql($sql_recordar);




//$sql_estados_check = "select id, glosa nombre from asiscapac_actividades_funcionarios_check ";
//$estados_check = consulta_sql($sql_estados_check);




//$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
//$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
//$nombre_arch = "sql-fulltables/$id_sesion.sql";


$sql_campo_actividades = "
select
id,                    
descripcion      as nombre
from asiscapac_actividades 
where 
ano = $ano
";
if ($id_origen != "") {
  $sql_campo_actividades = $sql_campo_actividades." and id_asiscapac_origen = $id_origen";
}
$sql_campo_actividades = $sql_campo_actividades." order by descripcion" ;
//echo("<br>$sql_campo_actividades");
$campos_actividades = consulta_sql($sql_campo_actividades);

$sql_estado_actividades = "select id, glosa as nombre from asiscapac_estado order by id";
$estados_actividades = consulta_sql($sql_estado_actividades);


?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

    <input type='hidden' id='id_profesores_seleccionados' name='id_profesores_seleccionados'>
<!--    <input type='hidden' id='id_current_url' name='id_current_url' value=<?php echo($id_current_url); ?>> -->

    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>


        <td class="celdaFiltro">
          Año: <br>
          <select name='ano' id='id_ano' onChange="submitform();">
            <?php 
                    $ss = "";
                    for ($x=$ano_min_db;$x<=($ano_vigente);$x++) {
                      if ($x == $ano) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }

                      echo("<option value=$x $ss>$x</option>");
                    }
            ?>
          </select>
        </td>
        
<td class="celdaFiltro" style='display:none;'>
  Origen: <br>
  <select class="filtro" name="id_origen" id="id_origen" onChange="submitform();">
    <!--<option value="">Todos</option>-->
    <?php 
      echo(select($origenes,$id_origen)); 
    ?>    
  </select>
  <input type="button" name='volver' value="volver" style='font-size: 9pt' onclick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_buscar&ano=<?php echo($ano); ?>&id_origen=2&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>';"/>
  <!--<input type="button" name='gestionar' value="gestionar" style='font-size: 9pt' onclick="window.location.href='https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/asiscapac_actividades_buscar&ano=<?php echo($ano); ?>&id_origen=2&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>';"/>-->
  <!--<input type="button" name='gestionar' value="gestionar" style='font-size: 9pt' onclick="window.location.href=getCurrentURL();"/>-->
</td>
<!--
        <td class="celdaFiltro">
          Actividades: <br>
          <select class="filtro" name="id_campo_actividades" id="id_campo_actividades" onChange="submitform();">
            <option value="">(Todas)</option>
            <?php 
              echo(select($campos_actividades,$id_campo_actividades)); 
            ?>    
          </select>
        </td>
                  -->
        <td class="celdaFiltro">
          Actividades Estados: <br>
          <select class="filtro" name="id_estado_actividades" id="id_estado_actividades" onChange="submitform();">
            <option value="">(Todas)</option>
            <?php 
              echo(select($estados_actividades,$id_estado_actividades)); 
            ?>    
          </select>
        </td>

        <?php if ($id_origen!="2") { //ASISTENCIA?>
                <td class="celdaFiltro">
                  Tipo: <br>
                  <select class="filtro" name="id_tipo" id="id_tipo" onChange="submitform();">
                    <option value="">Todos</option>
                    <?php 
                      echo(select($tipos,$id_tipo)); 
                    ?>    
                  </select>
                </td>
        <?php } ?>
        <!--
        <td class="celdaFiltro">
          Estado Actividades: <br>
          <select class="filtro" name="id_estado" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($estados,$id_estado)); 
            ?>    
          </select>
        </td>

        -->
        <!--
        <td class="celdaFiltro">
          Estado Check: <br>
          <select class="filtro" name="id_estado_check" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($estados_check,$id_estado_check)); 
            ?>    
          </select>
        </td>
        -->
        <!--
        <td class="celdaFiltro">
          Acción: <br>
          <input type="button" name='volver' value="volver" onclick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_buscar&ano=<?php echo($ano); ?>&id_origen=2&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>';"/>
          <input type="submit" name='convocar' value="convocar"/>
        </td>
        -->

        

<!--
        <td class="celdaFiltro">      
          Duración  :<br>
          Desde:<input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
          Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton">
        </td>
        -->
        <!--
        <td class="celdaFiltro">      
          Duración (minutos) :<br>
          <select name='duracion_minutos' id='id_duracion_minutos' onChange="submitform();">
          <option value="">Todos</option>
            <?php 
                    $ss = "";
                    for ($x=1;$x<=300;$x++) {
                      if ($x == $duracion_minutos) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }
                      echo("<option value=$x $ss>$x</option>");
                    }
            ?>
          </select>

        </td>
        <td class="celdaFiltro">
          Recordar: <br>
          <select class="filtro" name="id_recordar" onChange="submitform();">
            <option value="">No Aplicar</option>
            <?php 
              echo(select($recordars,$id_recordar)); 
            ?>    
          </select>
        </td>

                  -->















<!--
        <td class="celdaFiltro">
          Cohorte: <br>
<?php if ($regimen <> "PRE") { ?>          
          <select class="filtro" name="mes_cohorte" onChange="submitform();">
            <option value="0">-- mes --</option>
            <?php echo(select($meses_fn,$mes_cohorte)); ?>    
          </select>
          -
<?php } ?>
          <select class="filtro" name="semestre_cohorte" onChange="submitform();">
            <option value="0"></option>
            <?php echo(select($SEMESTRES_COHORTES,$semestre_cohorte)); ?>    
          </select>
          -
          <select class="filtro" name="cohorte" onChange="submitform();">
            <option value="0">Todas</option>
            <?php echo(select($cohortes,$cohorte)); ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Año Egreso: <br>
          <select class="filtro" name="ano_egreso" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($anos_egresos,$ano_egreso)); ?>
          </select>
          <?php if ($ano_egreso > 0) { ?>
          <select class="filtro" name="semestre_egreso" onChange="submitform();">
            <option value="-1">- Semestre --</option>
            <?php echo(select($semestres_egreso,$semestre_egreso)); ?>
          </select>
          <?php } ?>
          <?php if ($ano_egreso == -2) { ?>
          <input type="date" placeholder="Fec. ini" name="fec_ini_egreso" value="<?php echo($fec_ini_egreso); ?>" size="10" class="boton" style='font-size: 9pt'>
          <input type="date" placeholder="Fec. fin" name="fec_fin_egreso" value="<?php echo($fec_fin_egreso); ?>" size="10" class="boton" style='font-size: 9pt'>
          <script>document.getElementById("fec_ini_egreso").focus();</script>
          <input type='submit' name='buscar' value='Buscar' style='font-size: 9pt'>
          <?php } ?>
        </td>
        <td class="celdaFiltro">
          Moroso: <br>
          <select class="filtro" name="moroso_financiero" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($sino,$moroso_financiero)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Admisión: <br>
          <select class="filtro" name="admision" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($ADMISION,$admision)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Matriculado: <br>
          <select class="filtro" name="matriculado" onChange="submitform();">
            <option value="a">Todos</option>
            <?php echo(select($sino,$matriculado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Jornada:<br>
          <select class="filtro" name="jornada" onChange="submitform();">
            <option value="">Ambas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Régimen: <br>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
-->        
      </tr>
    </table>
    <input type="hidden" name="modo" id="modo" value="<?php echo($modo); ?>">
<!--    
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      
      <tr>
        <td class="celdaFiltro">
          Descripción :<br>
          <input type="text" name="id_descripcion" value="<?php echo($id_descripcion); ?>" size="110" id="id_descripcion" class='boton'>
        </td>
      </tr>
      <tr>
        <td class="celdaFiltro">
          link Zoom :<br>
          <input type="text" name="id_link_zoom" value="<?php echo($id_link_zoom); ?>" size="110" id="id_link_zoom" class='boton'>
        </td>
      </tr>

      <td class="celdaFiltro">
          Acción:<br>
          <input type='submit' name='grabar' value='grabar' style='font-size: 9pt'>
        </td>
          -->


      <!--
      <tr>
        <td class="celdaFiltro">
          Buscar por ID, RUT o nombre:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="60" id="texto_buscar" class='boton'>
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo(" <input type='submit' name='buscar' value='Vaciar'>");
          	};
          ?>          <script>document.getElementById("texto_buscar").focus();</script>
        </td>
        <td class="celdaFiltro">
          Acciones:<br>
          <a id="sgu_fancybox" href='<?php echo("$enlbase_sm=candidatos_egreso&regimen=$regimen"); ?>' class='boton'>Procesar y detectar Candidatos</a>
        </td>
      </tr>
          -->
    <!--</table>-->
</div>

<?php //if ($modo == "BUSCAR") 
{?>
<table cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="texto">
      <strong>Asistencia : <?php echo($porc_asistencia); ?>%</strong> (este porcentaje será utilizado en su Evaluación de Desempeño.)<br>
    </td>
  </tr>
</table>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" id='id_tabla_profesores'>
  <!--
  <tr bgcolor="#F1F9FF">

    <td class="texto" colspan="3">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="5">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa_SIES); ?>
    </td>
  </tr>
          -->
  <tr class='filaTituloTabla'>

<!--    <td class='tituloTabla'>Id Actividad</td> -->
    <td class='tituloTabla'>Actividad</td>
    <td class='tituloTabla'>Estado</td>
<!--    <td class='tituloTabla'>Fecha Inicio</td> -->
    <td class='tituloTabla'>Asistencia</td>
<!--    <td class='tituloTabla'>Minutos Zoom</td> -->
<!--    <td class='tituloTabla'>Sus minutos</td>-->
    <td class='tituloTabla'>Observación</td>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($funcionarios) > 0) {
		for ($x=0;$x<count($funcionarios);$x++) {
			extract($funcionarios[$x]);
      //echo("convocado = ".$convocado);
      $mySala = "";
      $myZoom = "";
      if ($link_zoom != "") {
        $myZoom = "<a href='$link_zoom' target='_blank'>Reunión</a>";
      }
      if ($sala!="") {
        $mySala = "Sala : $sala <br>";
      }
      $HTML_funcionarios .= "  <tr class='filaTabla'>\n"
//      . "    <td class='textoTabla' align='right'>$id_actividades</td>\n"
      . "    <td class='textoTabla' align='left'><strong>$glosa_actividad</strong><br>
                                                 $fecha_inicio<br>
                                                 Duración $minutos_reunion min, tiempo mínimo exigido : $porc_aprobacion%<br>
                                                 $mySala
                                                 $myZoom
                                                 </td>\n"
      . "    <td class='textoTabla' align='left'>$estado_actividad</td>\n"
//      . "    <td class='textoTabla' align='left'>$fecha_inicio</td>\n"
      . "    <td class='textoTabla' align='left'>$glosa_estado<br>
                                                $sus_minutos min</td>\n"
//      . "    <td class='textoTabla' align='right'>$minutos_reunion</td>\n"
//      . "    <td class='textoTabla' align='right'>$sus_minutos</td>\n"
      . "    <td class='textoTabla' align='left'>$observacion</td>\n"
      . "  </tr>\n";
		}

	} else {
		$HTML_funcionarios .= "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_funcionarios);
?>
</table><br>

<?php } ?>
  </form>

<!-- Fin: <?php echo($modulo); ?> -->


<script type="text/javascript">

  function marcarTodos() {
    console.log("estot en marcarTodos");

    var profSeleccionados = "";
    var sql_actualizar_curso_tmp = "";

    $("#id_profesores_seleccionados").val(profSeleccionados);

    maxFilas = sacaMaxFilas();
    usuarios_seleccionados = "";
    for (let i = 0; i <= maxFilas; i++) {
            try {
                  todosCheck = document.getElementById("id_todos_check");
                  if (todosCheck.checked == true){
                    opcionMarcarTodos = true;
                  } else {
                    opcionMarcarTodos = false;
                  }

                  idCheckBox = "id_incluir_"+i;
                  console.log("number one i="+i);
                  controlIdCheckBox = "#"+idCheckBox;
                  console.log("control = "+controlIdCheckBox);
                  if (opcionMarcarTodos) {
                    $(controlIdCheckBox).prop( "checked", true );
                  } else {
                    $(controlIdCheckBox).prop( "checked", false );
                  }
                  
                  console.log("number two");
                  cursoSelected = document.getElementById(idCheckBox);
                  
                  if (cursoSelected.checked == true){
                    //console.log("seleccionado = " + idCheckBox);
                    id_usuario = sacaValorColumna(i);
                    //console.log("id_usuario = "+id_usuario);
                    usuarios_seleccionados = usuarios_seleccionados + id_usuario + ",";
                  } else {
                    //console.log("debe cambiar color FONDO, inactivo");
                    cambiaColorFondoRow(i, false);
                  }
          } catch (error) {
              //SE HIZO PO>R LOS BLANCOS
              //console.error(error);
          }

    }
        


    var ss = usuarios_seleccionados;
    if (ss.length > 1) {
      ss = ss.substr(0,ss.length - 1); 
      //console.log(profSeleccionados);
      //console.log(sql_actualizar_curso_tmp);
      $("#id_profesores_seleccionados").val(ss);
      //$("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
      //$("#sql_eliminar_curso_tmp").val(sql_eliminar_curso_tmp);
      //$("#sql_crear_curso_tmp").val(sql_crear_curso_tmp);

    } else {
      $("#id_profesores_seleccionados").val("");
    }
  }
  function sacaMaxFilas() {
      var maxFilas = 0;
      $("#id_tabla_profesores tr").each(function (index) {
          if (!index) return;
          i = 1;
/*          
          $(this).find("td").each(function () {
  //            if (i == 1) {
                  //primera fila
  //            }
              //var id = $(this).text().toLowerCase().trim();
              //console.log("id="+id);
              maxFilas++;
          });
          */
          maxFilas++;
      });
      //console.log("*****regs totales : " + maxFilas);
      //maxFilas = maxFilas / 6; //MAX-ROWS 
      //console.log("*****maxFilas : " + maxFilas);
      return maxFilas;
    }  
    function sacaValorColumna(fila) {
      //console.log("estoy en sacaValorColumna("+fila+")");
      var maxFilas = 0;
      var idSeleccionados = "";
      $("#id_tabla_profesores tr").each(function (index) {
          if (!index) return;
          if (maxFilas == fila) {
            i = 0;            
                  $(this).find("td").each(function () {
          //            if (i == 1) {
                          //primera fila
          //            }
                      if (i == 4) { //id_uario
                        var id_usuario = $(this).text().toLowerCase().trim();
                        //console.log("maxFilas = " + maxFilas + ", i=" + i + "---* * *>id_usuario="+id_usuario);
                        idSeleccionados = idSeleccionados + id_usuario;
                      }
                      //var id = $(this).text().toLowerCase().trim();
                      //console.log("id="+id);
                      i++;
                  });
                  //break;
          }

          maxFilas++;
      });
      //console.log("*****regs totales : " + maxFilas);
      //console.log("*****maxFilas : " + maxFilas);
      //console.log("idSeleccionado = "+idSeleccionados);


      return idSeleccionados;
    }  

function armarQuerys() {
  //console.log("estoy en armarQuerys");
    var profSeleccionados = "";
    var sql_actualizar_curso_tmp = "";

    $("#id_profesores_seleccionados").val(profSeleccionados);
    //$("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
    
    maxFilas = sacaMaxFilas();
    usuarios_seleccionados = "";
    for (let i = 0; i <= maxFilas; i++) {
            try {
                  idCheckBox = "id_incluir_"+i;
                  cursoSelected = document.getElementById(idCheckBox);
                  if (cursoSelected.checked == true){
                    //console.log("seleccionado = " + idCheckBox);
                    id_usuario = sacaValorColumna(i);
                    //console.log("id_usuario = "+id_usuario);
                    usuarios_seleccionados = usuarios_seleccionados + id_usuario + ",";
                  } else {
                    //console.log("debe cambiar color FONDO, inactivo");
                    cambiaColorFondoRow(i, false);
                  }
          } catch (error) {
              //SE HIZO PO>R LOS BLANCOS
              //console.error(error);
          }

    }
    



    var ss = usuarios_seleccionados;
    if (ss.length > 1) {
      ss = ss.substr(0,ss.length - 1); 
      //console.log(profSeleccionados);
      //console.log(sql_actualizar_curso_tmp);
      $("#id_profesores_seleccionados").val(ss);
      //$("#sql_actualizar_curso_tmp").val(sql_actualizar_curso_tmp);
      //$("#sql_eliminar_curso_tmp").val(sql_eliminar_curso_tmp);
      //$("#sql_crear_curso_tmp").val(sql_crear_curso_tmp);

    } else {
      $("#id_profesores_seleccionados").val("");
    }

  }
/*
function setCurrentURL () {
  $("#id_current_url").val(window.location.href);
}
*/

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 1000,
		'height'			: 550,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: true,
		'titleShow'         : false,
		'titlePosition'     : 'inside',
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'width'				: 600,
		'height'			: 550,
		'maxHeight'			: 550,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});

  /*
  $("#id_origen").change(function(){
  
    alert($(this).val());
  
      if ($(this).val()==1) {
          //capacitacion
          //$('#id_tipo').prop('disabled', true);
          //DESAHILITAR
          //$('#id_tipo').attr('disabled', 'disabled');
          $('#id_tipo').prop('disabled', true);
          alert("inhabilitado");
      } else {
        //$('#id_tipo').prop('disabled', false);
        //HABILITAR
        $('#id_tipo').removeAttr('disabled');
        alert("habiliatado");
      }

  });
*/

//setCurrentURL();
}

);
</script>
