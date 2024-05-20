<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_prod_acad  = $_REQUEST['id_prod_acad'];
$cancelar      = $_REQUEST['cancelar'];
$reactivar     = $_REQUEST['reactivar'];
$finalizar     = $_REQUEST['finalizar'];
$archivar      = $_REQUEST['archivar'];
$token         = $_REQUEST['token'];
$conf_cancelar = $_REQUEST['conf_cancelar'];

if ($conf_cancelar == md5("Si$token")) { 
    consulta_dml("UPDATE vcm.actividades SET estado='Cancelada' WHERE id=$id_actividad");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Cancela actividad',default)");
    echo(js("parent.jQuery.fancybox.close();"));
    exit;
}

if ($reactivar == "Si" && $token == md5($id_actividad)) {
    consulta_dml("UPDATE vcm.actividades SET estado='Programada' WHERE id=$id_actividad");
    consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Reactiva actividad',default)");
}

if ($finalizar == "Si" && $token == md5($id_actividad) && permiso_ejecutar($_SESSION["id_usuario"],'productos_acad_visar_termino')) {
	$participacion = consulta_sql("SELECT id FROM vcm.participacion_act WHERE id_actividad=$id_actividad AND (cant_personas IS NOT NULL OR cant_personas_virtuales IS NOT NULL)");
	$doctos = consulta_sql("SELECT id FROM vcm.documentos_act WHERE id_actividad=$id_actividad");
	$indicadores = consulta_sql("SELECT id FROM vcm.indicadores_act WHERE id_actividad=$id_actividad");

	if (count($participacion) == 0 || count($doctos) == 0 || count($indicadores) == 0) {
		echo(msje_js("ERROR: No es posible Finalizar esta actividad debido a que no tiene registrados la participación, los documentos o los indicadores.\\n\\n"
		            ."Debe registrar esta información antes de dar por Finalizada una actividad."));
		consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Intenta finalizar una actividad que está incompleta',default)");	
	} else {
		consulta_dml("UPDATE vcm.actividades SET estado='Finalizada' WHERE id=$id_actividad");
		consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Finaliza actividad',default)");	
		echo(msje_js("Se ha registrado la Finalización de la actividad satisfactoriamente."));
	}	
}

if ($archivar == "Si" && $token == md5($id_actividad) && permiso_ejecutar($_SESSION["id_usuario"],'productos_acad_visar_termino')) {
	consulta_dml("UPDATE vcm.actividades SET estado='Archivada' WHERE id=$id_actividad");
	consulta_dml("INSERT INTO vcm.actividades_audit VALUES ($id_actividad,{$_SESSION['id_usuario']},'Archiva actividad',default)");	
	echo(msje_js("Se ha archivado esta actividad."));
}

$SQL_ind = "SELECT char_comma_sum(it.nombre||': '||coalesce(valor::text||CASE WHEN porcentaje THEN '%' ELSE '' END,'*'))
            FROM dpii.indicadores_prod_acad AS ind 
            LEFT JOIN dpii.indicadores_tipo_prod_acad AS it ON it.id=ind.id_tipo 
            WHERE id_prod_acad=vpa.id";

$SQL_doctos = "SELECT char_comma_sum(doc_tipo.nombre||':'||doctos.id::text) 
               FROM dpii.documentos_prod_acad AS doctos
			   LEFT JOIN dpii.doctos_tipo_prod_acad AS doc_tipo ON doc_tipo.id=doctos.id_tipo
               WHERE doctos.id_prod_acad=vpa.id";

$SQL_autores = "SELECT char_comma_sum(aut.apellidos||' '||aut.nombres||' '||tipo) AS autores
                FROM dpii.autores_prod AS aut
				WHERE id_prod_acad=vpa.id";

$SQL_asig = "SELECT char_comma_sum(asig.codigo||' '||asig.nombre) AS asignaturas
             FROM dpii.prod_acad_asignaturas AS paa
			 LEFT JOIN prog_asig AS pa ON pa.id=paa.id_prog_asig
			 LEFT JOIN asignaturas AS asig ON asig.codigo=pa.cod_asignatura
			 WHERE paa.id_prod_acad=vpa.id";

$SQL_prod = "SELECT *,
                    to_char(fecha_inicio,'DD-tmMonth-YYYY') AS fec_inicio,
                    to_char(fecha_termino,'DD-tmMonth-YYYY') AS fec_termino,
                    dimension,
					($SQL_autores) AS autores,($SQL_asig) AS asignaturas,
                    ($SQL_ind) AS indicadores,($SQL_doctos) AS doctos
            FROM vista_dpii_productos_acad AS vpa
            WHERE id=$id_prod_acad";
$prod = consulta_sql($SQL_prod);

if (count($prod) == 1) {
    if ($cancelar == "Si" && $token == md5($id_actividad)) {
        $token2 = md5("Si$token");
        $enl_si = "$enlbase_sm=$modulo&id_prod_acad=$id_prod_acad&conf_cancelar=$token2&token=$token";
        $enl_no = "#";
        echo(confirma_js("¿Está seguro de establecer la Cancelación de esta Actividad ({$act[0]['nombre']})?",$enl_si,$enl_no));
    }

    $token = md5($id_prod_acad);
    $_REQUEST = array_merge($prod[0],$_REQUEST);
	$estado = "<span class='".str_replace(" ","",$_REQUEST['estado'])."'>&nbsp;{$_REQUEST['estado']}&nbsp;</span>";
    $_REQUEST['indicadores'] = str_replace(",","<br>",$_REQUEST['indicadores']);
    $_REQUEST['autores']     = str_replace(",","<br>",$_REQUEST['autores']);
    $_REQUEST['asignaturas'] = str_replace(",","<br>",$_REQUEST['asignaturas']);
    $_REQUEST['indicadores'] = str_replace(",","<br>",$_REQUEST['indicadores']);
	$_REQUEST['revista_enlace'] = "<a class='botoncito' target='_blank' href='{$_REQUEST['revista_enlace']}'>Ver revista</a>";
	$_REQUEST['libro_enlace']   = "<a class='botoncito' target='_blank' href='{$_REQUEST['libro_enlace']}'>Ver libro</a>";
    if ($_REQUEST['autores'] == "") { $_REQUEST['autores'] = "** Sin autor(es) ingresado(s) **"; }
    if ($_REQUEST['asignaturas'] == "") { $_REQUEST['asignaturas'] = "** Sin asignatura(s) ingresada(s) **"; }

	if ($prod[0]['doctos'] <> "") {
		$documentos = explode(",",$act[0]['doctos']);
		$HTML_doctos = "";
		for($x=0;$x<count($documentos);$x++) { 
		  $docto = explode(":",$documentos[$x]);
		  $HTML_doctos .= "<a href='productos_acad_doctos_descargar.php?id={$docto[1]}' class='enlaces' target='_blank'>📥 {$docto[0]} ({$docto[2]})</a><br>";
		}	
	} else { 
		$HTML_doctos = "<br><center>** Sin documentos **</center><br><br>"; 
  	}

	if ($_REQUEST['indicadores'] == "") { $_REQUEST['indicadores'] = "<br><center>** Sin indicadores **</center><br><br>"; }

}

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div class='texto' style="margin-top: 5px">
<?php switch ($_REQUEST['estado']) { case "En desarrollo": case "Aceptado": case "En revisión Editorial": case "En revisión de Pares": ?>
  <a href="<?php echo("$enlbase_sm=productos_acad_editar&id_prod_acad=$id_prod_acad")?>" class="boton">📝 Editar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_ver&id_prod_acad=$id_prod_acad&finalizar=Si&token=$token")?>" class="boton">🏁 Finalizar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_ver&id_prod_acad=$id_prod_acad&archivar=Si&token=$token")?>" class="boton">📦 Archivar</a> &nbsp;
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
  <a href="<?php echo("$enlbase_sm=productos_acad_autores&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">🪪 Autor(es)</a>
  <a href="<?php echo("$enlbase_sm=productos_acad_asignaturas&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">📚 Asignatura(s)</a>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_doctos&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">📎 Documentos</a>
<?php break; case "Archivada": ?>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_ver&id_prod_acad=$id_prod_acad&reactivar=Si&token=$token")?>" class="boton">✅ Reactivar</a> &nbsp;
<?php break; default: ?> 
<?php  if (perm_ejec_modulo($_SESSION["id_usuario"],'productos_acad_visar_termino')) { ?>
  <a href="<?php echo("$enlbase_sm=productos_acad_editar&id_prod_acad=$id_prod_acad")?>" class="boton">📝 Editar</a> &nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_autores&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">🪪 Autor(es)</a>
  <a href="<?php echo("$enlbase_sm=productos_acad_asignaturas&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">📚 Asignatura(s)</a>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo("$enlbase_sm=productos_acad_doctos&id_prod_acad=$id_prod_acad")?>" class="boton" id="sgu_fancybox_small">📎 Documentos</a>
<?php	} ?>
<?php } ?>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Producto Académico</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Dimensión / Tipo:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['dimension']." / ".$_REQUEST['nombre_tipo_prod']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Palabras Claves:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['palabras_clave']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Autor(es):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['autores']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Año / Estado:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['ano']." / ".$estado); ?></td>
    <td class='celdaNombreAttr'>Alcance:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['alcance']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Formato de Publicación:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['public_formato']); ?></td>
    <td class='celdaNombreAttr'>Medio de Publicación:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['medio_public']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Fecha de Inicio:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_inicio']); ?></td>
    <td class='celdaNombreAttr'>Fecha de Término:</td>
    <td class='celdaValorAttr'><?php echo($_REQUEST['fec_termino']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura(s):</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['asignaturas']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Registrado por:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['nombre_reg']); ?></td>
  </tr>

<?php if ($_REQUEST['dimension'] == "Revistas") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Revista</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_nombre">Nombre Revista:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['revista_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_numero">Número:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_numero']); ?></td>
    <td class='celdaNombreAttr'><label for="revista_editorial">Editorial:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_editorial']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_ciudad">Ciudad:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_ciudad']); ?></td>
    <td class='celdaNombreAttr'><label for="revista_pais">País:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_pais']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="revista_facto_impacto">Factor de Impacto:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_facto_impacto']); ?></td>
    <td class='celdaNombreAttr'><label for="revista_enlace">Enlace:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['revista_enlace']); ?></td>
  </tr>
<?php } ?>

<?php if ($_REQUEST['dimension'] == "Libros") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Libro</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_nombre">Nombre del Libro:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['libro_nombre']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_editorial">Editorial:</label></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['libro_editorial']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_ciudad">Ciudad:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['libro_ciudad']); ?></td>
    <td class='celdaNombreAttr'><label for="libro_pais">País:</label></td>
    <td class='celdaValorAttr' ><?php echo($_REQUEST['libro_pais']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="libro_enlace">Enlace:</label></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($_REQUEST['libro_enlace']); ?></td>
  </tr>
<?php	} ?>

<?php	if ($_REQUEST['dimension'] == "Informes") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Informe</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="informe_organismo">Organismo:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['informe_organismo']); ?></td>
  </tr>

<?php 	} ?>

<?php 	if ($_REQUEST['dimension'] == "Proyectos") { ?>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes del Proyecto</td></tr>
  <tr>
    <td class='celdaNombreAttr'><label for="proyecto_organismo">Organismo/Fondo:</label></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['informe_organismo']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><label for="proyecto_id_invest_princ">Investigador principal:</label></td>
    <td class='celdaValorAttr' colspan="3">	<?php echo($_REQUEST['proyecto_invest_princ']); ?></td>
  </tr>
<?php 	} ?>

<?php if ($_REQUEST['dimension'] == "Ponencias") { ?>

<tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Ponencia</td></tr>
<tr>
  <td class='celdaNombreAttr'><label for="libro_nombre">Nombre del Congreso/Seminario:</label></td>
  <td class='celdaValorAttr' colspan="3"><?php echo($_REQUEST['ponencia_nombre_congreso']); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'><label for="libro_ciudad">Ciudad</label>/<label for="libro_pais">País:</label></td>
  <td class='celdaValorAttr' ><?php echo($_REQUEST['ponencia_ciudad']); ?>/<?php echo($_REQUEST['ponencia_pais']); ?></td>
  <td class='celdaNombreAttr'>Modalidad:</td>
  <td class='celdaValorAttr' ><?php echo($_REQUEST['ponencia_modalidad']); ?></td>
</tr>
<?php	} ?>

  <tr>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Indicadores</td>
    <td class='celdaNombreAttr' colspan="2" style="text-align: center; ">Documentos</td>
  </tr>
  
  <tr>
    <td class='celdaValorAttr' colspan="2"><?php echo($_REQUEST['indicadores']); ?></td>
    <td class='celdaValorAttr' colspan="2"><?php echo($HTML_doctos); ?></td>
  </tr>


</table>


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
		'height'			: 500,
		'maxHeight'			: 500,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>