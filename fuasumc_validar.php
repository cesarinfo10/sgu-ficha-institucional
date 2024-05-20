<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
include("validar_modulo.php");

$id_fuas    = $_REQUEST['id_fuas'];
$puntaje_socioeconomico = calc_puntaje_socioeconomico($id_fuas);
$puntaje_notas          = calc_puntaje_notas($id_fuas);
$puntaje_sit_financiera = calc_puntaje_sit_financiera($id_fuas);
$puntaje_total = $puntaje_notas + $puntaje_sit_financiera + $puntaje_socioeconomico;

$puntaje_beca_umc = consulta_sql("SELECT porcentaje FROM dae.puntajes_becas_umc WHERE $puntaje_total BETWEEN puntaje_inferior AND puntaje_superior");
$beca_obtenida = $puntaje_beca_umc[0]['porcentaje']*100;

if ($_REQUEST['guardar'] == "Guardar") {
	if (!empty($_REQUEST['observaciones'])) {
		$observaciones = $_REQUEST['observaciones']; 
		
		$tipos_usuario       = tipos_usuario($_SESSION['tipo']);
		$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);
		
		$fecha_comentario = strftime("%A %e-%b-%Y a las %R");
		$_REQUEST['observaciones'] = "El $fecha_comentario, $nombre_real_usuario anotó:\n\n"
		                         . $_REQUEST['observaciones']."\n"
		                         . "<hr>";
	}
	switch($_REQUEST['estado']) {
		case "En preparacion":
			$msje = "Se ha marcado como En Preparación el formulario. Se ha enviado un aviso por email al estudiante.";
			$SQLupd = "UPDATE dae.fuas SET estado='{$_REQUEST['estado']}',observaciones=observaciones||'{$_REQUEST['observaciones']}' WHERE id=$id_fuas";
			break;
		case "Presentado":
			$msje = "Se ha marcado como Presentado el formulario. Se ha enviado un aviso por email al estudiante.";
			$SQLupd = "UPDATE dae.fuas SET estado='{$_REQUEST['estado']}',observaciones=observaciones||'{$_REQUEST['observaciones']}' WHERE id=$id_fuas";
		case "Validado":
			$msje = "Se ha marcado como Validado el formulario y ahora el alumno puede matricularse. Se ha enviado un aviso por email al estudiante.";

			$SQLupd = "UPDATE dae.fuas 
			           SET estado='{$_REQUEST['estado']}',
			               fecha_validacion=now(),
			               observaciones=observaciones||'{$_REQUEST['observaciones']}',
			               puntaje_notas=$puntaje_notas,
			               puntaje_socioeconomico=$puntaje_socioeconomico,
			               puntaje_sit_financiera=$puntaje_sit_financiera,
			               beca_otorgada=$beca_obtenida
			           WHERE id=$id_fuas";
			           
			//$observaciones = "Has obtenido un $beca_obtenida% de Beca UMC";
			break;
		case "Rechazado":
			$fecha = "fecha_rechazo=now()";
			$msje = "Se ha marcado como Rechazado el formulario. Se ha enviado un aviso por email al estudiante.";
			$SQLupd = "UPDATE dae.fuas SET estado='{$_REQUEST['estado']}',$fecha,observaciones=observaciones||'{$_REQUEST['observaciones']}' WHERE id=$id_fuas";
			break;
	}
	if (consulta_dml($SQLupd) == 1) {
		email_estudiante($id_fuas,$observaciones,$_REQUEST['estado']);
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

$SQL_fuas = "SELECT fuas.estado,to_char(fuas.fecha_creacion,'DD-tmMon-YYYY HH24:MI') AS fecha_creacion,
                    to_char(fuas.fecha_presentacion,'DD-tmMon-YYYY HH24:MI') AS fecha_presentacion,
                    to_char(fuas.fecha_validacion,'DD-tmMon-YYYY HH24:MI') AS fecha_validacion,
                    to_char(fuas.fecha_rechazo,'DD-tmMon-YYYY HH24:MI') AS fecha_rechazo,
                    fuas.observaciones,fuas.id_alumno,
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
             WHERE fuas.id=$id_fuas";
$fuas = consulta_sql($SQL_fuas);
extract($fuas[0]);

if ($estado == "En preparación") {
	echo(msje_js("ERROR: Esta postulación aún no ha sido presentada, por lo que no puede ser validada."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}

$contratos = consulta_sql("SELECT id FROM finanzas.contratos WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IS NOT NULL");

if (count($contratos) > 0) {
	echo(msje_js("ERROR: El/la estudiante tiene un contrato emitido para el proceso de Matrículas $ANO_MATRICULA.\\n\\n"
	            ."No se puede validar esta postulación a Beca UMC.\\n\\n"));

	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

$ESTADOS_POSTULACION = consulta_sql("SELECT * FROM vista_fuas_estados");

?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_fuas" value="<?php echo($id_fuas); ?>">

<div style='margin-top: 5px'>
  <input type='submit' name='guardar' value='Guardar' tabindex='99'>
  <input type="button" name="cancelar" value="Cancelar" onclick="history.back();">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de la Postulación</td></tr>
  <tr>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_fuas); ?></td>
    <td class='celdaNombreAttr'>Estado:</td>
    <td class='celdaValorAttr'><select name='estado' class='filtro' required><?php echo(select($ESTADOS_POSTULACION,$estado)); ?></select></td>
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
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Puntajes Calculados</td></tr>
  <tr>
    <td class='celdaNombreAttr'>Socio-económico:</td>
    <td class='celdaValorAttr'><?php echo($puntaje_socioeconomico); ?></td>
    <td class='celdaNombreAttr' rowspan='4'>Beca Obtenida:</td>
    <td class='celdaValorAttr' rowspan='4' style='text-align: center; vertical-align: middle'><b><big><big><?php echo($beca_obtenida); ?>%</big></big></b></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Situación Financiera:</td>
    <td class='celdaValorAttr'><?php echo($puntaje_sit_financiera); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Notas:</td>
    <td class='celdaValorAttr'><?php echo($puntaje_notas); ?></td>    
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Compromiso Cervantino:</td>
    <td class='celdaValorAttr'><?php echo($puntaje_compromiso_cervantino); ?></td>    
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td></tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr' colspan='3'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Apellidos:</td>
    <td class='celdaValorAttr'><?php echo($apellidos); ?></td>
    <td class='celdaNombreAttr'>Nombres:</td>
    <td class='celdaValorAttr'><?php echo($nombres); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Observaciones:</td>
    <td class="celdaValorAttr" colspan="3">
      <?php echo(nl2br($observaciones)); ?>
      <div class="celdaNombreAttr" style='text-align: center'>Añadir Observaciones</div>
      <textarea name="observaciones" value="" cols="40" rows="6" class="general" required></textarea>
    </td>
  </tr>
</table>
</form>

<?php

function email_estudiante($id_fuas,$observacion,$estado) {
	
	$SQL_fuas = "SELECT fuas.email,initcap(a.nombres) AS nombres_al,beca_otorgada FROM dae.fuas LEFT JOIN alumnos AS a ON a.id=fuas.id_alumno WHERE fuas.id=$id_fuas";
	$fuas = consulta_sql($SQL_fuas);
	
	$CR = "\r\n";
	
	if ($observacion <> "") { $observacion .= $CR.$CR; }

	$cabeceras = "From: SGU" . $CR
	           . "Content-Type: text/plain;charset=utf-8" . $CR;
	           
	$asunto = "Postulación a Beca UMC";
	$cuerpo = "Estimad@ {$fuas[0]['nombres_al']}," . $CR.$CR;
	
	switch ($estado) {
		case "Validado":
			//$cuerpo .= "Se ha validado tu Formulario de Postulación a Beca UMC. Ahora debes escribir un correo electrónico a tesoreria@corp.umc.cl para completar tu proceso de matrícula." . $CR.$CR
			$cuerpo .= "Se ha validado tu Formulario de Postulación a Beca UMC. Has obtenido una beca del {$fuas[0]['beca_otorgada']}%. ". $CR.$CR
			        .  "Ahora debes acercarte a Tesorería (noveno piso) para completar tu proceso de matrícula." . $CR.$CR
			        .  $observacion . $CR.$CR;
			break;
		case "Rechazado":
			$cuerpo .= "Se ha Rechazado tu Formulario de Postulación a Beca UMC, y la Dirección de asuntos Estudiantiles ha observado lo siguiente:" . $CR.$CR
			        .  $observacion . $CR.$CR
			        .  "Debes revisar tu formulario, reparar lo que se ha observado y nuevamente presentarlo (usando el botón «Presentar Postulación»)." . $CR.$CR;
			break;
		case "En preparación":
			$cuerpo .= "Se ha cambiado el estado de tu formulario a En preparación y la Dirección de asuntos Estudiantiles ha observado lo siguiente:" . $CR.$CR
			        .  $observacion . $CR.$CR
			        .  "Debes revisar tu formulario, reparar lo que se ha observado y nuevamente presentarlo (usando el botón «Presentar Postulación»)." . $CR.$CR;
			break;
		case "Presentado":
			$cuerpo .= "Se ha cambiado el estado de tu formulario a Presentado y la Dirección de asuntos Estudiantiles ha observado lo siguiente:" . $CR.$CR
			        .  $observacion . $CR.$CR
			        .  "Debes revisar tu formulario, reparar lo que se ha observado y nuevamente presentarlo (usando el botón «Presentar Postulación»)." . $CR.$CR;
			break;
	}
			
	$cuerpo .= "Gracias" . $CR
			.  "Atte.," . $CR.$CR
			.  "Dirección de Asuntos Estudiantiles";
	        
	mail($fuas[0]['email'],$asunto,$cuerpo,$cabeceras);
}

function calc_puntaje_socioeconomico($id_fuas) {
	// calcular puntaje socioeconomico (tabla deciles casen)
	$SQL_ing_gf = "SELECT sum(ing_liq_mensual_prom) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";
	$SQL_cant_gf = "SELECT count(id) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";
	$SQL_ing_percapita = "SELECT round((coalesce(($SQL_ing_gf),0) + fuas.ing_liq_mensual_prom)/(($SQL_cant_gf) + 1),0) AS ingreso_percapita FROM dae.fuas WHERE id=$id_fuas";
	$SQL_puntaje_socioeconomico = "SELECT puntaje FROM dae.puntajes_deciles_casen WHERE ($SQL_ing_percapita) BETWEEN lim_inferior AND lim_superior";
	$puntaje_socioeconomico = consulta_sql($SQL_puntaje_socioeconomico);
	return $puntaje_socioeconomico[0]['puntaje'];
	exit;
}
			
function calc_puntaje_notas($id_fuas) {
	global $ANO_MATRICULA;
	// calcular puntajes notas
	$SQL_cursos_ano = "SELECT id FROM cursos WHERE ano=$ANO_MATRICULA-1 AND semestre IN (1,2)";
	$SQL_alumno = "SELECT id_alumno FROM dae.fuas WHERE id=$id_fuas";			
	$SQL_ca_alumno = "SELECT round(avg(nota_final),1) AS prom FROM cargas_academicas WHERE id_curso IN ($SQL_cursos_ano) AND id_estado IN (1,2) AND id_alumno=($SQL_alumno)";
	$SQL_puntaje_notas = "SELECT puntaje FROM dae.puntajes_notas WHERE ($SQL_ca_alumno) BETWEEN nota_inferior AND nota_superior";
	$puntaje_notas = consulta_sql($SQL_puntaje_notas);
	if (count($puntaje_notas) == 0) {
		$SQL_ca_alumno = "SELECT round(avg(nota_final),1) AS prom FROM cargas_academicas WHERE id_estado IN (1,2) AND id_alumno=($SQL_alumno)";
		$SQL_puntaje_notas = "SELECT puntaje FROM dae.puntajes_notas WHERE ($SQL_ca_alumno) BETWEEN nota_inferior AND nota_superior";
		$puntaje_notas = consulta_sql($SQL_puntaje_notas);
		if (count($puntaje_notas) == 0) { $puntaje_notas[0]['puntaje'] = 0; }
	}		
	return $puntaje_notas[0]['puntaje'];
	exit;
}

function calc_puntaje_sit_financiera($id_fuas) {
	// calcular situacion financiera
	global $ANO_MATRICULA;

	$SQL_alumno = "SELECT rut FROM alumnos WHERE id=(SELECT id_alumno FROM dae.fuas WHERE id=$id_fuas)";
	$SQL_contratos = "SELECT id FROM finanzas.contratos WHERE id IN (SELECT id FROM vista_contratos_rut WHERE rut IN ($SQL_alumno)) AND ano=$ANO_MATRICULA-1 AND estado IS NOT NULL";
	$SQL_cobros = "SELECT cob.id,cob.fecha_venc,p.fecha,CASE WHEN id_glosa IN (20,22) THEN 1 ELSE null END AS repactada,
						  CASE WHEN p.fecha IS NULL THEN null
							   WHEN date_part('day',cob.fecha_venc-p.fecha)<=0 THEN 'si' ELSE null
						  END AS pago_atiempo,
						  CASE WHEN p.fecha IS NULL THEN null
							   WHEN date_part('day',cob.fecha_venc-p.fecha)>0 THEN 'si' ELSE null
						  END AS pago_noatiempo
				   FROM finanzas.pagos_detalle AS pd 
				   LEFT JOIN finanzas.cobros AS cob ON cob.id=pd.id_cobro 
				   LEFT JOIN finanzas.pagos AS p ON p.id=pd.id_pago 
				   WHERE cob.id_contrato in ($SQL_contratos) AND cob.fecha_venc<=(SELECT fecha_presentacion::date FROM dae.fuas WHERE id=$id_fuas)";
	$SQL_sit_finan = "SELECT CASE WHEN count(id)=count(pago_atiempo) THEN 'Siempre al día'
								  WHEN count(id)=count(pago_atiempo)+1 THEN 'Mayoritariamente al día'
								  WHEN count(id)=count(pago_atiempo)+count(pago_noatiempo) THEN 'Al día a la Postulación'
								  WHEN count(repactada)>0 AND count(id)>count(pago_atiempo)+count(pago_noatiempo) THEN 'Moroso con repactaciones'
								  WHEN count(id)>count(pago_atiempo)+count(pago_noatiempo) THEN 'Moroso sin repactaciones'
							 END AS sit_financiera
					  FROM ($SQL_cobros) AS foo";			                  
	$SQL_puntaje_sit_financiera = "SELECT puntaje FROM dae.puntajes_sit_financiera WHERE nombre IN ($SQL_sit_finan)";
	$puntaje_sit_financiera = consulta_sql($SQL_puntaje_sit_financiera);
	return $puntaje_sit_financiera[0]['puntaje'];
	exit;
}

?>
