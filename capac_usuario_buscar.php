<?php
  function universo_obligatorias($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_obligatorias_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }



  function universo_deteccion_necesidades($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_deteccion_necesidades_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function universo_estudios_superiores($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_estudios_superiores_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
      id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function universo_sin_deteccion_necesidades($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in ( 2, 4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
      ) = 0
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function universo_sin_deteccion_necesidades_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in (2,4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 

    and (
      select count(*) as cuenta
      from asiscapac_usuario_capacitaciones a
      where 
       id_usuario = $id_usuario
      and a.ano = $ano
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
      ) = 0
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }




  function universo_voluntarias($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_voluntarias_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  //--------------------------------------------------------------------------------------------------------------------------------------------------------
  function universo_obligatorias_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_obligatorias_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 1 --OBLIGATORIAS
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }




  function universo_deteccion_necesidades_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_deteccion_necesidades_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES  
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }

  function universo_estudios_superiores_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_estudios_superiores_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
    and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }


  function universo_sin_deteccion_necesidades_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in ( 2, 4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
      from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
      where true
      and a.id = b.id_asiscapac_capacitaciones
      and b.id_usuario = $id_usuario
      and b.ano = $ano
      and b.convocado = 't'
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES  
      ) = 0
      and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_sin_deteccion_necesidades_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo not in (2,4) --DETECCION_NECESIDADES , ESTUDIOA SUPERIORES 

    and (
      select count(*) as cuenta
      from asiscapac_usuario_capacitaciones a
      where 
       id_usuario = $id_usuario
      and a.ano = $ano
      and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
      and a.id_asiscapac_tipo = 2 --DETECCION_NECESIDADES    
      ) = 0
    and (
      select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 4 --ESTUDIOS SUPERIORES
      ) = 0
      and a.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_voluntarias_validadas($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
    where true
    and a.id = b.id_asiscapac_capacitaciones
    and b.id_usuario = $id_usuario
    and b.ano = $ano
    and b.convocado = 't'
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIAS
    and b.confirmado = 't'
    ";    
    $sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }
  function universo_voluntarias_validadas_usuario($ano, $id_usuario) {
    $ss = "
    select count(*) as cuenta
    from asiscapac_usuario_capacitaciones a
    where 
     id_usuario = $id_usuario
    and a.ano = $ano
    and a.id_asiscapac_estado in (2,3) --EJECUTADA - CERRADA
    and a.id_asiscapac_tipo = 3 --VOLUNTARIUAS
    and a.confirmado = 't'
    ";    
    //$sql     = consulta_sql($ss);
    //echo("<br>".$ss);
    extract($sql[0]);
    return $cuenta;
  }


//--------------------------------------------------------------------------------------------------------------------------------------------------------



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

$eliminar_evidencia = $_REQUEST['eliminar_evidencia'];
$confirmar_eliminar_evidencias = $_REQUEST['confirmar_eliminar_evidencias'];
$id_doctos_digitalizados = $_REQUEST['id_doctos_digitalizados'];



$ano            = $_REQUEST['ano'];
$id_estado_evidencia = $_REQUEST['id_estado_evidencia'];
//$id_origen      = "1"; //$_REQUEST['id_origen']; //CAPACITACIONES
$id_usuario_parametro = $_REQUEST['id_usuario_parametro'];
$nombre_usuario_parametro = $_REQUEST['nombre_usuario_parametro'];
$id_estado_check = $_REQUEST['id_estado_check'];
$id_campo_actividades = $_REQUEST['id_campo_actividades'];
$id_tipo      = $_REQUEST['id_tipo'];
$id_estado      = $_REQUEST['id_estado'];
$id_descripcion      = $_REQUEST['id_descripcion'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$duracion_minutos  = $_REQUEST['duracion_minutos'];
$id_recordar      = $_REQUEST['id_recordar'];
$id_link_zoom  = $_REQUEST['id_link_zoom'];


$id_usuario = $_SESSION['id_usuario'];

if ($id_usuario_parametro <> "") {
  $id_usuario = $id_usuario_parametro;
  $max_evidencias = -1; //SIN lÍMITES
//  $max_capacitaciones = "";
} else {
  $max_evidencias = 1;
}
$max_capacitaciones = 10;

$SQL = "select to_char(periodo_desde,'YYYY') ano_vigente  from periodo_eval where activo = 't';"; 
$ano_vigente = consulta_sql($SQL);
extract($ano_vigente[0]);

if ($ano == "") {
  $ano = $ano_vigente;
}



if ($eliminar_evidencia=="SI") {  
	$mensaje = "Se eliminará evidencia nº$id_doctos_digitalizados\\n"
		         . "Está seguro(a)?";
    $linkAquiMismo = "capac_usuario_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro&id_doctos_digitalizados=$id_doctos_digitalizados&eliminar_evidencia=SI&confirmar_eliminar_evidencias=SI";         
		$url_si = "$enlbase=$linkAquiMismo&eliminar_evidencia=&confirmar_eliminar_evidencias=SI";
		$url_no = "$enlbase=$linkAquiMismo&eliminar_evidencia=&confirmar_eliminar_evidencias=NO";
		echo(confirma_js($mensaje,$url_si,$url_no));
  
}
if ($confirmar_eliminar_evidencias=="SI") {  
  $SQL = "delete from capac_doctos_digitalizados where id = $id_doctos_digitalizados;"; 
              //            echo("<br>$SQL");
  consulta_dml($SQL);  
  $eliminar_evidencia = ""; 
  $confirmar_eliminar_evidencias = "";
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


$SQL = "select min(ano) ano_min_db from asiscapac_usuario_capacitaciones;"; 
$anitos = consulta_sql($SQL);
extract($anitos[0]);

if ($ano_min_db == "") {
  $ano_min_db = $ano_vigente;
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
  (
    select 
          CASE when ((
            select count(*) 
            from asiscapac_usuario_capacitaciones
            where confirmado = 't' and fecha_aceptacion is not null
            and id = a.id
          )> 0) THEN observacion
          ELSE (
            CASE when ((
              select count(*) 
              from asiscapac_usuario_capacitaciones
              where confirmado = 'f' and fecha_revocar is not null
              and id = a.id
            ) > 0) THEN observacion_revocar
            ELSE 
              ''
            END
            )
          END
          ) AS observacion
from asiscapac_usuario_capacitaciones a
where true
";

$SQL_consulta = $SQL_consulta." and id_usuario = $id_usuario";

//if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.ano = $ano";
//}

/*
if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_origen = $id_origen";
}
*/
if ($id_tipo != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_tipo = $id_tipo";
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
$SQL_consulta = $SQL_consulta." order by a.fecha_inicio asc";
//echo("<br>CONSULTA = $SQL_consulta");
$capacitaciones = consulta_sql($SQL_consulta);









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
a.id_asiscapac_estado id_asiscapac_estado_db,
(select glosa from asiscapac_estado where id = a.id_asiscapac_estado) glosa_estado,
0 universo_convocados,
0 as universo_presentes,
0 as universo_justificados,
0 as universo_licencia_medica,
0 as universo_inasistente,
trim(a.sala) id_sala
, (
  select s.nombre||' (cap. '||s.capacidad||')' FROM salas s where s.codigo = a.sala
  ) sala
  ,a.link_capacitaciones link_capacitaciones,
(
  select 
        CASE when ((
          select count(*) 
          from asiscapac_capacitaciones_funcionarios
          where confirmado = 't' and fecha_aceptacion is not null
          and id = b.id
        )> 0) THEN 'SI' 
        ELSE (
          CASE when ((
            select count(*) 
            from asiscapac_capacitaciones_funcionarios
            where confirmado = 'f' and fecha_revocar is not null
            and id = b.id
          ) > 0) THEN 'NO' 
          ELSE 
            'NADA'
          END
          )
        END
        ) AS confirmado,
  (
    select 
          CASE when ((
            select count(*) 
            from asiscapac_capacitaciones_funcionarios
            where confirmado = 't' and fecha_aceptacion is not null
            and id = b.id
          )> 0) THEN observacion
          ELSE (
            CASE when ((
              select count(*) 
              from asiscapac_capacitaciones_funcionarios
              where confirmado = 'f' and fecha_revocar is not null
              and id = b.id
            ) > 0) THEN observacion_revocar
            ELSE 
              ''
            END
            )
          END
          ) AS observacion_asignada
      

from asiscapac_capacitaciones a, asiscapac_capacitaciones_funcionarios b
where true
and a.id = b.id_asiscapac_capacitaciones
and b.id_usuario = $id_usuario
and b.convocado = 't'
";


if ($ano != "") {
  $SQL_consulta = $SQL_consulta." and a.ano = $ano";
  $SQL_consulta = $SQL_consulta." and b.ano = $ano";
}

if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_origen = $id_origen";
}
if ($id_tipo != "") {
  $SQL_consulta = $SQL_consulta." and a.id_asiscapac_tipo = $id_tipo";
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

if ($id_estado_evidencia <> "") {
  //  if ($id_estado_evidencia == 0) { //TODOS  
  
  //  }
    if ($id_estado_evidencia == 1) { //VALIDADAS
      $SQL_consulta = $SQL_consulta." 
      and (
          select 
                CASE when ((
                  select count(*) 
                  from asiscapac_capacitaciones_funcionarios
                  where confirmado = 't' and fecha_aceptacion is not null
                  and id = b.id
                )> 0) THEN 'SI' 
                ELSE (
                  CASE when ((
                    select count(*) 
                    from asiscapac_capacitaciones_funcionarios
                    where confirmado = 'f' and fecha_revocar is not null
                    and id = b.id
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
                from asiscapac_capacitaciones_funcionarios
                where confirmado = 't' and fecha_aceptacion is not null
                and id = b.id
              )> 0) THEN 'SI' 
              ELSE (
                CASE when ((
                  select count(*) 
                  from asiscapac_capacitaciones_funcionarios
                  where confirmado = 'f' and fecha_revocar is not null
                  and id = b.id
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
                from asiscapac_capacitaciones_funcionarios
                where confirmado = 't' and fecha_aceptacion is not null
                and id = b.id
              )> 0) THEN 'SI' 
              ELSE (
                CASE when ((
                  select count(*) 
                  from asiscapac_capacitaciones_funcionarios
                  where confirmado = 'f' and fecha_revocar is not null
                  and id = b.id
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


$SQL_consulta = $SQL_consulta." order by a.fecha_inicio asc";
//echo("<br>CONSULTA_ASIGNADAS = $SQL_consulta");

$capacitaciones_asignadas = consulta_sql($SQL_consulta);

//--------------------------------------------------------------------------------------------------------------------------------------------


$u_obligatorias = universo_obligatorias($ano, $id_usuario) 
                   + universo_obligatorias_usuario($ano, $id_usuario);


$u_deteccion_necesidades = universo_deteccion_necesidades($ano, $id_usuario) 
                         + universo_deteccion_necesidades_usuario($ano, $id_usuario);

$u_estudios_superiores = universo_estudios_superiores($ano, $id_usuario)
                       + universo_estudios_superiores_usuario($ano, $id_usuario);

$u_sin_deteccion_necesidades = universo_sin_deteccion_necesidades($ano, $id_usuario)
                             + universo_sin_deteccion_necesidades_usuario($ano, $id_usuario);

$u_voluntarias = universo_voluntarias($ano, $id_usuario) 
                + universo_voluntarias_usuario($ano, $id_usuario);
            

//--------------------------------------------------------------------------------------------------------------------------------------------


$u_obligatorias_validadas = universo_obligatorias_validadas($ano, $id_usuario)
            + universo_obligatorias_validadas_usuario($ano, $id_usuario);


$u_deteccion_necesidades_validadas = universo_deteccion_necesidades_validadas($ano, $id_usuario)
                                    + universo_deteccion_necesidades_validadas_usuario($ano, $id_usuario);

$u_estudios_superiores_validadas = universo_estudios_superiores_validadas($ano, $id_usuario)
                                  + universo_estudios_superiores_validadas_usuario($ano, $id_usuario);

$u_sin_deteccion_necesidades_validadas = universo_sin_deteccion_necesidades_validadas($ano, $id_usuario)
                                      + universo_sin_deteccion_necesidades_validadas_usuario($ano, $id_usuario);

$u_voluntarias_validadas = universo_voluntarias_validadas($ano, $id_usuario) 
                        + universo_voluntarias_validadas_usuario($ano, $id_usuario);

/*                        
echo("</br>UNIVERSOS");
echo("</br>u_obligatorias = $u_obligatorias");
echo("</br>u_deteccion_necesidades = $u_deteccion_necesidades");
echo("</br>u_estudios_superiores = $u_estudios_superiores");
echo("</br>u_sin_deteccion_necesidades = $u_sin_deteccion_necesidades");
echo("</br>u_voluntarias = $u_voluntarias");
echo("</br>u_obligatorias_validadas = $u_obligatorias_validadas");
echo("</br>u_deteccion_necesidades_validadas= $u_deteccion_necesidades_validadas");
echo("</br>u_estudios_superiores_validadas = $u_estudios_superiores_validadas");
echo("</br>u_sin_deteccion_necesidades_validadas = $u_sin_deteccion_necesidades_validadas");
echo("</br>u_voluntarias_validadas = $u_voluntarias_validadas");
echo("</br>----------------------------");
*/


             
//¿QUE CUADRO TOMARE??
//echo("</br>* * * * * * * *  * ");
if ($u_deteccion_necesidades) {
  //echo("REGLA DETECCION NECESIDADES");
  if ($u_obligatorias > 0) {
    $total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
  } else {
    $total1 = 0;
  }
  if ($u_deteccion_necesidades>0) {
    $total2 = ($u_deteccion_necesidades_validadas / $u_deteccion_necesidades) * 0.3;
  } else {
    $total2 = 0;
  }
  
  if ($u_voluntarias > 0) {
    $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
  } else {
    $total3 = 0;
  }

  $total = $total1 + $total2 +  $total3;
} else {
  if (($u_deteccion_necesidades==0) && ($u_estudios_superiores==0)) {
      //echo("</br>REGLA SIN DETECCION NECESIDADES");
      if ($u_sin_deteccion_necesidades>0) {
        $total1 = ($u_sin_deteccion_necesidades_validadas / $u_sin_deteccion_necesidades) * 0.9;
      } else {
        $total1 = 0;
      }
      
      $total2 = 0;
    
      if ($u_voluntarias > 0) {
        $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
      } else {
        $total3 = 0;
      }
      
  
      $total = $total1 + $total2 +  $total3;
     // echo("<br>total regla : $total");
    } else {
    if (($u_deteccion_necesidades==0) && ($u_estudios_superiores>0)) {
     // echo("REGLA ESTUDIOS SUPERIORES");
      if ($u_obligatorias > 0) {
        $total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
      } else {
        $total1 = 0;
      }

      //if ($u_estudios_superiores>0) {
      //  $total2 = ($u_estudios_superiores_validadas / $u_estudios_superiores) * 0.3;
      //} else {
      //  $total2 = 0;
      //}
      
      //if ($u_voluntarias > 0) {
      //  $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
      //} else {
      //  $total3 = 0;
      //}
      $total2 = 1*0.3;
      $total3 = 1*0.1;
        
      $total = $total1 + $total2 +  $total3;
      
      //echo("<br>total1 EST SUP= $total1");
      //echo("<br>total2 EST SUP= $total2");
      //echo("<br>total3 EST SUP= $total3");
      //echo("<br>total EST SUP= $total");
    }
  }
}
/*MODIF_*/
//echo("<br>capacitaciones = count($capacitaciones)");

if (count($capacitaciones) > 0 && ($u_estudios_superiores==0)) { //emedina 05112023 se incorpora _uestduios_superiores==0
  //echo("entra contar capacitaciones");
  $t = ($total + 0.1); // * 100; // + 0.1);
  //$t = ($total/100); // + 0.1);
  //$t = ($total/100 + 0.1)*100;
  $total = $t;
  //echo("<br>t : $t");
}
 



/*
if ($u_sin_deteccion_necesidades) {
  echo("</br>REGLA SIN DETECCION NECESIDADES");
  if ($u_sin_deteccion_necesidades>0) {
    $total1 = ($u_sin_deteccion_necesidades_validadas / $u_sin_deteccion_necesidades) * 0.9;
  } else {
    $total1 = 0;
  }
  
  $total2 = 0;

  if ($u_voluntarias > 0) {
    $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
  } else {
    $total3 = 0;
  }
  

    $total = $total1 + $total2 +  $total3;
} else {
  if ($u_deteccion_necesidades) {
    echo("REGLA DETECCION NECESIDADES");
    if ($u_obligatorias > 0) {
      $total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
    } else {
      $total1 = 0;
    }
    if ($u_deteccion_necesidades>0) {
      $total2 = ($u_deteccion_necesidades_validadas / $u_deteccion_necesidades) * 0.3;
    } else {
      $total2 = 0;
    }
    
    if ($u_voluntarias > 0) {
      $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
    } else {
      $total3 = 0;
    }
  
    $total = $total1 + $total2 +  $total3;
  } else {
    if ($u_estudios_superiores>0) {
      echo("REGLA ESTUDIOS SUPERIORES");
      if ($u_obligatorias > 0) {
        $total1 = ($u_obligatorias_validadas / $u_obligatorias) * 0.6;
      } else {
        $total1 = 0;
      }
      if ($u_estudios_superiores>0) {
        $total2 = ($u_estudios_superiores_validadas / $u_estudios_superiores) * 0.3;
      } else {
        $total2 = 0;
      }
      
      if ($u_voluntarias > 0) {
        $total3 = ($u_voluntarias_validadas / $u_voluntarias) * 0.1;
      } else {
        $total3 = 0;
      }
        
      $total = $total1 + $total2 +  $total3;
  
    }
  } 
}
*/
 
//echo("<br>total1 = $total1");
//echo("<br>total2 = $total2");
//echo("<br>total3 = $total3");
//echo("<br>total = $total");

$porc_capacitaciones = round(($total*100),0);

//echo("<br>porc_capacitaciones = $porc_capacitaciones");
//$ponderacion_deteccion_necesidades = 0;


//$porc_capacitaciones = round($ponderacion_deteccion_necesidades,1);
//$porc_capacitaciones = 34;

/*
$SQL_al_SIES = "SELECT split_part(a.rut,'-',1) AS rut,split_part(a.rut,'-',2) AS dv,
                       translate(upper(split_part(trim(a.apellidos),' ',1)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_pat,
                       translate(upper(split_part(trim(a.apellidos),' ',2)),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS ape_mat,
                       translate(upper(a.nombres),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre,
                       upper(a.genero) AS sexo,to_char(a.fec_nac,'DD-MM-YYYY') AS fec_nac,p.nacionalidad,
                       CASE a.jornada WHEN 'D' THEN c.cod_sies_diurno WHEN 'V' THEN c.cod_sies_vespertino END AS cod_carrera_sies,
                       translate(upper(c.nombre_titulo),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_titulo,
                       translate(upper(c.nombre_grado),'ÁÉÍÓÚÄËÏÖÜÀÈÌÒÙÑ','AEIOUAEIOUAEIOUN') AS nombre_grado,
                       1 AS tit_terminal,
                       CASE WHEN a.admision NOT IN (2,20) THEN ((ano_egreso-cohorte)+1)*2+(CASE WHEN semestre_egreso<=semestre_cohorte THEN -1 ELSE 0 END)-($SQL_al_presente) ELSE 0 END AS semestres_susp,
                       CASE WHEN a.admision NOT IN (2,20) THEN a.cohorte ELSE 9999 END AS ano_ing_carrera,a.semestre_cohorte AS sem_ing_carrera,
                       a.cohorte,a.semestre_cohorte,a.ano_egreso,a.semestre_egreso,c.nombre AS nombre_carrera,a.jornada
                FROM alumnos AS a
                LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                LEFT JOIN pais     AS p ON p.localizacion=a.nacionalidad
                LEFT JOIN al_estados AS ae ON ae.id=a.estado
                LEFT JOIN al_estados AS aet ON aet.id=a.estado_tramite
                LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY c.alias,a.jornada,a.apellidos,a.nombres"; 
                */
//$SQL_tabla_completa_SIES = "COPY ($SQL_al_SIES) to stdout WITH CSV HEADER";
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

/*
$sql_origen = "select id, glosa nombre from asiscapac_origen where id = $id_origen order by orden";
$origenes = consulta_sql($sql_origen);
*/

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



$sql_tipo = "select id, glosa nombre from asiscapac_tipo where id > 0 order by orden";
$tipos = consulta_sql($sql_tipo);

$sql_estados = "select id, glosa nombre from asiscapac_estado order by orden";
$estados = consulta_sql($sql_estados);

$sql_recordar = "select id, glosa nombre from asiscapac_recordar order by orden";
$recordars = consulta_sql($sql_recordar);

$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
//file_put_contents($nombre_arch,$SQL_tabla_completa_SIES);




?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<?php if ($id_usuario_parametro <> "") {?>
  <div class="tituloModulo">
    <?php echo("Simulado para : $id_usuario_parametro - $nombre_usuario_parametro<br>"); ?>
  </div>
<?php }?>

<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <input type="hidden" name="id_usuario_parametro" value="<?php echo($id_usuario_parametro); ?>">
    <input type="hidden" name="nombre_usuario_parametro" value="<?php echo($nombre_usuario_parametro); ?>">

    <input type="hidden" name="id_estado_check" id="id_estado_check" value="<?php echo($id_estado_check); ?>">
    <input type="hidden" name="id_campo_actividades" id="id_campo_actividades" value="<?php echo($id_campo_actividades); ?>">

    <input type="hidden" name="eliminar_evidencia" id="eliminar_evidencia" value="<?php echo($eliminar_evidencia); ?>">    
    <input type="hidden" name="confirmar_eliminar_evidencias" id="confirmar_eliminar_evidencias" value="<?php echo($confirmar_eliminar_evidencias); ?>">        
    <input type="hidden" name="id_doctos_digitalizados" id="id_doctos_digitalizados" value="<?php echo($id_doctos_digitalizados); ?>">

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
        <td class="celdaFiltro">
          Estado Evidencia: <br>
          <select class="filtro" name="id_estado_evidencia" id="id_estado_evidencia" onChange="submitform();">
            <option value="">(Seleccione)</option>
            <?php 
              echo(select($estados_evidencias,$id_estado_evidencia)); 
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
        <td class="celdaFiltro">
                  Tipo: <br>
                  <select class="filtro" name="id_tipo" id="id_tipo" onChange="submitform();">
                    <option value="">Todos</option>
                    <?php 
                      echo(select($tipos,$id_tipo)); 
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
        <!--
        <td class="celdaFiltro">
          Estado: <br>
          <select class="filtro" name="id_estado" onChange="submitform();">
            <option value="">Todos</option>
            <?php 
              echo(select($estados,$id_estado)); 
            ?>    
          </select>
        </td>
        -->
        <td class="celdaFiltro">
          Acción:<br>
          <input type="button" name='nuevo' value="Nueva" style='font-size: 9pt' onclick="window.location.href='<?php echo($enlbase); ?>=capac_usuario_nuevo&id_origen=<?php echo($id_origen); ?>&ano=<?php echo($ano); ?>&id_usuario_parametro=<?php echo($id_usuario_parametro); ?>&nombre_usuario_parametro=<?php echo($nombre_usuario_parametro); ?>&max_capacitaciones=<?php echo($max_capacitaciones); ?>'"/>
<!--          <input type="button" name="volver" value="Volver"  style='font-size: 9pt' onClick="window.location.href='<?php echo($enlbase); ?>=asiscapac_actividades_obligatorias&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>&id_estado_check=<?php echo($id_estado_check); ?>&modo=';"> -->
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




<table cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="texto">
      <strong>Capacitaciones : <?php echo($porc_capacitaciones); ?>%</strong> (este porcentaje será utilizado en su Evaluación de Desempeño, al finalizar el periodo vigente.)<br>
    </td>
  </tr>
</table>

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
  <tr class='filaTituloTabla'>
  <td class='tituloTabla' style='display:none;'>Id</td>
  <td class='tituloTabla' style='display:none;'>Origen</td>
<!--    <td class='tituloTabla'>Tipo</td>    -->
    <td class='tituloTabla'>Capacitaciones Asignadas</td>
    <td class='tituloTabla'>Tipo</td>
    <td class='tituloTabla'>Fecha</td>
    <!--<td class='tituloTabla'>Termino</td> -->
    <td class='tituloTabla'>Duración<br>(horas)</td>
    <!--<td class='tituloTabla'>Recordar</td> -->
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Evidencias</td>
    <td class='tituloTabla'>Estado Evidencia</td>
<!--    <td class='tituloTabla'>Revocado</td> -->
    <td class='tituloTabla'>Observación</td>

<!--    <td class='tituloTabla'>Tiempo mínimo<br>exigido.</td> -->
<!--    <td class='tituloTabla'>link Zoom</td> -->
<!--    <td class='tituloTabla'>Archivo Zoom</td>-->
<!--    <td class='tituloTabla'>Acción</td>-->
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($capacitaciones_asignadas) > 0) {
    
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
			
			$HTML_alumnos .= "  <tr class='filaTabla'>\n"
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

		for ($x=0;$x<count($capacitaciones_asignadas);$x++) {
			extract($capacitaciones_asignadas[$x]);
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
        if ($glosa_estado == "Programada") {
          $strEstado = "Subir";
        } else {
          $strEstado = "Subir"; //"Ver";
        }
/*        
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
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
id_link=$link_capacitaciones
'>$descripcion</a><br>
            $sala
            $glosa_tipo<br>
            $myLink
            $universo_convocados convocados, 
            <br>
            <a href='$enlbase=capac_convocar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado_check=' class='boton'>Convocar</a>
            <a href='$enlbase=asiscapac_actividades_obligatorias&=capac_convocar&ano=$ano&id_origen=$id_origen&id_campo_capacitaciones=$id_capacitacion&id_estado_check=' class='boton'>Revisar Evidencia</a>

            </td>\n"
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n"
                      . "  </tr>\n";
        } 
		}



 */
/*
 if ($confirmado == "NO") {
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'>$descripcion<br>
            $sala
            $glosa_tipo<br>
            $myLink
            <br>
            <a href='$enlbase=capac_subir_archivo&opcion_origen=2&ano=$ano&id_campo_capacitaciones=$id_capacitacion&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro' class='boton'>Subir Evidencias</a>
            </td>\n"
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
                    //  . "  </tr>\n";
 } else {
  $HTML_alumnos .= "  <tr class='filaTabla'>\n"
  . "    <td class='textoTabla' align='left'>$descripcion<br>
              $sala
              $glosa_tipo<br>
              $myLink
              </td>\n"
  . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                        . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                        . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
                      //  . "  </tr>\n";
  
 }
*/

/*
$SQL_cuenta_evidencias = "
select count(*) cuenta_evidencias
from 
capac_doctos_digitalizados
where 
id_usuario = $id_usuario
and id_asiscapac_capacitaciones = $id_capacitacion
and eliminado = 'f'
";
$mis_cuenta_evidencias = consulta_sql($SQL_cuenta_evidencias);
extract($mis_cuenta_evidencias[0]);
*/


//CASO UNO
$mostrarBotonSubirEvidencias = true;
if ($id_asiscapac_estado_db == "3") { //CERRADA
  $mostrarBotonSubirEvidencias = false;
}
if ($id_asiscapac_estado_db == "4") { //SUSPENDIDA
  $mostrarBotonSubirEvidencias = false;
}
//CASO DOS
/*
if ($mostrarBotonSubirEvidencias) {
  if ($max_evidencias <> -1 ) {
    if ($cuenta_evidencias >= $max_evidencias ) {
      $mostrarBotonSubirEvidencias = false;
    }
  }  
}
*/
if ($mostrarBotonSubirEvidencias) {
  $linkSubirEvidencias = "<a href='$enlbase=capac_subir_archivo&opcion_origen=2&ano=$ano&id_campo_capacitaciones=$id_capacitacion&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro' class='boton'>Subir Evidencias</a>";
} else {
  $linkSubirEvidencias = "";
}

if ($confirmado == "SI") {
    $HTML_alumnos .= "  <tr class='filaTabla'>\n"
    . "    <td class='textoTabla' align='left'>$descripcion<br>
                $sala
                $myLink
                </td>\n"
    . "    <td class='textoTabla' align='center'>$glosa_tipo</td>\n"
    . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                          . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                          . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
                        //  . "  </tr>\n";
      
}
if ($confirmado == "NO") { //REVOCADO
  $HTML_alumnos .= "  <tr class='filaTabla'>\n"
  . "    <td class='textoTabla' align='left'>$descripcion<br>
              $sala
              $myLink
              <br>
              $linkSubirEvidencias
              </td>\n"
  . "    <td class='textoTabla' align='center'>$glosa_tipo</td>\n"              
  . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                        . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                        . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
                      //  . "  </tr>\n";
  
}
if ($confirmado == "NADA") { 
  $HTML_alumnos .= "  <tr class='filaTabla'>\n"
  . "    <td class='textoTabla' align='left'>$descripcion<br>
              $sala
              $myLink
              <br>
              $linkSubirEvidencias
              </td>\n"
  . "    <td class='textoTabla' align='center'>$glosa_tipo</td>\n"
  . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                        . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                        . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
                      //  . "  </tr>\n";  
}

$HTML_alumnos .= "  <td class='textoTabla' valign='top' align='left'>\n";              

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
id_usuario = $id_usuario
and id_asiscapac_capacitaciones = $id_capacitacion
and eliminado = 'f'
order by fecha desc
";
$mis_evidencias = consulta_sql($SQL_evidencias);
//echo("$SQL_evidencias<br>");
for ($vv=0;$vv<count($mis_evidencias);$vv++) {
  extract($mis_evidencias[$vv]);
  //$HTML_alumnos .= $nombre_archivo."<br>";              
  $HTML_alumnos .= "<a href='capac_ver_evidencia.php?id_doctos_digitalizados=$id_doctos_digitalizados' target='_blank' class='enlaces'><small>Ver evidencia ($fecha)</small></a><br>";
}
$HTML_alumnos .= "  </td>\n";              
                    
if ($confirmado=="SI") {
  $HTML_alumnos .= "  <td class='textoTabla'><span style='color: green'><b> ✓ </b></span>(Validada)</td>";   
  //$HTML_alumnos .= "  <td class='textoTabla'></td>";   
} else {
        if ($confirmado=="NO") {
          //$HTML_alumnos .= "  <td class='textoTabla'></td>";   
          $HTML_alumnos .= "  <td class='textoTabla'><span style='color: red'><b> ✗ </b></span></span>(Revocada)</td>";   
        } else {
          //$HTML_alumnos .= "  <td class='textoTabla'></td>";   
          $HTML_alumnos .= "  <td class='textoTabla'></td>";   
        }
}

$HTML_alumnos .= "  <td class='textoTabla'>$observacion_asignada</td>";   


        $HTML_alumnos .= "  </tr>\n";


        } 
		}

	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
?>
</table><br>






















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
  <tr class='filaTituloTabla'>
  <td class='tituloTabla' style='display:none;'>Id</td>
  <td class='tituloTabla' style='display:none;'>Origen</td>
<!--    <td class='tituloTabla'>Tipo</td>    -->
    <td class='tituloTabla'>Mis Capacitaciones  <?php echo(count($capacitaciones)); ?> de <?php echo($max_capacitaciones); ?> </td>
    <td class='tituloTabla'>Tipo</td>
    <td class='tituloTabla'>Fecha</td>
    <!--<td class='tituloTabla'>Termino</td> -->
    <td class='tituloTabla'>Duración<br>(horas)</td>
    <!--<td class='tituloTabla'>Recordar</td> -->
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Evidencias</td>
    <td class='tituloTabla'>Estado Evidencia</td>
<!--    <td class='tituloTabla'>Revocado</td> -->
    <td class='tituloTabla'>Observación</td>


<!--    <td class='tituloTabla'>Tiempo mínimo<br>exigido.</td> -->
<!--    <td class='tituloTabla'>link Zoom</td> -->
<!--    <td class='tituloTabla'>Archivo Zoom</td>-->
<!--    <td class='tituloTabla'>Acción</td>-->
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($capacitaciones) > 0) {
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
			
			$HTML_alumnos .= "  <tr class='filaTabla'>\n"
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
			//if ($id_origen=="1") 
      { //CAPACITACIONES
        if ($glosa_estado == "Programada") {
          $strEstado = "Subir";
        } else {
          $strEstado = "Subir"; //"Ver";
        }
/*        
        $HTML_alumnos .= "  <tr class='filaTabla'>\n"
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

/*
if ($confirmado == "NO") {
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_usuario_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_asiscapac_tipo=$id_asiscapac_tipo&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_usuario_parametro=$id_usuario_parametro&
nombre_usuario_parametro=$nombre_usuario_parametro

  '>$descripcion</a><br>
              $sala
              $myLink
              <br>
              <a href='$enlbase=capac_subir_archivo&opcion_origen=1&ano=$ano&id_campo_capacitaciones=$id_capacitacion&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro' class='boton'>Subir Evidencias</a>
              </td>\n"
              . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"            
  . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                        . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                        . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
} else {
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_usuario_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_asiscapac_tipo=$id_asiscapac_tipo&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_usuario_parametro=$id_usuario_parametro&
nombre_usuario_parametro=$nombre_usuario_parametro
  '>$descripcion</a><br>
              $sala
              $myLink              
              </td>\n"
              . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"            
  . "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                        . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                        . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";

}
*/
if ($confirmado == "SI") {
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_usuario_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_asiscapac_tipo=$id_asiscapac_tipo&
id_asiscapac_estado=$id_asiscapac_estado_db&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_usuario_parametro=$id_usuario_parametro&
nombre_usuario_parametro=$nombre_usuario_parametro
'>$descripcion</a><br>
            $sala
            $myLink              
            </td>\n"
            . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"            
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
    
  
}


$SQL_cuenta_evidencias = "
select count(*) cuenta_evidencias
from 
capac_doctos_digitalizados
where 
id_usuario = $id_usuario
and id_asiscapac_usuario_capacitaciones = $id_capacitacion
and eliminado = 'f'";
$mis_cuenta_evidencias = consulta_sql($SQL_cuenta_evidencias);
extract($mis_cuenta_evidencias[0]);



//CASO UNO
$mostrarBotonSubirEvidencias = true;
if ($id_asiscapac_estado_db == "3") { //CERRADA
  $mostrarBotonSubirEvidencias = false;
}
if ($id_asiscapac_estado_db == "4") { //SUSPENDIDA
  $mostrarBotonSubirEvidencias = false;
}

//CASO DOS

if ($mostrarBotonSubirEvidencias) {
  if ($max_evidencias <> -1 ) {
    if ($cuenta_evidencias >= $max_evidencias ) {
      $mostrarBotonSubirEvidencias = false;
    }
  }  
}


if ($mostrarBotonSubirEvidencias) {
  $linkSubirEvidencias = "<a href='$enlbase=capac_subir_archivo&opcion_origen=1&ano=$ano&id_campo_capacitaciones=$id_capacitacion&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro' class='boton'>Subir Evidencias</a>";
} else {
  $linkSubirEvidencias = "";
}


if ($confirmado == "NO") {
$HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_usuario_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_asiscapac_tipo=$id_asiscapac_tipo&
id_asiscapac_estado=$id_asiscapac_estado_db&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_usuario_parametro=$id_usuario_parametro&
nombre_usuario_parametro=$nombre_usuario_parametro

'>$descripcion</a><br>
            $sala
            $myLink
            <br>
            $linkSubirEvidencias
            </td>\n"
            . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"            
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";
  
}
if ($confirmado == "NADA") {
  $HTML_alumnos .= "  <tr class='filaTabla'>\n"
. "    <td class='textoTabla' align='left'><a class='enlaces' 
href='$enlbase=capac_usuario_edit&id_capacitaciones=$id_capacitacion&
id_campo_capacitaciones=$id_capacitacion&
ano=$ano&
id_tipo_general_capacitacion=$id_asiscapac_tipo&
fec_ini_asist=$fec_ini_asist&
fec_fin_asist=$fec_fin_asist&
duracion_horas=$duracion&
sala=$id_sala&
id_asiscapac_tipo=$id_asiscapac_tipo&
id_asiscapac_estado=$id_asiscapac_estado_db&
id_descripcion=$descripcion&
id_link=$link_capacitaciones&
id_usuario_parametro=$id_usuario_parametro&
nombre_usuario_parametro=$nombre_usuario_parametro

'>$descripcion</a><br>
            $sala
            $myLink
            <br>
            $linkSubirEvidencias
            </td>\n"
            . "    <td class='textoTabla' align='left'>$glosa_tipo</td>\n"            
. "    <td class='textoTabla' align='left'><font size='1'>inicio : $fecha_inicio</font> <br> <font size='1'>término : $fecha_termino</font> </td>\n"
                      . "    <td class='textoTabla' align='center'>$duracion</td>\n"
                      . "    <td class='textoTabla' align='center'>$glosa_estado</td>\n";

}



$HTML_alumnos .= "  <td class='textoTabla' valign='top' align='left'>\n";              


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
id_usuario = $id_usuario
and id_asiscapac_usuario_capacitaciones = $id_capacitacion
and eliminado = 'f'
order by fecha desc
";
$mis_evidencias = consulta_sql($SQL_evidencias);
//echo("$SQL_evidencias<br>");
for ($vv=0;$vv<count($mis_evidencias);$vv++) {
  extract($mis_evidencias[$vv]);
  //$HTML_alumnos .= $nombre_archivo."<br>";              
//lagarto  


//$HTML_alumnos .= "<a href='capac_ver_evidencia.php?id_doctos_digitalizados=$id_doctos_digitalizados' target='_blank' class='enlaces'><small>Ver evidencia ($fecha)</small>
//  &nbsp;&nbsp<a href='principal.php?modulo=capac_usuario_buscar&ano=$ano&id_origen$id_origen&id_estado=$id_estado&id_usuario_parametro=$id_usuario_parametro&nombre_usuario_parametro=$nombre_usuario_parametro&id_doctos_digitalizados=$id_doctos_digitalizados&eliminar_evidencia=SI' class='enlaces'><small>Elim</small></a><br></a><br>";

  $HTML_alumnos .= "<a href='capac_ver_evidencia.php?id_doctos_digitalizados=$id_doctos_digitalizados' target='_blank' class='enlaces'><small>Ver evidencia ($fecha)</small></a><br></a><br>";

}
$HTML_alumnos .= "  </td>\n";              

if ($confirmado=="SI") {
  $HTML_alumnos .= "  <td class='textoTabla'><span style='color: green'><b> ✓ </b></span>(Validada)</td>";   
//  $HTML_alumnos .= "  <td class='textoTabla'></td>";   
} else {
        if ($confirmado=="NO") {
  //        $HTML_alumnos .= "  <td class='textoTabla'></td>";   
          $HTML_alumnos .= "  <td class='textoTabla'><span style='color: red'><b> ✗ </b></span></span>(Revocada)</td>";   
        } else {
//          $HTML_alumnos .= "  <td class='textoTabla'></td>";   
          $HTML_alumnos .= "  <td class='textoTabla'></td>";   
        }
}
$HTML_alumnos .= "  <td class='textoTabla'>$observacion</td>";   

          $HTML_alumnos .= "  </tr>\n";              
        } 
		}

	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='8'>"
		              . "      No hay registros para los criterios de búsqueda/selección"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
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
