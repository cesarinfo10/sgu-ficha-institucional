<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");
$ids_carreras = $_SESSION['ids_carreras'];

$ID_GLOSA_EXAMEN = "10,47,5,9"; // glosa examen de grado


$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$ano               = $_REQUEST['ano'];
$mes               = $_REQUEST['mes'];
$id_estado         = $_REQUEST['id_estado'];
$tipo              = $_REQUEST['tipo'];
$fecha_ini         = $_REQUEST['fecha_ini'];
$fecha_fin         = $_REQUEST['fecha_fin'];
$id_sala           = $_REQUEST['id_sala'];
$semestre_cohorte  = $_REQUEST['semestre_cohorte'];
$mes_cohorte       = $_REQUEST['mes_cohorte'];
$cohorte           = $_REQUEST['cohorte'];
$moroso_financiero = $_REQUEST['moroso_financiero'];
$id_carrera        = $_REQUEST['id_carrera'];
$id_escuela        = $_REQUEST['id_escuela'];
$jornada           = $_REQUEST['jornada'];
$regimen           = $_REQUEST['regimen'];
$mostrar           = $_REQUEST['mostrar'];

//if (empty($_REQUEST['id_estado'])) { $id_estado = "Presentada,Pendiente"; }
if (empty($_REQUEST['id_estado'])) { $id_estado = -1; }
if (empty($_REQUEST['ano'])) { $ano = date("Y"); }
//if (empty($_REQUEST['cohorte'])) { $cohorte = 0; }
//if (empty($_REQUEST['semestre_cohorte'])) { $semestre_cohorte = 0; }
//if (empty($_REQUEST['mes_cohorte'])) { $mes_cohorte = 0; }
//if (empty($_REQUEST['estado'])) { $estado = -1; }
if (empty($_REQUEST['moroso_financiero'])) { $moroso_financiero = -1; }
if (empty($_REQUEST['regimen'])) { $regimen = "'PRE','PRE-D'"; }
if (empty($_REQUEST['mostrar'])) { $mostrar = 'mias'; }
if (empty($_REQUEST['fecha_ini'])) { $fecha_ini = date('Y')."-01-01"; }
if (empty($_REQUEST['fecha_fin'])) { $fecha_fin = date('Y')."-12-31"; }
if (empty($cond_base)) { $cond_base = "true"; }
if (empty($_REQUEST['id_escuela']) && !$_SESSION['id_escuela']) { $id_escuela = $_SESSION['id_escuela']; }

$condicion = "WHERE $cond_base  ";
$cond_estudiantes = "";

if ($buscar == 'Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(vete.nombres::text ~* '$cadena_buscada' OR "
				   .  " vete.ruts::text ~* '$cadena_buscada' "
				   .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$cohorte = $semestre_cohorte = $estado = $id_carrera = $admision = $matriculado = $regimen = null;
} else {


	if ($id_estado <> "-1") { $condicion .= "AND (et.estado IN ('".implode("','",explode(",",$id_estado))."')) "; }

	if ($ano > 0) { 
		$condicion .= "AND (date_part('year',et.fecha_examen) = $ano) "; 
		if ($mes > 0) { $condicion .= "AND (date_part('month',et.fecha_examen) = $mes) "; }
	}

	if ($tipo > 0) { $condicion .= "AND (et.tipo = $tipo) "; }

	if ($id_escuela > 0) { $condicion .= "AND (et.id_escuela = $id_escuela) "; }

	if ($id_sala == -2) { $condicion .= "AND (et.sala IS NULL) "; }
	elseif ($id_sala <> "t" AND !empty($id_sala)) { $condicion .= " AND (et.sala = '$id_sala') "; }

	if ($ano == -2 && !empty($fecha_ini) && !empty($fecha_fin)) { $condicion .= " AND et.fecha_examen::date BETWEEN '$fecha_ini'::date AND '$fecha_fin'::date "; }

	//if ($cohorte > 0) { $condicion .= "AND (a.cohorte = '$cohorte') "; }

	//if ($semestre_cohorte > 0) { $condicion .= "AND (a.semestre_cohorte = $semestre_cohorte) "; }

	//if ($mes_cohorte > 0) { $condicion .= "AND (a.mes_cohorte = $mes_cohorte) "; }
	 
	//if ($moroso_financiero <> "-1") { $condicion .= "AND (a.moroso_financiero = '$moroso_financiero') "; }
	
	if ($id_carrera <> "") { $condicion .= "AND ($id_carrera = ANY (ids_carrera_alumno) ) "; }

	//if ($jornada <> "") { $cond_estudiantes .= "AND (a.jornada = '$jornada') "; }

	if ($admision <> "") { $cond_estudiantes .= "AND (a.admision = '$admision') "; }

	//if ($regimen <> "" && $regimen <> "t") { $cond_estudiantes .= "AND (c.regimen = '$regimen') "; }

	//$SQL_mias = "SELECT id_solicitud FROM gestion.solic_respuestas WHERE id_usuario={$_SESSION['id_usuario']} AND fecha_reasignacion IS NULL AND id_usuario_reasig IS NULL";
	//if ($mostrar == "mias") { $condicion .= "AND (s.id IN ($SQL_mias)) "; }

	//if ($matriculado == "t") { $condicion .= "AND (m.id_alumno IS NOT NULL) "; } 
	//elseif ($matriculado == "f") { $condicion .= "AND (m.id_alumno IS NULL) "; }
	
}

//if (!empty($ids_carreras) && empty($id_carrera)) {
//	$condicion .= " AND carrera_actual IN ($ids_carreras) ";
//}

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_pago_examen = "SELECT count(id) FROM finanzas.cobros WHERE id_alumno=a.id AND id_glosa IN ($ID_GLOSA_EXAMEN) AND pagado AND fecha_venc>=now()::date-'6 months'::interval";

$SQL_cant_est = "SELECT count(ete.id_alumno) FROM examenes_terminales_estudiantes AS ete WHERE ete.id_exam_term=et.id";
					
$SQL_grupo_estud = "SELECT char_comma_sum(upper(a.apellidos)||' '||initcap(a.nombres)||' '||CASE WHEN ($SQL_pago_examen) >= 1 THEN '✅' ELSE '⛔' END) 
                    FROM examenes_terminales_estudiantes AS ete 
					LEFT JOIN alumnos AS a ON a.id=ete.id_alumno
					LEFT JOIN carreras AS c ON c.id=a.carrera_actual
					WHERE ete.id_exam_term=et.id $cond_estudiantes";
					
$SQL_grupo_estud_email = "SELECT char_comma_sum(lower(a.email)||coalesce(','||nombre_usuario||'@'||'alumni.umc.cl')) 
                          FROM examenes_terminales_estudiantes AS ete 
					      LEFT JOIN alumnos AS a ON a.id=ete.id_alumno
					      LEFT JOIN carreras AS c ON c.id=a.carrera_actual
					      WHERE ete.id_exam_term=et.id $cond_estudiantes";

$SQL_docentes = "SELECT char_comma_sum(vp.nombre) FROM examenes_terminales_docentes LEFT JOIN vista_profesores AS vp ON vp.id=id_profesor WHERE id_examen=et.id";

$SQL_exam_term = "SELECT et.id,to_char(et.fecha_reg,'dd-tmMon-YYYY HH24:MI') AS fecha_reg,
                         et.estado,to_char(et.estado_fecha,'dd-tmMon-YYYY') AS estado_fecha,
                         et.tipo,et.tema,to_char(et.fecha_examen,'tmDay dd-tmMon-YYYY HH24:MI') AS fecha_examen,s.nombre AS sala,
						 u.nombre AS ministro_de_fe,($SQL_cant_est) AS cant_estudiantes,
						 ($SQL_docentes) AS comision,e.nombre AS escuela,
						 ($SQL_grupo_estud) AS estudiantes,($SQL_grupo_estud_email) AS estudiantes_email
                  FROM examenes_terminales AS et
				  LEFT JOIN vista_examenes_terminales AS vet USING (id)
				  LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
				  LEFT JOIN escuelas AS e ON e.id=et.id_escuela
				  LEFT JOIN salas AS s ON s.codigo=et.sala
				  LEFT JOIN vista_examenes_terminales_estudiantes AS vete ON vete.id_exam_term=et.id
				  $condicion 			  
				  ORDER BY et.fecha_examen ASC ";
echo("<!-- $SQL_exam_term -->");

$SQL_tabla_completa = "COPY ($SQL_exam_term) to stdout WITH CSV HEADER";
$SQL_exam_term .= "$limite_reg OFFSET $reg_inicio";
$exam_term = consulta_sql($SQL_exam_term);
						   
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

if (count($exam_term) > 0) {
	$SQL_total_exam_term =  "SELECT count(et.id) AS total_exam_term 
                             FROM examenes_terminales AS et
							 LEFT JOIN vista_examenes_terminales AS vet USING (id)
				             LEFT JOIN vista_usuarios AS u ON u.id=et.id_ministro_de_fe
							 LEFT JOIN escuelas AS e ON e.id=et.id_escuela
			                 LEFT JOIN salas AS s ON s.codigo=et.sala
							 LEFT JOIN vista_examenes_terminales_estudiantes AS vete ON vete.id_exam_term=et.id
						     $condicion";
	$total_exam_term = consulta_sql($SQL_total_exam_term);
	$tot_reg = $total_exam_term[0]['total_exam_term'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$cond_carreras = "WHERE true ";
if ($ids_carreras <> "") { $cond_carreras .= "AND id IN ($ids_carreras) "; }
if ($regimen <> "")      { $cond_carreras .= "AND regimen IN ($regimen) "; }
if ($id_escuela <> "-1")   { $cond_carreras .= "AND id_escuela=$id_escuela "; }

$SQL_carreras = "SELECT id,nombre||' ('||alias||')' AS nombre,CASE WHEN admision THEN 'Vigente' ELSE 'No vigente' END AS grupo FROM carreras $cond_carreras ORDER BY admision DESC,nombre;";
$carreras = consulta_sql($SQL_carreras);

//echo($SQL_carreras);

$SQL_al_estados = "SELECT id,nombre FROM al_estados WHERE nombre NOT IN ('Moroso') ORDER BY id;";
$al_estados = consulta_sql($SQL_al_estados);

$ESTADOS = consulta_sql("SELECT * FROM vista_exam_terminales_estados");

$cohortes = $anos;

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
							array("id"=>2,"nombre"=>2));

$REGIMENES = consulta_sql("SELECT * FROM regimenes");

$ESCUELAS = consulta_sql("SELECT id,nombre FROM escuelas WHERE activa ORDER BY nombre");

$MOSTRAR = array(array('id' => 'mias', 'nombre' => 'Sólo las mías'));

$SALAS = consulta_sql("SELECT codigo AS id,nombre||' ('||coalesce(capacidad,0)||' sillas)' AS nombre,'Piso '||piso||'°' AS grupo FROM salas WHERE activa ORDER BY piso,nombre;");
$SALAS = array_merge(array(array('id' => "-2", 'nombre' => "** Sin Sala **")),$SALAS);

$ANOS = array();
for($anos=date("Y")+1;$anos>=2021;$anos--) { $ANOS = array_merge($ANOS,array(array('id'=>$anos,'nombre'=>$anos))); }
$ANOS = array_merge(array(array('id'=>-2,'nombre'=>"Otro")),$ANOS);

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$TIPOS = consulta_sql("SELECT id,nombre FROM vista_exam_terminales_tipos ORDER BY nombre");

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

          <?php if ($ano > 0) { ?>
          <select class="filtro" name="mes" onChange="submitform();">
            <option value="-1">- Mes --</option>
            <?php echo(select($meses_palabra,$mes)); ?>
          </select>
          <?php } ?>
          <?php if ($ano == -2) { ?>
		    <input type='date' name='fecha_ini' value="<?php echo($fecha_ini); ?>" class="boton" style='font-size: 9pt' onChange="submitform();"> al
            <input type='date' name='fecha_fin' value="<?php echo($fecha_fin); ?>" class="boton" style='font-size: 9pt' onChange="submitform();">
          <?php } ?>
		</td>
		<td class="celdaFiltro">
		  Estado: <br>
		  <select class="filtro" name="id_estado" onChange="submitform();">
			<option value="-1">Todos</option>
			<?php echo(select($ESTADOS,$id_estado)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Tipo: <br>
		  <select class="filtro" name="id_tipo" onChange="submitform();">
			<option value="-1">Todos</option>
            <?php echo(select($TIPOS,$id_tipo)); ?>
		  </select>
        </td>
		<!-- 
		<td class="celdaFiltro">
          Fecha Examen:<br>
          <input type='date' name='fecha_ini' value="<?php echo($fecha_ini); ?>" class="boton" style='font-size: 9pt' onChange="submitform();"> al
          <input type='date' name='fecha_fin' value="<?php echo($fecha_fin); ?>" class="boton" style='font-size: 9pt' onChange="submitform();">
        </td>
		-->
		<td class="celdaFiltro">
          Sala:<br>
		  <select class="filtro" name="id_sala" onChange="submitform();">
			<option value="t">Todas</option>
            <?php echo(select_group($SALAS,$id_sala)); ?>
		  </select>
        </td>		
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
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
		  Moroso: <br>
		  <select class="filtro" name="moroso_financiero" onChange="submitform();">
			<option value="-1">Todos</option>
			<?php echo(select($sino,$moroso_financiero)); ?>
		  </select>
		</td>
-->
        <td class="celdaFiltro">
          Escuela:<br>
		  <select class="filtro" name="id_escuela" onChange="submitform();">
			<option value="-1">Todos</option>
            <?php echo(select($ESCUELAS,$id_escuela)); ?>
		  </select>
        </td>

        <td class="celdaFiltro">
		  Carrera/Programa:<br>
		  <select class="filtro" name="id_carrera" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select_group($carreras,$id_carrera)); ?>
		  </select>
<!--
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
		</td> -->
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
		<td class="celdaFiltro">
		  Buscar por RUT o nombre (estudiante):<br>
		  <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="40" id="texto_buscar" class='boton'>
		  <input type='submit' name='buscar' value='Buscar'>          
		  <?php 
		  	if ($buscar == "Buscar" && $texto_buscar <> "") {
		  		echo(" <input type='submit' name='buscar' value='Vaciar'>");
		  	}
		  ?>          <script>document.getElementById("texto_buscar").focus();</script>
		</td>
		<td class="celdaFiltro">
          Acciones:<br>
          <a href="<?php echo("$enlbase_sm=examenes_terminales_crear"); ?>" id="sgu_fancybox_medium" class="boton">🗓 Programar</a>
          <!-- <a href="<?php echo("$enlbase_sm=examenes_terminales_proponer"); ?>" id="sgu_fancybox_medium" class="boton">⛏ Proponer Tema</a> -->
        </td>
	  </tr>
	</table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
	<td class="texto" colspan='3'>
	  Mostrando <b><?php echo($tot_reg); ?></b> examenes en total, en página(s) de
	  <select class='filtro' name="cant_reg" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($CANT_REGS,$cant_reg)); ?>
	  </select> filas
	</td>
	<td class="texto" colspan='3' align='right'>
	  <?php echo($HTML_paginador); ?>
	  <?php echo($boton_tabla_completa); ?>
	</td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan='5'>Examen</td>
    <td class='tituloTabla' rowspan='2'>Estudiante(s)</td>
  </tr>
  <tr class='filaTituloTabla'>
	<td class='tituloTabla'><small>ID<br>Fecha<br>Estado</small></td>
	<td class='tituloTabla' width='300'>Tipo y<br>Tema</td>
	<td class='tituloTabla'>Fecha y Hora<br>Sala</td>
	<td class='tituloTabla'>Comisión</td>
	<td class='tituloTabla' width='100'>Escuela</td>
  </tr>
<?php
	$HTML = "";
	if (count($exam_term) > 0) {
		for ($x=0;$x<count($exam_term);$x++) {
			extract($exam_term[$x]);
			
			$enl = "$enlbase_sm=examenes_terminales_ver&id_examen=$id";
			$enlace = "a class='enlitem' href='$enl'";

			$tema = "<a href='$enl' id='sgu_fancybox_medium' class='enlaces'>$tema</a>";

			$estado = "<div class='".str_replace(" ","",$estado)."'>&nbsp;$estado&nbsp;<br><sup>$estado_fecha</sup></div>";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }

			$comision = str_replace(",","<br>",$comision);
			$estudiantes = str_replace(",","<br>",$estudiantes);

			$fecha_examen = str_replace(" ","<br>",$fecha_examen);
			$fecha_reg = str_replace(" ","<br>",$fecha_reg);

			$HTML .= "  <tr class='filaTabla'>\n"
				  .  "    <td class='textoTabla' align='center'>$id<br><small>$fecha_reg</small><br>$estado</td>\n"
				  .  "    <td class='textoTabla' align='justify'><center><small><b>$tipo</b></small></center>$tema</td>\n"
				  .  "    <td class='textoTabla' align='center'>$fecha_examen<br>Sala $sala<br></td>\n"
				  .  "    <td class='textoTabla'><small><b><i>$ministro_de_fe</i></b><br>$comision</small></td>\n"
				  .  "    <td class='textoTabla' align='center'><small>$escuela</small></td>\n"
				  .  "    <td class='textoTabla'><small>$estudiantes</small></td>\n"
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

