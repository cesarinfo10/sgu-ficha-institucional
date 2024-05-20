<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$ID_CARRERA_ELECTIVOS = 12;

$ids_carreras = $_SESSION['ids_carreras'];
if (!empty($ids_carreras)) { $ids_carreras .= ",$ID_CARRERA_ELECTIVOS"; }
 
include("validar_modulo.php");

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if (empty($reg_inicio)) { $reg_inicio = 0; }

$texto_buscar    = $_REQUEST['texto_buscar'];
$buscar          = $_REQUEST['buscar'];
$id_carrera      = $_REQUEST['id_carrera'];
$ano             = $_REQUEST['ano'];
$semestre        = $_REQUEST['semestre'];
$jornada         = $_REQUEST['jornada'];
$regimen         = $_REQUEST['regimen'];
$seccion         = $_REQUEST['seccion'];
$cerrado         = $_REQUEST['cerrado'];
$ayudantia       = $_REQUEST['ayudantia'];
$recep_acta      = $_REQUEST['recep_acta'];
$recep_acta_comp = $_REQUEST['recep_acta_comp'];
$dia             = $_REQUEST['dia'];
$fec_ini_asist   = $_REQUEST['fec_ini_asist'];
$fec_fin_asist   = $_REQUEST['fec_fin_asist'];
$tipo_clase      = $_REQUEST['tipo_clase'];

$titulo  = $_REQUEST['titulo'];
$filtros = $_REQUEST['filtros'];

if ($_REQUEST['ano'] == "") { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }
//if (empty($_REQUEST['cerrado'])) { $cerrado = "f"; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }

if ($dia > 0 && $buscar == "") { $fec_fin_asist = $fec_ini_asist = date("Y-m-d",strtotime("last Sunday +$dia day")); }

if ($dia == -1 && $buscar == "") { $_REQUEST['fec_ini_asist'] = $_REQUEST['fec_fin_asist'] = ""; }

if ($tipo_clase == "") { $tipo_clase="t"; }

if ($_REQUEST['fec_ini_asist'] == "") { $fec_ini_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Ini_Sem1) : date("Y-m-d",$Fec_Ini_Sem2); }
if ($_REQUEST['fec_fin_asist'] == "") { $fec_fin_asist = ($SEMESTRE == 1) ? date("Y-m-d",$Fec_Fin_Sem1) : date("Y-m-d",$Fec_Fin_Sem2); }

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

if (!empty($id_carrera)) { $condiciones .= " AND id_carrera=$id_carrera "; }

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

if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND (car.regimen = '$regimen') "; }

if ($dia > 0) { $condiciones .= " AND $dia IN (c.dia1,c.dia2,c.dia3) "; }

if ($tipo_clase <> "t") { $condiciones .= " AND c.tipo_clase = '$tipo_clase' "; }

if ($ids_carreras <> "") { $condiciones .= " AND id_carrera IN ($ids_carreras) "; }

$SQL_cant_s1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_nc    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_s2    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion))";
$SQL_cant_nf    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE c.id IN (id,id_fusion)) and id_estado NOT IN (6,10)";
//$SQL_cant_clases = "SELECT count(ap.id) FROM asist_profesores AS ap WHERE ap.id_curso=vista_cursos.id AND asiste='p'";
//$SQL_cursos      = "                      ($SQL_cant_clases)*2 AS cant_horas";
$SQL_cal         = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' WHEN c.seccion=9 THEN 'NC' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";
$SQL_fusiones   = "SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id";

$SQL_cant_sesiones = "SELECT count(id) AS cant_sesiones FROM cursos_sesiones WHERE id_curso=c.id";

$SQL_cursos_sesiones = "SELECT cs.id FROM cursos_sesiones AS cs WHERE id_curso=c.id AND fecha BETWEEN '$fec_ini_asist'::date AND '$fec_fin_asist'::date";
$SQL_asist = "SELECT count(id_sesion) FROM ca_asistencia caa LEFT JOIN cargas_academicas ca on ca.id=caa.id_ca WHERE id_sesion IN ($SQL_cursos_sesiones) AND (id_estado IS NULL OR id_estado NOT IN (6,10,11,12)) ";

$SQL_asist_presentes = $SQL_asist . " AND presente";
$SQL_asist_ausentes  = $SQL_asist . " AND NOT presente";

$SQL_cant_presenciales = "SELECT count(id) FROM cargas_academicas WHERE id_curso=vc.id AND asistencia='Presencial'";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_prog_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion||' '||asignatura AS asignatura,profesor,rut,u.funcion,
                           u.categorizacion,cant_alumnos_asist(c.id),ga.nombre AS grado_academico,
                           $ANO-(SELECT min(ano) FROM cursos WHERE id_profesor=u.id)+1 AS antiguedad,
                           ($SQL_cant_sesiones) AS cant_sesiones,
                           c.semestre||'-'||c.ano AS periodo,sesion1,sesion2,sesion3,($SQL_cal) AS calendarizado,
                           to_char(c.fec_ini,'TMDay DD-tmMon-YYYY') AS fec_ini,to_char(c.fec_fin,'TMDay DD-tmMon-YYYY') AS fec_fin,c.seccion,
                           ($SQL_fusiones) AS asig_fusionadas,
                           CASE WHEN ayudantia THEN 'Si' ELSE 'No' END AS ayudantia
                    FROM vista_gestion_cursos AS vc
                    LEFT JOIN cursos AS c USING (id)
                    LEFT JOIN carreras AS car ON car.id=vc.id_carrera
                    LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
                    LEFT JOIN grado_acad AS ga ON ga.id=u.grado_academico
                    WHERE id_fusion IS NULL $condiciones 
                    ORDER BY c.ano DESC, c.semestre DESC, vc.carrera, c.seccion, cod_asignatura ";

$SQL_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura, asignatura,c.semestre||'-'||c.ano AS periodo,c.id_profesor,
                      upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,vc.carrera,sesion1,sesion2,sesion3,
                      cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,to_char(c.fecha_acta,'DD-MM-YYYY') AS cerrado_fecha,
                      ($SQL_cant_s1) AS s1, ($SQL_cant_nc) AS nc, ($SQL_cant_s2) AS s2, ($SQL_cant_nf) AS nf, ($SQL_cal) AS cal,
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
echo("<!-- $SQL_cursos -->");
$cursos = consulta_sql($SQL_cursos);
if (count($cursos) > 0) {
	$SQL_cursos2 = "SELECT count(c.id) AS cant_cursos FROM vista_cursos AS vc LEFT JOIN cursos AS c USING (id) LEFT JOIN carreras AS car ON car.id=vc.id_carrera LEFT JOIN usuarios AS u ON u.id=vc.id_profesor WHERE id_fusion IS NULL $condiciones;";
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
	            . "&recep_acta_comp=$recep_acta_comp"
	            . "&dia=$dia"
              . "&tipo_clase=$tipo_clase"
	            . "&texto_buscar=$texto_buscar"
	            . "&buscar=$buscar"
	            . "&r_inicio";
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

/*
if (count($cursos) == 1 && $texto_buscar <> "") {
	echo(js("window.location='$enlbase=ver_curso&id_curso={$cursos[0]['id']}';"));
}*/
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

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<?php if ($titulo<>"no") { ?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<?php } ?>
<form name="formulario" action="principal.php" method="get">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">

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
          <select class="filtro" name="regimen" onChange="formulario.id_carrera.value=''; submitform();">
            <option value="t">Todos</option>
            <?php echo(select($REGIMENES,$regimen)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Tipo Clase:<br>
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
          Día:<br>
          <select class='filtro' name="dia" onChange="submitform();">
			<option value='-1'>Todos</option>
            <?php echo(select($dias_palabra,$dia)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Rango de fecha para Asistencia Estudiantes:<br>
          Desde: <input type="date" name="fec_ini_asist" value="<?php echo($fec_ini_asist); ?>" class="boton" style='font-size: 8pt' onBlur="formulario.fec_fin_asist.value=this.value;">
          Hasta: <input type="date" name="fec_fin_asist" value="<?php echo($fec_fin_asist); ?>" class="boton" style='font-size: 8pt'>
          <input type='submit' name='buscar' value='Buscar'>
        </td>
        <td class="celdaFiltro">
          Horarios:<br>
          <?php echo($boton_horarios); ?>
        </td>
      </tr>
    </table>
</div>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="20">
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
    <td class='tituloTabla'>Periodo<br><small>Tipo Clase</small></td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla'>Classroom</td>
    <td class='tituloTabla'>Cal</td>
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
			
			$asignatura = "<div>$cod_asignatura </div><div>$asignatura</div><div>$asig_fusionadas</div>";
			$asignatura = "<a href='$enlbase=ver_curso&id_curso=$id' class='enlaces'>$asignatura</a>";

      if ($cant_presenciales <> "0") { $cant_presenciales = ": ".$cant_presenciales."P"; } else { $cant_presenciales = ""; }
			
			echo("  <tr class='filaTabla' $bgcolor_cerrado>\n");
			echo("    <td class='textoTabla'><span style='color: #7F7F7F'>$id</span></td>");
			echo("    <td class='textoTabla'>$asignatura</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$al_asist<br><small>[$cant_alumnos]</small></td>");
			echo("    <td class='textoTabla' style='text-align: center'>$cant_sesiones</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$asistencia</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$periodo<br><small>$tipo_clase $cant_presenciales</small></td>");
			echo("    <td class='textoTabla'><div>$apellidos </div><div>$nombres</div></td>");
			echo("    <td class='textoTabla'>$cod_google_classroom</td>");
			echo("    <td class='textoTabla'><span class='$cal'>$cal</span></td>");
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
</script>
