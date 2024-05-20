<?php

$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$aprob_ant         = $_REQUEST['aprob_ant'];
$matriculado       = $_REQUEST['matriculado'];
$orden_riesgo      = $_REQUEST['orden_riesgo'];
$riesgo_acad       = $_REQUEST['riesgo_acad'];
$riesgo_finan      = $_REQUEST['riesgo_finan'];
$riesgo            = $_REQUEST['riesgo'];

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = 1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = 't'; }
if (empty($cond_base)) { $cond_base = "true"; }
if (empty($_REQUEST['orden_riesgo'])) { $orden_riesgo = "riesgo_ponderado DESC"; }
$modulo_destino = "ver_alumno";

$sem_ant = $ano_ant = 0;
if ($SEMESTRE == 2)     { $sem_ant = 1; $ano_ant = $ANO; }
elseif ($SEMESTRE <= 1) { $sem_ant = 2; $ano_ant = $ANO - 1; }

$SQL_cursos_ant     = "SELECT id FROM cursos WHERE ano=$ano_ant AND semestre=$sem_ant";
$SQL_cursos_aprob   = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_estado=1 AND id_curso IN ($SQL_cursos_ant)";
$SQL_cursos_insc    = "SELECT count(id_curso) FROM cargas_academicas WHERE id_alumno=a.id AND id_curso IN ($SQL_cursos_ant)";
$SQL_tasa_aprob_ant = "CASE WHEN ($SQL_cursos_insc) > 0 THEN (($SQL_cursos_aprob)::real/($SQL_cursos_insc)::real*100)::numeric(4,1) ELSE 0 END";

$SQL_riesgo_ponderado = "(coalesce(riesgo_academico,100)::real*0.5+coalesce(riesgo_financiero,0)::real*0.5)::numeric(3,0)";

$condicion = "WHERE $cond_base  ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion = "WHERE ";
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
	 
	if ($estado <> "-1") {
		$condicion .= "AND (estado = '$estado') ";
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
	
	if ($riesgo_acad <> "t") {
		$orden_riesgo = "riesgo_academico DESC";
		if ($riesgo_acad == 1) { $condicion .= " AND (riesgo_academico BETWEEN 83 AND 100 ) "; }
		if ($riesgo_acad == 2) { $condicion .= " AND (riesgo_academico BETWEEN 67 AND 82.9) "; }
		if ($riesgo_acad == 3) { $condicion .= " AND (riesgo_academico BETWEEN 51 AND 66.9) "; }
		if ($riesgo_acad == 4) { $condicion .= " AND (riesgo_academico BETWEEN 35 AND 50.9) "; }
		if ($riesgo_acad == 5) { $condicion .= " AND (riesgo_academico BETWEEN  0 AND 34.9) "; }
	}
	
	if ($riesgo_finan <> "t") {
		$orden_riesgo = "riesgo_financiero DESC";
		if ($riesgo_finan == 1) { $condicion .= " AND (riesgo_financiero BETWEEN 83 AND 100 ) "; }
		if ($riesgo_finan == 2) { $condicion .= " AND (riesgo_financiero BETWEEN 67 AND 82.9) "; }
		if ($riesgo_finan == 3) { $condicion .= " AND (riesgo_financiero BETWEEN 51 AND 66.9) "; }
		if ($riesgo_finan == 4) { $condicion .= " AND (riesgo_financiero BETWEEN 35 AND 50.9) "; }
		if ($riesgo_finan == 5) { $condicion .= " AND (riesgo_financiero BETWEEN  0 AND 34.9) "; }
	}

	if ($riesgo <> "t") {
		$orden_riesgo = "riesgo_ponderado DESC";
		if ($riesgo == 1) { $condicion .= " AND (($SQL_riesgo_ponderado) BETWEEN 83 AND 100 ) "; }
		if ($riesgo == 2) { $condicion .= " AND (($SQL_riesgo_ponderado) BETWEEN 67 AND 82.9) "; }
		if ($riesgo == 3) { $condicion .= " AND (($SQL_riesgo_ponderado) BETWEEN 51 AND 66.9) "; }
		if ($riesgo == 4) { $condicion .= " AND (($SQL_riesgo_ponderado) BETWEEN 35 AND 50.9) "; }
		if ($riesgo == 5) { $condicion .= " AND (($SQL_riesgo_ponderado) BETWEEN  0 AND 34.9) "; }
	}
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_cursos_actuales = "SELECT id FROM cursos WHERE semestre=$SEMESTRE AND ano=$ANO";

$SQL_rend_acad = "SELECT ca.id_alumno,count(id_curso) AS cant_asig,
                         sum(CASE WHEN solemne1>=4 THEN 1 ELSE 0 END) AS s1_aprob,
                         sum(CASE WHEN solemne1>=1 AND solemne1<4  THEN 1 ELSE 0 END) AS s1_reprob,
                         sum(CASE WHEN solemne1=-1 THEN 1 ELSE 0 END) AS s1_nsp,
                         sum(CASE WHEN nota_catedra>=4 THEN 1 ELSE 0 END) AS nc_aprob,
                         sum(CASE WHEN nota_catedra>=1 AND nota_catedra<4  THEN 1 ELSE 0 END) AS nc_reprob,
                         sum(CASE WHEN nota_catedra=-1 THEN 1 ELSE 0 END) AS nc_nsp,
                         sum(CASE WHEN solemne2>=4 THEN 1 ELSE 0 END) AS s2_aprob,
                         sum(CASE WHEN solemne2>=1 AND solemne2<4  THEN 1 ELSE 0 END) AS s2_reprob,
                         sum(CASE WHEN solemne2=-1 THEN 1 ELSE 0 END) AS s2_nsp,
                         sum(CASE WHEN c1>=4 THEN 1 ELSE 0 END) AS c1_aprob,
                         sum(CASE WHEN c1>=1 AND c1<4  THEN 1 ELSE 0 END) AS c1_reprob,
                         sum(CASE WHEN c1=-1 THEN 1 ELSE 0 END) AS c1_nsp,
                         sum(CASE WHEN c2>=4 THEN 1 ELSE 0 END) AS c2_aprob,
                         sum(CASE WHEN c2>=1 AND c2<4  THEN 1 ELSE 0 END) AS c2_reprob,
                         sum(CASE WHEN c2=-1 THEN 1 ELSE 0 END) AS c2_nsp,
                         sum(CASE WHEN c3>=4 THEN 1 ELSE 0 END) AS c3_aprob,
                         sum(CASE WHEN c3>=1 AND c3<4  THEN 1 ELSE 0 END) AS c3_reprob,
                         sum(CASE WHEN c3=-1 THEN 1 ELSE 0 END) AS c3_nsp,
                         sum(CASE WHEN c4>=4 THEN 1 ELSE 0 END) AS c4_aprob,
                         sum(CASE WHEN c4>=1 AND c4<4  THEN 1 ELSE 0 END) AS c4_reprob,
                         sum(CASE WHEN c4=-1 THEN 1 ELSE 0 END) AS c4_nsp,
                         sum(CASE WHEN c5>=4 THEN 1 ELSE 0 END) AS c5_aprob,
                         sum(CASE WHEN c5>=1 AND c5<4  THEN 1 ELSE 0 END) AS c5_reprob,
                         sum(CASE WHEN c5=-1 THEN 1 ELSE 0 END) AS c5_nsp,
                         sum(CASE WHEN c6>=4 THEN 1 ELSE 0 END) AS c6_aprob,
                         sum(CASE WHEN c6>=1 AND c6<4  THEN 1 ELSE 0 END) AS c6_reprob,
                         sum(CASE WHEN c6=-1 THEN 1 ELSE 0 END) AS c6_nsp,
                         sum(CASE WHEN c7>=4 THEN 1 ELSE 0 END) AS c7_aprob,
                         sum(CASE WHEN c7>=1 AND c7<4  THEN 1 ELSE 0 END) AS c7_reprob,
                         sum(CASE WHEN c7=-1 THEN 1 ELSE 0 END) AS c7_nsp
                  FROM cargas_academicas AS ca 
                  LEFT JOIN calificaciones_parciales AS cp ON cp.id_ca=ca.id 
                  LEFT JOIN matriculas               AS m  ON (m.id_alumno=ca.id_alumno AND semestre=$SEMESTRE AND ano=$ANO)
                  WHERE id_curso IN ($SQL_cursos_actuales) AND (ca.id_estado IS NULL OR ca.id_estado IN (1,2))
                  GROUP BY ca.id_alumno";
//echo($SQL_rend_acad);
$SQL_rend_acad = "SELECT id_alumno,cant_asig,
                         (s1_aprob+nc_aprob+s2_aprob+c1_aprob+c2_aprob+c3_aprob+c4_aprob+c5_aprob+c6_aprob+c7_aprob) AS tot_aprob,
                         (s1_reprob+nc_reprob+s2_reprob+c1_reprob+c2_reprob+c3_reprob+c4_reprob+c5_reprob+c6_reprob+c7_reprob) AS tot_reprob,
                         (s1_nsp+nc_nsp+s2_nsp+c1_nsp+c2_nsp+c3_nsp+c4_nsp+c5_nsp+c6_nsp+c7_nsp) AS tot_nsp
                  FROM ($SQL_rend_acad) AS rend_acad";
$SQL_rend_acad = "SELECT id_alumno,cant_asig,(tot_aprob+tot_reprob+tot_nsp) AS tot_calif_ingresadas,
                         CASE WHEN (tot_aprob+tot_reprob+tot_nsp)>0 THEN (((tot_reprob+tot_nsp)::real/(tot_aprob+tot_reprob+tot_nsp)::real)*100)::numeric(3,0) ELSE 0 END AS riesgo_academico
                  FROM ($SQL_rend_acad) AS rend_acad_resumen";
                  
$SQL_morosidad = "SELECT vcr.rut,sum(monto_moroso) AS monto_moroso,sum(monto_saldot) AS monto_saldot
                  FROM vista_contratos AS vc 
                  LEFT JOIN vista_contratos_rut AS vcr ON vcr.id=vc.id
                  WHERE estado IS NOT NULL
                  GROUP BY vcr.rut";
$SQL_morosidad = "SELECT rut,
                         CASE WHEN monto_saldot>0 THEN ((coalesce(monto_moroso,0)::real/monto_saldot::real)*100)::numeric(3,0) ELSE 0 END AS riesgo_financiero 
                  FROM ($SQL_morosidad) AS morisidad_resumen";

$SQL_alumnos = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                       a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                       CASE WHEN estado_tramite IS NOT NULL THEN ae.nombre||'/'||aet.nombre ELSE ae.nombre END AS estado,
                       CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                       CASE WHEN ($SQL_cursos_insc)>0 THEN (($SQL_cursos_aprob)::real/($SQL_cursos_insc)::real*100) ELSE 0 END::numeric(4,1) AS tasa_aprobacion_ant,
                       coalesce(riesgo_academico,100) AS riesgo_academico,coalesce(cant_asig,0) AS cant_asig,coalesce(tot_calif_ingresadas,0) AS tot_calif_ingresadas,
                       coalesce(riesgo_financiero,0) AS riesgo_financiero,
                       ($SQL_riesgo_ponderado) AS riesgo_ponderado
                FROM alumnos AS a
                LEFT JOIN carreras         AS c   ON c.id=a.carrera_actual
                LEFT JOIN al_estados       AS ae  ON ae.id=a.estado
                LEFT JOIN al_estados       AS aet ON aet.id=a.estado_tramite
                LEFT JOIN ($SQL_rend_acad) AS ra  ON ra.id_alumno=a.id
                LEFT JOIN ($SQL_morosidad) AS mo  ON mo.rut=a.rut
                LEFT JOIN matriculas       AS m   ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                $condicion
                ORDER BY $orden_riesgo,nombre                 
                $limite_reg
                OFFSET $reg_inicio;";
$alumnos = consulta_sql($SQL_alumnos);

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
            . "&riesgo_acad=$riesgo_acad"
            . "&riesgo_finan=$riesgo_finan"
            . "&riesgo=$riesgo"
            . "&orden_riesgo=$orden_riesgo"
            . "&texto_buscar=$texto_buscar"
            . "&buscar=$buscar"
            . "&r_inicio";

if (count($alumnos) > 0) {
	$SQL_total_alumnos =  "SELECT count(a.id) AS total_alumnos 
	                       FROM alumnos AS a 
	                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
	                       LEFT JOIN ($SQL_rend_acad) AS ra  ON ra.id_alumno=a.id
	                       LEFT JOIN ($SQL_morosidad) AS mo  ON mo.rut=a.rut
	                       LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
	                       $condicion";
	$total_alumnos = consulta_sql($SQL_total_alumnos);
	$tot_reg = $total_alumnos[0]['total_alumnos'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
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

/*
$RIESGO = array(array("id" => 1, "nombre" => 'Alto (67% ~ 100%)'),
                array("id" => 2, "nombre" => 'Mediano (51% ~ 66%)'),
                array("id" => 3, "nombre" => 'Bajo (0% ~ 50%)'));
*/

$RIESGO = array(array("id" => 1, "nombre" => 'Muy Alto (83% ~ 100%)'),
                array("id" => 2, "nombre" => 'Alto (67% ~ 82%)'),
                array("id" => 3, "nombre" => 'Medio (51% ~ 66%)'),
                array("id" => 4, "nombre" => 'Bajo (35% ~ 50%)'),
                array("id" => 5, "nombre" => 'Muy bajo (0% ~ 34%)'));

$ORDEN_RIESGO = array(array("id" => 'riesgo_academico',       "nombre" => 'Riesgo Académico (ascendente)'),
                      array("id" => 'riesgo_academico DESC',  "nombre" => 'Riesgo Académico (descendente)'),
                      array("id" => 'riesgo_financiero',      "nombre" => 'Riesgo Financiero (ascendente)'),
                      array("id" => 'riesgo_financiero DESC', "nombre" => 'Riesgo Financiero (descendente)'),
                      array("id" => 'riesgo_ponderado',       "nombre" => 'Riesgo Ponderado (ascendente)'),
                      array("id" => 'riesgo_ponderado DESC',  "nombre" => 'Riesgo Ponderado (descendente)'));

?>
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<br>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
    <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
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
          Estado: <br>
          <select class="filtro" name="estado" onChange="submitform();">
            <option value="-1">Todos</option>
            <?php echo(select($al_estados,$estado)); ?>
          </select>
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
        <td class="celdaFiltro">
          Tasa de Aprobación Anterior: <br>
          <select class="filtro" name="aprob_ant" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($APROB_ANT,$aprob_ant)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Riesgo Academico: <br>
          <select class="filtro" name="riesgo_acad" onChange="formulario.riesgo_finan.value='t'; formulario.riesgo.value='t'; submitform();">
            <option value="t">Todos</option>
            <?php echo(select($RIESGO,$riesgo_acad)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Riesgo Financiero: <br>
          <select class="filtro" name="riesgo_finan" onChange="formulario.riesgo_acad.value='t'; formulario.riesgo.value='t'; submitform();">
            <option value="t">Todos</option>
            <?php echo(select($RIESGO,$riesgo_finan)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Riesgo Ponderado: <br>
          <select class="filtro" name="riesgo" onChange="formulario.riesgo_finan.value='t'; formulario.riesgo_acad.value='t'; submitform();">
            <option value="t">Todos</option>
            <?php echo(select($RIESGO,$riesgo)); ?>
          </select>
        </td>
        <td class="celdaFiltro">
          Ordenar por: <br>
          <select class="filtro" name="orden_riesgo" onChange="submitform();">
            <?php echo(select($ORDEN_RIESGO,$orden_riesgo)); ?>
          </select>
        </td>
      </tr>
    </table>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
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
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="5">
      Mostrando <b><?php echo($tot_reg); ?></b> alumno(s) en total, en página(s) de
      <select class='filtro' name="cant_reg" onChange="submitform();">
        <option value="-1">Todos</option>
        <?php echo(select($CANT_REGS,$cant_reg)); ?>
      </select> filas
    </td>
    <td class="texto" align="right" colspan="8">
      <?php echo($HTML_paginador); ?>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan='2'>ID</td>
    <td class='tituloTabla' rowspan='2'>RUT</td>
    <td class='tituloTabla' rowspan='2'>Nombre</td>
    <td class='tituloTabla' rowspan='2'>Carrera</td>
    <td class='tituloTabla' rowspan='2'>Cohorte</td>
    <td class='tituloTabla' rowspan='2'>Estado</td>
    <td class='tituloTabla' rowspan='2'><small>Asig.<br>Insc.</small></td>
    <td class='tituloTabla' rowspan='2'><small>Calif.<br>Reg.</small></td>
    <td class='tituloTabla' colspan='3'>Riesgo de Deserción</td>
    <td class='tituloTabla' rowspan='2'><small>Aprobación<br>Anterior</small></td>
    <td class='tituloTabla' rowspan='2'>Mat?</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Académico</td>
    <td class='tituloTabla'>Financiero</td>
    <td class='tituloTabla'>Ponderado</td>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($alumnos) > 0) {
		for ($x=0;$x<count($alumnos);$x++) {
			extract($alumnos[$x]);
			
			$enl = "$enlbase=$modulo_destino&id_alumno=$id&rut=$rut";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			list($col_destacada) = explode(" ",$orden_riesgo);
			$bgcolor[$col_destacada] = "bgcolor='#FFFF80'";
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
			               . "    <td class='textoTabla' align='right'><small>$cant_asig</small></td>\n"
			               . "    <td class='textoTabla' align='right'><small>$tot_calif_ingresadas</small></td>\n"
			               . "    <td class='textoTabla' align='right' {$bgcolor['riesgo_academico']}>$riesgo_academico%</td>\n"
			               . "    <td class='textoTabla' align='right' {$bgcolor['riesgo_financiero']}>$riesgo_financiero%</td>\n"
			               . "    <td class='textoTabla' align='right' {$bgcolor['riesgo_ponderado']}>$riesgo_ponderado%</td>\n"
			               . "    <td class='textoTabla' align='right'>$tasa_aprobacion_ant%</td>\n"
			               . "    <td class='textoTabla'>$matriculado</td>\n"
			               . "  </tr>\n";
		}
	} else {
		$HTML_alumnos = "  <tr>"
		              . "    <td class='textoTabla' colspan='13'>"
		              . "      ** No hay registros para los criterios de búsqueda/selección **"
		              . "    </td>\n"
		              . "  </tr>";
	}
	echo($HTML_alumnos);
?>
</table><br>
  </form>

<!-- Fin: <?php echo($modulo); ?> -->

