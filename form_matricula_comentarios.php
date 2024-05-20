<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_contrato = $_REQUEST['id_contrato'];

if (!is_numeric($id_contrato)) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

$SQL_contrato = "SELECT c.*,coalesce(va.id,vp.id) AS id,coalesce(va.rut,vp.rut) AS rut,coalesce(va.nombre,vp.nombre) AS nombre,                        
                        to_char(c.fecha,'DD-MM-YYYY') AS fecha,coalesce(c.comentarios,'**** No hay observaciones *****') AS comentarios
                 FROM finanzas.contratos AS c
                 LEFT JOIN alumnos       AS al  ON al.id=c.id_alumno
                 LEFT JOIN vista_alumnos AS va  ON va.id=c.id_alumno
                 LEFT JOIN vista_pap     AS vp  ON vp.id=c.id_pap
                 LEFT JOIN pap                  ON pap.id=c.id_pap
                 WHERE c.id=$id_contrato";
$contrato     = consulta_sql($SQL_contrato);
if (count($contrato) == 0) {
	echo(js("location.href='$enlbase=gestion_contratos';"));
	exit;
}

$comentarios = nl2br(str_replace("###","blockquote",$contrato[0]['comentarios']));

if ($_REQUEST['guardar'] == "Guardar") {
	$fecha_hora = strftime("%x %X");
	$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);

	$comentarios = "El $fecha_hora, $nombre_real_usuario anotó:\n"
	             . "<###>".$_REQUEST['comentarios']."</###>"
	             . "*****\n\n";
	$SQL_contrato_upd = "UPDATE finanzas.contratos SET comentarios=coalesce(comentarios,'')||'$comentarios' WHERE id=$id_contrato";
	if (consulta_dml($SQL_contrato_upd) > 0) {
		echo(msje_js("Se ha guardado exitósamente el comentario."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
}

?>
<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="principal_sm.php" method="post">
<input type="hidden" name="modulo"      value="<?php echo($modulo); ?>">
<input type="hidden" name="id_contrato" value="<?php echo($id_contrato); ?>">

<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Generales del Contrato</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Número:</td>
    <td class='celdaValorAttr'><?php echo($id_contrato); ?></td>
    <td class='celdaNombreAttr'>Fecha:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['fecha']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['tipo']); ?></td>
    <td class='celdaNombreAttr'>Periodo:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['semestre'].'-'.$contrato[0]['ano']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Alumno</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($contrato[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($contrato[0]['nombre']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Observaciones</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan='4'>
      <div class="celdaNombreAttr" style="text-align: left; ">Actuales:</div>
      <div class='celdaValorAttr' style='height: 80px; overflow: auto;'><?php echo($comentarios); ?></div>
      <div class="celdaNombreAttr" style="text-align: left; ">Nueva:</div>
      <div class='celdaValorAttr'>
        <textarea name='comentarios' style='width: 450px; height: 80px'><?php echo($_REQUEST['comentarios']); ?></textarea>
      </div>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
