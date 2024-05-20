<?php

function sacaCommaFinal($s) {
  $ss = $s;
  $ult = "";
  $ult = substr($ss,strlen($ss)-1,1); 
  if ($ult == ",") {
    $ss = substr($ss,0,strlen($ss)-1);
  }
  return $ss;
}

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ids_carreras = $_SESSION['ids_carreras'];

$ID_CARRERA_ELECTIVOS = 12;

$ids_carreras = $_SESSION['ids_carreras'];
if (!empty($ids_carreras)) { $ids_carreras .= ",$ID_CARRERA_ELECTIVOS"; }

include("validar_modulo.php");
$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$moodleActivado   = $_REQUEST['moodleActivado'];
if ($moodleActivado=="") {
  $moodleActivado ="NO";
}
//echo("LOST:".$moodleActivado);
$parametro_sincronizar   = $_REQUEST['parametro_sincronizar'];
$parametro_lista_id_cursos   = $_REQUEST['parametro_lista_id_cursos'];

$texto_buscar        = $_REQUEST['texto_buscar'];
$buscar              = $_REQUEST['buscar'];
$id_carrera          = $_REQUEST['id_carrera'];
$ano                 = $_REQUEST['ano'];
$semestre            = $_REQUEST['semestre'];
$jornada             = $_REQUEST['jornada'];
$regimen             = $_REQUEST['regimen'];
$seccion             = $_REQUEST['seccion'];
$cerrado             = $_REQUEST['cerrado'];
$ayudantia           = $_REQUEST['ayudantia'];
$recep_acta          = $_REQUEST['recep_acta'];
$recep_acta_comp     = $_REQUEST['recep_acta_comp'];
$filtro_moodle       = $_REQUEST['filtro_moodle'];
$dia                 = $_REQUEST['dia'];
$fec_ini_asist       = $_REQUEST['fec_ini_asist'];
$fec_fin_asist       = $_REQUEST['fec_fin_asist'];
$tipo_clase          = $_REQUEST['tipo_clase'];
$sesiones_realizadas = $_REQUEST['sesiones_realizadas'];


$titulo  = $_REQUEST['titulo'];
$filtros = $_REQUEST['filtros'];

if ($parametro_sincronizar=="SI") {
  if ($parametro_lista_id_cursos<>"") {
    //echo("<br>ha llegado param origen : ".$parametro_lista_id_cursos);
    //******************************************************************************* */
    //exec_moodle_parametro($parametro_lista_id_cursos); //rutina que existe en proceso_moodle.php
    //shell_exec("php proceso_moodle.php ejecutar");
    shell_exec("php proceso_moodle.php $parametro_lista_id_cursos");
    $moodleActivado = "SI";
    echo(msje_js("Sincronización Terminada."));
    $parametro_sincronizar = "";
    $parametro_lista_id_cursos = "";
    //echo(js("location='$enlbase=crear_moodle_servicio';"));
    //******************************************************************************* */
  }
}



if ($_REQUEST['ano'] == "") { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }
//if (empty($_REQUEST['cerrado'])) { $cerrado = "f"; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

if (empty($sesiones_realizadas)) { $sesiones_realizadas = -1; }

if ($dia > 0 && $buscar == "") { $fec_fin_asist = $fec_ini_asist = date("Y-m-d",strtotime("last Sunday +$dia day")); }

if ($dia == -1 && $buscar == "") { $_REQUEST['fec_ini_asist'] = $_REQUEST['fec_fin_asist'] = ""; }

if ($tipo_clase == "") { $tipo_clase="t"; }

if ($_REQUEST['fec_ini_asist'] == "") { $fec_ini_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Ini_Sem1) : date("Y-m-d",$Fec_Ini_Sem2); }
if ($_REQUEST['fec_fin_asist'] == "") { $fec_fin_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Fin_Sem1) : date("Y-m-d",$Fec_Fin_Sem2); }

$SQL_cant_sesiones = "SELECT count(id) AS cant_sesiones FROM cursos_sesiones WHERE id_curso=c.id";

$condiciones = "";

if ($texto_buscar <> "" &&  $buscar == "Buscar") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar       = explode(" ",$texto_buscar_regexp);
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condiciones   .= " AND (lower(asignatura) ~* '$cadena_buscada' OR "
					   .  "      cod_asignatura ~* '$cadena_buscada' OR "
					   .  "      text(c.id) ~* '$cadena_buscada' OR "
					   .  "      lower(profesor) ~* '$cadena_buscada') ";
	}
	$id_carrera = $ano = $semestre = $jornada = $regimen = null;
} else {
	$texto_buscar = "";
}

//if (!empty($id_carrera)) { $condiciones .= " AND id_carrera=$id_carrera "; }
if (!empty($id_carrera)) { $condiciones .= " AND $id_carrera = ANY (ids_carreras) "; }

if ($ano > 0) {	$condiciones .= " AND c.ano=$ano "; }

if ($semestre > -1) { $condiciones .= " AND c.semestre=$semestre "; }

if ($jornada == 'D') { $condiciones .= " AND c.seccion BETWEEN 1 AND 4 "; }
elseif ($jornada == 'V') { $condiciones .= " AND c.seccion BETWEEN 5 AND 9 "; }

if ($seccion > 0) { $condiciones .= " AND c.seccion=$seccion "; }
//if ($seccion == "") { $condiciones .= " AND c.seccion <> 9 "; }

if ($cerrado == "t") { $condiciones .= " AND c.cerrado "; } 
elseif ($cerrado == "f") {  $condiciones .= " AND NOT c.cerrado "; } 

if ($ayudantia == "t")     { $condiciones .= " AND c.ayudantia "; }      
elseif ($ayudantia == "f") { $condiciones .= " AND NOT c.ayudantia "; }

if ($recep_acta == "t")     { $condiciones .= " AND c.recep_acta "; }      
elseif ($recep_acta == "f") { $condiciones .= " AND NOT c.recep_acta "; }

if ($recep_acta_comp == "t") { $condiciones .= " AND c.recep_acta_comp "; } 
elseif ($recep_acta_comp == "f") { $condiciones .= " AND NOT c.recep_acta_comp "; }  
//echo("<br>filtro_moodle = ".$filtro_moodle);
if ($filtro_moodle == "1") //SINCRONIZADO
{ 
  $condiciones .= " AND c.diferencias_sgu_moodle='OK'"; 
} 
elseif ($filtro_moodle == "2") { //DESINCRONIZADO
          $condiciones .= " AND (c.id_moddle_servicio is not null 
          and c.course_id_moodle is not null 
          and (c.diferencias_sgu_moodle!='OK' or c.diferencias_sgu_moodle is null)
          ) 
          "; 
  }  

if ($tipo_clase <> "t") { $condiciones .= " AND c.tipo_clase = '$tipo_clase' "; }

//if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND (car.regimen = '$regimen') "; }
if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND ('$regimen' = ANY (regimenes)) "; }

if ($dia > 0) { $condiciones .= " AND $dia IN (c.dia1,c.dia2,c.dia3) "; }

if ($sesiones_realizadas >= 0) { $condiciones .= " AND ($SQL_cant_sesiones) = $sesiones_realizadas "; }

$sql_moodle = "
select id, nombre from 
(
select 0 as id, 'Desatendido' nombre
union
select 1 as id, 'Sincronizado' nombre
union
select 2 as id, 'Desincronizado' nombre
)
as a
order by id
";  
$select_moodle = consulta_sql($sql_moodle);

//if ($ids_carreras <> "") { $condiciones .= " AND id_carrera IN ($ids_carreras) "; }
if ($ids_carreras <> "") { $condiciones .= " AND array[$ids_carreras] && (ids_carreras) "; }

$SQL_cant_s1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_c1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(c1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas AS ca LEFT JOIN calificaciones_parciales AS cap ON cap.id_ca=ca.id WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_nc    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_s2    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_nf    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion)) and id_estado NOT IN (6,10)";
//$SQL_cant_clases = "SELECT count(ap.id) FROM asist_profesores AS ap WHERE ap.id_curso=vista_cursos.id AND asiste='p'";
//$SQL_cursos      = "                      ($SQL_cant_clases)*2 AS cant_horas";
$SQL_cal         = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";
$SQL_fusiones   = "SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id";

$SQL_cursos_sesiones = "SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '$fec_ini_asist'::date AND '$fec_fin_asist'::date";
$SQL_asist = "SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN ($SQL_cursos_sesiones) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ";

$SQL_asist_presentes = $SQL_asist . " AND presente";
$SQL_asist_ausentes  = $SQL_asist . " AND NOT presente";

$SQL_cant_presenciales = "SELECT count(id) FROM cargas_academicas WHERE id_curso=vc.id AND asistencia='Presencial'";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_prog_cursos = "SELECT vc.carreras,c.id,vc.cod_asignatura||'-'||c.seccion||' '||vc.asignatura AS asignatura,profesor,rut,
                           u.funcion||coalesce(' ('||horas_planta_docencia||')','') as funcion,
                           u.categorizacion,cant_alumnos_asist(c.id),
						   (pa.horas_semanal*pa.nro_semanas_semestrales) AS horas_semestrales,
						   ga.nombre AS grado_academico,
                           $ANO-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           ($SQL_cant_sesiones) AS cant_sesiones,
                           c.semestre||'-'||c.ano AS periodo,sesion1,sesion2,sesion3,($SQL_cal) AS calendarizado,
                           to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                           ($SQL_fusiones) AS asig_fusionadas,
                           CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia
                    FROM vista_gestion_cursos AS vc
                    LEFT JOIN cursos     AS c USING (id)
                    LEFT JOIN carreras   AS car ON car.id=vc.id_carrera
                    LEFT JOIN usuarios   AS u ON u.id=vc.id_profesor
                    LEFT JOIN grado_acad AS ga ON ga.id=u.grado_academico
					LEFT JOIN prog_asig  AS pa ON pa.id=c.id_prog_asig 
                    WHERE id_fusion IS NULL $condiciones 
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, vc.cod_asignatura ";

$SQL_cursos = "SELECT c.diferencias_sgu_moodle as diferencias_moodle, 
                      diferencias_sgu_moodle_fec as diferencias_moodle_fec, 
                      c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura, asignatura,c.semestre||'-'||c.ano AS periodo,c.id_profesor,
                      upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,u.nombre_usuario||'@profe.umc.cl' AS email_gsuite,vc.carrera,car.regimen,sesion1,sesion2,sesion3,
                      cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,
                      ($SQL_cant_c1) AS c1, ($SQL_cant_s1) AS s1, ($SQL_cant_nc) AS nc, ($SQL_cant_s2) AS s2, ($SQL_cant_nf) AS nf, ($SQL_cal) AS cal,
                      to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                      ($SQL_asist_presentes) AS asist_presentes,($SQL_asist_ausentes) AS asist_ausentes,
                      cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') AS cant_sesiones,
                      CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') > 0 AND ($SQL_asist) > 0
                           THEN round((($SQL_asist_presentes)::numeric*100/($SQL_asist))) ELSE 0 
                      END AS tasa_presentes,
                      CASE WHEN cant_alumnos_asist(c.id) > 0 AND cantidad_sesiones_curso(c.id,'$fec_ini_asist','$fec_fin_asist') > 0 AND ($SQL_asist) > 0
                           THEN round((($SQL_asist_ausentes)::numeric*100/($SQL_asist))) ELSE 0 
                      END AS tasa_ausentes,c.cod_google_classroom,c.tipo_clase,($SQL_cant_presenciales) AS cant_presenciales,
                      ($SQL_fusiones) AS asig_fusionadas
               FROM vista_gestion_cursos AS vc
               LEFT JOIN cursos AS c USING (id)
               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
               LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
               WHERE id_fusion IS NULL $condiciones 
               ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura,c.seccion ";
$SQL_tabla_completa = "COPY ($SQL_cursos) to stdout WITH CSV HEADER";
$SQL_tabla_completa_pc = "COPY ($SQL_prog_cursos) to stdout WITH CSV HEADER";
$SQL_cursos .= "$limite_reg OFFSET $reg_inicio";
//echo("<!-- $SQL_cursos -->");
$cursos = consulta_sql($SQL_cursos);
//echo($SQL_cursos);
if (count($cursos) > 0) {
	$SQL_cursos2 = "SELECT count(c.id) AS cant_cursos 
	                FROM vista_gestion_cursos AS vc
					LEFT JOIN cursos AS c USING (id) 
					LEFT JOIN carreras AS car ON car.id=vc.id_carrera 
					LEFT JOIN usuarios AS u ON u.id=vc.id_profesor 
					WHERE id_fusion IS NULL $condiciones;";
	$cursos2 = consulta_sql($SQL_cursos2);
	$tot_reg = $cursos2[0]['cant_cursos'];
	$enlace_nav = "$enlbase=$modulo"
	            . "&id_carrera=$id_carrera"
	            . "&ano=$ano"
	            . "&semestre=$semestre"
	            . "&jornada=$jornada"
	            . "&regimen=$regimen"
	            . "&seccion=$seccion"
	            . "&cerrado=$cerrado"
	            . "&recep_acta=$recep_acta"
              . "&tipo_clase=$tipo_clase"
	            . "&recep_acta_comp=$recep_acta_comp"
	            . "&dia=$dia"
	            . "&texto_buscar=$texto_buscar"
	            . "&buscar=$buscar"
	            . "&r_inicio";
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

/*
if (count($cursos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_curso&id_curso={$cursos[0]['id']}';"));
}*/
/*
$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;");
*/

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND activa ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_carreras_novig = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras AND NOT activa ORDER BY nombre;";
$carreras_novig = consulta_sql($SQL_carreras_novig);


$estado_cursos = array(array("id"=>"f","nombre"=>"Abierto"),
                       array("id"=>"t","nombre"=>"Cerrado"));
                       
$boton_horarios = "";
if ($semestre>0 && $ano>0) {
	$boton_horarios = "<a href='$enlbase=cursos_horarios&semestre=$semestre&ano=$ano&id_carrera=$id_carrera&jornada=$jornada' class='boton'>
	                     Ver filtrado
	                   </a>";
}

$SECCIONES = consulta_sql("SELECT DISTINCT ON (seccion) seccion AS id,seccion AS nombre FROM cursos");

$_SESSION['enlace_volver'] = "$enlbase=$modulo"
                           . "&id_carrera=$id_carrera"
                           . "&ano=$ano"
                           . "&semestre=$semestre"
                           . "&jornada=$jornada"
                           . "&regimen=$regimen"
                           . "&seccion=$seccion"
                           . "&cerrado=$cerrado"
                           . "&recep_acta=$recep_acta"
                           . "&recep_acta_comp=$recep_acta_comp"
                           . "&dia=$dia"
                           . "&texto_buscar=$texto_buscar"
                           . "&buscar=$buscar"
                           . "&r_inicio=$reg_inicio";
                           
$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$TIPO_CLASES = consulta_sql("SELECT * FROM vista_tipo_clase");

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$boton_tabla_completa_pc = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=pc_$id_sesion');\" class='boton'><small>T. Comp. Prog. Cursos</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
$nombre_arch_pc = "sql-fulltables/pc_$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);
file_put_contents($nombre_arch_pc,$SQL_tabla_completa_pc);

$SESIONES_REALIZADAS = array();
for ($x=0;$x<=18;$x++) { $SESIONES_REALIZADAS[$x] = array("id" => $x, "nombre" => $x); }
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<?php if ($titulo<>"no") { ?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<?php } ?>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="parametro_sincronizar" id="parametro_sincronizar" value="<?php echo($parametro_sincronizar); ?>">
<input type="hidden" name="parametro_lista_id_cursos" id="parametro_lista_id_cursos" value="<?php echo($parametro_lista_id_cursos); ?>">


<?php if ($filtros<>"no") { ?>
<div class="texto" style="margin-top: 5px">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Año:<br>
          <select class='filtro' name="ano" onChange="submitform();">
            <option value="0">Todos</option>
            <?php echo(select($anos,$ano)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Semestre:<br>
          <select class='filtro' name="semestre" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($semestres,$semestre)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Estado:<br>
          <select class='filtro' name="cerrado" onChange="submitform();">
            <option value="">Todos</option>
            <?php echo(select($estado_cursos,$cerrado)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class='filtro' name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <optgroup label='Vigentes'>
              <?php echo(select($carreras,$id_carrera)); ?>
            </optgroup>
            <optgroup label='No vigentes'>
              <?php echo(select($carreras_novig,$id_carrera)); ?>
            </optgroup>
          </select>
        </td>
        </td>
        <td class="celdaFiltro">      
          Jornada:<br>
          <select class='filtro' name="jornada" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($JORNADAS,$jornada)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Sección:<br>
          <select class='filtro' name="seccion" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($SECCIONES,$seccion)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          <div align='left'>Régimen:</div>
          <select class="filtro" name="regimen" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Modalidad:<br>
          <select class='filtro' name="tipo_clase" onChange="submitform();">
            <option value="t">Todas</option>
            <?php echo(select($TIPO_CLASES,$tipo_clase)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Acta recep.:<br>
          <select class='filtro' name="recep_acta" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$recep_acta)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Ayudantía:<br>
          <select class='filtro' name="ayudantia" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$ayudantia)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Acta Comp. recep.:<br>
          <select class='filtro' name="recep_acta_comp" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$recep_acta_comp)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Moodle Sync.:<br>
          <input type="checkbox" id="chk_moodle" name="chk_moodle" value="chk_moodle" onclick=presionaCheckMoodle()> <label for="cbox2" name="label_activar" id="label_activar">Activar</label>
            <select class='filtro' name="filtro_moodle" id="filtro_moodle" onChange="submitform();">
              <option value="">Todas</option>
              <?php echo(select($select_moodle,$filtro_moodle)); ?>
            </select>
            <input type="checkbox" id="chk_moodle_info" name="chk_moodle_info" value="chk_moodle_info" onclick=presionaCheckInfoMoodle()> <label for="cbox2" name="label_activarInfoMoodle" id="label_activarInfoMoodle">Info.</label>
            

            <input type="button" name="sincronizar_moodle" id="sincronizar_moodle" value="Sincronizar Moodle" onClick="javascript:sincronizarMoodle('$enlbase=ver_curso&id_curso=$id')">

            <input type="hidden" name="moodleActivado" value="<?php echo($moodleActivado); ?>" id="moodleActivado" onChange="submitform();">
        </td>

      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por Código o nombre de asignatura, N° de Acta o nombre del Profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="45" class='boton' id="texto_buscar">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
          <script>document.getElementById('texto_buscar').focus();</script>
        </td>
        <td class="celdaFiltro">      
          S.R.:<br>
          <select class='filtro' name="sesiones_realizadas" onChange="submitform();">
			      <option value='-1'>Todos</option>
            <?php echo(select($SESIONES_REALIZADAS,$sesiones_realizadas)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Día:<br>
          <select class='filtro' name="dia" onChange="submitform();">
			<option value='-1'>Todos</option>
            <?php echo(select($dias_palabra,$dia)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Rango de fecha para Asistencia Estudiantes:<br>
          Desde:<input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" onBlur="formulario.fec_fin_asist.value=this.value;">
          Hasta:<input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton">
          <input type='submit' name='buscar' value='Buscar' class="botoncito">          
        </td>
        <td class="celdaFiltro">
          Horarios:<br>
          <?php echo($boton_horarios); ?>
        </td>
      </tr>
    </table>
</div>
<?php } ?>
<?php
      $contadorAlumnosDesincronizadosSGU = 0;
      $SQL = "
      select count(*) as cuenta from (
        select distinct rut from 
        moodle_desincronizados
        where origen = 'SGU')
        as a";
      $fff = consulta_sql($SQL);	
      $contadorAlumnosDesincronizadosSGU = $fff[0]['cuenta'];

      $contadorAlumnosDesincronizadosMOODLE = 0;
      $SQL = "
      select count(*) as cuenta from (
        select distinct rut from 
        moodle_desincronizados
        where origen = 'MOODLE')
        as a";
      $fff = consulta_sql($SQL);	
      $contadorAlumnosDesincronizadosMOODLE = $fff[0]['cuenta'];

        $contadorServiciosDesincronizados = 0;
        $SQL = "
        select count(*) as cuenta from (
          select id as id_moodle_servicio from moodle_servicios
          except
          select distinct id_moddle_servicio from cursos
          where 
          id_moddle_servicio is not null
          ) as a
  ";
        $fff = consulta_sql($SQL);	
        $contadorServiciosDesincronizados = $fff[0]['cuenta'];
          
?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" id="tabla_info_moodle" name="tabla_info_moodle" border ="1">
    <tr class='filaTituloTabla'>
        <td class='tituloTabla'><span style="color: #000000">Info Moodle</span> </td>
    </tr>

    <tr class='filaTituloTabla'>
        <td class='celdaFiltro'  align="left"><span style="color: #7F7F7F"><?php echo($contadorAlumnosDesincronizadosSGU); ?> Alumnos desincronizados en SGU</span> </td>
    </tr>

<?php if ($contadorAlumnosDesincronizadosMOODLE > 0) { ?>
    <tr class='filaTituloTabla'>
        <td class='celdaFiltro'  align="left"><span style="color: #7F7F7F"><?php echo($contadorAlumnosDesincronizadosMOODLE); ?> Alumnos desincronizados en MOODLE</span> </td>
    </tr>
<?php } ?>

    <tr class='filaTituloTabla'>
        <td class='celdaFiltro'  align="left"><span style="color: #7F7F7F"><?php echo($contadorServiciosDesincronizados); ?> servicios que estan omitidos.</span> </td>
    </tr>
</table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="8">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
      <?php echo($boton_tabla_completa_pc); ?>
    </td>
    <td colspan="3"></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><span style="color: #7F7F7F">N° de<br>Acta</span></td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>A.A.<br><small>[A.I.]</small></td>
    <td class='tituloTabla'>S.R.</td>
    <td class='tituloTabla'>Asist.<br>Est.</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Classroom</td>
    <td class='tituloTabla'>Cal</td>
    <td class='tituloTabla'>C1</td>
    <td class='tituloTabla'>S1</td>
    <td class='tituloTabla'>NC</td>
    <td class='tituloTabla'>S2</td>
    <td class='tituloTabla'>NF</td>
    <td class='tituloTabla'>Horario<br>{sala}</td>
  </tr>
<?php
	if (count($cursos) > 0) {
		$_verde   = "color: #009900; text-align: center";
		$_naranjo = "color: #FFA500; text-align: center";
		$_rojo    = "color: #ff0000; text-align: center";

		for ($x=0; $x<count($cursos); $x++) {
			extract($cursos[$x]);
			//$cant_horas = total_horas_control_asist_2011($id);
			$est_s1 = $est_nc = $est_s2 = $est_rec = "color: #000000";
			
			/*
			if (strlen($asignatura)>30) { $asignatura = mb_substr($asignatura,0,30)."...";}
			if (strlen($profesor)>20)   { $profesor   = mb_substr($profesor,0,20)."...";}
			*/
			if ($asig_fusionadas <> "") { 
				$asig_fusionadas = explode(",",$asig_fusionadas);
				$asig_fusionadas = "<small>Fusionada con:<br>&nbsp;&nbsp;".implode("<br>&nbsp;&nbsp;",$asig_fusionadas)."</small>";
			}
			
			if ($s1==100) { $est_s1 = $_verde; } elseif ($s1==0) { $est_s1 = $_rojo; } else { $est_s1 = $_naranjo; }
			if ($c1==100) { $est_c1 = $_verde; } elseif ($c1==0) { $est_c1 = $_rojo; } else { $est_c1 = $_naranjo; }
			if ($nc==100) { $est_nc = $_verde; } elseif ($nc==0) { $est_nc = $_rojo; } else { $est_nc = $_naranjo; }
			if ($s2==100) { $est_s2 = $_verde; } elseif ($s2==0) { $est_s2 = $_rojo; } else { $est_s2 = $_naranjo; }
			if ($nf==100) { $est_nf = $_verde; } elseif ($nf==0) { $est_nf = $_rojo; } else { $est_nf = $_naranjo; }
			
			if ($seccion == 9) { $sesion1 = "<small>Comienzo: $fec_ini</small>"; $sesion2 = "<small>Término: $fec_fin</small>"; }
			
			$bgcolor_cerrado = "";
			if ($cerrado == "t") { $bgcolor_cerrado = "bgcolor='#C0FFC0'"; }
			
//			$tasa_presentes = round(($asist_presentes/$cant_sesiones)*100 / $cant_alumnos,0);
//			$tasa_ausentes  = round(($asist_ausentes/$cant_sesiones)*100 / $cant_alumnos,0);
			$asistencia = "<span style='color: green'><b> ✓ </b>$tasa_presentes%</span><br><span style='color: red'><b> ✗ </b>$tasa_ausentes%</span>";
			$asistencia = "<a href='$enlbase_sm=cursos_libro_clases&id_curso=$id' id='sgu_fancybox' class='enlaces'>$asistencia</a>";

      //PREGUNTAR POR ESTE TROZO DE CODIGO
			$asignatura = "<div>$cod_asignatura </div><div>$asignatura</div><div>$asig_fusionadas</div>";
			//$asignatura = "<a href='$enlbase=ver_curso&id_curso=$id' class='enlaces'>$asignatura</a>";
      $asignatura = "<a href=javascript:mostraVerCursos('$enlbase=ver_curso&id_curso=$id') class='enlaces'>$asignatura</a>";
      //FIN PREGUNTAR
			
      if ($diferencias_moodle=="OK") {
        $colorMoodle = "green";
      } else {
        if ($diferencias_moodle=="") {
          $colorMoodle = "black";
        } else {
          $colorMoodle = "red";
        }
        
      }


			echo("  <tr class='filaTabla' $bgcolor_cerrado>\n");
			echo("    <td class='textoTabla'><span style='color: #7F7F7F'>$id</span></td>");
      $lista_id_cursos = $lista_id_cursos.$id.",";
			echo("    <td class='textoTabla'>$asignatura</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$al_asist<br><small>[$cant_alumnos
                                                                        <span style='color: $colorMoodle'><b>●</b></span>
                                                                      ]
                                                                    
                                                                    </small></td>");
			echo("    <td class='textoTabla' style='text-align: center'>$cant_sesiones</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$asistencia</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$periodo<br><small>$tipo_clase <!-- $cant_presenciales --></small></td>");
			echo("    <td class='textoTabla'><div>$apellidos </div><div>$nombres</div></td>");
			echo("    <td class='textoTabla'>$cod_google_classroom</td>");
			echo("    <td class='textoTabla'><span class='$cal'>$cal</span></td>");
			echo("    <td class='textoTabla' style='$est_c1'><small>$c1%</small></td>");
			echo("    <td class='textoTabla' style='$est_s1'><small>$s1%</small></td>");
			echo("    <td class='textoTabla' style='$est_nc'><small>$nc%</small></td>");
			echo("    <td class='textoTabla' style='$est_s2'><small>$s2%</small></td>");
			echo("    <td class='textoTabla' style='$est_nf'><small>$nf%</small></td>");
			echo("    <td class='textoTabla'><div>$sesion1 </div><div>$sesion2 </div><div>$sesion3</div></td>");
			echo("  </tr>");
		}
    
	} else {
		echo("<td class='textoTabla' colspan='13' align='center'><br>** No hay registros para los criterios de b&uacute;squeda/selección **<br><br></td>\n");
	}
?>
</table><br>
<?php 
$lista_id_cursos = sacaCommaFinal($lista_id_cursos);
?>
<input type='hidden' name='lista_id_cursos' value='<?php echo($lista_id_cursos); ?>'; id='lista_id_cursos'>

</form>
<div class="texto">
  A.A.: Alumnos Asistentes (no se cuentan los suspendidos/retirados/abandonados)<br>
  A.I.: Alumnos Inscritos<br>
  S.R.: Sesiones Realizadas (asistencia del docente al curso)<br>
  A.E.: Asistencia Estudiantes (sesiones según rango de fechas)<br>
  Cal.: Calendarización del curso<br>
  S1, NC, S2: Indican estado ingreso de estas calificaciones<br>
  NF: Indica estado de cierre de cursos (cálculo de Notas Finales y Situaciones)
</div>
<!-- Fin: <?php echo($modulo); ?> -->

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 800,
		'maxHeight'			: 700,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 480,
		'maxHeight'			: 480,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small2").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 800,
		'height'			: 600,
		'maxHeight'			: 600,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoSize'			: false,
		'fitToView'			: true,
		'autoDimensions'	: false,
		'closeBtn'	        : true,
		'closeClick'	    : false,
		'modal'      	    : true,
		'width'				: 9999,
		'maxHeight'			: 9999,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});
function inactivarMoodle() {
  $("#sincronizar_moodle").hide();
  $("#filtro_moodle").hide();
  $("#label_activar").text("Activar");
  $("#moodleActivado").val("NO");
  $("#chk_moodle").prop('checked', false);
  $("#chk_moodle_info").hide();
  //alert("debe colocar NO");

}
function activarMoodle() {
  $("#sincronizar_moodle").show();
  $("#filtro_moodle").show();
  $("#label_activar").text("");
  $("#moodleActivado").val("SI");
  $("#chk_moodle").prop('checked', true);
  $("#chk_moodle_info").show();
  //alert("debe colocar SI");
}


function inactivarTablaInfoMoodle() {
  $("#tabla_info_moodle").hide();

}
function activarTablaInfoMoodle() {
  $("#tabla_info_moodle").show();
}

function presionaCheckMoodle() {
        chk_moodle = "chk_moodle";
        chk = document.getElementById(chk_moodle);
        if (chk.checked == true){ //tiene que desactivar
          activarMoodle();
        } else {
          inactivarMoodle();
        }
}  
function presionaCheckInfoMoodle() {
        chk_moodle = "chk_moodle_info";
        chk = document.getElementById(chk_moodle);
        if (chk.checked == true){ //tiene que desactivar
          activarTablaInfoMoodle();
        } else {
          inactivarTablaInfoMoodle();
        }
}  

function mostraVerCursos(url) {
  
        chk_moodle = "chk_moodle";
        chk = document.getElementById(chk_moodle);
        if (chk.checked == true){ //tiene que desactivar
          url = url + "&moodleActivado=SI"
        } else {
          url = url + "&moodleActivado=NO"
        }
        //BIE http://dev3.sgu.umc.cl/sgu/principal.php?modulo=ver_curso&id_curso=15691
        //MAL http://dev3.sgu.umc.cl/principal.php?modulo=ver_curso&id_curso=15691&moodleActivado=NO'
        //onsole.log("puerto="+window.location.port);
        var pSaltar = "https://" + window.location.hostname + ":" + window.location.port + "/sgu/" + url;
        //console.log(pSaltar);
        window.location.href = pSaltar;
        //console.log(url);
}

function almacenaVariable(nombreVariable, valor) {
  //RRR
	var i=document.createElement('input');
		i.type='hidden';
		i.name=nombreVariable;
		i.value=valor;
	return i;
}
function enviarValoresEvaluar(parametro_sincronizar, parametro_lista_id_cursos){
  //RRR
		var f = document.createElement('form');
    //alert("debe saltar : " + window.location.href);
		//f.action='?modulo=gestion_cursos';
    f.action=window.location.href;
		f.method='POST';
		f.target='_self';
		
		var i=document.createElement('input');

		i = almacenaVariable("parametro_sincronizar", parametro_sincronizar);
		f.appendChild(i);

		i = almacenaVariable("parametro_lista_id_cursos", parametro_lista_id_cursos);
		f.appendChild(i);



		document.body.appendChild(f);
		f.submit();
}

function sincronizarMoodle(url) {
        var bb = false;
        document.getElementById("sincronizar_moodle").disabled = true;
        var r = confirm("Está a punto de ejecutar un proceso el cual tomará un tiempo para sincronizar los cursos en pantalla");
        if (r == true) {
/*          
          //alert("sincronizar...window.location.href= "+window.location.href);
          listaCursos = $("#lista_id_cursos").val();
          //alert(listaCursos);
          //url = url + "&parametro_lista_id_cursos=" + listaCursos;
          //var pSaltar = "http://" + window.location.hostname + ":" + window.location.port + "/sgu/" + url;


          var pSaltar = window.location.href+ "&parametro_sincronizar=SI&parametro_lista_id_cursos=" + listaCursos;
      $("#parametro_sincronizar").text("SI");
      alert("ha pasado verificacion");
          console.log(pSaltar);
          window.location.href = pSaltar;
*/          
        /*NUEVO RRR*/
          parametro_sincronizar="SI";
          listaCursos = $("#lista_id_cursos").val();
          //alert("listo para enviar valores");
          enviarValoresEvaluar(parametro_sincronizar, listaCursos);
        /*FIN NUEVO RRR*/
          bb = true;
        } else {
          bb = false;
          document.getElementById("sincronizar_moodle").disabled = false;
        }
        return bb;
}
$(document).ready(function(){
  /*
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	console.log(queryString);
	var moodleActivado = urlParams.getAll('moodleActivado');
	if (moodleActivado == 'SI') {
    activarMoodle();
	} else {
		inactivarMoodle();
	}
  */
  
  moodleActivado = $("#moodleActivado").val();
  inactivarTablaInfoMoodle();
//  alert(moodleActivado);
  if ((moodleActivado == "SI")) {
    activarMoodle();
  } else {
    inactivarMoodle();
  }
});  
</script>
