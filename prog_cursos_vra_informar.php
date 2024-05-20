<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$EMAIL_VRAF = "ccovarrubias@umcervantes.cl";
$EMAIL_VRA  = "hsoto@umcervantes.cl";

if (!is_numeric($_REQUEST['id_prog_curso'])) {
	echo(js("window.location='$enlbase=';"));
	exit;
} else {
	$id_prog_curso = $_REQUEST['id_prog_curso'];
	$confirmacion  = $_REQUEST['confirmacion'];
	$comentarios   = $_REQUEST['comentarios'];
}

$SQL_prog_curso = "SELECT vpc.*,pc.fecha AS fecha_creacion
                   FROM vista_prog_cursos AS vpc
                   LEFT JOIN prog_cursos AS pc ON pc.id=vpc.id
                   WHERE vpc.id=$id_prog_curso";
$prog_curso = consulta_sql($SQL_prog_curso);
if (count($prog_curso) == 0) {
	echo(js("window.location='$enlbase=';"));
	exit;
}
extract($prog_curso[0],EXTR_PREFIX_ALL,"pc");

if ($_REQUEST['informar'] == "Informar" && $confirmacion == "") {
	$confirmacion = md5($id_prog_curso);
	$msje = "Está seguro de informar esta programación de cursos?\\n"
	      . "Considere que informar esta programación de cursos implicará una notificación inmediata "
	      . "al Vicerrector de Administración y Finanzas para someter a su revisión la misma";
	$url_si = "$enlbase=prog_cursos_vra_informar&id_prog_curso=$id_prog_curso&confirmacion=$confirmacion&comentarios=$comentarios&informar=Informar";
	$url_no = "$enlbase=prog_cursos_ver&id_prog_curso=$id_prog_curso";
	echo(confirma_js($msje,$url_si,$url_no));
	exit;
} elseif ($confirmacion == md5($id_prog_curso) && $_REQUEST['informar'] == "Informar") {
	//echo("UDPATE prog_cursos SET comentarios='$comentarios' WHERE id=$id_prog_curso");
	if (consulta_dml("UPDATE prog_cursos SET comentarios='$comentarios' WHERE id=$id_prog_curso") > 0) {
	
		$cabeceras = "From: SGU" . "\r\n"
		           . "Content-Type: text/plain;charset=utf-8" . "\r\n";
		           
		$cuerpo = "La Vicerrectoria Académica ha validado e informa sobre la programación de cursos de la escuela de $pc_escuela del periodo $pc_periodo, para su revisión."
		        . "Esta programación está disponible en el SGU, en el módulo 'Prog. Cursos VRAF'.\r\n\r\n\r\n";
		$cuerpo .= !empty($comentarios) ? "\r\nSe ha añadido un comentario de VRA a esta programación:\r\n$comentarios" : "";
		         
		$asunto = "Prog. Cursos: $pc_escuela visada po VRA ";
		
		mail($EMAIL_VRAF,$asunto,$cuerpo,$cabeceras);
		mail($EMAIL_VRA,$asunto,$cuerpo,$cabeceras);
		
		$emails_escuela = consulta_sql("SELECT email FROM usuarios WHERE id_escuela=$pc_id_escuela and tipo IN (1,2) AND activo;");
		for($x=0;$x<count($emails_escuela);$x++) {
			mail($emails_escuela[$x]['email'],$asunto,$cuerpo,$cabeceras);
		}
		
		echo(msje_js("Se ha informado exitosamente la programación de cursos. Recibirá una notificación por email"));
		echo(js("window.location='$enlbase=prog_cursos_vra';"));
	} 
	exit;
}
		
?>

<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
	Informar a VRAF<?php echo($nombre_modulo); ?>
</div><br>

<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_prog_curso" value="<?php echo($id_prog_curso); ?>">

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr>
    <td align="center" class="textoTabla" style="vertical-align: middle;">
      <input type="submit" name="informar" value="Informar">
      <input type="button" name="cancelar" value="Cancelar">
    </td>
  </tr>
</table><br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class="celdaNombreAttr" colspan="4"><center>Antecedentes de la Prog. de Cursos</center></td>  
  </tr>
  <tr>
    <td class="celdaNombreAttr">Escuela:</td>
    <td class="celdaValorAttr"><?php echo($pc_escuela); ?></td>
    <td class="celdaNombreAttr">Periodo:</td>
    <td class="celdaValorAttr"><?php echo($pc_periodo); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Creador:</td>
    <td class="celdaValorAttr" colspan="3"><?php echo($pc_creador); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Fec. Creación:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha); ?></td>
    <td class="celdaNombreAttr">Fec. Informa:</td>
    <td class="celdaValorAttr"><?php echo($pc_fecha_mod); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">
    <td class="celdaNombreAttr"><center>Cantidad de Cursos</center></td>
    <td class="celdaNombreAttr" colspan="2"><center>Costo Semestral</center></td>  
  </tr>
  <tr>
    <td class="celdaNombreAttr">Propuesto:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos); ?></td>
    <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral,0,',','.')); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Visado VRA:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos_vra); ?></td>    
    <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral_vra,0,',','.')); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Ofertado:</td>
    <td class="celdaValorAttr"><?php echo($pc_cant_cursos_ofertado); ?></td>    
    <td class="celdaValorAttr" colspan="2">$<?php echo(number_format($pc_costo_semestral_ofertado,0,',','.')); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Comentarios:</td>
    <td class="celdaValorAttr" colspan="3"><textarea name="comentarios"><?php echo($pc_cometarios); ?></textarea></td>
  </tr>
</table><br>

</form>
<!-- Fin: <?php echo($modulo); ?> -->
