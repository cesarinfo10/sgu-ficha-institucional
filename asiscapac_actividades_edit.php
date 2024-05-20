<?php
/*
function cuentaRegistroActividades($ano, $id_asiscapac_origen, 
                                  $id_asiscapac_tipo, 
                                  $id_asiscapac_estado, 
                                  $fecha_inicio, 
                                  $fecha_termino) {
  $ss = "
      select count(*) as cuenta from asiscapac_actividades
    where
    ano = $ano 
    and id_asiscapac_origen = 1
    and id_asiscapac_tipo = 1
    and id_asiscapac_estado = 1
    and fecha_inicio = '$fecha_inicio'
    and fecha_termino = '$fecha_termino'

  ";
  if ($jornada <> "") {
    $ss = $ss." AND       (a.jornada = '$jornada')";
  }
  
  
  
      $sqlCuenta     = consulta_sql($ss);

      //echo("<br>".$ss);


      extract($sqlCuenta[0]);
      return $cuenta;
  
}
  */

  function sacaEstadoActividad($id_asiscapac_actividades) {

        $ss = "
          select id_asiscapac_estado from asiscapac_actividades
          where id = $id_asiscapac_actividades
        ";
        $sql     = consulta_sql($ss);

        //echo("<br>".$ss);


        extract($sql[0]);
        return $id_asiscapac_estado;
}
function sacaPorcAprobacion($id_asiscapac_actividades) {

  $ss = "
    select COALESCE(porc_aprobacion,0) porc_aprobacion from asiscapac_actividades
    where id = $id_asiscapac_actividades
  ";
  $sql     = consulta_sql($ss);

  //echo("<br>".$ss);


  extract($sql[0]);
  return $porc_aprobacion;
}
function sacaSala($id_asiscapac_actividades) {

  $ss = "
    select trim(sala) AS id from asiscapac_actividades
    where id = $id_asiscapac_actividades
  ";
  $sql     = consulta_sql($ss);

  //echo("<br>".$ss);


  extract($sql[0]);
  return $id;
}

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$modulo_destino = "ver_alumno";
 
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
$suspender = $_REQUEST['suspender'];
$cerrar = $_REQUEST['cerrar'];
$eliminar = $_REQUEST['eliminar'];

if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$modo = "EDIT"; //$_REQUEST['modo'];
$id_asiscapac_actividades = $_REQUEST['id_asiscapac_actividades'];
$grabar      = $_REQUEST['grabar'];

$ano            = $_REQUEST['ano'];
$id_origen      = $_REQUEST['id_origen'];
$id_estado_check      = $_REQUEST['id_estado_check'];
$id_campo_actividades      = $_REQUEST['id_campo_actividades'];
$id_aprobacion  = $_REQUEST['id_aprobacion'];
$id_tipo      = $_REQUEST['id_tipo'];
//$id_estado      = $_REQUEST['id_estado'];
$id_descripcion      = $_REQUEST['id_descripcion'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$duracion_minutos  = $_REQUEST['duracion_minutos'];
$id_recordar      = $_REQUEST['id_recordar'];
$id_link_zoom  = $_REQUEST['id_link_zoom'];
$sala        = $_REQUEST['sala'];			  


$estado_actividad = "";
$strActividad = "";
if ($id_asiscapac_actividades<>"") {
  $estado_actividad = sacaEstadoActividad($id_asiscapac_actividades);
  if ($estado_actividad == 1) {
    $strActividad = "PROGRAMADA";
  }
  if ($estado_actividad == 2) {
    $strActividad = "EJECUTADA";
  }
  if ($estado_actividad == 3) {
    $strActividad = "CERRADA";
  }
  if ($estado_actividad == 4) {
    $strActividad = "SUSPENDIDA";
  }

}
/*
if ($grabar == "") {
  $id_aprobacion = sacaPorcAprobacion($id_asiscapac_actividades);
}

echo("<br>id_aprobacion = $id_aprobacion");
*/


if ($ano == "") {
  $ano = $ANO;
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

//echo("<br>ANDES que sucede sala = $sala");
if ($grabar=="") {
  if ($id_asiscapac_actividades != "") {
    $sala = sacaSala($id_asiscapac_actividades);  
    //echo("<br>que sucede sala = $sala");
  }
}

/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
if ($eliminar <> "") {
  //echo("estoy en eliminar");

  //$puedeSeguir = true;
  //if ($puedeSeguir) {
    //echo("<br>estoy en 1");
          $SQL = "
          delete from asiscapac_actividades_obligatorias_funcionarios
          where ano = (
            select ano from asiscapac_actividades
            where 
            id_asiscapac_estado = 1
            and id = $id_asiscapac_actividades
          )
          and id_asiscapac_actividades = $id_asiscapac_actividades 
        ;";
        //echo($SQL);
          if (consulta_dml($SQL) == 1) {
  //          $puedeSeguir = true;

          } else {
  //          $puedeSeguir = false;
          }                 
  //} 
  //if ($puedeSeguir) {
  //  echo("<br>estoy en 2");
          $SQL = "
          delete from asiscapac_zoom
          where ano = (
            select ano from asiscapac_actividades
            where 
            id_asiscapac_estado = 1
            and id = $id_asiscapac_actividades 
          )
          and id_asiscapac_actividades = $id_asiscapac_actividades 
                  ;";
        //echo($SQL);
        if (consulta_dml($SQL) == 1) {
  //          $puedeSeguir = true;

          } else {
  //          $puedeSeguir = false;
          }                 
  //} 
  //if ($puedeSeguir) {
  //  echo("<br>estoy en 3");
          $SQL = "
          delete from asiscapac_actividades
          where 
          id_asiscapac_estado = 1
          and id = $id_asiscapac_actividades
          
        ;";
        //echo($SQL);
        if (consulta_dml($SQL) == 1) {
  //          $puedeSeguir = true;

          } else {
  //          $puedeSeguir = false;
          }                 
  //} 
  //echo("<br>estoy en 4");
  //if ($puedeSeguir) {
    echo(msje_js("Actividad eliminada exitosamente"));
    echo(js("location='$enlbase=asiscapac_actividades_buscar&ano=$ano&id_origen=$id_origen&id_estado_check=$id_estado_check&id_campo_actividades=$id_campo_actividades';"));
  //} else {
  //  echo(msje_js("Error : al momento de eliminar."));          
  //}                  

}

if ($cerrar <> "") { //CERRAR ACTIVIDAD
  $SQL = "
  update asiscapac_actividades
  set 
  id_asiscapac_estado = 3
  where id = $id_asiscapac_actividades

;";
//echo($SQL);
  if (consulta_dml($SQL) > 0) {
    echo(msje_js("Registro Actualizado exitosamente"));
   
//echo(js("location='https://sgu.umc.cl/sgu/principal.php?modulo=../sgu_rc/EFIMERO/asiscapac_actividades_nuevo';"));
//     echo(js("location='$enlbase=asiscapac_actividades_nuevo';"));
echo(js("location='$enlbase=asiscapac_actividades_buscar&ano=$ano&id_origen=$id_origen&id_estado_check=$id_estado_check&id_campo_actividades=$id_campo_actividades';"));

  } else {
          echo(msje_js("Error* : al momento de grabar."));          
  }                  

}

if ($suspender <> "") {
        $SQL_correo = "select email as email_usuario, 
          nombre_usuario as nombre_usuario_operador, 
          nombre as nombre_operador, 
          apellido as apellido_operador  
          from usuarios where id in (
        select id_usuario from asiscapac_actividades_obligatorias_funcionarios
        where ano = $ano
        and id_asiscapac_actividades = $id_asiscapac_actividades
        and convocado = 't'

        )
        and email is not null";


          $envio_correo = consulta_sql($SQL_correo);
          $envioMensaje = false;
          for ($y=0;$y<count($envio_correo);$y++) {
                  extract($envio_correo[$y]);
                  //AQUI DEBE ENVIAR CORREO
                  $sql_act = "select descripcion act_descripcion, 
                        to_char(fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_inicio, 
                        to_char(fecha_termino,'DD \"de\" tmMonth \"de\" YYYY a las HH24:MI') act_fecha_termino, 
                          link_zoom act_link_zoom 
                          from asiscapac_actividades 
                          where id = $id_campo_actividades";
                  $my_act = consulta_sql($sql_act);
                  extract($my_act[0]);
//echo("<br>se envia correo a : $nombre_operador $apellido_operador");

                  $asunto = "SGU: Suspensión de convocatoria para $act_fecha_inicio : $act_descripcion";
                  $cuerpo = "Sr(a) $nombre_operador $apellido_operador, \n\n";
                  $cuerpo .= "Informamos que la convocatoria de Asistencia Obligatoria, relacionada con la actividad '$act_descripcion', la que fue citada para el $act_fecha_inicio horas, queda Suspendida.\n\n";
                  $cuerpo .= "Agradecemos la consideración de esta información.\n\n";
                  $cuerpo .= "Saludos cordiales.\n\n";
                  $cuerpo .= "Unidad de Recursos Humanos\nUniversidad Miguel de Cervantes";
                  $cabeceras = "From: SGU" . "\r\n"
                              . "Content-Type: text/plain;charset=utf-8" . "\r\n";

                  //                mail($email_usuario,$asunto,$cuerpo,$cabeceras);
                  //if ($y == 0) {
                    //mail("rmazuela@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
                    mail("dcarreno@corp.umc.cl",$asunto,$cuerpo,$cabeceras);
                    $envioMensaje = true;
                  //}

          }





          $SQL = "
          update asiscapac_actividades
          set 
          id_asiscapac_estado = 4
          where id = $id_asiscapac_actividades

        ;";
        //echo($SQL);
          if (consulta_dml($SQL) > 0) {
            if ($envioMensaje) {
              echo(msje_js("Se ha se han enviado correctamente los correos con la suspensión de la actividad."));
            } else {
              echo(msje_js("Registro actualizado correctamente, Sin embargo no se han enviado correos."));
            }
            //echo(msje_js("Registro Actualizado exitosamente"));
        echo(js("location='$enlbase=asiscapac_actividades_buscar&ano=$ano&id_origen=$id_origen&id_estado_check=$id_estado_check&id_campo_actividades=$id_campo_actividades';"));

          } else {
                  echo(msje_js("Error** : al momento de grabar."));          
          }                  

}

if ($grabar == "grabar") {
  //if ($modo=="NUEVO") 
  {
    //se procede a almacenar registro.
    //verificaciones de los campos
    $puedeSeguir = true;
    if ($puedeSeguir) {
      if ($id_origen == "") {
        echo(msje_js("Falta Ingresar Origen"));
        $puedeSeguir = false;
      }  
    }

    if ($id_origen == "1") { //capacitacion
      $id_tipo = 0;
      if ($puedeSeguir) {
        if ($id_tipo == "") {
          echo(msje_js("Falta Ingresar Tipo"));
          $puedeSeguir = false;
        }  
      }
    } else {
      $id_tipo = "0";
    }
        /*
    if ($puedeSeguir) {
      if ($id_estado == "") {
        echo(msje_js("Falta Ingresar Estado"));
        $puedeSeguir = false;
      }  
    } 
    */   
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
        echo(msje_js("Falta Ingresar Duración (Minutos)"));
        $puedeSeguir = false;
      }  
    }
    /*
    if ($puedeSeguir) {
      if ($id_recordar == "") {
        echo(msje_js("Falta Ingresar Recordar"));
        $puedeSeguir = false;
      }  
    }
*/
/*
    if ($puedeSeguir) {
      if ($id_link_zoom == "") {
        echo(msje_js("Falta Ingresar Link Zoom"));
        $puedeSeguir = false;
      }  
    }
*/
  if ($id_link_zoom == "") {
    $campoLinkZoom = "null";
  } else {
    $campoLinkZoom = "'$id_link_zoom'";
  }

    if ($id_recordar == "") {
      $campoRecordar = "null";
    } else {
      $campoRecordar = $id_recordar;
    }

    if ($puedeSeguir) {

//      $registros =  cuentaRegistroActividades($ano, $id_asiscapac_origen, 
//                                              $id_asiscapac_tipo, 
//                                              $id_asiscapac_estado, 
//                                              $fec_ini_asist, 
//                                              $fec_fin_asist);

//      if ($registros == 0) 
      {

  
        //$fecha = date("Y-m-d");
        if ($sala == "") {
          $mySala = "null";
        } else {
          $mySala = "'$sala'";
        }
    
              $SQL = "
              update asiscapac_actividades
              set 
                descripcion = '$id_descripcion',
                fecha_inicio = '$fec_ini_asist',
                fecha_termino = '$fec_fin_asist',
                duracion = $duracion_minutos,
                id_asiscapac_recordar = $campoRecordar,
                link_zoom = $campoLinkZoom,
                porc_aprobacion = $id_aprobacion,
                sala = $mySala
              where id = $id_asiscapac_actividades

 ;";
//        echo($SQL);
              if (consulta_dml($SQL) > 0) {
/*                
                $id_tipo = "";
                //$id_estado = "";
                $id_descripcion = "";
                $fec_ini_asist = "";
                $fec_fin_asist = "";
                $duracion_minutos = "";
                $id_recordar = "";
                $id_link_zoom = "";
*/
                echo(msje_js("Registro Actualizado exitosamente"));
               
echo(js("location='$enlbase=asiscapac_actividades_buscar&ano=$ano&id_origen=$id_origen&id_estado_check=$id_estado_check&id_campo_actividades=$id_campo_actividades';"));

              } else {
                      echo(msje_js("Error*** : al momento de grabar."));          
              }                  




      } //else {
        //echo(msje_js("Registro existente."));          
      //}


    }
  }
} else {
  $id_aprobacion = sacaPorcAprobacion($id_asiscapac_actividades);
}



/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */







$sem_ant = $ano_ant = 0;
if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

$condicion = "WHERE $cond_base  ";

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

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }
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

$SQL_consulta = "
select 
ano db_ano,
id db_id_actividad, 
id_asiscapac_origen db_id_asiscapac_origen,
id_asiscapac_tipo db_id_asiscapac_tipo,
descripcion db_descripcion,
fecha_inicio db_fecha_inicio,
fecha_termino db_fecha_termino,
duracion db_duracion, 
id_asiscapac_recordar db_id_asiscapac_recordar,
link_zoom db_link_zoom,
id_asiscapac_estado db_id_asiscapac_estado
from asiscapac_actividades
where id = $id_asiscapac_actividades;
";

//echo("<br>CONSULTA = $SQL_consulta");
$actividades = consulta_sql($SQL_consulta);
extract($actividades[0]);
//$id_origen = $db_id_asiscapac_origen;
$id_tipo = $db_id_asiscapac_tipo;
$id_recordar = $db_id_asiscapac_recordar;

//echo("<br>lectura : id_origen = $id_origen");
//echo("<br>lectura : id_tipo = $id_tipo");
//echo("<br>lectura : id_recordar = $id_recordar");


$sql_origen = "select id, glosa nombre from asiscapac_origen where id = $id_origen order by orden";
$origenes = consulta_sql($sql_origen);

$sql_tipo = "select id, glosa nombre from asiscapac_tipo where id = $db_id_asiscapac_tipo order by orden";
$tipos = consulta_sql($sql_tipo);

//$sql_estados = "select id, glosa nombre from asiscapac_estado order by orden";
//$estados = consulta_sql($sql_estados);

$sql_recordar = "select id, glosa nombre from asiscapac_recordar  order by orden";
$recordars = consulta_sql($sql_recordar);


$salas    = consulta_sql("SELECT trim(codigo) AS id,nombre||' (cap. '||capacidad||')' AS nombre FROM salas WHERE activa ORDER BY piso,nombre;");

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
//$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
//$nombre_arch = "sql-fulltables/$id_sesion.sql";
//file_put_contents($nombre_arch,$SQL_tabla_completa_SIES);

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <input type="hidden" name="id_asiscapac_actividades" value="<?php echo($id_asiscapac_actividades); ?>">
    <input type="hidden" name="id_estado_check" value="<?php echo($id_estado_check); ?>">
    <input type="hidden" name="id_campo_actividades" value="<?php echo($id_campo_actividades); ?>">
    
    
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>


        <td class="celdaFiltro">
          Año: <br>
          <select name='ano' id='id_ano' onChange="submitform();" >
            <?php 
            /*
                    $ss = "";
                    for ($x=$ANO;$x<=($ANO);$x++) {
                      if ($x == $db_ano) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }

                      echo("<option value=$x $ss>$x</option>");
                    }
                    */
                    echo("<option value=$ano selected>$ano</option>");
            ?>
          </select>
        </td>
        
        <td class="celdaFiltro" style='display:none;'>
          Origen: <br>
          <select class="filtro" name="id_origen" id="id_origen"  >
            <!--<option value="">Todos</option>-->
            <?php 
              echo(select($origenes,$id_origen)); 
              //$id_origen = $origenes[0]['id'];
              //$nombre = $origenes[0]['nombre'];
              //  echo("<option value=$id_origen selected>$nombre</option>");
            ?>    
          </select>
        </td>
        <?php if ($id_origen!="2") { //ASISTENCIA?>
                <td class="celdaFiltro">
                  Tipo: <br>
                  <select class="filtro" name="id_tipo" id="id_tipo">
                    <!--<option value="">Todos</option>-->
                    <?php 
                      echo(select($tipos,$id_tipo)); 
                    ?>    
                  </select>
                </td>
        <?php } ?>
        
        <!--<td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="id_estado" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($estados,$id_estado)); 
            ?>    
          </select>
        </td>
        -->

        <td class="celdaFiltro" >      
          Duración (periodo)  :<br>
          Desde:<input type="datetime-local" name="fec_ini_asist" value="<?php echo($db_fecha_inicio); ?>" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
<!--          Hasta:<input type="datetime-local" name="fec_fin_asist" value="<?php echo($db_fecha_termino); ?>" class="boton"> -->
        </td>
        <td class="celdaFiltro" style='display:none;'>      
          Hasta:<input type="datetime-local" name="fec_fin_asist" value="<?php echo($db_fecha_termino); ?>" class="boton">
        </td>

        <td class="celdaFiltro">      
          Duración (Minutos) :<br>
          <select name='duracion_minutos' id='id_duracion_minutos'>
          <option value="">Todos</option>
            <?php 
                    $ss = "";
                    for ($x=15;$x<=600;$x+=15) {
                      if ($x == $db_duracion) {
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
        Sala : <br>
        <select name="sala" id="sala">
        <option value="">-- Seleccione --</option>
        <?php echo(select($salas,$sala)); ?>        
        </select>
      </td>

        <td class="celdaFiltro">
          Recordar: <br>
          <select class="filtro" name="id_recordar">
            <!--<option value="">No Aplicar</option>-->
            <?php 
              echo(select($recordars,$id_recordar)); 
            ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Tiempo mínimo exigido: <br> 
          <!--<select class="filtro" name="id_aprobacion" onChange="submitform();">-->
          <select class="filtro" name="id_aprobacion">
          <!--<option value="">Seleccionar</option>-->
            <?php 
                    $ss = "";
                    for ($x=1;$x<=100;$x++) {
                      if ($x == $id_aprobacion) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }
                      echo("<option value=$x $ss>$x%</option>");
                    }
            ?>
          </select>

        </td>


















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
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
    <tr>
        <td class="celdaFiltro">
          Título Actividad :<br>
          <input type="text" name="id_descripcion" value="<?php echo($db_descripcion); ?>" size="125" id="id_descripcion" class='boton'>
        </td>
      </tr>
      <tr>
        <td class="celdaFiltro">
          link Zoom :<br>
          <input type="text" name="id_link_zoom" value="<?php echo($db_link_zoom); ?>" size="125" id="id_link_zoom" class='boton'>
        </td>
      </tr>

      <td class="celdaFiltro">
          Acción:<br>
          <!--
          &nbsp;
          <input type='submit' name='recordar' value='recordar' style='font-size: 9pt'>
          &nbsp;
          <input type='submit' name='suspender' value='suspender' style='font-size: 9pt'>
          -->
          <?php 
            //echo("QUE SUCEDE1 : $strActividad");
              if ($strActividad != "CERRADA") {          ?>
              <?php if ($strActividad != "SUSPENDIDA") {          ?>
              <input type='submit' name='grabar' value='grabar' style='font-size: 9pt'>
              <?php } ?>

              <?php if ($strActividad!="SUSPENDIDA") { ?>
                      <input type='submit' name='suspender' value='Suspender' style='font-size: 9pt' onClick="return confirm('Está seguro de suspender actividad (Se enviará correo a los participantes)?');">
              <?php } ?>

              <?php if ($strActividad != "SUSPENDIDA") {          ?>
                <input type='submit' name='cerrar' value='Cerrar Actividad' style='font-size: 9pt' onClick="return confirm('Está seguro de cerrar actividad?');">          
                <?php } ?>

              <?php if ($strActividad == "PROGRAMADA")  {?>
                <input type='submit' name='eliminar' value='Eliminar' style='font-size: 9pt' onClick="return confirm('Está seguro de eliminar actividad?');">          
              <?php }?>

              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php } 
          //else 
          { 
            ?>

              <?php 
                //echo("QUE SUCEDE : $strActividad");
                if ($strActividad == "SUSPENDIDA")  {?>
                <input type='submit' name='eliminar' value='Eliminar' style='font-size: 9pt' onClick="return confirm('Está seguro de eliminar actividad?');">          
              <?php }?>


            <input type="button" name="volver" value="Volver"  style='font-size: 9pt' onClick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>'">
          <?php } ?>
        </td>



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
    </table>
</div>

  </form>

<!-- Fin: <?php echo($modulo); ?> -->


<script type="text/javascript">
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

  $("#id_origen").attr("selectedIndex",0);
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

});
</script>
