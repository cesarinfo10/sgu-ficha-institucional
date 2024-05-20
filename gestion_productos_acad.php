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

$texto_buscar      = $_REQUEST['texto_buscar'];
$buscar            = $_REQUEST['buscar'];
$id_ano            = $_REQUEST['id_ano'];
$id_estado         = $_REQUEST['id_estado'];
$id_dimension      = $_REQUEST['id_dimension'];
$id_tipo           = $_REQUEST['id_tipo'];
$id_alcance        = $_REQUEST['id_alcance'];
$id_formato_public = $_REQUEST['id_formato_public'];
$id_usuario_reg    = $_REQUEST['id_usuario_reg'];

//if (empty($_REQUEST['matriculado'])) { $matriculado = "t"; }
if (empty($_REQUEST['id_ano'])) { $id_ano = $ANO; }
if (empty($_REQUEST['id_dimension'])) { $id_dimension = ""; }
if (empty($_REQUEST['id_usuario_reg'])) { 
	if (count(consulta_sql("SELECT id FROM dpii.productos_acad WHERE ano=$id_ano AND id_usuario_reg={$_SESSION['id_usuario']}")) > 0) {
		$id_usuario_reg = $_SESSION['id_usuario'];
	}
}
if (empty($cond_base)) { $cond_base = "true"; }

$condicion = "WHERE $cond_base  ";

if ($buscar == '🔍 Buscar' && $texto_buscar <> "") {
	$texto_buscar_regexp = sql_regexp($texto_buscar);
	$textos_buscar = explode(" ",$texto_buscar_regexp);
	$condicion .= " AND ";
	for ($x=0;$x<count($textos_buscar);$x++) {
		$cadena_buscada = strtolower($textos_buscar[$x]);
		$condicion .= "(lower(vpa.nombre) ~* '$cadena_buscada' OR "
				   .  " text(vpa.id) ~* '$cadena_buscada' OR "
				   . "  lower(palabras_clave) ~* '$cadena_buscada' "
				   .  ") AND ";
	}
	$condicion=substr($condicion,0,strlen($condicion)-4);
	$id_ano = $id_estado = $id_dimension = $id_tipo = $id_modalidad = $id_unidad = $id_responsable = $id_tipo_publico = $id_alcance = null;
} else {

	if ($id_ano > 0) { $condicion .= "AND (vpa.ano = $id_ano) "; }

	if ($id_estado > 0) { $condicion .= "AND (vpa.id_estado = $id_estado) "; }
	elseif ($id_estado == "Finalizados") { $condicion .= "AND (vpa.id_estado IN (SELECT id FROM dpii.estados_prod WHERE termino_exitoso)) "; }

	if ($id_dimension <> "") { 
		$condicion .= "AND (vpa.id_tipo IN (SELECT id FROM dpii.tipos_prod WHERE dimension='$id_dimension')) ";
		$SQL_tipos_prod = "SELECT id FROM dpii.tipos_prod WHERE dimension='$id_dimension' AND id=$id_tipo";
		if (count(consulta_sql($SQL_tipos_prod)) == 0) { $id_tipo = null; } 
	}

	if ($id_tipo > 0) { $condicion .= "AND (vpa.id_tipo = $id_tipo) "; }

	if ($id_alcance <> "") { $condicion .= "AND (vpa.alcance = '$id_alcance') "; }

	if ($id_formato_public <> "") { $condicion .= "AND (vpa.id_formato_public = '$id_formato_public') "; }
	 	
	if ($id_usuario_reg > 0) { $condicion .= "AND (vpa.id_usuario_reg = $id_usuario_reg	) "; }
	
}

$enlace_nav = "texto_buscar=$texto_buscar&"
            . "buscar=$buscar"
            . "id_ano=$id_ano"
            . "id_estado=$id_estado"
            . "id_dimension=$id_dimension"
            . "id_tipo=$id_tipo"
            . "id_alcance=$id_alcance"
            . "id_formato_public=$id_formato_public"
            . "id_usuario_reg=$id_usuario_reg";

$SQL_ind = "SELECT char_comma_sum(it.nombre||': '||coalesce(valor::text||CASE WHEN porcentaje THEN '%' ELSE '' END,'*'))
            FROM dpii.indicadores_prod_acad AS ind 
            LEFT JOIN dpii.indicadores_tipo_prod_acad AS it ON it.id=ind.id_tipo 
            WHERE id_prod_acad=vpa.id";

$SQL_doctos = "SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text) 
               FROM dpii.documentos_prod_acad AS doctos
			   LEFT JOIN dpii.doctos_tipo_prod_acad AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE doctos.id_prod_acad=vpa.id";

$SQL_autores = "SELECT char_comma_sum(aut.apellidos||' '||aut.nombres) AS autores
                FROM dpii.autores_prod AS aut
                WHERE id_prod_acad=vpa.id";

$limite_reg = "LIMIT $cant_reg";
if ($cant_reg == -1) { $limite_reg = ""; }

$SQL_prod = "SELECT vpa.id,vpa.fecha_reg,vpa.estado,
                    vpa.nombre,fecha_inicio,vpa.fecha_termino,
                    vpa.dimension,vpa.nombre_tipo_prod,vpa.alcance,
					vpa.medio_public,vpa.public_formato,vpa.nombre_reg,
					($SQL_autores) AS autores,
                    ($SQL_doctos) AS doctos ,($SQL_ind) AS indicadores 
             FROM vista_dpii_productos_acad AS vpa
			 $condicion
			 ORDER BY fecha_reg ";

//echo($SQL_prod);

$SQL_tabla_completa = "COPY ($SQL_act) to stdout WITH CSV HEADER";
$SQL_prod .= "$limite_reg OFFSET $reg_inicio";

$prod = consulta_sql($SQL_prod);

if (count($prod) > 0) {
	$SQL_total_prod = "SELECT count(vpa.id) AS total_prod
                       FROM vista_dpii_productos_acad AS vpa
 	                   $condicion";
	$total_prod = consulta_sql($SQL_total_prod);
	$tot_reg = $total_prod[0]['total_prod'];
	
	$HTML_paginador = "Páginas ".html_paginador($tot_reg,$reg_inicio,$cant_reg,$enlace_nav);
}

$HTML = "";
if (count($prod) > 0) {
	for ($x=0;$x<count($prod);$x++) {
		extract($prod[$x]);
		
		$enl = "$enlbase_sm=productos_acad_ver&id_prod_acad=$id";

		$nombre = "<a class='enlaces' id='sgu_fancybox_medium' href='$enl'>$nombre</a>";

		$fecha_reg = str_replace(" ","<br>",$fecha_reg);

		$fecha_inicio = strftime("%A %d de %B de %Y",strtotime($fecha_inicio));
		$fecha_termino = strftime("%A %d de %B de %Y",strtotime($fecha_termino));

		$estado = "<span class='".str_replace(" ","",$estado)."'>&nbsp;$estado&nbsp;</span>";
		$alcance = "<span class='Urgente'>$alcance</span>";

		if ($indicadores == "") { 
			$indicadores = "<center>*** Sin registros ***</center>"; 
		} else {
			$indicadores = "<li>".str_replace("\"","",str_replace(",","</li><li>",trim($indicadores,"{}")));
		}
		
		if ($autores == "") { 
			$autores = "*** Sin autor(es) ingresado(s) ***";
		} else {
			$autores = "<li>".str_replace("\"","",str_replace(",","</li><li>",trim($autores,"{}")));
		}

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
		
		if ($autores == "") { $autores = "** Sin autor(es) ingresado(s) **"; }
		if ($asignaturas == "") { $asignaturas = "** Sin asignatura(s) ingresada(s) **"; }	

		$HTML .= "  <tr class='filaTabla'>\n"
			  .  "    <td class='textoTabla' align='center'>$id<br><small>$fecha_reg</small><br>$estado</td>\n"
			  .  "    <td class='textoTabla'><b>$nombre</b><br><small>Inicio: $fecha_inicio<br>Término: $fecha_termino</small></td>\n"
			  .  "    <td class='textoTabla'><small><ul style='padding-left: 15px; hyphens: auto'>$autores</ul></small></td>\n"
			  .  "    <td class='textoTabla' align='center'><div class='Regular'>$dimension</div><small>$nombre_tipo_prod<br><br></small><div style='text-align: right'>$alcance</div></td>\n"
			  .  "    <td class='textoTabla'><small><ul style='padding-left: 15px; hyphens: auto'>$asignaturas</ul></small></td>\n"
			  .  "    <td class='textoTabla'><small><ul style='padding-left: 15px; hyphens: auto'>$indicadores</li></ul></small></td>\n"
			  .  "    <td class='textoTabla'><small>$HTML_doctos</small></td>\n"
			  .  "    <td class='textoTabla'><small>$nombre_reg</small></td>\n"
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
			. "&id_responsable=$id_responsable"
			. "&texto_buscar=$texto_buscar"
			. "&buscar=$buscar"
			. "&r_inicio";

$id_sesion = $_SESSION['usuario']."_".$modulo."_".session_id();
$boton_tabla_completa = "<a href='#' onClick=\"javascript:window.open('tabla_completa.php?id_sesion=$id_sesion');\" class='boton'><small>📥 Tabla Completa</small></a>";
$nombre_arch = "sql-fulltables/$id_sesion.sql";
file_put_contents($nombre_arch,$SQL_tabla_completa);

$ANOS           = consulta_sql("SELECT DISTINCT ON (ano) ano AS id,ano AS nombre FROM dpii.productos_acad ORDER BY ano DESC");

$cond_estado_prod = ($id_dimension <> "") ? "WHERE '$id_dimension' = ANY (dimension)" : "";
$ESTADOS          = consulta_sql("SELECT id,nombre,CASE WHEN NOT termino_exitoso THEN 'Avance' ELSE 'Finalizado exitoso' END AS grupo FROM dpii.estados_prod $cond_estado_prod");

$DIMENSIONES    = consulta_sql("SELECT id,nombre FROM vista_dpii_dimensiones_prod ORDER BY nombre");

$cond_tipo_prod = ($id_dimension <> "") ? "WHERE dimension='$id_dimension'" : "";
$TIPOS          = consulta_sql("SELECT id,nombre,dimension AS grupo FROM dpii.tipos_prod $cond_tipo_prod ORDER BY grupo,nombre");

$USUARIOS_REG    = consulta_sql("SELECT id_usuario_reg AS id,nombre_reg||' ('||count(id)||')' AS nombre FROM vista_dpii_productos_acad GROUP BY id_usuario_reg,nombre_reg ORDER BY nombre_reg");
$FORMATOS_PUBLIC = consulta_sql("SELECT id,nombre FROM vista_dpii_formato_public_prod ORDER BY nombre");
$ALCANCE         = consulta_sql("SELECT id,nombre FROM vista_dpii_alcance_prod ORDER BY nombre");

$ESTADOS[] = array('id' => "Finalizados", 'nombre' => "Finalizados (Terminado, Publicado o Expuesto)", 'grupo' => "Finalizado exitoso");

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class="texto" style='margin-top: 5px'>
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
			<?php echo(select_group($ESTADOS,$id_estado)); ?>
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
		  <select class="filtro" name="id_tipo" onChange="submitform();">
			<option value="-1">Todas</option>
            <?php echo(select_group($TIPOS,$id_tipo)); ?>
		  </select>
        </td>
	  </tr>
	</table>
	<table cellpadding="1" border="0" cellspacing="2" width="auto">
       <td class="celdaFiltro">
		  Alcance: <br>
		  <select class="filtro" name="id_alcance" onChange="submitform();">
			<option value="">Todos</option>
			<?php echo(select($ALCANCE,$id_alcance)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Formato: <br>
		  <select class="filtro" name="id_formato_public" onChange="submitform();">
			<option value="">Todas</option>
			<?php echo(select($FORMATOS_PUBLIC,$id_formato_public)); ?>
		  </select>
		</td>
		<td class="celdaFiltro">
		  Registrado por: <br>
		  <select class="filtro" name="id_usuario_reg" style="spacing: 0px" onChange="submitform();">
			<option value="t">Todas</option>
			<?php echo(select($USUARIOS_REG,$id_usuario_reg)); ?>
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

		<td class="celdaFiltro">
		  Reportes:<br>
	      <a href="<?php echo("$enlbase_sm=productos_acad_resumen&$enlace_nav")?>" class="boton" id="sgu_fancybox_medium">📐 Resumen de Productividad</a>
		</td>
		<td class="celdaFiltro">
		  Acciones:<br>
	      <a href="<?php echo("$enlbase_sm=productos_acad_crear")?>" class="boton" id="sgu_fancybox_medium">📄 Nuevo Producto Académico</a>
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
	<td class='tituloTabla' width='150'>Autor(es)</td>
	<td class='tituloTabla' width='150'><small>Dimensión<br>Tipo<br>Alcance</small></td>
	<td class='tituloTabla' width='150'>Aignaturas</td>
	<td class='tituloTabla' width='150'>Indicadores</td>
	<td class='tituloTabla'>Documentos</td>
	<td class='tituloTabla' width='100'><small>Registrado por</small></td>
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
		'width'				: 1200,
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
		'width'				: 1000,
		'height'			: 850,
		'maxHeight'			: 850,
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

