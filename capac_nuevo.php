<?php
function cuentaRegistroCapacitaciones($ano, $id_origen, 
                                  $id_asiscapac_tipo, 
                                  $id_asiscapac_estado, 
                                  $fecha_inicio, 
                                  $fecha_termino) {
  $ss = "
      select count(*) as cuenta from asiscapac_capacitaciones
    where
    ano = $ano 
    and id_asiscapac_origen = $id_origen
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

$modo = "NUEVO"; //$_REQUEST['modo'];
$grabar      = $_REQUEST['grabar'];

$ano            = $_REQUEST['ano'];
$id_origen      = 1; //$_REQUEST['id_origen']; //CAPACITACIONES
//$id_subtipo_capacitacion = $_REQUEST['id_subtipo_capacitacion'];
$id_tipo_general_capacitacion = $_REQUEST['id_tipo_general_capacitacion'];
$id_tipo      = $_REQUEST['id_tipo'];
//$id_estado      = $_REQUEST['id_estado'];
$id_descripcion      = $_REQUEST['id_descripcion'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$duracion_horas  = $_REQUEST['duracion_horas'];
$id_aprobacion  = $_REQUEST['id_aprobacion'];
$id_recordar      = $_REQUEST['id_recordar'];
$id_link  = $_REQUEST['id_link'];
$sala        = $_REQUEST['sala'];			  


$id_mes      = $_REQUEST['id_mes'];
$id_unidad  = $_REQUEST['id_unidad'];
$id_usuario_seleccionado  = $_REQUEST['id_usuario_seleccionado'];



$SQL = "select to_char(periodo_desde,'YYYY') ano_vigente  from periodo_eval where activo = 't';"; 
$ano_vigente = consulta_sql($SQL);
extract($ano_vigente[0]);

if ($ano == "") {
  $ano = $ano_vigente;
}


$SQL = "select min(ano) ano_min_db from asiscapac_capacitaciones;"; 
$anitos = consulta_sql($SQL);
extract($anitos[0]);



if ($id_aprobacion == "") {
  $id_aprobacion = "70";
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
echo("<br>duracion_horas...$duracion_horas");
echo("<br>id_recordar...$id_recordar");
echo("<br>id_link...$id_link");
*/


//$id_origen      = $_REQUEST['id_origen'];
$id_estado_check      = $_REQUEST['id_estado_check'];
$id_campo_actividades      = $_REQUEST['id_campo_actividades'];


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




/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */
/*********************************************************************************************************************************** */

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

    //$id_tipo = 0;
    if ($puedeSeguir) {
      if ($id_tipo_general_capacitacion == "") {
        echo(msje_js("Falta Ingresar Tipo"));
        $puedeSeguir = false;
      }  
    }
    /*
    if ($puedeSeguir) {
      if ($id_subtipo_capacitacion == "") {
        echo(msje_js("Falta Ingresar Tipo"));
        $puedeSeguir = false;
      }  
    }
    */
    
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
      if ($duracion_horas == "") {
        echo(msje_js("Falta Ingresar Duración (Horas)"));
        $puedeSeguir = false;
      }  
    }

    $time1 = strtotime($fec_ini_asist);
    $time2 = strtotime($fec_fin_asist);
    if($time2<$time1){
      echo(msje_js("Error en Fecha"));
      $puedeSeguir = false;
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
      if ($id_link == "") {
        echo(msje_js("Falta Ingresar Link Zoom"));
        $puedeSeguir = false;
      }  
    }
*/

    if ($id_link == "") {
      $campoLink = "null";
    } else {
      $campoLink = "'$id_link'";
    }
/*
    
    if ($id_recordar == "") {
      $campoRecordar = "null";
    } else {
      $campoRecordar = $id_recordar;
    }
    */
    if ($sala == "") {
      $mySala = "null";
    } else {
      $mySala = "'$sala'";
    }
    if ($puedeSeguir) {

      $registros =  cuentaRegistroCapacitaciones($ano, $id_origen, 
                                              $id_asiscapac_tipo, 
                                              $id_asiscapac_estado, 
                                              $fec_ini_asist, 
                                              $fec_fin_asist);

      if ($registros == 0) {

              //$fecha = date("Y-m-d");
              $SQL = "
              insert into asiscapac_capacitaciones(
                ano,
                id_asiscapac_origen,
                id_asiscapac_tipo,
--                id_asiscapac_subtipo,
                descripcion,
                fecha_inicio,
                fecha_termino,
                duracion,
                id_asiscapac_estado,
                sala,
                link_capacitaciones
            ) values
            (
              $ano, 
              $id_origen, 
              $id_tipo_general_capacitacion, 
--              $id_subtipo_capacitacion,
              '$id_descripcion',
              '$fec_ini_asist',
              '$fec_fin_asist',
              $duracion_horas, 
              1,
              $mySala,
              $campoLink
            )
              ;";
        echo($SQL);
              if (consulta_dml($SQL) > 0) {
/*                
                $id_tipo = "";
                //$id_estado = "";
                $id_descripcion = "";
                $fec_ini_asist = "";
                $fec_fin_asist = "";
                $duracion_horas = "";
                $id_recordar = "";
                $id_link = "";
*/
                echo(msje_js("Registro Exitoso"));
               
echo(js("location='$enlbase=capac_buscar&ano=$ano&id_origen=$id_origen&id_estado_check=$id_estado_check&id_campo_actividades=$id_campo_actividades&id_mes=$id_mes&id_unidad=$id_unidad&id_usuario_seleccionado=$id_usuario_seleccionado';"));
              } else {
                      echo(msje_js("Error : al momento de grabar."));          
              }                  




      } else {
        echo(msje_js("Registro existente."));          
      }


    }
  }
}



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
*/

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
id id_actividad, 
(select glosa from asiscapac_origen where id = id_asiscapac_origen) glosa_origen,
(select glosa from asiscapac_tipo where id = id_asiscapac_tipo) glosa_tipo,
descripcion,
to_char(fecha_inicio,'DD-tmMon-YYYY') fecha_inicio,
to_char(fecha_termino,'DD-tmMon-YYYY') fecha_termino,
duracion, 
(select glosa from asiscapac_recordar where id = id_asiscapac_recordar) glosa_recordar,
link_zoom,
(select glosa from asiscapac_estado where id = id_asiscapac_estado) glosa_estado
from asiscapac_actividades
where true
";
if ($id_origen != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_origen = $id_origen";
}
if ($id_tipo != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_tipo = $id_tipo";
}
if ($id_estado != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_estado = $id_estado";
}
if ($duracion_horas != "") {
  $SQL_consulta = $SQL_consulta." and duracion = $duracion_horas";
}
if ($id_recordar != "") {
  $SQL_consulta = $SQL_consulta." and id_asiscapac_recordar = $id_recordar";
}
$SQL_consulta = $SQL_consulta." order by id desc";
*/
//echo("<br>CONSULTA = $SQL_consulta");
//$actividades = consulta_sql($SQL_consulta);

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

$sql_origen = "select id, glosa nombre from asiscapac_origen where id = $id_origen order by orden";
$origenes = consulta_sql($sql_origen);

$sql_tipo = "select id, glosa nombre from asiscapac_tipo where id > 0 order by orden";
$tipos = consulta_sql($sql_tipo);

//$sql_subtipo = "select id, glosa nombre from asiscapac_subtipo order by orden";
//$subtipos = consulta_sql($sql_subtipo);


$sql_estados = "select id, glosa nombre from asiscapac_estado order by orden";
$estados = consulta_sql($sql_estados);

$sql_recordar = "select id, glosa nombre from asiscapac_recordar order by orden";
$recordars = consulta_sql($sql_recordar);


$salas    = consulta_sql("SELECT trim(codigo) AS id,nombre||' (cap. '||capacidad||')' AS nombre FROM salas WHERE activa ORDER BY piso,nombre;");



$id_sesion = "SIES_".$_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa_SIES = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa SIES</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
//file_put_contents($nombre_arch,$SQL_tabla_completa_SIES);

?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <input type="hidden" name="id_mes" id="id_mes" value="<?php echo($id_mes); ?>">    
    <input type="hidden" name="id_unidad" id="id_unidad" value="<?php echo($id_unidad); ?>">        
    <input type="hidden" name="id_usuario_seleccionado" id="id_usuario_seleccionado" value="<?php echo($id_usuario_seleccionado); ?>">

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
        <?php 
          } 
          */
          ?>
        
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
        <td class="celdaFiltro">      
          Tipo :<br>
<!--
          <select name='id_tipo_general_capacitacion' id='id_tipo_general_capacitacion' onChange="submitform();">
          <option value="">Seleccione</option>
          <?php 
                    $ss = "";
                    for ($x=1;$x<=3;$x+=1) {
                      if ($x == $id_tipo_general_capacitacion) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }
                      if ($x == 1) {
                        $strX = "Obligatoria";
                      }
                      if ($x == 2) {
                        $strX = "Voluntaria";
                      }
                      if ($x == 3) {
                        $strX = "Detección Necesidades";
                      }

                      echo("<option value=$x $ss>$strX</option>");
                    }
                    
            ?>

          </select>
-->

          <select class="filtro" name="id_tipo_general_capacitacion" id="id_tipo_general_capacitacion" onChange="submitform();">
                    <option value="">Seleccione</option>
                    <?php 
                      echo(select($tipos,$id_tipo_general_capacitacion)); 
                    ?>    
                  </select>

<!--
          <select class="filtro" name="id_subtipo_capacitacion" id="id_subtipo_capacitacion" onChange="submitform();">
                    <option value="">Seleccione</option>
                    <?php 
                      echo(select($subtipos,$id_subtipo_capacitacion)); 
                    ?>    
                  </select>
-->

<!--         
          <select name='id_subtipo_capacitacion' id='id_subtipo_capacitacion' onChange="submitform();">
          <option value="">Seleccione</option>
          <?php 
                    $ss = "";
                    for ($x=1;$x<=2;$x+=1) {
                      if ($x == $id_subtipo_capacitacion) {
                        $ss = "selected";
                      } else {
                        $ss = "";
                      }
                      if ($x == 1) {
                        $strX = "Online";
                      }
                      if ($x == 2) {
                        $strX = "Presencial";
                      }

                      echo("<option value=$x $ss>$strX</option>");
                    }
            ?>

          </select>
                  -->
        </td>



        <td class="celdaFiltro">      
          Duración  :<br>
          Desde:<input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" min="<?php echo($ano); ?>-01-01" max="<?php echo($ano); ?>-12-31" class="boton" onBlur="formulario.fec_ini_asist.value=this.value;">
          Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" min="<?php echo($ano); ?>-01-01" max="<?php echo($ano); ?>-12-31" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
        </td>
<!--        <td class="celdaFiltro" style='display:none;'>      
          Hasta:<input type="datetime-local" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton">
        </td> -->

        <td class="celdaFiltro">      
          Cantidad (Horas) :<br>
          <select name='duracion_horas' id='id_duracion_horas' onChange="submitform();">
          <option value="">Todos</option>
            <?php 
                    $ss = "";
                    for ($x=1;$x<=50;$x+=1) {
                      if ($x == $duracion_horas) {
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
          <select name="sala" onChange="submitform();">
          <option value="">-- No Aplica --</option>
          <?php echo(select($salas,$_REQUEST['sala'])); ?>        
          </select>
        </td>
        <!--
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
        Tiempo mínimo exigido:: <br> 
          <select class="filtro" name="id_aprobacion" onChange="submitform();">

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
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
    <tr>
        <td class="celdaFiltro">
          Nombre Capacitación :<br>
          <input type="text" name="id_descripcion" value="<?php echo($id_descripcion); ?>" size="125" id="id_descripcion" class='boton'>
        </td>
      </tr>
      
      <tr>
        <td class="celdaFiltro">
          link capacitación :<br>
          <input type="text" name="id_link" value="<?php echo($id_link); ?>" size="125" id="id_link" class='boton'>
        </td>
      </tr>
      
      <td class="celdaFiltro">
          Acción:<br>
          <input type='submit' name='grabar' value='grabar' style='font-size: 9pt'>
          <input type="button" name="volver" value="Volver"  style='font-size: 9pt' onClick="window.location.href='<?php echo($enlbase); ?>=capac_buscar&ano=<?php echo($ano); ?>&id_origen=<?php echo($id_origen); ?>&id_estado_check=<?php echo($id_estado_check); ?>&id_campo_actividades=<?php echo($id_campo_actividades); ?>&id_mes=<?php echo($id_mes); ?>&id_unidad=<?php echo($id_unidad); ?>&id_usuario_seleccionado=<?php echo($id_usuario_seleccionado); ?>'">
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
