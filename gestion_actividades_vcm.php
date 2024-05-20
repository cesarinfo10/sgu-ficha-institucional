<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
setlocale(LC,"es_CL.UTF8");
include("validar_modulo.php");
$ids_carreras = $_SESSION['ids_carreras'];

$cant_reg = $_REQUEST['cant_reg'];
if (empty($_REQUEST['cant_reg'])) { $cant_reg = 30; }
$tot_reg  = 0;

$reg_inicio = $_REQUEST['r_inicio'];
if ($reg_inicio=="") { $reg_inicio = 0; }

$texto_buscar    = $_REQUEST['texto_buscar'];
$buscar          = $_REQUEST['buscar'];
$id_ano          = $_REQUEST['id_ano'];
$id_estado       = $_REQUEST['id_estado'];
$id_dimension    = $_REQUEST['id_dimension'];
$id_tipo_act     = $_REQUEST['id_tipo_act'];
$id_tipo_unidad  = $_REQUEST['id_tipo_unidad'];
$id_alcance      = $_REQUEST['id_alcance'];
$id_modalidad    = $_REQUEST['id_modalidad'];
$id_unidad       = $_REQUEST['id_unidad'];
$id_usuario_resp = $_REQUEST['id_usuario_resp'];
$id_tipo_publico = $_REQUEST['id_tipo_publico'];

//if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['id_ano'])) { $id_ano = $ANO; }
if (empty($_REQUEST['id_dimension'])) { $id_dimension = ""; }
if (empty($_REQUEST['id_usuario_resp'])) { 
	if (count(consulta_sql("SELECT id FROM vcm.actividades WHERE ano=$id_ano AND id_responsable={$_SESSION['id_usuario']}")) > 0) {
		$id_usuario_resp = $_SESSION['id_usuario'];
	}
}
if (empty($cond_base)) { $cond_base = "true"; }

$SQL_programadas = "SELECT id FROM vcm.actividades WHERE fecha_termino::date < now()::date AND estado='Programada'";
if (count(consulta_sql($SQL_programadas)) > 0) {
	consulta_dml("UPDATE vcm.actividades SET estado='Realizada' WHERE fecha_termino::date < now()::date AND estado='Programada'");
}
$SQL_realizadas = "SELECT id FROM vcm.actividades WHERE (now()::date - fecha_termino::date) >= 15 AND estado='Realizada'";
if (count(consulta_sql($SQL_realizadas)) > 0) {
	consulta_dml("UPDATE vcm.actividades SET estado='Pendiente' WHERE (now()::date - fecha_termino::date) >= 15 AND estado='Realizada'");
}


$condicion = "WHERE $cond_base  ";

if ($buscar == '🔍 Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(act.nombre) ~* '$cadena_buscada' OR "
				   .  " text(act.id) ~* '$cadena_buscada' "
				   .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$id_ano = $id_estado = $id_dimension = $id_tipo = $id_modalidad = $id_unidad = $id_responsable = $id_tipo_publico = $id_alcance = null;
} else {

	if ($id_ano > 0) { $condicion .= "AND (ano = $id_ano) "; }

	if ($id_estado <> "") { 
		if ($id_estado == 'Efectuadas') { $condicion .= " AND (act.estado IN ('Realizada','Pendiente','Finalizada'))"; }
		else {$condicion .= "AND (act.estado = '$id_estado') "; }
	}

	if ($id_dimension <> "") { $condicion .= "AND (id_tipo IN (SELECT id FROM vcm.tipos_act WHERE dimension='$id_dimension')) "; $id_tipo = null;}

	if ($id_tipo_act > 0) { $condicion .= "AND (id_tipo = $id_tipo_act) "; }

	if ($id_alcance <> "") { $condicion .= "AND (alcance = '$id_alcance') "; }

	if ($id_modalidad <> "") { $condicion .= "AND (modalidad = '$id_modalidad') "; }
	 
	if ($id_unidad > 0) { $condicion .= "AND ($id_unidad IN (id_unidad1,id_unidad2,id_unidad3)) "; }

	if ($id_tipo_unidad <> "") { $condicion .= "AND ('$id_tipo_unidad' IN (u1.tipo,u2.tipo,u3.tipo)) "; }
	
	if ($id_usuario_resp > 0) { $condicion .= "AND (id_responsable = $id_usuario_resp	) "; }

	if ($id_tipo_publico <> "") { $condicion .= "AND ('$id_tipo_publico' = ANY (tipo_publico)) "; }
	
}

$enlace_nav = "texto_buscar=$texto_buscar&"
            . "buscar=$buscar"
            . "id_ano=$id_ano"
            . "id_estado=$id_estado"
            . "id_dimension=$id_dimension"
            . "id_tipo_act=$id_tipo_act"
            . "id_alcance=$id_alcance"
            . "id_modalidad=$id_modalidad"
            . "id_unidad=$id_unidad"
            . "id_usuario_resp=$id_usuario_resp"
            . "id_tipo_publico=$id_tipo_publico";

$SQL_asist_tot = "SELECT sum(coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0)) 
                  FROM vcm.participacion_act
			      WHERE id_actividad=act.id";
			  
$SQL_asist = "SELECT char_comma_sum(tipo_publico||': '||coalesce((coalesce(cant_personas,0)+coalesce(cant_personas_virtuales,0))::text,'*')) 
              FROM vcm.participacion_act
			  WHERE id_actividad=act.id";

$SQL_ind = "SELECT char_comma_sum(it.nombre||': '||coalesce(valor::text||CASE WHEN porcentaje THEN '%' ELSE '' END,'*'))
            FROM vcm.indicadores_act AS ind 
            LEFT JOIN vcm.indicadores_act_tipo AS it ON it.id=ind.id_tipo 
            WHERE id_actividad=act.id";

$SQL_doctos = "SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text) 
               FROM vcm.documentos_act AS doctos
			   LEFT JOIN vcm.documentos_act_tipo AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE doctos.id_actividad=act.id";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_act = "SELECT act.*,($SQL_asist) AS asistencia,($SQL_doctos) AS doctos ,($SQL_ind) AS indicadores,($SQL_asist_tot) AS asist_tot 
            FROM vista_vcm_actividades AS act
			LEFT JOIN gestion.unidades AS u1 ON u1.id=act.id_unidad1
			LEFT JOIN gestion.unidades AS u2 ON u2.id=act.id_unidad2
			LEFT JOIN gestion.unidades AS u3 ON u3.id=act.id_unidad3
			$condicion
			ORDER BY fecha_termino ";


//echo($SQL_act);

$SQL_tabla_completa = "COPY ($SQL_act) to stdout WITH CSV HEADER";
$SQL_act .= "$limite_reg OFFSET $reg_inicio";

$actividades = consulta_sql($SQL_act);

if (count($actividades) > 0) {
	$SQL_total_act = "SELECT count(act.id) AS total_act 
                      FROM vista_vcm_actividades AS act
					  LEFT JOIN gestion.unidades AS u1 ON u1.id=act.id_unidad1
			          LEFT JOIN gestion.unidades AS u2 ON u2.id=act.id_unidad2
			          LEFT JOIN gestion.unidades AS u3 ON u3.id=act.id_unidad3
	                  $condicion";
	$total_act = consulta_sql($SQL_total_act);
	$tot_reg = $total_act[0]['total_act'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$HTML = "";
if (count($actividades) > 0) {
	for ($x=0;$x<count($actividades);$x++) {
		extract($actividades[$x]);
		
		$enl = "$enlbase_sm=actividades_vcm_ver&id_actividad=$id";

		$nombre = "<a class='enlaces' id='sgu_fancybox_medium' href='$enl'>$nombre</a>";

		$fecha_reg = str_replace(" ","<br>",$fecha_reg);

		$fecha_inicio = strftime("%A %d de %B de %Y %H:%M",strtotime($fecha_inicio));
		$fecha_termino = strftime("%A %d de %B de %Y %H:%M",strtotime($fecha_termino));

		$estado = "<span class='".str_replace(" ","",$estado)."'>&nbsp;$estado&nbsp;</span>";
		$alcance = "<span class='Urgente'>$alcance</span>";

		$tipo_publico = str_replace("\"","",str_replace(",","<br>",trim($tipo_publico,"{}")));		              
		$asistencia = str_replace("\"","",str_replace(",","<br>",trim($asistencia,"{}")))
		            . "<br><b>Total: $asist_tot</b>";
		if ($indicadores == "") { 
			$indicadores = "<center>*** Sin registros ***</center>"; 
		} else {
			$indicadores = "<li>".str_replace("\"","",str_replace(",","</li><li>",trim($indicadores,"{}")));
		}

		$nombre_unidad1 = ($nombre_unidad1 <> "") ? "<li>$nombre_unidad1</li>" : "";
		$nombre_unidad2 = ($nombre_unidad2 <> "") ? "<li>$nombre_unidad2</li>" : "";
		$nombre_unidad3 = ($nombre_unidad3 <> "") ? "<li>$nombre_unidad3</li>" : "";
		$organizador_externo = ($organizador_externo <> "") ? "<hr><li>$organizador_externo</li>" : "";

		$HTML_doctos = "";
		if ($doctos <> "") {
			$documentos = explode(",",$doctos);
			for($y=0;$y<count($documentos);$y++) { 
			  $docto = explode(":",$documentos[$y]);
			  $HTML_doctos .= "<a href='actividades_vcm_doctos_descargar.php?id={$docto[1]}' class='enlaces' target='_blank'>📥 {$docto[0]}</a><br>";
			}	
		} else {
			$HTML_doctos = "<center>*** Sin documentos ***</center>";
		}
		
		$HTML .= "  <tr class='filaTabla'>\n"
			  .  "    <td class='textoTabla' align='center'>$id<br><small>$fecha_reg</small><br>$estado</td>\n"
			  .  "    <td class='textoTabla'><b>$nombre</b><br><small>Inicio: $fecha_inicio<br>Término: $fecha_termino</small></td>\n"
			  .  "    <td class='textoTabla'><small><ul style='padding-left: 15px; hyphens: auto'>$nombre_unidad1 $nombre_unidad2 $nombre_unidad3 $organizador_externo</ul></small></td>\n"
			  .  "    <td class='textoTabla' align='center'><div class='Regular'>$dimension</div><small>$nombre_tipo_act<br><br></small><div style='text-align: right'>$alcance</div></td>\n"
			  .  "    <td class='textoTabla' align='center'><small><b>$modalidad</b><div style='text-align: right'>$asistencia</div></small></td>\n"
			  .  "    <td class='textoTabla'><small><ul style='padding-left: 15px; hyphens: auto'>$indicadores</li></ul></small></td>\n"
			  .  "    <td class='textoTabla'><small>$HTML_doctos</small></td>\n"
			  .  "    <td class='textoTabla'><small>$nombre_responsable</small></td>\n"
			  .  "  </tr>\n";
	}
} else {
	$HTML = "  <tr>"
				  . "    <td class='textoTabla' colspan='12' align='center'>"
				  . "      <br>** No hay registros para los criterios de búsqueda/selección **<br><br>"
				  . "    </td>\n"
				  . "  </tr>";
}

$enlace_nav = "$enlbase=$modulo"
			. "&ano=$ano"
			. "&id_estado=$id_estado"
			. "&id_dimension=$id_dimension"
			. "&id_tipo=$id_tipo"
			. "&id_alcance=$id_alcance"
			. "&id_modalidad=$id_modalidad"
			. "&id_unidad=$id_unidad"            
			. "&id_responsable=$id_responsable"
			. "&id_carrera=$id_carrera"
			. "&id_tipo_publico=$id_tipo_publico"
			. "&texto_buscar=$texto_buscar"
			. "&buscar=$buscar"
			. "&r_inicio";

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>📥 Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$ANOS           = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM vcm.actividades ORDER BY ano DESC");
$ESTADOS        = consulta_sql("SELECT id,nombre FROM vista_vcm_estado_act");
$DIMENSIONES    = consulta_sql("SELECT id,nombre FROM vista_vcm_dimensiones_act ORDER BY nombre");
$cond_tipo_act  = ($id_dimension <> "") ? "WHERE dimension='$id_dimension'" : "";
$TIPOS          = consulta_sql("SELECT id,nombre,dimension AS grupo FROM vcm.tipos_act $cond_tipo_act ORDER BY grupo,nombre");
$cond_tipo_unidad = ($id_tipo_unidad <> "") ? "AND u.tipo='$id_tipo_unidad'" : "";
$UNIDADES       = consulta_sql("SELECT u.id,u.nombre,uu.nombre AS grupo FROM gestion.unidades u LEFT JOIN gestion.unidades uu ON uu.id=u.dependencia WHERE u.dependencia IS NOT NULL $cond_tipo_unidad ORDER BY uu.id,u.nombre");
$RESPONSABLES   = consulta_sql("SELECT id_responsable AS id,nombre_responsable||' ('||count(id)||')' AS nombre FROM vista_vcm_actividades GROUP BY id_responsable,nombre_responsable ORDER BY nombre_responsable");
$MODALIDADES    = consulta_sql("SELECT id,nombre FROM vista_vcm_modalidad_act ORDER BY nombre");
$TIPO_PUBLICO   = consulta_sql("SELECT id,nombre FROM vista_vcm_tipo_publico ORDER BY nombre");
$TIPOS_UNIDADES = consulta_sql("SELECT id,nombre FROM vista_tipos_unidades ORDER BY nombre");
$ALCANCE        = consulta_sql("SELECT id,nombre FROM vista_vcm_alcance_act ORDER BY nombre");

$ESTADOS[] = array('id' => "Efectuadas", 'nombre' => "Efectuadas (Realizadas, Pendientes, Archivadas y Finalizadas)");

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
		  <select class="filtro" name="id_ano" onChange="submitform();">
            <option value="-1">Todos</option>
			<?php echo(select($ANOS,$id_ano)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Estado: <br>
		  <select class="filtro" name="id_estado" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($ESTADOS,$id_estado)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Dimensión: <br>
		  <select class="filtro" name="id_dimension" onChange="submitform();">
			<option value="">Todas</option>
            <?php echo(select($DIMENSIONES,$id_dimension)); ?>
		  </select>
        </td>
		<td class="celdaFiltro">
		  Tipo: <br>
		  <select class="filtro" name="id_tipo_act" onChange="submitform();">
			<option value="-1">Todas</option>
            <?php echo(select_group($TIPOS,$id_tipo_act)); ?>
		  </select>
        </td>
		<td class="celdaFiltro">
		  Alcance: <br>
		  <select class="filtro" name="id_alcance" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($ALCANCE,$id_alcance)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Modalidad: <br>
		  <select class="filtro" name="id_modalidad" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select($MODALIDADES,$id_modalidad)); ?>
		  </select>
		</td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
		<td class="celdaFiltro">
		  Tipo Unidad: <br>
		  <select class="filtro" name="id_tipo_unidad" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select_group($TIPOS_UNIDADES,$id_tipo_unidad)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Unidad organizadora: <br>
		  <select class="filtro" name="id_unidad" onChange="submitform();">
			<option value="-1">Todas</option>
			<?php echo(select_group($UNIDADES,$id_unidad)); ?>    
		  </select>
		</td>
		<td class="celdaFiltro">
		  Responsable: <br>
		  <select class="filtro" name="id_usuario_resp" style="spacing: 0px" onChange="submitform();">
			<option value="t">Todas</option>
			<?php echo(select($RESPONSABLES,$id_usuario_resp)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Público Objetivo: <br>
		  <select class="filtro" name="id_tipo_publico" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($TIPO_PUBLICO,$id_tipo_publico)); ?>
		  </select>
		</td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
	  <tr>
		<td class="celdaFiltro">
		  Buscar por nombre:<br>
		  <input type="text" name="texto_buscar" value="<?php echo($texto_buscar); ?>" size="30" id="texto_buscar" class='boton'>
		  <input type='submit' name='buscar' value='🔍 Buscar' class="boton>          
		  <?php 
		  	if ($buscar == "🔍 Buscar" && $texto_buscar <> "") {
		  		echo(" <input type='submit' name='buscar' value='Vaciar'>");
		  	}
		  ?>          <script>document.getElementById("texto_buscar").focus();</script>
		</td>
		<!-- 
		<td class="celdaFiltro">
		  Gestionar tipos de:<br>
	      <a href="<?php echo("$enlbase_sm=actividades_vcm_mantenedor&tabla=tipos_act")?>" class="botoncito" id="sgu_fancybox_medium">📋 Actividades</a>
	      <a href="<?php echo("$enlbase_sm=actividades_vcm_mantenedor&tabla=documentos_act_tipo")?>" class="botoncito" id="sgu_fancybox_medium">📄 Doctos.</a>
	      <a href="<?php echo("$enlbase_sm=actividades_vcm_mantenedor&tabla=indicadores_act_tipo")?>" class="botoncito" id="sgu_fancybox_medium">📶 Indicadores</a>
		</td>
		-->
		<td class="celdaFiltro">
		  Reportes:<br>
	      <a href="<?php echo("$enlbase_sm=actividades_vcm_reporte_participacion&$enlace_nav")?>" class="boton" id="sgu_fancybox_medium">📐 Participación</a>
		</td>
		<td class="celdaFiltro">
		  Acciones:<br>
	      <a href="<?php echo("$enlbase_sm=actividades_vcm_crear")?>" class="boton" id="sgu_fancybox_medium">📆 Nueva Actividad</a>
		</td>
	  </tr>
	</table>
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr bgcolor="#F1F9FF">
	<td class="texto" colspan='5'>
	  Mostrando <b><?php echo($tot_reg); ?></b> actividades en total, en página(s) de
	  <select class='filtro' name="cant_reg" onChange="submitform();">
		<option value="-1">Todos</option>
		<?php echo(select($CANT_REGS,$cant_reg)); ?>
	  </select> filas
	  <?php echo($HTML_paginador); ?>
    </td>
    <td class="texto" colspan='6' align='right'>
	  <?php echo($boton_tabla_completa); ?>
	</td>
  </tr>
  <tr class='filaTituloTabla'>
	<td class='tituloTabla'><small>ID<br>Fecha Reg<br>Estado</small></td>
	<td class='tituloTabla' width='250'>Nombre<br><small>Fechas</small></td>
	<td class='tituloTabla' width='150'>Unidad(es)<br>Organizadora(s)</td>
	<td class='tituloTabla' width='150'><small>Dimensión<br>Tipo<br>Alcance</small></td>
	<td class='tituloTabla'>Modalidad<br><small>Público Obj. y Asistencia</small></td>
	<td class='tituloTabla' width='175'>Indicadores</td>
	<td class='tituloTabla'>Documentos</td>
	<td class='tituloTabla' width='100'>Responsable</td>
  </tr>
  <?php echo($HTML); ?>
</table><br>
</form>

<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1400,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function () {
	$('#id_responsable').selectize({
		sortField: 'text'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 1200,
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

