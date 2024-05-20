<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$solic_estado      = $_REQUEST['solic_estado'];
$solic_tipo        = $_REQUEST['solic_tipo'];
$ano               = $_REQUEST['ano'];
$id_carrera        = $_REQUEST['id_carrera'];
$jornada           = $_REQUEST['jornada'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$estado            = $_REQUEST['estado'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$admision          = $_REQUEST['admision'];
$regimen           = $_REQUEST['regimen'];
$matriculado       = $_REQUEST['matriculado'];
$mostrar           = $_REQUEST['mostrar'];

//if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['solic_estado'])) { $solic_estado = "Presentada,Pendiente"; }
if (empty($_REQUEST['ano'])) { $ano = $ANO; }
if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
//if (empty($_REQUEST['regimen'])) { $regimen = 'PRE'; }
if (empty($_REQUEST['mostrar'])) { $mostrar = 'mias'; }
if (empty($cond_base)) { $cond_base = "true"; }

$condicion = "WHERE $cond_base  ";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(a.nombres||' '||a.apellidos) ~* '$cadena_buscada' OR "
				   .  " a.rut ~* '$cadena_buscada' OR "
				   .  " text(a.id) ~* '$cadena_buscada' "
				   .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {

	if ($solic_estado <> "-1") { $condicion .= "AND (s.estado IN ('".implode("','",explode(",",$solic_estado))."')) "; }

	if ($ano > 0) { $condicion .= "AND (date_part('year',s.fecha) = $ano) "; }

	if ($solic_tipo > 0) { $condicion .= "AND (s.id_tipo = $solic_tipo) "; }

	if ($cohorte > 0) { $condicion .= "AND (a.cohorte = '$cohorte') "; }

	if ($semestre_cohorte > 0) { $condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) "; }

	if ($mes_cohorte > 0) { $condicion .= "AND (a.mes_cohorte = $mes_cohorte) "; }
	 
	if ($moroso_financiero <> "-1") { $condicion .= "AND (a.moroso_financiero = '$moroso_financiero') "; }
	
	if ($id_carrera <> "") { $condicion .= "AND (a.carrera_actual = '$id_carrera') "; }

	if ($jornada <> "") { $condicion .= "AND (a.jornada = '$jornada') "; }

	if ($admision <> "") { $condicion .= "AND (a.admision = '$admision') "; }

	if ($regimen <> "" && $regimen <> "t") { $condicion .= "AND (c.regimen = '$regimen') "; }

	$SQL_mias = "SELECT id_solicitud FROM gestion.solic_respuestas WHERE id_usuario={$_SESSION['id_usuario']} AND fecha_reasignacion IS NULL AND id_usuario_reasig IS NULL";
	if ($mostrar == "mias") { $condicion .= "AND (s.id IN ($SQL_mias)) "; }

	if ($matriculado == "t") { $condicion .= "AND (m.id_alumno IS NOT NULL) "; } 
	elseif ($matriculado == "f") { $condicion .= "AND (m.id_alumno IS NULL) "; }
	
}

if (!empty($ids_carreras) && empty($id_carrera)) {
	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_solic_resp = "SELECT sr.id_usuario,u.nombre_usuario,gu.alias,
                          CASE WHEN visto_bueno = 't' THEN 'Aceptada' 
						       WHEN visto_bueno = 'f' THEN 'Rechazada' 
						       WHEN visto_bueno IS NULL AND fecha_reasignacion IS NOT NULL THEN 'Reasignada'
							   ELSE 'Sin responder' 
						  END AS vobo,
						  to_char(fecha_respuesta,'DD-tmMon-YYYY HH24:MI') AS fecha_respuesta,
						  to_char(fecha_reasignacion,'DD-tmMon-YYYY HH24:MI') AS fecha_reasignacion
				   FROM gestion.solic_respuestas AS sr
				   LEFT JOIN usuarios         AS u  ON u.id=sr.id_usuario
				   LEFT JOIN gestion.unidades AS gu ON gu.id=u.id_unidad
				   WHERE sr.id_solicitud = s.id";

$SQL_solic_resp = "SELECT char_comma_sum(alias||' ('||nombre_usuario||'): '||vobo||' '||coalesce(fecha_respuesta,fecha_reasignacion,'')) AS resp FROM ($SQL_solic_resp) AS solic_resp";

$SQL_solic = "SELECT s.id,a.rut,a.nombres,a.apellidos,va.carrera||'-'||a.jornada AS carrera,
                     a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,va.estado,a.moroso_financiero,
                     ts.nombre AS tipo_solic,s.estado AS estado_solic,resp_obs,ts.alias AS alias_solic,
                     to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,
					 to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,
					 ($SQL_solic_resp) AS responsables,s.email,s.tel_movil,s.telefono
			  FROM gestion.solicitudes AS s
			  LEFT JOIN gestion.solic_tipos AS ts ON ts.id = s.id_tipo
			  LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno
			  LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno
			  LEFT JOIN carreras			AS c  ON c.id = a.carrera_actual 
			  $condicion
			  ORDER BY s.estado_fecha DESC ";

//echo($SQL_fuas);
$SQL_tabla_completa = "COPY ($SQL_solic) to stdout WITH CSV HEADER";
$SQL_solic .= "$limite_reg OFFSET $reg_inicio";
$solic = consulta_sql($SQL_solic);
						   
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
			. "&ver_datos_contacto=$ver_datos_contacto"
			. "&texto_buscar=$texto_buscar"
			. "&buscar=$buscar"
			. "&r_inicio";

if (count($solic) > 0) {
	$SQL_total_solic =  "SELECT count(a.id) AS total_solic 
			            FROM gestion.solicitudes AS s
						LEFT JOIN gestion.solic_tipos AS ts ON ts.id = s.id_tipo
						LEFT JOIN vista_alumnos       AS va ON va.id = s.id_alumno
						LEFT JOIN alumnos             AS a  ON a.id = s.id_alumno
						LEFT JOIN carreras			AS c  ON c.id = a.carrera_actual 
						$condicion";
	$total_solic = consulta_sql($SQL_total_solic);
	$tot_reg = $total_solic[0]['total_solic'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen='$regimen' "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras $cond_carreras ORDER BY nombre;";
$carreras = consulta_sql($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$SOLIC_ESTADOS = consulta_sql("SELECT * FROM vista_solic_estados");
$SOLIC_ESTADOS = array_merge(array(array('id' => "Presentada,Pendiente", 'nombre' => "Presentada o Pendiente")),$SOLIC_ESTADOS);

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
							array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$MOSTRAR = array(array('id' => 'mias', 'nombre' => 'Sólo las mías'));

$ANOS = array();
for($anos=date("Y")+1;$anos>=2021;$anos--) { $ANOS = array_merge($ANOS,array(array('id'=>$anos,'nombre'=>$anos))); }

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$tipos_solic = consulta_sql("SELECT id,nombre FROM gestion.solic_tipos WHERE activo ORDER BY nombre");
$tipos_solic_novig = consulta_sql("SELECT id,nombre FROM gestion.solic_tipos WHERE NOT activo ORDER BY nombre");
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto">
  <form name="formulario" action="principal.php" method="get">
	<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
	  <td class="celdaFiltro">
		  Año: <br>
		  <select class="filtro" name="ano" onChange="submitform();">
			<?php echo(select($ANOS,$ano)); ?>    
		  </select>
		</td>
		</td>		<td class="celdaFiltro">
		  Estado: <br>
		  <select class="filtro" name="solic_estado" onChange="submitform();">
			<option value="-1">Todos</option>
			<?php echo(select($SOLIC_ESTADOS,$solic_estado)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Tipo: <br>
		  <select class="filtro" name="solic_tipo" onChange="submitform();">
			<option value="-1">Todas</option>
			<optgroup label='Operativas'>
			  <?php echo(select($tipos_solic,$solic_tipo)); ?>
			</optgroup>
			<optgroup label='No operativas'>
			  <?php echo(select($tipos_solic_novig,$solic_tipo)); ?>
			</optgroup>
		  </select>
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
		  Mostrar: <br>
		  <select class="filtro" name="mostrar" onChange="submitform();">
			<option value="t">Todas</option>
			<?php echo(select($MOSTRAR,$mostrar)); ?>
		  </select>
		</td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
		<td class="celdaFiltro">
		  Buscar por ID, RUT o nombre (estudiante):<br>
		  <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar" class='boton'>
		  <input type='submit' name='buscar' value='Buscar'>          
		  <?php 
		  	if ($buscar == "Buscar" && $texto_buscar <> "") {
		  		echo(" <input type='submit' name='buscar' value='Vaciar'>");
		  	}
		  ?>          <script>document.getElementById("texto_buscar").focus();</script>
		</td>
	  </tr>
	</table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
	<td class="texto" colspan='6'>
	  Mostrando <b><?php echo($tot_reg); ?></b> solicitudes en total, en página(s) de
	  <select class='filtro' name="cant_reg" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($CANT_REGS,$cant_reg)); ?>
	  </select> filas
	  <?php echo($HTML_paginador); ?>
	  <?php echo($boton_tabla_completa); ?>
	</td>
  </tr>
  <tr class='filaTituloTabla'>
	<td class='tituloTabla'><small>N°<br>[Fecha]</small></td>
	<td class='tituloTabla'><small>RUT<br>Nombre</small></td>
	<td class='tituloTabla'><small>Carrera<br>Cohorte<br>Estado</small></td>
	<td class='tituloTabla'><small>Tipo<br>Estado</small></td>
	<td class='tituloTabla'>Responsable(s)</td>
  </tr>
<?php
	$HTML = "";
	if (count($solic) > 0) {
		for ($x=0;$x<count($solic);$x++) {
			extract($solic[$x]);
			
			$enl = "$enlbase=solicitudes_gestionar&id_solic=$id&id_alumno=$id_alumno";
			$enlace = "a class='enlitem' href='$enl'";

			$fecha = str_replace(" ","<br>",$fecha);

			$estado_solic = "<span class='".str_replace(" ","",$estado_solic)."'>&nbsp;$estado_solic&nbsp;</span>";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }

			$responsables = str_replace(",","<br>",$responsables);
	
			$HTML .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
				  .  "    <td class='textoTabla' align='center'>$id<br><small>$fecha</small></td>\n"
				  .  "    <td class='textoTabla'><small>$rut</small><br>$apellidos<br>$nombres</td>\n"
				  .  "    <td class='textoTabla' align='center'>$carrera<br><small>$cohorte $mes_cohorte<br>$estado</small></td>\n"
				  .  "    <td class='textoTabla' align='center'>$tipo_solic<br>$estado_solic<br><small>$estado_fecha</small></td>\n"
				  .  "    <td class='textoTabla' align='left'><small>$responsables</small></td>\n"
				  .  "  </tr>\n";
		}
	} else {
		$HTML = "  <tr>"
					  . "    <td class='textoTabla' colspan='12'>"
					  . "      No hay registros para los criterios de búsqueda/selección"
					  . "    </td>\n"
					  . "  </tr>";
	}
	echo($HTML);
?>
</table><br>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 750,
		'maxHeight'			: 750,
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
		'width'				: 850,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- Fin: <?php echo($modulo); ?> -->

