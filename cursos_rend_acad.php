<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if ($_SESSION['tipo'] > 0) { echo(msje_js("Módulo en mantención")); }

$ids_carreras = $_SESSION['ids_carreras'];
 
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
$recep_acta      = $_REQUEST['recep_acta'];
$recep_acta_comp = $_REQUEST['recep_acta_comp'];
$dia             = $_REQUEST['dia'];
$tipo_clase          = $_REQUEST['tipo_clase'];

if ($_REQUEST['ano'] == "") { $ano = $ANO; }
if ($_REQUEST['semestre'] == "") { $semestre = $SEMESTRE; }
//if (empty($_REQUEST['cerrado'])) { $cerrado = "f"; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if ($tipo_clase == "") { $tipo_clase="t"; }

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
	$id_carrera = $ano = $semestre = null;
} else {
	$texto_buscar = "";
}

//if (!empty($id_carrera)) { $condiciones .= " AND id_carrera=$id_carrera "; }
if (!empty($id_carrera)) { $condiciones .= " AND $id_carrera = ANY (ids_carreras) "; }

if ($ano > 0) {	$condiciones .= " AND c.ano=$ano "; }

if ($semestre > -1) { $condiciones .= " AND c.semestre=$semestre "; }

if ($seccion > 0) { $condiciones .= " AND c.seccion=$seccion "; }

if ($jornada == 'D') { $condiciones .= " AND c.seccion BETWEEN 1 AND 4 "; }
elseif ($jornada == 'V') { $condiciones .= " AND c.seccion BETWEEN 5 AND 8 "; }

if ($cerrado == "t") { $condiciones .= " AND c.cerrado"; } elseif ($cerrado == "f") {  $condiciones .= " AND NOT c.cerrado"; } 

//if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND (car.regimen = '$regimen') "; }
if ($regimen <> "" && $regimen <> "t") { $condiciones .= " AND ('$regimen' = ANY (regimenes)) "; }

if ($tipo_clase <> "t") { $condiciones .= " AND c.tipo_clase = '$tipo_clase' "; }

if ($recep_acta == "t")      { $condiciones .= " AND c.recep_acta"; }      elseif ($recep_acta == "f")      { $condiciones .= " AND NOT c.recep_acta"; }
if ($recep_acta_comp == "t") { $condiciones .= " AND c.recep_acta_comp"; } elseif ($recep_acta_comp == "f") { $condiciones .= " AND NOT c.recep_acta_comp"; }  

//if ($ids_carreras <> "") { $condiciones .= " AND id_carrera IN ($ids_carreras) "; }
if ($ids_carreras <> "") { $condiciones .= " AND array[$ids_carreras] && (ids_carreras) "; }

$SQL_prom_s1 = "SELECT coalesce(avg(solemne1)::numeric(2,1)::text,'---') FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1>=1";
$SQL_prom_s2 = "SELECT avg(solemne2)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2>=1";
$SQL_prom_nc = "SELECT avg(nota_catedra)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra>=1";
$SQL_prom_nf = "SELECT avg(nota_final)::numeric(2,1) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND (solemne1>=1 OR solemne2>=1)";
$SQL_desvest_nf = "SELECT stddev(nota_final)::numeric(5,4) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND (solemne1>=1 OR solemne2>=1)";

$SQL_aprob_s1 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1>=4";
$SQL_aprob_s2 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2>=4";
$SQL_aprob_nc = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra>=4";
$SQL_aprob_nf = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_final>=4";

$SQL_reprob_s1 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1 between 1 and 3.9";
$SQL_reprob_s2 = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne2 between 1 and 3.9";
$SQL_reprob_nc = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_catedra between 1 and 3.9";
$SQL_reprob_nf = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND nota_final between 1 and 3.9";
$SQL_cant_NSP  = "SELECT count(id_alumno) FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) AND cant_alumnos_asist(vc.id)>0 AND solemne1=-1 AND solemne2=-1 AND nota_catedra<=1 AND id_estado=2";

$SQL_cant_s1    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne1)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))";
$SQL_cant_nc    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(nota_catedra)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))";
$SQL_cant_s2    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(solemne2)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion))";
$SQL_cant_nf    = "SELECT CASE WHEN count(id_alumno)>0 THEN ((count(id_estado)::float/count(id_alumno)::float)*100)::int2 ELSE 0 END FROM cargas_academicas WHERE id_curso IN (SELECT id FROM cursos WHERE vc.id IN (id,id_fusion)) and id_estado NOT IN (6,10)";

//$SQL_cant_clases = "SELECT count(ap.id) FROM asist_profesores AS ap WHERE ap.id_curso=vista_cursos.id AND asiste='p'";
//$SQL_cursos      = "                      ($SQL_cant_clases)*2 AS cant_horas";
$SQL_cal         = "SELECT CASE WHEN count(id)=count(materia) AND count(id)>0 THEN 'SI' ELSE 'NO' END AS cal FROM calendarizaciones WHERE id_curso=c.id";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_fusiones   = "SELECT char_comma_sum(vc.cod_asignatura||'-'||vc.seccion||' '||vc.asignatura) AS asig_fusionadas FROM vista_gestion_cursos AS vc LEFT JOIN cursos AS ca USING (id) WHERE ca.id_fusion = c.id";

$SQL_cursos = "SELECT c.id,cod_asignatura||'-'||c.seccion AS cod_asignatura,asignatura,c.semestre||'-'||c.ano AS periodo,
                      upper(u.apellido) as apellidos,initcap(u.nombre) AS nombres,vc.carrera,
                      coalesce(sesion1,'')||' '||coalesce(sesion2,'')||' '||coalesce(sesion3,'') as horario,
                      cantidad_alumnos(c.id) AS cant_alumnos,cant_alumnos_asist(c.id) AS al_asist,c.cerrado,c.tipo_clase AS modalidad,
                      ($SQL_cant_s1) AS s1, ($SQL_cant_nc) AS nc, ($SQL_cant_s2) AS s2, ($SQL_cant_nf) AS nf, ($SQL_cal) AS cal,
                      ($SQL_prom_s1) AS prom_s1, ($SQL_prom_s2) AS prom_s2, ($SQL_prom_nc) AS prom_nc, ($SQL_prom_nf) AS prom_nf,($SQL_desvest_nf) AS desvest_nf,
                      ($SQL_aprob_s1) AS aprob_s1, ($SQL_aprob_s2) AS aprob_s2, ($SQL_aprob_nc) AS aprob_nc, ($SQL_aprob_nf) AS aprob_nf,
                      ($SQL_reprob_s1) AS reprob_s1, ($SQL_reprob_s2) AS reprob_s2, ($SQL_reprob_nc) AS reprob_nc, ($SQL_reprob_nf) AS reprob_nf,
                      ($SQL_cant_NSP) as cant_nsp,
                      ($SQL_fusiones) AS asig_fusionadas
               FROM vista_gestion_cursos AS vc
               LEFT JOIN cursos AS c USING (id)
               LEFT JOIN usuarios AS u ON u.id=vc.id_profesor
               LEFT JOIN carreras AS car ON car.id=vc.id_carrera
               WHERE true $condiciones AND c.id_fusion IS NULL
               ORDER BY c.ano DESC, c.semestre DESC, cod_asignatura ";
$SQL_tabla_completa = "COPY ($SQL_cursos) to stdout WITH CSV HEADER";
$SQL_cursos .= "$limite_reg OFFSET $reg_inicio;";
//echo("<!-- $SQL_cursos -->");
$cursos = consulta_sql($SQL_cursos);
if (count($cursos) > 0) {
	$SQL_cursos2 = "SELECT count(c.id) AS cant_cursos 
                  FROM vista_gestion_cursos AS vc 
                  LEFT JOIN cursos c USING (id) 
                  LEFT JOIN carreras AS car ON car.id=vc.id_carrera 
                  LEFT JOIN usuarios AS u ON u.id=vc.id_profesor 
                  WHERE true $condiciones AND c.id_fusion IS NULL";
	$cursos2 = consulta_sql($SQL_cursos2);
	$tot_reg = $cursos2[0]['cant_cursos'];
	$reg_ini_sgte = $reg_inicio + $cant_reg;
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
	            . "&texto_buscar=$texto_buscar"
	            . "&buscar=$buscar"
	            . "&r_inicio";
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}


$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }
$carreras = consulta_sql("SELECT id,nombre,CASE WHEN activa THEN 'Vigentes' ELSE 'No vigentes' END AS grupo FROM carreras $cond_carreras ORDER BY activa DESC,nombre;");

$estado_cursos = array(array("id"=>"f","nombre"=>"Abierto"),
                       array("id"=>"t","nombre"=>"Cerrado"));
                       
$boton_horarios = "";
if ($semestre>0 && $ano>0) {
	$boton_horarios = " <span class='boton'>
	                      <a href='$enlbase=cursos_horarios&semestre=$semestre&ano=$ano&id_carrera=$id_carrera&jornada=$jornada' class='enlaces'>
	                        Ver horarios
	                      </a>
	                    </span><br><br>";
}

$_SESSION['enlace_volver'] = "$enlbase=$modulo&id_carrera=$id_carrera&ano=$ano&semestre=$semestre&jornada=$jornada&cerrado=$cerrado&recep_acta=$recep_acta&recep_acta_comp=$recep_acta_comp&texto_buscar=$texto_buscar&buscar=$buscar&r_inicio=$reg_inicio";


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
                           
$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();

$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$TIPO_CLASES = consulta_sql("SELECT * FROM vista_tipo_clase");

$SECCIONES = consulta_sql("SELECT DISTINCT ON (seccion) seccion AS id,seccion AS nombre FROM cursos");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style="margin-top: 5px">
  <form name="formulario" action="" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
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
            <?php echo(select_group($carreras,$id_carrera)); ?>
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
          Acta Comp. recep.:<br>
          <select class='filtro' name="recep_acta_comp" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($sino,$recep_acta_comp)); ?>
          </select>
        </td>
        <td class="celdaFiltro">      
          Día:<br>
          <select class='filtro' name="dia" onChange="submitform();">
			<option value='-1'>Todos</option>
            <?php echo(select($dias_palabra,$dia)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Buscar por Código o nombre de asignatura, número de acta o nombre del profesor:<br>
          <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="55" class='boton' id="texto_buscar">
          <input type='submit' name='buscar' value='Buscar'>          
          <?php 
          	if ($buscar == "Buscar" && $texto_buscar <> "") {
          		echo("<input type='submit' name='buscar' value='Vaciar'>");          		
          	}
          ?>
          <script>document.getElementById('texto_buscar').focus();</script>
        </td>
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> curso(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="4">
      <?php echo($HTML_paginador); ?>
      <?php echo($boton_tabla_completa); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>Asignatura</td>
    <td class='tituloTabla'>A.A.</td>
    <td class='tituloTabla'>Periodo</td>
    <td class='tituloTabla'>Profesor</td>
    <td class='tituloTabla' width='50'>S1</td>
    <td class='tituloTabla' width='50'>NC</td>
    <td class='tituloTabla' width='50'>S2</td>
    <td class='tituloTabla'>Sit. Final</td>
  </tr>
<?php
	if (count($cursos) > 0) {
		$_verde   = "color: #009900; text-align: center";
		$_naranjo = "color: #FFA500; text-align: center";
		$_rojo    = "color: #ff0000; text-align: center";

		for ($x=0; $x<count($cursos); $x++) {
			extract($cursos[$x]);

			$est_s1 = $est_nc = $est_s2 = $est_rec = "color: #000000";
			
			/*
			if (strlen($asignatura)>30) { $asignatura = mb_substr($asignatura,0,30)."...";}
			if (strlen($profesor)>20)   { $profesor   = mb_substr($profesor,0,20)."...";}
			*/

			if ($asig_fusionadas <> "") { 
				$asig_fusionadas = explode(",",$asig_fusionadas);
				$asig_fusionadas = "<small>Fusionada con:<br>&nbsp;&nbsp;".implode("<br>&nbsp;&nbsp;",$asig_fusionadas)."</small>";
			}
			
			$prom_s1 = "<span style='text-decoration:overline;'>x</span>:$prom_s1";
			$prom_s2 = "<span style='text-decoration:overline;'>x</span>:$prom_s2";
			$prom_nc = "<span style='text-decoration:overline;'>x</span>:$prom_nc";
			$prom_nf = "<span style='text-decoration:overline;'>x</span>:$prom_nf";

			$tot_s1 = $aprob_s1 + $reprob_s1;
			$tot_s2 = $aprob_s2 + $reprob_s2;
			$tot_nc = $aprob_nc + $reprob_nc;
			$tot_nf = $aprob_nf + $reprob_nf - $cant_nsp;
			
			$porc_aprob_s1 = round(($aprob_s1 / ($tot_s1))*100,0);
			$porc_aprob_s2 = round(($aprob_s2 / ($tot_s2))*100,0);
			$porc_aprob_nc = round(($aprob_nc / ($tot_nc))*100,0);
			$porc_aprob_nf = round(($aprob_nf / ($tot_nf))*100,0);

			if ($porc_aprob_s1>66) { $est_s1 = $_verde; } elseif ($porc_aprob_s1==0) { $est_s1 = $_naranjo; } else { $est_s1 = $_rojo; }					
			if ($porc_aprob_s2>66) { $est_s2 = $_verde; } elseif ($porc_aprob_s2==0) { $est_s2 = $_naranjo; } else { $est_s2 = $_rojo; }
			if ($porc_aprob_nc>66) { $est_nc = $_verde; } elseif ($porc_aprob_nc==0) { $est_nc = $_naranjo; } else { $est_nc = $_rojo; }
			if ($porc_aprob_nf>66) { $est_nf = $_verde; } elseif ($porc_aprob_nf==0) { $est_nf = $_naranjo; } else { $est_nf = $_rojo; }

			$S1 = "<small style='$est_s1'>$porc_aprob_s1% &nbsp; $prom_s1 Rinden: $tot_s1</small>";
			$S2 = "<small style='$est_s2'>$porc_aprob_s2% &nbsp; $prom_s2 Rinden: $tot_s2</small>";
			$NC = "<small style='$est_nc'>$porc_aprob_nc% &nbsp; $prom_nc Rinden: $tot_nc</small>";
			//$NF = "<small>Aprob.: <span style='$est_nf'>$aprob_nf ($porc_aprob_nf%)</span> NSP:$cant_nsp &nbsp; $prom_nf &nbsp; σ: $desvest_nf</small>";
			$NF = "<small>Aprob.: <span style='$est_nf'>$porc_aprob_nf%</span> NSP:$cant_nsp &nbsp; $prom_nf &nbsp; σ: $desvest_nf</small>";
			
			$enl = "$enlbase=ver_curso&id_curso=$id";
			$enlace = "<a class='enlitem' href='$enl'>";
			echo("  <tr class='filaTabla' $bgcolor_cerrado onClick=\"window.location='$enl';\">\n");
			echo("    <td class='textoTabla'>$id</td>");
			echo("    <td class='textoTabla'><div>$cod_asignatura</div><div>$asignatura</div><div>$asig_fusionadas</div></td>");
			echo("    <td class='textoTabla' style='text-align: right'>$al_asist</td>");
			echo("    <td class='textoTabla' style='text-align: center'>$periodo<br><small>$modalidad</small></td>");
			echo("    <td class='textoTabla'>$apellidos<br>$nombres</td>");
			echo("    <td class='textoTabla' width='70'>$S1</td>");
			echo("    <td class='textoTabla' width='70'>$NC</td>");
			echo("    <td class='textoTabla' width='70'>$S2</td>");
			echo("    <td class='textoTabla' width='135'>$NF</td>");
			echo("  </tr>");
		}
	} else {
		echo("<td class='textoTabla' colspan='12'>** No hay registros para los criterios de b&uacute;squeda/selección **</td>\n");
	}
?>
</table><br>
<div class="texto">
  A.A.: Alumnos Asistentes (no se cuentan los suspendidos/retirados/abandonados)<br>
  Cal.: Calendarización del curso<br>
  S1, NC, S2 y NF: Estadśiticas de las Calificaciones respectivas
</div>
<!-- Fin: <?php echo($modulo); ?> -->

