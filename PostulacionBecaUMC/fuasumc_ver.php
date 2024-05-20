<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}


$id_fuas   = $_REQUEST['id_fuas'];
$id_alumno = $_REQUEST['id_alumno'];

if (!empty($id_fuas) && !empty($id_alumno) && !empty($_REQUEST['elim_id_fg'])) {
	consulta_dml("DELETE FROM dae.fuas_doctos_ing WHERE id_fuas_grupo_familiar={$_REQUEST['elim_id_fg']}");
	consulta_dml("DELETE FROM dae.fuas_grupo_familiar WHERE id={$_REQUEST['elim_id_fg']}");
}

if (!empty($id_fuas) && !empty($id_alumno) && !empty($_REQUEST['elim_id_docto'])) {
	consulta_dml("DELETE FROM dae.fuas_doctos_ing WHERE id={$_REQUEST['elim_id_docto']}");
}

$SQL_fuas = "SELECT fuas.estado,to_char(fuas.fecha_creacion,'DD-tmMon-YYYY HH24:MI') AS fecha_creacion,
                    to_char(fuas.fecha_presentacion,'DD-tmMon-YYYY HH24:MI') AS fecha_presentacion,
                    to_char(fuas.fecha_validacion,'DD-tmMon-YYYY HH24:MI') AS fecha_validacion,
                    to_char(fuas.fecha_rechazo,'DD-tmMon-YYYY HH24:MI') AS fecha_rechazo,
                    rut,nombres,apellidos,c.nombre AS carrera,
	                CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
	                semestre_cohorte||'-'||cohorte AS cohorte,
	                fuas.email,fuas.telefono,fuas.tel_movil,ne.nombre AS nivel_educ,fuas.estado_civil,
	                CASE WHEN fuas.enfermo_cronico THEN 'Si' ELSE 'No' END AS enfermo_cronico,fuas.nombre_enfermedad,
                    fuas.pertenece_pueblo_orig,CASE WHEN fuas.acred_pert_pueblo_orig THEN 'Si' ELSE 'No' END AS acred_pert_pueblo_orig,
                    act.nombre AS cat_ocupacional,CASE WHEN fuas.jefe_hogar THEN 'Si' ELSE 'No' END AS jefe_hogar,fuas.ing_liq_mensual_prom,
                    fuas.domicilio_grupo_fam,com.nombre AS comuna_grupo_fam,reg.nombre AS region_grupo_fam,tenencia_dom_grupo_fam
             FROM dae.fuas
             LEFT JOIN alumnos            AS a   ON a.id=fuas.id_alumno
             LEFT JOIN carreras           AS c   ON c.id=a.carrera_actual
             LEFT JOIN comunas            AS com ON com.id=fuas.comuna_grupo_fam
             LEFT JOIN regiones           AS reg ON reg.id=fuas.region_grupo_fam
             LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=fuas.nivel_educ
             LEFT JOIN dae.actividades    AS act ON act.id=fuas.cat_ocupacional
             WHERE fuas.id=$id_fuas AND a.id=$id_alumno";
$fuas = consulta_sql($SQL_fuas);

if (count($fuas) == 1) {
	$doctos_ing = consulta_sql("SELECT id,tipo_docto FROM dae.fuas_doctos_ing WHERE id_fuas=$id_fuas");
	$HTML_doctos_ing = "";
	for($x=0;$x<count($doctos_ing);$x++) {
		if ($fuas[0]['estado'] == "En preparación" || $fuas[0]['estado'] == "Rechazado") {
			$msje_elim  = "¿Está seguro de eliminar el documento {$doctos_ing[$x]['tipo_docto']}?";
			$boton_elim = "<a href='#' onClick=\"if (confirm('$msje_elim')) { window.location='$enlbase=fuasumc_ver&elim_id_docto={$doctos_ing[$x]['id']}&id_fuas=$id_fuas&id_alumno=$id_alumno'; }\" class='enlaces' style='color: red'><big>✘</big></a>";
		}

		$HTML_doctos_ing .= "$boton_elim <a href='fuasumc_ver_docto_ing.php?id_docto={$doctos_ing[$x]['id']}' class='enlaces' target='_blank'>{$doctos_ing[$x]['tipo_docto']}</a><br>";
	}

	$HTML_gf = "";
	$tot_ing_liq_mensual_prom = 0;
	
	$SQL_grupofam = "SELECT gf.*,to_char(gf.fecha_nacimiento,'DD-tmMon-YYYY') AS fec_nac,
	                        CASE WHEN gf.enfermo_cronico THEN 'Si' ELSE 'No' END AS enfermo_cronico,
	                        CASE WHEN gf.jefe_hogar THEN 'Si' ELSE 'No' END AS jefe_hogar,ne.nombre AS nivel_educ,
	                        act.nombre AS cat_ocupacional
	                 FROM dae.fuas_grupo_familiar AS gf
	                 LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=gf.nivel_educ
	                 LEFT JOIN dae.actividades    AS act ON act.id=gf.cat_ocupacional
	                 WHERE id_fuas=$id_fuas";
	$grupofam = consulta_sql($SQL_grupofam);
	for($x=0;$x<count($grupofam);$x++) {
		
		$HTML_doctos_gf = "";
		$doctos_gf = consulta_sql("SELECT id,tipo_docto FROM dae.fuas_doctos_ing WHERE id_fuas_grupo_familiar={$grupofam[$x]['id']}");
		for ($y=0;$y<count($doctos_gf);$y++) {
			if ($fuas[0]['estado'] == "En preparación" || $fuas[0]['estado'] == "Rechazado") {
				$msje_elim  = "¿Está seguro de eliminar el documento {$doctos_gf[$y]['tipo_docto']}?";
				$boton_elim = "<a href='#' onClick=\"if (confirm('$msje_elim')) { window.location='$enlbase=fuasumc_ver&elim_id_docto={$doctos_gf[$y]['id']}&id_fuas=$id_fuas&id_alumno=$id_alumno'; }\" class='enlaces' style='color: red'><big>✘</big></a>";
			}
			$HTML_doctos_gf .= "$boton_elim <a href='fuasumc_ver_docto_ing.php?id_docto={$doctos_gf[$y]['id']}' class='enlaces' target='_blank'><small>{$doctos_gf[$y]['tipo_docto']}</small></a><br>";
		}
		$tot_ing_liq_mensual_prom += $grupofam[$x]['ing_liq_mensual_prom'];
		$ing_liq_mensual_prom = number_format($grupofam[$x]['ing_liq_mensual_prom'],0,',','.');

		if ($fuas[0]['estado'] == "En preparación" || $fuas[0]['estado'] == "Rechazado") {
			$boton_editar = "<a href='$enlbase_sm=fuasumc_agregar_int_grupofam&id_fuas=$id_fuas&id_gf={$grupofam[$x]['id']}&forma=editar' class='boton' id='sgu_fancybox_small'><small>Editar</small></a>";
			$msje_elim = "¿Está seguro de eliminar a {$grupofam[$x]['rut']} {$grupofam[$x]['apellidos']} {$grupofam[$x]['nombres']} del grupo familiar?";
			$boton_elim   = "<a href='#' onClick=\"if (confirm('$msje_elim')) { window.location='$enlbase=fuasumc_ver&elim_id_fg={$grupofam[$x]['id']}&id_fuas=$id_fuas&id_alumno=$id_alumno'; }\" class='enlaces' style='color: red'><big>✘</big></a>";
			$boton_subir_docto = "<a href='$enlbase_sm=fuasumc_subir_docto_ing&id_gf={$grupofam[$x]['id']}' class='boton' id='sgu_fancybox_small'><small>Subir Doctos Ingresos</small></a>";
		}

		$HTML_gf .= "<tr class='filaTabla'>\n"
		         .  "  <td class='textoTabla' style='text-align: center'>$boton_elim</td>"
		         .  "  <td class='textoTabla'>{$grupofam[$x]['rut']} $boton_editar<br><small>{$grupofam[$x]['apellidos']}<br>{$grupofam[$x]['nombres']}</small></td>"
		         .  "  <td class='textoTabla' width='100'><small>{$grupofam[$x]['parentesco']}</small></td>"
		         .  "  <td class='textoTabla'>{$grupofam[$x]['fec_nac']}</td>"
		         .  "  <td class='textoTabla'>{$grupofam[$x]['jefe_hogar']}</td>"
		         .  "  <td class='textoTabla' width='100'><small>{$grupofam[$x]['nivel_educ']}</small></td>"
		         .  "  <td class='textoTabla' width='100'><small>{$grupofam[$x]['cat_ocupacional']}</small></td>"
		         .  "  <td class='textoTabla'>{$grupofam[$x]['enfermo_cronico']}<small><br>{$grupofam[$x]['nombre_enfermedad']}</small></td>"
		         .  "  <td class='textoTabla' style='text-align: right'>$$ing_liq_mensual_prom</td>"
		         .  "  <td class='textoTabla'>$HTML_doctos_gf $boton_subir_docto</td>"
		         .  "</tr>";
	}
	$HTML_int_grupo_fam = $HTML_gf;	
}

extract($fuas[0]);

$tot_ing_liq_mensual_prom += $ing_liq_mensual_prom;
$ing_liq_mensual_prom_percapita = round($tot_ing_liq_mensual_prom / (1 + count($grupofam)),0);
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Ver Postulación a Beca UMC
</div>
<div class='texto' style='margin-top: 5px'>
<?php if ($fuas[0]['estado'] == "En preparación" || $fuas[0]['estado'] == "Rechazado") { ?>	
  <a href="<?php echo("$enlbase_sm=fuasumc_editar&id_fuas=$id_fuas"); ?>" id='sgu_fancybox_medium' class='boton'>Editar</a>
  <a href="<?php echo("$enlbase_sm=fuasumc_presentar_postulacion&id_fuas=$id_fuas"); ?>" id='sgu_fancybox_medium' class='boton'>Presentar Postulación</a>
<?php } ?>
  <a href="<?php echo("fuasumc_salir.php"); ?>"class='boton'>Salir</a>
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Postulación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_fuas); ?></td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><?php echo($estado); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>F. Creación:</td>
    <td class='celdaValorAttr'><?php echo($fecha_creacion); ?></td>
    <td class='celdaNombreAttr'>F. Presentación:</td>
    <td class='celdaValorAttr'><?php echo($fecha_presentacion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>F. Validación:</td>
    <td class='celdaValorAttr'><?php echo($fecha_validacion); ?></td>
    <td class='celdaNombreAttr'>F. Rechazo:</td>
    <td class='celdaValorAttr'><?php echo($fecha_rechazo); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales y Curriculares del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_alumno); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><?php echo($apellidos); ?></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><?php echo($nombres); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($carrera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Jornada:</td>
    <td class='celdaValorAttr'><?php echo($jornada); ?></td>
    <td class='celdaNombreAttr'>Cohorte:</td>
    <td class='celdaValorAttr'><?php echo($cohorte); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto del Alumno</td></tr>

  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>e-mail:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($email); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Fijo:</u></td>
    <td class='celdaValorAttr'><b>+56</b> <?php echo($telefono); ?></td>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Tel. Móvil:</u></td>
    <td class='celdaValorAttr'><b>+56</b> <?php echo($tel_movil); ?></td>
  </tr>

  <tr><td class='celdaValorAttr' colspan="4"><small>&nbsp;</small></td></tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Caracterización del Alumno</td></tr>

  <tr>
    <td class='celdaNombreAttr'><u>Nivel Educacional:</u></td>
    <td class='celdaValorAttr'><?php echo($nivel_educ); ?></td>
    <td class='celdaNombreAttr'><u>Estado Civil:</u></td>
    <td class='celdaValorAttr'><?php echo($estado_civil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Enfermo Crónico?</u></td>
    <td class='celdaValorAttr'><?php echo($enfermo_cronico); ?></td>
    <td class='celdaNombreAttr'><u>Nombre Enfermedad:</u></td>
    <td class='celdaValorAttr'><?php echo($nombre_enfermedad); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Pertenencia a Pueblo Originario:</u></td>
    <td class='celdaValorAttr' colspan='2'><?php echo($pertenece_pueblo_orig); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan='2'><u>Pertenencia acreditada?:</u></td>
    <td class='celdaValorAttr' colspan='2'><?php echo($acred_pert_pueblo_orig); ?></td>
  </tr>

  <tr><td class='celdaValorAttr' colspan="4"><small>&nbsp;</small></td></tr>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Laborales del Alumno</td></tr>  

  <tr>
    <td class='celdaNombreAttr'><u>Categoría Ocupacional:</u></td>
    <td class='celdaValorAttr'><?php echo($cat_ocupacional); ?></td>
    <td class='celdaNombreAttr'><u>Jefe de Hogar?</u></td>
    <td class='celdaValorAttr'><?php echo($jefe_hogar); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top' colspan='2'><u>Ingreso Líquido Mensual Promedio:</u></td>
    <td class='celdaValorAttr' colspan='2'>$<?php echo(number_format($ing_liq_mensual_prom,0,',','.')); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top' colspan='2'>Documentos de Acreditación de Renta:</td>
    <td class='celdaValorAttr' colspan='2'>
      <?php echo($HTML_doctos_ing); ?>
      <a href="<?php echo("$enlbase_sm=fuasumc_subir_docto_ing&id_fuas=$id_fuas"); ?>" id='sgu_fancybox_small' class='boton'><small>Subir Doctos Ingresos</small></a>
    </td>
  </tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <blockquote><blockquote><blockquote><blockquote>
	  <small>
		Debes justificar tus ingresos con uno de los siguientes documentos de respaldo:<br>
		<br>
	    - Certificado de Cotizaciones de AFP (para trabajadores dependientes o contratados)<br>
	    - Carpeta Tributaria (para trabajadores independientes)<br>
	    - Certificado de Pensiones (para jubilados, pensionados o montepiados)<br>
	    - Certificado de Cesantía (para mayores de 18 años que no tengan empleo)<br>
	    <br>
	    NOTA: En un grupo familiar pueden existir más de un jefe de hogar.
	  </small>
	  </blockquote></blockquote></blockquote></blockquote>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Vivienda del Grupo Familiar</td></tr>
  <tr>
    <td class='celdaNombreAttr' style='vertical-align: top'><u>Dirección:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($domicilio_grupo_fam); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Comuna:</u></td>
    <td class='celdaValorAttr'><?php echo($comuna_grupo_fam); ?></td>
    <td class='celdaNombreAttr'><u>Región:</u></td>
    <td class='celdaValorAttr'><?php echo($region_grupo_fam); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Tenencia:</u></td>
    <td class='celdaValorAttr' colspan='3'><?php echo($tenencia_dom_grupo_fam); ?></td>
  </tr>
</table>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="10">Antecedentes de los Integrantes del Grupo Familiar<br><small>(se excluye al alumno/a)</small></td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'></td>
    <td class='tituloTabla'><small>RUT<br>Apellidos<br>Nombres</small></td>
    <td class='tituloTabla'><small>Parentesco</small></td>
    <td class='tituloTabla'><small>Fec. Nac.</small></td>
    <td class='tituloTabla'><small>Jefe de<br>Hogar?</small></td>
    <td class='tituloTabla'><small>Nivel<br>Educacional</small></td>
    <td class='tituloTabla'><small>Categoría<br>Ocupacional</small></td>
    <td class='tituloTabla'><small>Enf. Crónico<br>Nom. Enfermedad</small></td>
    <td class='tituloTabla'><small>Ingreso Líquido<br>Mensual Promedio</small></td>
    <td class='tituloTabla'><small>Doctos.</small></td>
  </tr>
  <?php echo($HTML_int_grupo_fam); ?>
  <tr>
    <td class='celdaValorAttr' colspan='10' style='text-align: center'>
      <a href="<?php echo("$enlbase_sm=fuasumc_agregar_int_grupofam&id_fuas=$id_fuas&forma=crear"); ?>" id='sgu_fancybox_small' class='boton'><small>+ Añadir integrante</small></a>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="8">Total Ingreso Líquido Mensual Promedio (del Grupo Familiar, incluido el alumno/a):</td>
    <td class='celdaValorAttr' style="text-align: right; ">$<?php echo(number_format($tot_ing_liq_mensual_prom,0,',','.')); ?></td>
    <td class='celdaValorAttr'>&nbsp;</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="8">Ingreso Líquido Mensual Promedio Per Cápita (del Grupo Familiar, incluido el alumno/a):</td>
    <td class='celdaValorAttr' style="text-align: right; ">$<?php echo(number_format($ing_liq_mensual_prom_percapita,0,',','.')); ?></td>
    <td class='celdaValorAttr'>&nbsp;</td>
  </tr>
</table>
<!--
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan="3" style="text-align: center; ">Antecedentes de los Egresos del Grupo Familiar</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style="text-align: center; ">Glosa</td>
    <td class='tituloTabla' style="text-align: center; ">Monto</td>
    <td class='tituloTabla' style="text-align: center; ">Doctos.</td>
  </tr>
  <?php echo($HTML_egresos_grupo_fam); ?>
  <tr>
    <td class='celdaValorAttr' colspan='3' style='text-align: center'>
      <a href="<?php echo("$enlbase_sm=fuasumc_agregar_egreso_grupofam&id_fuas=$id_fuas"); ?>" id='sgu_fancybox_small' class='boton'><small>+ Añadir egreso familiar</small></a>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Total Egresos Familiares:</td>
    <td class='celdaValorAttr' style="text-align: right; ">$<?php echo(number_format($tot_egresos_grupofam,0,',','.')); ?></td>
    <td class='celdaValorAttr'>&nbsp;</td>
  </tr>
</table>
-->
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

