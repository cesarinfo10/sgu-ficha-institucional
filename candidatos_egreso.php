<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$ids_carreras = $_SESSION['ids_carreras'];

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_carrera        = $_REQUEST['id_carrera'];
$id_malla          = $_REQUEST['id_malla'];
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

$regimen = consulta_sql("SELECT id,nombre FROM regimenes WHERE id='$regimen'");
$nombre_regimen = $regimen[0]['nombre'];
$regimen = $regimen[0]['id'];

if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = "PRE"; }
if (empty($_REQUEST['aprob_ant'])) { $aprob_ant = "t"; }

$condicion = "WHERE true ";

if ($cohorte > 0) { $condicion .= "AND (cohorte = '$cohorte') "; }

if ($semestre_cohorte > 0) { $condicion .= "AND (semestre_cohorte = $semestre_cohorte) "; }

if ($mes_cohorte > 0) { $condicion .= "AND (mes_cohorte = $mes_cohorte) "; }
 
if ($estado <> "-1") { $condicion .= "AND (estado = '$estado') "; }

if ($moroso_financiero <> "-1") { $condicion .= "AND (moroso_financiero = '$moroso_financiero') "; }

if ($id_carrera <> "") { $condicion .= "AND (carrera_actual = '$id_carrera') "; }

if ($id_malla <> "") { $condicion .= "AND (malla_actual=$id_malla) "; }

if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

if ($admision <> "") { $condicion .= "AND (a.admision = '$admision') "; }

if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (c.regimen = '$regimen') "; }

if ($matriculado == "t") { $condicion .= "AND (m.id_alumno IS NOT NULL) "; } 
elseif ($matriculado == "f") { $condicion .= "AND (m.id_alumno IS NULL) ";}


$hoy = strftime("%Y%m%d");
$cand_egresados = consulta_sql("SELECT 1 FROM pg_tables WHERE schemaname='tmp' AND tablename='cand_egresados_$hoy'");

if (count($cand_egresados) == 0 || $_REQUEST['recalcular'] == 'Recalcular') {
	consulta_dml("DROP TABLE tmp.cand_egresados_$hoy;");
	consulta_dml("CREATE TABLE tmp.cand_egresados_$hoy (id_alumno int4 references alumnos,cant_asig_restantes int2);");

	$SQL_alumnos = "SELECT a.id AS id_alumno 
					FROM alumnos AS a 
					LEFT JOIN carreras AS c ON c.id=a.carrera_actual 
					LEFT JOIN al_estados AS ae ON ae.id=estado
					WHERE ae.nombre NOT IN ('Egresado','Titulado','Licenciado','Graduado','Post-Titulado')
					  AND c.regimen='$regimen'";
	$alumnos = consulta_sql($SQL_alumnos);

	for($x=0;$x<count($alumnos);$x++) {
		extract($alumnos[$x]);
		$SQL_asig_aprob = "SELECT CASE WHEN homologada OR convalidado OR examen_con_rel 
								  THEN id_pa 
								  ELSE c.id_prog_asig END AS id_prog_asig 
						   FROM cargas_academicas AS ca 
						   LEFT JOIN cursos AS c ON c.id=ca.id_curso 
						   WHERE id_estado in (1,3,4,5) AND id_alumno=$id_alumno";
		$asig_aprob = consulta_sql($SQL_asig_aprob);
		
		if (count($asig_aprob) > 0) {
			$SQL_avance = "SELECT vdm.cod_asignatura,avance.id_prog_asig 
						   FROM vista_detalle_malla vdm 
						   LEFT JOIN ($SQL_asig_aprob) AS avance ON avance.id_prog_asig=vdm.id_prog_asig 
						   WHERE id_malla = (SELECT malla_actual FROM alumnos WHERE id=$id_alumno)";
						   
			$SQL_asig_restantes = "SELECT $id_alumno,count(cod_asignatura) 
								   FROM ($SQL_avance) AS avance 
								   WHERE id_prog_asig IS NULL AND cod_asignatura IS NOT NULL";
			$SQL_asig_restantes = "INSERT INTO tmp.cand_egresados_$hoy $SQL_asig_restantes";
			consulta_dml($SQL_asig_restantes);
		}
	}

	consulta_dml("DELETE FROM tmp.cand_egresados_$hoy WHERE cant_asig_restantes > 12");
	echo(js("location.href='$enlbase_sm=candidatos_egreso&regimen=$regimen';"));
}

$SQL_cand_egresados = "SELECT a.id,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,
                              c.alias||'-'||a.jornada AS carrera,a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,
                              moroso_financiero,ae.nombre AS estado,
                              CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero,
                              cant_asig_restantes
                       FROM tmp.cand_egresados_$hoy AS ce
                       LEFT JOIN alumnos AS a ON a.id=ce.id_alumno
                       LEFT JOIN carreras AS c ON c.id=a.carrera_actual
                       LEFT JOIN al_estados AS ae ON ae.id=a.estado
                       LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO)
                       $condicion
                       ORDER BY cant_asig_restantes,apellidos,nombres";
$cand_egresados = consulta_sql($SQL_cand_egresados);

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

if ($id_carrera > 0) {
	$SQL_mallas = "SELECT id,trim(alias_carrera)||'/'||ano AS nombre FROM vista_mallas WHERE id_carrera=$id_carrera ORDER BY ano";
	$MALLAS = consulta_sql($SQL_mallas);
}

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");



?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
  <form name="formulario" method="get">
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
	  </tr>
    </table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Carrera/Programa:<br>
          <select class="filtro" name="id_carrera" onChange="submitform();">
            <option value="">Todas</option>
            <?php echo(select($carreras,$id_carrera)); ?>
          </select>
        </td>
<?php if ($id_carrera > 0) { ?>
        <td class="celdaFiltro">
          Malla:<br>
          <select class="filtro" name="id_malla" onChange="submitform();">
            <option value="">Cualquiera</option>
            <?php echo(select($MALLAS,$id_malla)); ?>
          </select>
        </td>
<?php } ?>		
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
<!--        <td class="celdaFiltro">
          Tasa de Aprobación Anterior: <br>
          <select class="filtro" name="aprob_ant" onChange="submitform();">
            <option value="t">Todos</option>
            <?php echo(select($APROB_ANT,$aprob_ant)); ?>
          </select>
        </td> -->
      </tr>
    </table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr bgcolor="#F1F9FF">
    <td class="texto" colspan="7">
      Mostrando <b><?php echo(count($cand_egresados)); ?></b> alumno(s) en total<!-- de <b><?php echo($nombre_regimen); ?>--></b>
    </td>
    <td class="texto">
      <a href='<?php echo($_SERVER['REQUEST_URI']); ?>&recalcular=Recalcular' onClick="return confirm('Está seguro de recalcular?\n\nEsta operación puede tomar algunos minutos.')" class='boton'>Recalcular</a>
    </td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Asignaturas<br>Restantes</td> 
    <td class='tituloTabla'>Mat?</td>
  </tr>
<?php
	$HTML_alumnos = "";
	if (count($cand_egresados) > 0) {
		for ($x=0;$x<count($cand_egresados);$x++) {
			extract($cand_egresados[$x]);
			
			$enl = "$enlbase_sm=ver_alumno&id_alumno=$id&rut=$rut&vista=avance_malla";
			$enlace = "a class='enlitem' href='$enl'";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			if (!empty($duracion)) { $duracion .= " semestres"; }
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
			               . "    <td class='textoTabla'>$id</td>\n"
			               . "    <td class='textoTabla'>$rut</td>\n"
			               . "    <td class='textoTabla'><a class='enlitem' href='$enl'>$nombre</a></td>\n"
			               . "    <td class='textoTabla'>$carrera</td>\n"
			               . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
			               . "    <td class='textoTabla'>$estado</td>\n"
			               . "    <td class='textoTabla'>$cant_asig_restantes</td>\n"
			               . "    <td class='textoTabla'>$matriculado</td>\n"
			               . "  </tr>\n";
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
});
</script>
