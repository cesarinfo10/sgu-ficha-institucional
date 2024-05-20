<?php
if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");
$modulo_destino = "ver_alumno";
 
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;
 
$reg_inicio = $_REQUEST['r_inicio']; 
if ($reg_inicio=="") { $reg_inicio = 0; }

$modo = $_REQUEST['modo'];
$grabar      = $_REQUEST['grabar'];


$ocultar_uno  = $_REQUEST['ocultar_uno'];
$ocultar_dos  = $_REQUEST['ocultar_dos'];

if ($ocultar_uno == "") {
  $ocultar_uno = "NO";
}
if ($ocultar_dos == "") {
  $ocultar_dos = "NO";
}


$id_unidad  = $_REQUEST['id_unidad'];
$id_usuario_seleccionado  = $_REQUEST['id_usuario_seleccionado'];
$id_estado_evidencia = $_REQUEST['id_estado_evidencia'];
$id_fecha_orden = $_REQUEST['id_fecha_orden'];

$id_usuario_confirmar  = $_REQUEST['id_usuario_confirmar'];

$confirmar = $_REQUEST['confirmar'];
$id_campo_usuario_capacitaciones = $_REQUEST['id_campo_usuario_capacitaciones'];
$usuario_evidencia = $_REQUEST['usuario_evidencia'];
$id_tipo_general_capacitacion = $_REQUEST['id_tipo_general_capacitacion'];
$ano            = $_REQUEST['ano'];
$id_origen      = "1"; //$_REQUEST['id_origen']; //CAPACITACIONES
$id_estado_check = $_REQUEST['id_estado_check'];
$id_campo_actividades = $_REQUEST['id_campo_actividades'];
$id_tipo      = $_REQUEST['id_tipo'];
$id_estado      = $_REQUEST['id_estado'];
$id_mes      = $_REQUEST['id_mes'];
$id_descripcion      = $_REQUEST['id_descripcion'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$duracion_minutos  = $_REQUEST['duracion_minutos'];
$id_recordar      = $_REQUEST['id_recordar'];
$id_link_zoom  = $_REQUEST['id_link_zoom'];

$SQL = "select to_char(periodo_desde,'YYYY') ano_vigente  from periodo_eval where activo = 't';"; 
$anos_vigentes = consulta_sql($SQL);
extract($anos_vigentes[0]);


if ($ano == "") {
  $ano = $ano_vigente; //$ANO
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

$eliminar_evidencia = $_REQUEST['eliminar_evidencia'];
$confirmar_eliminar_evidencias = $_REQUEST['confirmar_eliminar_evidencias'];
$eliminar_capacitacion = $_REQUEST['eliminar_capacitacion'];
$confirmar_eliminar_capacitacion = $_REQUEST['confirmar_eliminar_capacitacion'];
$capacitaciones_usuarios_id = $_REQUEST['capacitaciones_usuarios_id'];

$id_doctos_digitalizados = $_REQUEST['id_doctos_digitalizados'];

//$accion_capacitacion      = $_REQUEST['accion_capacitacion'];
$id_campo_capacitaciones      = $_REQUEST['id_campo_capacitaciones'];

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


$SQL = "
update asiscapac_capacitaciones
set id_asiscapac_estado = 2
	where ano = $ano
	and id_asiscapac_estado = 1
	and now() >= fecha_inicio+CAST('1 days' AS INTERVAL)
;"; 
//                        echo("<br>$SQL");
consulta_dml($SQL);      

$SQL = "
update asiscapac_usuario_capacitaciones
set id_asiscapac_estado = 2
	where ano = $ano
	and id_asiscapac_estado = 1
	and now() >= fecha_inicio+CAST('1 days' AS INTERVAL)
;"; 
//                        echo("<br>$SQL");
consulta_dml($SQL);   


if ($eliminar_evidencia=="SI") {  
	$mensaje = "Se eliminará evidencia nº$id_doctos_digitalizados\\n"
		         . "Está seguro(a)?";
             
    $linkAquiMismo = "capac_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_campo_usuario_capacitaciones=$id_campo_usuario_capacitaciones&ocultar_uno=$accion_etiqueta_uno&ocultar_dos=$accion_etiqueta_dos&id_doctos_digitalizados=$id_doctos_digitalizados&id_mes=$id_mes&id_unidad=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado&usuario_evidencia=$usuario_evidencia";         
		$url_si = "$enlbase=$linkAquiMismo&eliminar_evidencia=&confirmar_eliminar_evidencias=SI";
		$url_no = "$enlbase=$linkAquiMismo&eliminar_evidencia=&confirmar_eliminar_evidencias=NO";
		echo(confirma_js($mensaje,$url_si,$url_no));
  
}
if ($confirmar_eliminar_evidencias=="SI") {  
  $SQL = "delete from capac_doctos_digitalizados where id = $id_doctos_digitalizados;"; 
                       //   echo("<br>$SQL");
  consulta_dml($SQL);  
  $eliminar_evidencia = ""; 
  $confirmar_eliminar_evidencias = "";

  //verificamos si no queda ninguna evidencia, si es así entonces debe dejar el estado de la evidencia = null
  //redbull
  $SQL_evidencias = "
  select 
  count(*) as cuenta_evidencia_usuario from 
  capac_doctos_digitalizados
  where 
  id_usuario = $usuario_evidencia
  and id_asiscapac_usuario_capacitaciones = $id_campo_usuario_capacitaciones
  and eliminado = 'f'
  ";
 // echo($SQL_evidencias);

  $evidencias_usuario = consulta_sql($SQL_evidencias);
  extract($evidencias_usuario[0]);
  
  if ($cuenta_evidencia_usuario == 0) {
    $SQL = "
    update asiscapac_usuario_capacitaciones
    set confirmado = null
    where id = $id_campo_usuario_capacitaciones
    ;"; 
    //echo("<br>$SQL");
    consulta_dml($SQL);   
    
  }


}  



if ($eliminar_capacitacion=="SI") {  
	$mensaje = "Se eliminará capacitación nº$capacitaciones_usuarios_id, con todas sus evidencias.\\n"
		         . "Está seguro(a)?";
             
    $linkAquiMismo = "capac_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_campo_usuario_capacitaciones=$id_campo_usuario_capacitaciones&ocultar_uno=$accion_etiqueta_uno&ocultar_dos=$accion_etiqueta_dos&id_doctos_digitalizados=$id_doctos_digitalizados&id_mes=$id_mes&id_unidad=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado&usuario_evidencia=$usuario_evidencia&capacitaciones_usuarios_id=$capacitaciones_usuarios_id";         
		$url_si = "$enlbase=$linkAquiMismo&eliminar_capacitacion=&confirmar_eliminar_capacitacion=SI";
		$url_no = "$enlbase=$linkAquiMismo&eliminar_capacitacion=&confirmar_eliminar_capacitacion=NO";
		echo(confirma_js($mensaje,$url_si,$url_no));
  
}
if ($confirmar_eliminar_capacitacion=="SI") {  
/*
  $SQL_USUARIO_CAPACITACIONES = "
  select id_asiscapac_usuario_capacitaciones
  from capac_doctos_digitalizados
  where id = $id_doctos_digitalizados
  
  ";
  echo("</br>".$SQL_USUARIO_CAPACITACIONES);

  $usuario_capacitaciones = consulta_sql($SQL_USUARIO_CAPACITACIONES);
  extract($usuario_capacitaciones[0]);
  */

  $SQL = "delete from capac_doctos_digitalizados where id_asiscapac_usuario_capacitaciones = $capacitaciones_usuarios_id;"; 
  //echo("<br>$SQL");
  consulta_dml($SQL);  
  $eliminar_capacitacion = ""; 
  $confirmar_eliminar_capacitacion = "";

  //verificamos si no queda ninguna evidencia, si es así entonces debe dejar el estado de la evidencia = null
  //redbull
  $SQL_DELETE = "delete from asiscapac_usuario_capacitaciones where id = $capacitaciones_usuarios_id";
  //echo("<br>$SQL_DELETE");
  consulta_dml($SQL_DELETE);  
  

}  

$SQL = "select min(ano) ano_min_db from asiscapac_capacitaciones;"; 
$anitos = consulta_sql($SQL);
extract($anitos[0]);

if ($ano_min_db == "") {
  $ano_min_db = $ano_vigente;
} 

/*
if ($accion_capacitacion <> "") {
    if ($accion_capacitacion = "CERRAR") {
        $idEstado = 3;
    }
    if ($accion_capacitacion = "SUSPENDER") {
      $idEstado = 4;
    }

    $SQL = "
    update asiscapac_capacitaciones
    set 
    id_asiscapac_estado = $iEstado
    where 
    id = $id_campo_capacitaciones
    ;"; 
    //                        echo("<br>$SQL");
    consulta_dml($SQL);      
}
*/
if ($confirmar <> "") {
  if ($id_usuario_confirmar <> "") {
//    $cuenta = existenRegistros($ano, 
//                    //$id_asiscapac_origen, 
//                    $id_campo_capacitaciones, 
//                    $id_usuario_confirmar);
//    //echo("<br>cuenta = $cuenta");
    if ($confirmar == "SI") {
      $bConfirmar = 't';
    } else {
      $bConfirmar = 'f';
    }
//    if ($cuenta==0) {
//          //$fecha = date("Y-m-d");
//          $SQL = "
//          insert into asiscapac_capacitaciones_funcionarios
//          (ano, 
//          id_asiscapac_capacitaciones, 
//          id_usuario, 
//          convocado,
//          confirmado
//          ) 
//          (select 
//          $ano, 
//          $id_campo_capacitaciones, 
//          id,
//          't',
//          '$bConfirmar'
//          from usuarios where id = $id_usuario_confirmar
//          )
//          ;";
//    //                       echo("<br>$SQL");
//          if (consulta_dml($SQL) > 0) {
//          } else {
//          }                  
//    } else {
          $SQL = "
          update asiscapac_usuario_capacitaciones
          set 
          confirmado = '$bConfirmar'
          where 
          id = $id_campo_usuario_capacitaciones
          ;"; 
    //                        echo("<br>$SQL");
          consulta_dml($SQL);      
//    }

  }
}

/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */

//--ACTIVIDADES PROGRAMADAS con 1 dia debe dejarlas EJECUTADAS

/*
$SQL = "
update asiscapac_actividades
set id_asiscapac_estado = 2
	where ano = $ano
	and id_asiscapac_estado = 1
	and now() >= fecha_inicio+CAST('1 days' AS INTERVAL)
;"; 
//                        echo("<br>$SQL");

consulta_dml($SQL);      
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
        echo(msje_js("Falta Ingresar Duración (Minutos)"));
        $puedeSeguir = false;
      }  
    }
    
      //    if ($puedeSeguir) {
      //      if ($id_recordar == "") {
      //        echo(msje_js("Falta Ingresar Recordar"));
      //        $puedeSeguir = false;
      //      }  
      //    }

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

/*





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

*/
/*
$SQL_consulta = "
select 
a.id id_capacitacion, 
(select glosa from asiscapac_origen where id = a.id_asiscapac_origen) glosa_origen,
(select glosa from asiscapac_tipo where id = a.id_asiscapac_tipo) glosa_tipo,
(select glosa from asiscapac_subtipo where id = a.id_asiscapac_subtipo) glosa_subtipo,
a.descripcion descripcion,
to_char(a.fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY <br>a las HH24:MI') fecha_inicio, 
to_char(a.fecha_termino,'DD \"de\" tmMonth \"de\" YYYY <br>a las HH24:MI') fecha_termino, 
a.duracion duracion, 
--(select glosa from asiscapac_recordar where id = a.id_asiscapac_recordar) glosa_recordar,
--a.link_zoom link_zoom,
(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) glosa_estado,
--a.porc_aprobacion porc_aprobacion,

--CONVOCADOS
--(select count(*) from asiscapac_actividades_obligatorias_funcionarios b
--where b.ano = $ano
--and b.id_asiscapac_actividades = a.id
--and b.convocado = 't') as universo_convocados,
0 as universo_convocados, 
--PRESENTES
--(select count(*) from asiscapac_actividades_obligatorias_funcionarios b
--where b.ano = $ano
--and b.id_asiscapac_actividades = a.id
--and b.convocado = 't'
--and b.id_asiscapac_actividades_funcionarios_check = 2 --PRESENTES
--) as universo_presentes,
0 as universo_presentes,
--JUSTIFICADOS
--(select count(*) from asiscapac_actividades_obligatorias_funcionarios b
--where b.ano = $ano
--and b.id_asiscapac_actividades = a.id
--and b.convocado = 't'
--and b.id_asiscapac_actividades_funcionarios_check = 3 --JUSTIFICADOS
--) as universo_justificados,
0 as universo_justificados,
--LICENCIA MEDICA
--(
--select count(*) from asiscapac_actividades_obligatorias_funcionarios b
--where b.ano = $ano
--and b.id_asiscapac_actividades = a.id
--and b.convocado = 't'
--and b.id_asiscapac_actividades_funcionarios_check = 4 --LICENCIA MEDICA
--) as universo_licencia_medica,
0 as universo_licencia_medica,
--INASISTENTE
--(
--select count(*) from asiscapac_actividades_obligatorias_funcionarios b
--where b.ano = $ano
--and b.id_asiscapac_actividades = a.id
--and b.convocado = 't'
--and b.id_asiscapac_actividades_funcionarios_check = 5 --INASISTENTE
--) as universo_inasistente
0 as universo_inasistente
, (
  select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
  ) sala
from asiscapac_capacitaciones a
where true
";
*/
$SQL_consulta = "
select 
a.id id_capacitacion, 
(select glosa from asiscapac_origen where id = a.id_asiscapac_origen) glosa_origen,
(select glosa from asiscapac_tipo where id = a.id_asiscapac_tipo) glosa_tipo,
a.id_asiscapac_tipo id_asiscapac_tipo, 
a.descripcion descripcion,
to_char(a.fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY') fecha_inicio, 
to_char(a.fecha_termino,'DD \"de\" tmMonth \"de\" YYYY') fecha_termino, 
to_char(a.fecha_inicio,'YYYY-MM-DD') fec_ini_asist,
to_char(a.fecha_termino,'YYYY-MM-DD') fec_fin_asist,
a.duracion duracion, 
id_asiscapac_estado id_asiscapac_estado_db, 
(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) glosa_estado,
--CONVOCADOS
(select count(*) from asiscapac_capacitaciones_funcionarios b
where b.ano = $ano
and b.id_asiscapac_capacitaciones = a.id
and b.convocado = 't') as universo_convocados,
(select count(*) from asiscapac_capacitaciones_funcionarios b
where b.ano = $ano
and b.id_asiscapac_capacitaciones = a.id
and b.confirmado = 't') as universo_confirmados,
0 as universo_presentes,
0 as universo_justificados,
0 as universo_licencia_medica,
0 as universo_inasistente,
trim(a.sala) id_sala
, (
  select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
  ) sala
  ,a.link_capacitaciones link_capacitaciones
from asiscapac_capacitaciones a
where true
";


if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.ano = $ano";
}
if ($id_tipo_general_capacitacion != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_tipo = $id_tipo_general_capacitacion";
}

if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_origen = $id_origen";
}
if ($id_tipo_general_capacitacion != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_tipo = $id_tipo_general_capacitacion";
}
if ($id_estado != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_estado = $id_estado";
}
if ($duracion_minutos != "") {
  $SQL_consulta = $SQL_consulta." and a.duracion = $duracion_minutos";
}
/*
if ($id_recordar != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_recordar = $id_recordar";
}
*/
if ($id_mes != "") {
  $SQL_consulta = $SQL_consulta." and EXTRACT(    MONTH FROM a.fecha_inicio ) = $id_mes";
}

if ($id_fecha_orden == "") {
  $sqlfecha_orden = "order by a.descripcion";
}
if ($id_fecha_orden == "1") {
  $sqlfecha_orden = "order by a.fecha_inicio asc, a.descripcion ";
}
if ($id_fecha_orden == "2") {
  $sqlfecha_orden = "order by a.fecha_inicio desc, a.descripcion ";
}



//$SQL_consulta = $SQL_consulta." order by a.fecha_inicio asc";
$SQL_consulta = $SQL_consulta.$sqlfecha_orden;


//echo("<br>CONSULTA = $SQL_consulta");
$capacitaciones = consulta_sql($SQL_consulta);









$SQL_consulta = "
select 
a.id, 
a.id capacitaciones_usuarios_id,
g.nombre nombre_unidad,
a.id id_asiscapac_usuario_capacitaciones,
id_usuario id_user,
u.nombre_usuario nombre_usuario, 
concat(u.nombre,' ',u.apellido) nombre_completo,
a.id id_capacitacion, 
(select glosa from asiscapac_tipo where id = a.id_asiscapac_tipo) glosa_tipo,
a.id_asiscapac_tipo id_asiscapac_tipo, 
a.descripcion descripcion,
to_char(a.fecha_inicio,'DD \"de\" tmMonth \"de\" YYYY') fecha_inicio, 
to_char(a.fecha_termino,'DD \"de\" tmMonth \"de\" YYYY') fecha_termino, 
to_char(a.fecha_inicio,'YYYY-MM-DD') fec_ini_asist,
to_char(a.fecha_termino,'YYYY-MM-DD') fec_fin_asist,
a.duracion duracion, 
a.id_asiscapac_estado id_asiscapac_estado_db, 
(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) glosa_estado,
0 as universo_convocados,
0 as universo_presentes,
0 as universo_justificados,
0 as universo_licencia_medica,
0 as universo_inasistente,
trim(a.sala) id_sala
, (
  select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
  ) sala
  ,a.link_capacitaciones link_capacitaciones,
  (select nombre from regimenes where id = a.id_regimen) glosa_regimen,
a.id_regimen id_regimen,
(
  select 
        CASE when ((
          select count(*) 
          from asiscapac_usuario_capacitaciones
          where confirmado = 't' and fecha_aceptacion is not null
          and id = a.id
        )> 0) THEN 'SI' 
        ELSE (
          CASE when ((
            select count(*) 
            from asiscapac_usuario_capacitaciones
            where confirmado = 'f' and fecha_revocar is not null
            and id = a.id
          ) > 0) THEN 'NO' 
          ELSE 
            'NADA'
          END
          )
        END
        ) AS confirmado,
observacion as observacion,
observacion_revocar as observacionrevocar
from asiscapac_usuario_capacitaciones a, usuarios u, gestion.unidades g
where 
      u.id = a.id_usuario 
      and g.id = u.id_unidad
";

//$SQL_consulta = $SQL_consulta." and id_usuario = $id_usuario";

//if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.ano = $ano";
//}

/*
if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_origen = $id_origen";
}
*/
if ($id_usuario_seleccionado != "") {
  $SQL_consulta = $SQL_consulta." and a.id_usuario = $id_usuario_seleccionado"; 
}
if ($id_unidad != "") {
  $SQL_consulta = $SQL_consulta." and u.id_unidad = $id_unidad";
}

if ($id_tipo_general_capacitacion != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_tipo = $id_tipo_general_capacitacion";
}
if ($id_estado != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_estado = $id_estado";
}
if ($duracion_minutos != "") {
  $SQL_consulta = $SQL_consulta." and a.duracion = $duracion_minutos";
}
/*
if ($id_recordar != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_recordar = $id_recordar";
}
*/
if ($id_mes != "") {
  $SQL_consulta = $SQL_consulta." and EXTRACT(    MONTH FROM a.fecha_inicio ) = $id_mes";
}
if ($id_estado_evidencia <> "") {
//  if ($id_estado_evidencia == 0) { //TODOS  

//  }
  if ($id_estado_evidencia == 1) { //VALIDADAS
    $SQL_consulta = $SQL_consulta." 
    and (
      select 
            CASE when ((
              select count(*) 
              from asiscapac_usuario_capacitaciones
              where confirmado = 't' and fecha_aceptacion is not null
              and id = a.id
            )> 0) THEN 'SI' 
            ELSE (
              CASE when ((
                select count(*) 
                from asiscapac_usuario_capacitaciones
                where confirmado = 'f' and fecha_revocar is not null
                and id = a.id
              ) > 0) THEN 'NO' 
              ELSE 
                'NADA'
              END
              )
            END
            ) = 'SI'
            ";
    
  }
  if ($id_estado_evidencia == 2) { //REVOCADAS
    $SQL_consulta = $SQL_consulta." 
    and (
      select 
            CASE when ((
              select count(*) 
              from asiscapac_usuario_capacitaciones
              where confirmado = 't' and fecha_aceptacion is not null
              and id = a.id
            )> 0) THEN 'SI' 
            ELSE (
              CASE when ((
                select count(*) 
                from asiscapac_usuario_capacitaciones
                where confirmado = 'f' and fecha_revocar is not null
                and id = a.id
              ) > 0) THEN 'NO' 
              ELSE 
                'NADA'
              END
              )
            END
            ) = 'NO'
            ";

  }
  if ($id_estado_evidencia == 3) { //SIN ESTADO
    $SQL_consulta = $SQL_consulta." 
    and (
      select 
            CASE when ((
              select count(*) 
              from asiscapac_usuario_capacitaciones
              where confirmado = 't' and fecha_aceptacion is not null
              and id = a.id
            )> 0) THEN 'SI' 
            ELSE (
              CASE when ((
                select count(*) 
                from asiscapac_usuario_capacitaciones
                where confirmado = 'f' and fecha_revocar is not null
                and id = a.id
              ) > 0) THEN 'NO' 
              ELSE 
                'NADA'
              END
              )
            END
            ) = 'NADA'
            ";

  }

}
if ($id_fecha_orden == "") {
  $sqlfecha_orden = "order by a.id_usuario, a.descripcion, a.fecha_inicio desc";
}
if ($id_fecha_orden == "1") {
  $sqlfecha_orden = "order by a.fecha_inicio asc, a.descripcion ";
}
if ($id_fecha_orden == "2") {
  $sqlfecha_orden = "order by a.fecha_inicio desc, a.descripcion ";
}

//$SQL_consulta = $SQL_consulta." order by a.id_usuario, a.descripcion, a.fecha_inicio desc";
$SQL_consulta = $SQL_consulta.$sqlfecha_orden;
//echo("<br>CONSULTA = $SQL_consulta");
$capacitaciones_voluntarias = consulta_sql($SQL_consulta);


$SQL_COMPLETA = "
select 
c.id
,c.ano
--,c.id_asiscapac_origen
,'CAPACITACION' origen
--,c.id_asiscapac_tipo
,(select glosa from asiscapac_tipo where id = c.id_asiscapac_tipo) glosa_tipo
,c.descripcion
,to_char(c.fecha_inicio,'dd/mm/yyyy') fecha_inicio
,to_char(c.fecha_termino,'dd/mm/yyyy') fecha_termino
,c.duracion duracion_horas
,c.link_capacitaciones
--,c.id_asiscapac_estado
,(select glosa from asiscapac_estado where id = c.id_asiscapac_estado) glosa_estado
,c.sala 
--id,
--,ano
--id_asiscapac_capacitaciones
,f.id_usuario
,(select nombre_usuario from usuarios where id = f.id_usuario) usuario
,(select concat(nombre,' ',apellido) from usuarios where id = f.id_usuario) nombre_usuario
--,id_asiscapac_actividades_funcionarios_check
,f.observacion
--,convocado
,(case when f.convocado = 't' then 'SI' else 'NO' end) convocado
,f.confirmado
,f.observacion_revocar
,to_char(f.fecha_aceptacion,'dd/mm/yyyy') fecha_aceptacion
,to_char(f.fecha_aceptacion,'hh:mi:ss') hora_aceptacion
,to_char(f.fecha_revocar,'dd/mm/yyyy') fecha_revocar
,to_char(f.fecha_revocar,'hh:mi:ss') hora_revocar
,(
	select count(*) from capac_doctos_digitalizados
	where id_asiscapac_capacitaciones = f.id_asiscapac_capacitaciones
	and id_usuario = f.id_usuario
 ) evidencias

from asiscapac_capacitaciones c, asiscapac_capacitaciones_funcionarios f
where c.ano = $ano
and f.id_asiscapac_capacitaciones = c.id
";

$SQL_tabla_completa = "COPY ($SQL_COMPLETA) to stdout WITH CSV HEADER";



$SQL_COMPLETA_VOLUNTARIA = "
select 
--id
--,ano
--id_asiscapac_tipo
(select glosa from asiscapac_tipo where id = f.id_asiscapac_tipo) tipo
,f.descripcion
,f.fecha_inicio
,f.fecha_termino
,f.duracion duracion_horas
,f.link_capacitaciones
,(select glosa from asiscapac_estado where id = f.id_asiscapac_estado) estado
,f.sala
,f.id_usuario
,(select nombre_usuario from usuarios where id = f.id_usuario) usuario
,(select concat(nombre,' ',apellido) from usuarios where id = f.id_usuario) nombre_usuario
,f.confirmado
,f.observacion_revocar
,f.observacion
,to_char(f.fecha_aceptacion,'dd/mm/yyyy') fecha_aceptacion
,to_char(f.fecha_aceptacion,'hh:mi:ss') hora_aceptacion
,to_char(f.fecha_revocar,'dd/mm/yyyy') fecha_revocar
,to_char(f.fecha_revocar,'hh:mi:ss') hora_revocar
--,f.id_regimen
from asiscapac_usuario_capacitaciones f
where ano = $ano
";

$SQL_tabla_completa_voluntaria = "COPY ($SQL_COMPLETA_VOLUNTARIA) to stdout WITH CSV HEADER";


/*
$enlace_nav = "$enlbase=$modulo"
            . "&mes_cohorte=$mes_cohorte"
            . "&semestre_cohorte=$semestre_cohorte"
            . "&cohorte=$cohorte"
            . "&estado=$estado"
            . "&moroso_financiero=$moroso_financiero"
            . "&admision=$admision"            
            . "&matriculado=$matriculado"
            . "&id_carrera=$id_carrera"
            . "&jornada=$jornada"
            . "&regimen=$regimen"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";
*/
//if (count($alumnos) > 0) {
  /*
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos 
	                       FROM alumnos AS a 
	                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
	                       LEFT JOIN al_estados AS ae ON ae.id=a.estado
	                       LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
	                       $condicion";
                         */
	//$total_alumnos = consulta_sql($SQL_total_alumnos);
	//$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	//$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
//}
/*
if (count($alumnos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_alumno&id_alumno={$alumnos[0]['id']}&rut={$alumnos[0]['rut']}';"));
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$APROB_ANT = array(array("id" => 1, "nombre" => 'Mala (0%)'),
                   array("id" => 2, "nombre" => 'Regular (1% ~ 39%)'),
                   array("id" => 3, "nombre" => 'Buena (40% ~ 100%)'));
*/
                   /*
$SQL_anos_egresos = "SELECT DISTINCT ON (ano_egreso) ano_egreso AS id,ano_egreso AS nombre 
                     FROM alumnos AS a
                     LEFT JOIN al_estados AS ae ON ae.id=a.estado
                     WHERE $cond_base
                     ORDER BY id DESC";
$anos_egresos = consulta_sql($SQL_anos_egresos);
$anos_egresos = array_merge(array(array('id'=>-2,'nombre'=>"Otro")),$anos_egresos);
*/

$sql_origen = "select id, glosa nombre from asiscapac_origen where id = $id_origen order by orden";
$origenes = consulta_sql($sql_origen);

$sql_tipo = "select id, glosa nombre from asiscapac_tipo where id > 0 order by orden";
$tipos = consulta_sql($sql_tipo);

$sql_estados = "select id, glosa nombre from asiscapac_estado order by orden";
$estados = consulta_sql($sql_estados);

$sql_unidades = "select id, concat(alias,' - ', nombre) nombre from gestion.unidades where activa = 't' order by alias";
$unidades = consulta_sql($sql_unidades);


$sql_usuarios = "
select u.id id, concat(u.nombre_usuario ,' - ' ,u.nombre ,' ' ,u.apellido) as nombre
from usuarios u
where 
u.tipo <> 3 
and u.activo";
if ($id_unidad != "") {
  $sql_usuarios = $sql_usuarios." and u.id_unidad = $id_unidad";
}
$sql_usuarios = $sql_usuarios." order by u.nombre, u.apellido";

$usuarios = consulta_sql($sql_usuarios);


$sql_estado_evidencia = "
select id, nombre from (
	select 0 id, 'Todas' nombre
	union
	select 1 id, 'Validada' nombre
	union
	select 2 id, 'Revocada' nombre
	union
	select 3 id, 'Sin estado' nombre
	) as a
	order by id
";
$estados_evidencias = consulta_sql($sql_estado_evidencia);


$sql_fechas_ordenes = "
select id, nombre from (
	select 1 id, 'Ascendente' nombre
	union
	select 2 id, 'Descendente' nombre
	) as a
	order by id
";
$fechas_ordenes = consulta_sql($sql_fechas_ordenes);


$sql_recordar = "select id, glosa nombre from asiscapac_recordar order by orden";
$recordars = consulta_sql($sql_recordar);

//$id_sesion = "CAPAC_".$_SESSION['usuario']."_".$modulo."_".session_id();
$id_sesion = "CAPAC_".$_SESSION['usuario']."_capac_buscar_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$id_sesion = "CAPAC_VOLUNTARIA_".$_SESSION['usuario']."_capac_buscar_".session_id();
$boton_tabla_completa_voluntaria = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa_voluntaria);

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

    <input type="hidden" name="id_campo_usuario_capacitaciones" value="<?php echo($id_campo_usuario_capacitaciones); ?>">
    <input type="hidden" name="usuario_evidencia" value="<?php echo($usuario_evidencia); ?>">
    
    
    <input type="hidden" name="id_estado_check" id="id_estado_check" value="<?php echo($id_estado_check); ?>">
    <input type="hidden" name="ocultar_uno" id="ocultar_uno" value="<?php echo($ocultar_uno); ?>">
    <input type="hidden" name="ocultar_dos" id="ocultar_dos" value="<?php echo($ocultar_dos); ?>">
    <input type="hidden" name="id_campo_actividades" id="id_campo_actividades" value="<?php echo($id_campo_actividades); ?>">
    <input type="hidden" name="eliminar_evidencia" id="eliminar_evidencia" value="<?php echo($eliminar_evidencia); ?>">    
    <input type="hidden" name="confirmar_eliminar_evidencias" id="confirmar_eliminar_evidencias" value="<?php echo($confirmar_eliminar_evidencias); ?>">        
    <input type="hidden" name="eliminar_capacitacion" id="eliminar_capacitacion" value="<?php echo($eliminar_capacitacion); ?>">    
    <input type="hidden" name="confirmar_eliminar_capacitacion" id="confirmar_eliminar_capacitacion" value="<?php echo($confirmar_eliminar_capacitacion); ?>">        
    <input type="hidden" name="capacitaciones_usuarios_id" id="capacitaciones_usuarios_id" value="<?php echo($capacitaciones_usuarios_id); ?>">        
    
    <input type="hidden" name="id_doctos_digitalizados" id="id_doctos_digitalizados" value="<?php echo($id_doctos_digitalizados); ?>">

    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Año: <br>
          <select name='ano' id='id_ano' onChange="submitform();">
            <?php 
                    $ss = "";
                    for ($x=$ano_min_db;$x<=($ANO);$x++) {
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
        </td>

        <?php 
        /*
        if ($id_origen!="2") { //ASISTENCIA?>
                <td class="celdaFiltro">
                  Tipo: <br>
                  <select class="filtro" name="id_tipo" id="id_tipo" onChange="submitform();">
                    <option value="">Todos</option>
                    <?php 
                      echo(select($tipos,$id_tipo)); 
                    ?>    
                  </select>
                </td>
        <?php } */?>
        <td class="celdaFiltro">
          Mes: <br>
          <select class="filtro" name="id_mes" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($meses_fn,$id_mes)); 
            ?>    
          </select>
        </td>

        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="id_estado" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($estados,$id_estado)); 
            ?>    
          </select>
        </td>


        <td class="celdaFiltro">
          Tipo: <br>
          <select class="filtro" name="id_tipo_general_capacitacion" id="id_tipo_general_capacitacion" onChange="submitform();">
                    <option value="">Todos</option>
                    <?php 
                      echo(select($tipos,$id_tipo_general_capacitacion)); 
                    ?>    
                  </select>

        </td>
        <td class="celdaFiltro">
          Ordenar x Fecha: <br>
          <select class="filtro" name="id_fecha_orden" id="id_fecha_orden" onChange="submitform();">
            <option value="">(Seleccione)</option>
            <?php 
              echo(select($fechas_ordenes,$id_fecha_orden)); 
            ?>    
          </select>
        </td>

        <td class="celdaFiltro">
          Acción:<br>
          <input type="button" name='nuevo' value="Nueva" style='font-size: 9pt' onclick="window.location.href='<?php echo($enlbase); ?>=capac_nuevo&id_origen=<?php echo($id_origen); ?>&ano=<?php echo($ano); ?>&id_mes=<?php echo($id_mes); ?>&id_unidad=<?php echo($id_unidad); ?>&id_usuario_seleccionado=<?php echo($id_usuario_seleccionado); ?>'"/>
          <input type="button" name='simular' value="Simular Usuario" style='font-size: 9pt' onclick="window.location.href='<?php echo($enlbase); ?>=capac_simular_usuario'"/>
<!--          <input type="button" name="volver" value="Volver"  style='font-size: 9pt' onClick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_obligatorias&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>&id_estado_check=<?php echo($id_estado_check); ?>&modo=';"> -->
<?php
	echo($boton_tabla_completa);
?>
        </td>

        <td class="celdaFiltro" style='display:none;'>      
          Duración (periodo)  :<br>
          Desde:<input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
       <!--   Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton"> -->
        </td>
        <td class="celdaFiltro" style='display:none;'>      
        Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton">
        </td>

        <td class="celdaFiltro" style='display:none;'>      
          Duración (Minutos) :<br>
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
        <td class="celdaFiltro" style='display:none;'>
          Recordar: <br>
          <select class="filtro" name="id_recordar" onChange="submitform();">
            <option value="">No Aplicar</option>
            <?php 
              echo(select($recordars,$id_recordar)); 
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
      <!--
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
    </table>
</div>

<?php //if ($modo == "BUSCAR") 
{?>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
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
<?php 
/*
  if ($ocultar_uno == "SI") {
    $etiqueta = "mostrar";
    $accion_etiqueta_uno = "NO";
  }
  if ($ocultar_uno == "NO") {
    $etiqueta = "ocultar";
    $accion_etiqueta_uno = "SI";
  }
*/

?>

  <tr class='filaTituloTabla'>
  <td class='tituloTabla' style='display:none;'>Id</td>
  <td class='tituloTabla' style='display:none;'>Origen</td>
<!--    <td class='tituloTabla'>Tipo</td>    -->
    <td class='tituloTabla'>Capacitaciones asignadas por la Institución  <a href='principal.php?modulo=capac_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen);?>&id_estado=<?php echo($id_estado); ?>&id_campo_usuario_capacitaciones=<?php echo($id_asiscapac_usuario_capacitaciones); ?>&ocultar_uno=<?php echo($accion_etiqueta_uno); ?>&ocultar_dos=<?php echo($accion_etiqueta_dos); ?>' class='text'><?php echo($etiqueta); ?></a>    </td>
    <td class='tituloTabla'>Tipo</td>
    <td class='tituloTabla'>Fecha</td>
    <!--<td class='tituloTabla'>Termino</td> -->
    <td class='tituloTabla'>Duración<br>(horas)</td>
    <!--<td class='tituloTabla'>Recordar</td> -->
    <td class='tituloTabla'>Estado</td>
<!--    <td class='tituloTabla'>Tiempo mínimo<br>exigido.</td> -->
<!--    <td class='tituloTabla'>link Zoom</td> -->
<!--    <td class='tituloTabla'>Archivo Zoom</td>-->
<!--    <td class='tituloTabla'>Acción</td>-->
  </tr>
<?php
	$HTML_formar = "";
	//if ((count($capacitaciones) > 0) and ($ocultar_uno=='NO')) {
  if (count($capacitaciones) > 0) {

    if ($ocultar_uno=='NO') {
            /*    
                for ($x=0;$x<count($alumnos);$x++) {
                  extract($alumnos[$x]);
                  
                  
                  $enl = "$enlbase_sm=editar_alumno_egreso&id_alumno=$id&rut=$rut";
                  $enlace = "a class='enlitem' href='$enl' title='Ver ficha de Egreso'";
                  
                  if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
                  
                  if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
                  
                  if (!empty($duracion)) { $duracion .= " semestres"; }

                  $enl_id = "$enlbase_sm=ver_alumno&id_alumno=$id&rut=$rut";			
                  $id = "<a href='$enl_id' id='sgu_fancybox' class='enlaces' title='Ver ficha de estudiante'>$id</a>";
                  
                  $nombre = "<a class='enlaces' href='$enl' title='Ver/Editar ficha de Titulación' id='sgu_fancybox_small'>$nombre</a>";
                  
                  $HTML_formar .= "  <tr class='filaTabla'>\n"
                                . "    <td class='textoTabla'>$id</td>\n"
                                . "    <td class='textoTabla'>$rut</td>\n"
                                . "    <td class='textoTabla'>$nombre</td>\n"
                                . "    <td class='textoTabla'>$carrera</td>\n"
                                . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
                                . "    <td class='textoTabla'>$periodo_egreso</td>\n"
                                . "    <td class='textoTabla' align='right'>$duracion</td>\n"
                                . "    <td class='textoTabla'>$matriculado</td>\n"
                                . "  </tr>\n";
                }
            */

            for ($x=0;$x<count($capacitaciones);$x++) {
              extract($capacitaciones[$x]);
              $myLink = "";
              $myLink = substr($link_capacitaciones,0,40); 
              if ($myLink != "") {
                $myLink = $myLink."...<br>";
              }
              

              if ($sala != "") {
                $sala = "Sala : $sala<br>";
              }
              //if ($porc_aprobacion!="") {
              //  $porc_aprobacion = $porc_aprobacion."%";
              //}
              
              if ($id_origen=="1") { //CAPACITACIONES
                
            //        if ($glosa_estado == "Programada") {
            //          $strEstado = "Subir";
            //        } else {
            //          $strEstado = "Subir"; //"Ver";
            //        }
            /*        
                $HTML_formar .= "  <tr class='filaTabla'>\n"
            //                      . "    <td class='textoTabla' align='left' style='display:none;'>$glosa_tipo <br> $glosa_subtipo</td>\n"
            //                      . "    <td class='textoTabla' align='left' style='display:none;'>$id_capacitacion</td>\n"
            //                      . "    <td class='textoTabla' align='left' style='display:none;'>$glosa_origen</td>\n"
            //                      . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"
            . "    <td class='textoTabla' align='left'><a class='enlaces' href='$enlbase=asiscapac_actividades_edit&id_asiscapac_actividades=$id_capacitacion&id_campo_actividades=$id_capacitacion&id_origen=$id_origen&ano=$ano'>$descripcion</a><br>
                    $sala
                    $glosa_tipo - $glosa_subtipo
                    $myLinkZoom
                    $universo_convocados convocados, 
                    $universo_presentes presentes,
                    $universo_inasistente inasistentes, 
                    $universo_justificados justificados,
                    $universo_licencia_medica licencias médicas
                    <br>
                    <a href='$enlbase=asiscapac_actividades_subir_archivo&id_asiscapac_actividades=$id_capacitacion&id_campo_actividades=$id_capacitacion&ano=$ano&id_origen=$id_origen' class='boton'>Subir</a>
                    <a href='$enlbase=asiscapac_actividades_obligatorias&=asiscapac_actividades_obligatorias&ano=$ano&id_origen=$id_origen&id_campo_actividades=$id_capacitacion&id_estado_check=' class='boton'>Convocar</a>

                    </td>\n"
            . "    <td class='textoTabla' align='left'>$fecha_inicio <br> $fecha_termino </td>\n"
                              . "    <td class='textoTabla' align='center'>$duracion</td>\n"
            //                      . "    <td class='textoTabla' align='left'>$glosa_recordar</td>\n"
                              . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n"
            //                      . "    <td class='textoTabla' align='left'>$myLinkZoom...</td>\n"
            //                      . "    <td class='textoTabla' align='center'>$porc_aprobacion</td>\n"
            //                      . "    <td class='textoTabla' align='center'><a class='enlaces' href='$enlbase=asiscapac_actividades_subir_archivo&id_asiscapac_actividades=$id_capacitacion&id_campo_actividades=$id_capacitacion&ano=$ano&id_origen=$id_origen'>Subir</a></td>\n"                      
            //          . "    <td class='textoTabla' align='center'>
            //                <a class='enlaces' href='$enlbase=asiscapac_actividades_obligatorias&=asiscapac_actividades_obligatorias&ano=$ano&id_origen=$id_origen&id_campo_actividades=$id_capacitacion&id_estado_check='>Convocar</a>                
            //                </td>\n"                      
                              . "  </tr>\n";
            */

$linkBotonConvocar = "<a href='$enlbase=capac_convocar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado_check=&id_mes=$id_mes&id_unidad_dos=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado' class='boton'>Convocar</a>";
$linkBotonEvidencias = "<a href='$enlbase=capac_usuarios_evidencias&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado_check=&id_mes=$id_mes&id_unidad_dos=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado' class='boton'>Revisar Evidencias</a>";
$mostrarBotonConvocar = "";
$mostrarBotonEvidencias = "";
if ($id_asiscapac_estado_db == "1") { //PROGRAMADA
  $mostrarBotonConvocar = $linkBotonConvocar;
  $mostrarBotonEvidencias = $linkBotonEvidencias;

}
if ($id_asiscapac_estado_db == "2") { //EJECUTADA
  $mostrarBotonConvocar = $linkBotonConvocar;
  $mostrarBotonEvidencias = $linkBotonEvidencias;

}

$HTML_formar .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
id_origen=$id_origen&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_mes=$id_mes&
id_unidad=$id_unidad&
id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado
'>$descripcion</a><br>
        $sala
<!--        $glosa_tipo<br> -->
        $myLink
        $universo_convocados convocados, $universo_confirmados confirmados
        <br>
        $mostrarBotonConvocar
        $mostrarBotonEvidencias
<!--
        <a href='$enlbase=capac_buscar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado=$id_estado&accion_capacitacion=CERRAR' class='boton'>Cerrar</a>
        <a href='$enlbase=capac_buscar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado=$id_estado&accion_capacitacion=SUSPENDER' class='boton'>Suspender</a>
-->        

        </td>\n"
        . "    <td class='textoTabla' align='center'>$glosa_tipo</td>\n"        
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                  . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                  . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n"
                  . "  </tr>\n";


                } 
            }

    } //if ocultar == NO
    else {
      $HTML_formar = "  <tr>"
      . "    <td class='textoTabla' colspan='8'>"
      . "      Los registros se encuentran ocultos"
      . "    </td>\n"
      . "  </tr>";
    }




	} else {
    if ($ocultar_uno == "SI") {
      $HTML_formar = "  <tr>"
      . "    <td class='textoTabla' colspan='8'>"
      . "      Los registros se encuentran ocultos"
      . "    </td>\n"
      . "  </tr>";

    } else {
		  $HTML_formar = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
    }
	}
	echo($HTML_formar);
?>
</table><br>




















<!--SEGUNDO FILTRO-->
<table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Unidad: <br>
          <select class="filtro" name="id_unidad" id="id_unidad" onChange="submitform();">
            <option value="">(Todas)</option>
            <?php 
              echo(select($unidades,$id_unidad)); 
            ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Usuarios: <br>
          <select class="filtro" name="id_usuario_seleccionado" id="id_usuario_seleccionado" onChange="submitform();">
            <option value="">(Seleccione)</option>
            <?php 
              echo(select($usuarios,$id_usuario_seleccionado)); 
            ?>    
          </select>
        </td>
        <td class="celdaFiltro">
          Estado Evidencia: <br>
          <select class="filtro" name="id_estado_evidencia" id="id_estado_evidencia" onChange="submitform();">
            <option value="">(Seleccione)</option>
            <?php 
              echo(select($estados_evidencias,$id_estado_evidencia)); 
            ?>    
          </select>
        </td>

        <td class="celdaFiltro">
          Acción: <br>
          <?php
	echo($boton_tabla_completa_voluntaria);
?>

        </td>


      </tr>
</table>

<!--FIN SEGUNDO FILTRO-->





















<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
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
          <?php 

/*
  if ($ocultar_dos == "SI") {
    $etiqueta = "mostrar";
    $accion_etiqueta_dos = "NO";
  }
  if ($ocultar_dos == "NO") {
    $etiqueta = "ocultar";
    $accion_etiqueta_dos = "SI";
  }
*/

?>



  <tr class='filaTituloTabla'>
  <td class='tituloTabla' style='display:none;'>Id</td>
  <td class='tituloTabla' style='display:none;'>Origen</td>
  <td class='tituloTabla'>Unidad</td>
    <td class='tituloTabla'>id_user  <a href='principal.php?modulo=capac_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen);?>&id_estado=<?php echo($id_estado); ?>&id_campo_usuario_capacitaciones=<?php echo($id_asiscapac_usuario_capacitaciones); ?>&ocultar_uno=<?php echo($accion_etiqueta_uno); ?>&ocultar_dos=<?php echo($accion_etiqueta_dos); ?>' class='text'><?php echo($etiqueta); ?></a>    </td>
    <td class='tituloTabla'>username</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Tipo</td>   
    <td class='tituloTabla'>Capacitaciones</td>
<!--    <td class='tituloTabla'>Regimen</td> -->
    <td class='tituloTabla'>Fecha</td>
    <!--<td class='tituloTabla'>Termino</td> -->
    <td class='tituloTabla'>Duración<br>(horas)</td>
    <!--<td class='tituloTabla'>Recordar</td> -->
    <td class='tituloTabla'>Estado</td>
<!--    <td class='tituloTabla'>Tiempo mínimo<br>exigido.</td> -->
<!--    <td class='tituloTabla'>link Zoom</td> -->
<!--    <td class='tituloTabla'>Archivo Zoom</td>-->
<!--    <td class='tituloTabla'>Acción</td>-->
<td class='tituloTabla'>Evidencias</td>
<td class='tituloTabla'>Estado Evidencia</td>
<!--<td class='tituloTabla'>Revocado</td>-->
<td class='tituloTabla'>Acción</td>

  </tr>
<?php
	$HTML_formar = "";  
	if (count($capacitaciones_voluntarias) > 0) {
/*    
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			
			$enl = "$enlbase_sm=editar_alumno_egreso&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl' title='Ver ficha de Egreso'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			if (!empty($duracion)) { $duracion .= " semestres"; }

			$enl_id = "$enlbase_sm=ver_alumno&id_alumno=$id&rut=$rut";			
			$id = "<a href='$enl_id' id='sgu_fancybox' class='enlaces' title='Ver ficha de estudiante'>$id</a>";
			
			$nombre = "<a class='enlaces' href='$enl' title='Ver/Editar ficha de Titulación' id='sgu_fancybox_small'>$nombre</a>";
			
			$HTML_formar .= "  <tr class='filaTabla'>\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'>$nombre</td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$periodo_egreso</td>\n"
			               . "    <td class='textoTabla' align='right'>$duracion</td>\n"
			               . "    <td class='textoTabla'>$matriculado</td>\n"
			               . "  </tr>\n";
		}
*/

		for ($x=0;$x<count($capacitaciones_voluntarias);$x++) {
			extract($capacitaciones_voluntarias[$x]);
      $myLink = "";
			$myLink = substr($link_capacitaciones,0,40); 
      if ($myLink != "") {
        $myLink = $myLink."...<br>";
      }
      

      if ($sala != "") {
        $sala = "Sala : $sala<br>";
      }
      //if ($porc_aprobacion!="") {
      //  $porc_aprobacion = $porc_aprobacion."%";
      //}
			//if ($id_origen=="1") 
      { //CAPACITACIONES
//        if ($glosa_estado == "Programada") {
//          $strEstado = "Subir";
//        } else {
//          $strEstado = "Subir"; //"Ver";
//        }
/*        
$HTML_formar .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='center'>$id_user</td>\n"
. "    <td class='textoTabla' align='center'>$nombre_usuario</td>\n"
. "    <td class='textoTabla' align='center'>$nombre_completo</td>\n"

. "    <td class='textoTabla' align='left'>$descripcion<br>
            $sala
            $myLink
            <br>
            <a href='$enlbase=asiscapac_actividades_obligatorias&=capac_convocar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado_check=' class='boton'>Revisar Evidencia</a>

            </td>\n"
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";


*/
                      $HTML_formar .= "  <tr class='filaTabla'>\n"
                      . "    <td class='textoTabla' align='left'>$nombre_unidad</td>\n"
                      . "    <td class='textoTabla' align='center'>$id_user</td>\n"
                      . "    <td class='textoTabla' align='left'>$nombre_usuario</td>\n"
                      . "    <td class='textoTabla' align='left'>$nombre_completo</td>\n"
                      . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"
                      . "    <td class='textoTabla' align='left'>$descripcion<br>
                                  <small>$sala
                                  $myLink
                                  </small>
                                  </br>
                                  <a href='principal.php?modulo=capac_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&ocultar_uno=$accion_etiqueta_uno&ocultar_dos=$accion_etiqueta_dos&id_doctos_digitalizados=$id_doctos_digitalizados&eliminar_capacitacion=SI&id_mes=$id_mes&id_unidad=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado&usuario_evidencia=$id_user&capacitaciones_usuarios_id=$capacitaciones_usuarios_id' class='enlaces'><small>Elim</small></a>
                                  </td>\n"
//                                  . "    <td class='textoTabla' align='left'>$glosa_regimen</td>\n"
                      . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";

                      
                      $HTML_formar .= "  <td class='textoTabla' valign='top' align='left'>\n";                          




                      $SQL_evidencias = "
                      select 
                      id id_doctos_digitalizados,
                      to_char(fecha,'DD-tmMon-YYYY HH24:MI') fecha, 
                      id_asiscapac_usuario_capacitaciones,
                      id_asiscapac_capacitaciones,
                      nombre_archivo,
                      --mime,
                      --archivo,
                      eliminado,
                      id_usuario
                      from 
                      capac_doctos_digitalizados
                      where 
                      id_usuario = $id_user
                      and id_asiscapac_usuario_capacitaciones = $id_capacitacion
                      and eliminado = 'f'
                      order by fecha desc
                      ";
                      $mis_evidencias = consulta_sql($SQL_evidencias);
                      //echo("$SQL_evidencias<br>");
                      for ($vv=0;$vv<count($mis_evidencias);$vv++) {
                        extract($mis_evidencias[$vv]);
                        //$HTML_formar .= $nombre_archivo."<br>";              
//lagarto
                        $HTML_formar .= "<a href='capac_ver_evidencia.php?id_doctos_digitalizados=$id_doctos_digitalizados' target='_blank' class='enlaces'><small>Ver evidencia ($fecha)</small>
                              &nbsp;&nbsp<a href='principal.php?modulo=capac_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&ocultar_uno=$accion_etiqueta_uno&ocultar_dos=$accion_etiqueta_dos&id_doctos_digitalizados=$id_doctos_digitalizados&eliminar_evidencia=SI&id_mes=$id_mes&id_unidad=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado&usuario_evidencia=$id_user' class='enlaces'><small>Elim</small></a><br>";
                      }
                      $HTML_formar .= "  </td>\n";              
                      

/*                      
                      if ($confirmado=="SI") {
                        $HTML_formar .= "  <td class='textoTabla'><span style='color: green'><b> ✓ </b></span></td>";   
                      } else {
                        $HTML_formar .= "  <td class='textoTabla'></td>";   
                      }
  */
  if ($confirmado=="SI") {
    $HTML_formar .= "  <td class='textoTabla'><span style='color: green'><b> ✓ </b></span>(Validada)</td>";   
    //$HTML_formar .= "  <td class='textoTabla'></td>";   
  } else {
          if ($confirmado=="NO") {
            //$HTML_formar .= "  <td class='textoTabla'></td>";   
            $HTML_formar .= "  <td class='textoTabla'><span style='color: red'><b> ✗ </b></span></span>(Revocada)</td>";   
          } else {
            //$HTML_formar .= "  <td class='textoTabla'></td>";   
            $HTML_formar .= "  <td class='textoTabla'></td>";   
          }
  }
  

/*
                      if ($confirmado=="SI") {
                       //$HTML_formar .= "  <td class='textoTabla'><a href='principal.php?modulo=capac_buscar&ano=$ano&id_origen=$id_origen&id_estado=$id_estado&id_usuario_confirmar=$id&confirmar=NO&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones' class='text'>OK</a></td>";
                        //$HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id&confirmar=NO&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Revocar</a></td>";   
                        $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=NO&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Revocar</a></td>";   
                      } else {
                        //$HTML_formar .= "  <td class='textoTabla'><a href='principal.php?modulo=capac_buscar&ano=$ano&id_origen=$id_origen&id_estado=$id_estado&id_usuario_confirmar=$id&confirmar=SI&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones' class='text'>Sin confirmar</a></td>";
                        $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=SI&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Sin Confirmar</a></td>";   
                        
                      }
  */
                if ($id_asiscapac_estado_db == "3") { //CERRADA
                    $mostrar = 1;
                }
                if ($id_asiscapac_estado_db == "4") { //SUSPENDIDA
                    $mostrar = 1;
                }
                if ($id_asiscapac_estado_db == "1") { //PROGRAMADA
                  $mostrar = 2;
                }
                if ($id_asiscapac_estado_db == "2") { //EJECUTADA
                  $mostrar = 2;
                }
                if ($mostrar == 1) {

                  if ($confirmado=="SI") {
                    $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=NO&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Ver</a></td>";   
                  } else {
                          if ($confirmado=="NO") {
                            $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=SI&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Ver</a></td>";   
                          } else {
                            $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano_usuario=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=NADA&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Ver</a></td>";   
                          }
                  }
                
                }

                if ($mostrar == 2) {
                  if ($confirmado=="SI") {
                    $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=NO&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Ver</a></td>";   
                  } else {
                          if ($confirmado=="NO") {
                            $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=SI&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Ver</a></td>";   
                          } else {
                            $HTML_formar .= "  <td class='textoTabla'><a href='$enlbase_sm=capac_revocar_usuario&ano_usuario=$ano&id_origen=$id_origen&id_campo_usuario_capacitaciones=$id_asiscapac_usuario_capacitaciones&id_estado_check=$id_estado_check&id_usuario_confirmar=$id_user&confirmar=NADA&param_observacion=$observacion&param_observacionrevocar=$observacionrevocar' class='text'  id='sgu_fancybox_small'>Establecer</a></td>";   
                          }
                  }
                
                }
  


        } 
		}

	} else {
		$HTML_formar = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_formar);
?>
</table><br>






<?php } ?>
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
