<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if ($_SESSION['tipo'] <> 3) {
	include("validar_modulo.php");
	$id_profesor = $_REQUEST['id_profesor'];
	if (!is_numeric($id_profesor)) {
		echo(js("location.href='principal.php?modulo=gestion_profesores';"));
		exit;
	}
}

$mod_ant = $_SERVER['HTTP_REFERER'];
if ($mod_ant == "") { $mod_ant = "$enlbase=gestion_profesores"; }

$SQL_profesor = "SELECT vp.id,vp.rut,vp.nombre,vp.genero,vp.fec_nac,vp.direccion,vp.comuna,vp.region,
                        vp.telefono,vp.tel_movil,vp.email,vp.email_personal,vp.escuela,vp.nacionalidad,
                        vp.nombre_usuario,vp.grado_academico,to_char(u.grado_acad_fecha,'DD-MM-YYYY') as grado_acad_fecha,
                        vp.grado_acad_universidad,vp.doc_fotocopia_ci,vp.doc_curriculum_vitae,vp.doc_certif_grado_acad,
                        'Profesor(a) '||u.categorizacion||'(a)' as categorizacion,u.grado_acad_nombre,p.nombre AS grado_acad_pais,
                        u.horas_planta,u.horas_planta_docencia,u.horas_plazo_fijo,u.horas_plazo_fijo_docencia,u.horas_honorarios,u.horas_honorarios_docencia,u.funcion,
                        (SELECT count(id) FROM cursos WHERE id_profesor=u.id AND id_fusion IS NULL) AS cant_cursos,
                        CASE WHEN u.activo THEN 'SI' ELSE 'NO' END AS activo,dscn.nombre AS cargo_normalizado_sies,u.estado_carpeta_docto
               FROM vista_profesores AS vp
               LEFT JOIN usuarios AS u USING (id)
               LEFT JOIN pais AS p ON p.localizacion=u.grado_acad_pais
               LEFT JOIN docentes_sies_cargos_normalizados AS dscn ON dscn.id=u.id_cargo_normalizado_sies

               WHERE vp.id=$id_profesor;";
$profesor = consulta_sql($SQL_profesor);
if (count($profesor) == 0) {
	echo(js("location.href='principal.php?modulo=gestion_profesores';"));
	exit;
} else {
	extract($profesor[0]);
	
	$SQL_profesor_pago = "SELECT tipo_deposito,if.nombre AS banco_deposito,tipo_cuenta_deposito,nro_cuenta_deposito,if.nombre AS banco_deposito,fpp.email,
								 to_char(fecha_reg,'DD-tmMon-YYYY HH24:MI') AS fecha_reg,u_reg.nombre_usuario AS usuario_reg,
								 to_char(fecha_mod,'DD-tmMon-YYYY HH24:MI') AS fecha_mod,
								 u_mod.nombre_usuario AS usuario_mod,id_usuario_reg,id_usuario_mod
						  FROM finanzas.profesores_pago AS fpp
						  LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=fpp.cod_banco_deposito
						  LEFT JOIN usuarios AS u_reg ON u_reg.id=fpp.id_usuario_reg
						  LEFT JOIN usuarios AS u_mod ON u_mod.id=fpp.id_usuario_mod
						  WHERE id_profesor=$id_profesor";
	$profesor_pago = consulta_sql($SQL_profesor_pago);
	/*
	if (count($profesor_pago) == 0) {
		echo(msje_js("ATENCIÓN: No se encuentran definidos los Antecedentes de Pago.\\n\\n"
		            ."Para registrar por primera vez pinche en el botón \"Actualizar Antecedentes de Pago\"."));
	}
	*/
	
	$usuario_reg = ($profesor_pago[0]['id_usuario_reg'] == $_SESSION['id_usuario']) ? "Mi" : $profesor_pago[0]['usuario_reg'];
	$usuario_mod = ($profesor_pago[0]['id_usuario_mod'] == $_SESSION['id_usuario']) ? "Mi" : $profesor_pago[0]['usuario_mod'];
	
	$registrado_por = ($profesor_pago[0]['fecha_reg'] <> "") ? $usuario_reg." el ".$profesor_pago[0]['fecha_reg'] : "*** Sin registro ***";
	$modificado_por = ($profesor_pago[0]['fecha_mod'] <> "") ? $usuario_mod." el ".$profesor_pago[0]['fecha_mod'] : "*** Sin modificaciones ***";
	
	if($doc_curriculum_vitae == "Si") {
		$arch_cv = "<a href='ver_profesor_cv.php?id_profesor=$id'>Ver documento</a>";
	} 

	$horas_planta              .= ($horas_planta <> "") ? " semanales" : "";
	$horas_planta_docencia     .= ($horas_planta_docencia <> "") ? " semanales" : "";
	$horas_plazo_fijo          .= ($horas_plazo_fijo <> "") ? " semanales" : "";
	$horas_plazo_fijo_docencia .= ($horas_plazo_fijo_docencia <> "") ? " semanales" : "";
	$horas_honorarios          .= ($horas_honorarios <> "") ? " semanales" : "";
	$horas_honorarios_docencia .= ($horas_honorarios_docencia <> "") ? " semanales" : "";
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<?php if ($_SESSION['tipo'] < 3) { ?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Acciones:<br>
      <?php
			echo("<a href='$enlbase_sm=editar_profesor&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Editar</a> ");
			echo("<a href='$enlbase_sm=editar_profesor_datos_pago&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Editar Antecedentes de Pago</a> ");
			//echo("<a href='$enlbase=profesor_adjuntar_cv&id_profesor=$id_profesor' class='boton'>Adjuntar Curriculum Vitae</a> ");
			echo("<a href='$mod_ant' class='boton'>Volver</a> ");
      ?>
    </td>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Gestión:<br>
      <?php
			echo("<a href='$enlbase_sm=gestion_ev_docente_profes&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Evaluación Docente</a> ");
			//echo("<a href='$enlbase=profesor_asignar_nombre_usuario&id_profesor=$id_profesor' class='boton'>Asignar Nombre de Usuario</a> ");
			echo("<a href='$enlbase_sm=profesor_passwd&id_profesor=$id_profesor' class='boton'>Crear nueva Contraseña</a> ");
      ?>
    </td>
  </tr>
</table>
<?php } ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Género:</td>
    <td class='celdaValorAttr'><?php echo($genero); ?></td>
    <td class='celdaNombreAttr' nowrap>Fecha de nacimiento:</td>
    <td class='celdaValorAttr'><?php echo($fec_nac); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nacionalidad:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($nacionalidad); ?></td>
  </tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Académicos</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Categorización:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($categorizacion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Grado Académico:</td>
    <td class='celdaValorAttr'><?php echo($grado_academico); ?></td>
    <td class='celdaNombreAttr' nowrap>Fecha de obtención:</td>
    <td class='celdaValorAttr'><?php echo($grado_acad_fecha); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre Grado/Título:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($grado_acad_nombre); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Institución otorgante:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($grado_acad_universidad); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>País de la Institución:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($grado_acad_pais); ?></td>
  </tr>

<?php if ($profesor_pago[0]['tipo_deposito'] <> "") { ?>  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Pago</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Forma de pago:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor_pago[0]['tipo_deposito']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Banco:</td>
    <td class='celdaValorAttr'><?php echo($profesor_pago[0]['banco_deposito']); ?></td>
    <td class='celdaNombreAttr'>Tipo de Cuenta:</td>
    <td class='celdaValorAttr'><?php echo($profesor_pago[0]['tipo_cuenta_deposito']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>N° Cuenta:</td>
    <td class='celdaValorAttr'><?php echo($profesor_pago[0]['nro_cuenta_deposito']); ?></td>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr'><?php echo($profesor_pago[0]['email']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Registrado por:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($registrado_por); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Modificado por:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($modificado_por); ?></td>
  </tr>
<?php } ?>
  
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Documentación</td></tr>  
  <tr>
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Curriculum Vitae:</td>
    <td class='celdaValorAttr' width="50%" colspan="2">
      <span class="<?php echo($doc_curriculum_vitae); ?>"><?php echo($doc_curriculum_vitae); ?></span>
      <?php echo($arch_cv); ?>
    </td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Certificado de Grado Académico:</td>
    <td class='celdaValorAttr' width="50%" colspan="2"><span class="<?php echo($doc_certif_grado_acad); ?>"><?php echo($doc_certif_grado_acad); ?></span></td>
  </tr>
  <tr>  
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Estado Carpeta Documentos:</td>
    <td class='celdaValorAttr' width="50%" colspan="2"><?php echo($estado_carpeta_docto); ?></td>
  </tr>
<!--
  <tr>  
    <td class='celdaNombreAttr' style="font-weight: lighter;" colspan="2">Fotocopia C.I.:</td>
    <td class='celdaValorAttr' width="50%" colspan="2"><span class="<?php echo($doc_fotocopia_ci); ?>"><?php echo($doc_fotocopia_ci); ?></span></td>
  </tr>
-->
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Contacto</td></tr>  
  <tr>
    <td class='celdaNombreAttr'>Dirección:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($direccion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Comuna:</td>
    <td class='celdaValorAttr'><?php echo($comuna); ?></td>
    <td class='celdaNombreAttr'>Región:</td>
    <td class='celdaValorAttr' nowrap><?php echo($region); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Telefóno fijo:</td>
    <td class='celdaValorAttr'><?php echo($telefono); ?></td>
    <td class='celdaNombreAttr'>Telefóno móvil:</td>
    <td class='celdaValorAttr'><?php echo($tel_movil); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>e-Mail Personal:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($email_personal); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Administrativos</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Activo:</td>
    <td class='celdaValorAttr'><span class="<?php echo($activo); ?>"><?php echo($activo); ?></span></td>
    <td class='celdaNombreAttr'>Nombre de usuario:</td>
    <td class='celdaValorAttr'><?php echo($nombre_usuario); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Escuela:</td>
    <td class='celdaValorAttr'><?php echo($escuela); ?></td>
    <td class='celdaNombreAttr'>Función:</td>
    <td class='celdaValorAttr'><?php echo($funcion); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Cargo Normalizado (SIES):</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($cargo_normalizado_sies); ?></td>
  </tr>
    
<?php if ($_SESSION['tipo'] == 0) { ?>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="2" style="text-align: center; "></td>
    <td class='tituloTabla' style="text-align: center; ">Horas Contratadas</td>
    <td class='tituloTabla' style="text-align: center; " nowrap>Horas sólo Docencia<br><small>(incluidas en las horas contratadas)</small></td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style='text-align: right'>Planta:</td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_planta); ?></td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_planta_docencia); ?></td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style='text-align: right'>Plazo Fijo:</td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_plazo_fijo); ?></td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_plazo_fijo_docencia); ?></td>
  </tr>
  <tr>
    <td class='tituloTabla' colspan="2" style='text-align: right'>Honorarios:</td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_honorarios); ?></td>
    <td class='celdaValorAttr' align='center'><?php echo($horas_honorarios_docencia); ?></td>
  </tr>
<?php } ?>
</table>
<iframe src='<?php echo("$enlbase_sm=gestion_cursos&texto_buscar=$nombre&buscar=Buscar&titulo=no&filtros=no"); ?>' style='margin-top: 5px' width='100%' height='500' frameborder='0' sandbox='allow-top-navigation'></iframe>

<script type="text/javascript">

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 800,
		'maxHeight'			: 800,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 700,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { },
		'type'				: 'iframe'
	});
});
</script>
