<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_postulante = $_REQUEST['id_postulante'];

if (!is_numeric($id_postulante)) {
	echo(js("location.href='$enlbase=gestion_postulantes123';"));
	exit;
}

$SQL_postulante = "SELECT pap.id,vp.rut,vp.nombre,coalesce(comentarios,'**** No hay observaciones *****') AS comentarios
                   FROM pap
                   LEFT JOIN vista_pap AS vp USING (id)
                   WHERE pap.id=$id_postulante";
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) == 0) {
	echo(js("location.href='$enlbase=gestion_postulantes';"));
	exit;
}

$comentarios = nl2br(str_replace("###","blockquote",$postulante[0]['comentarios']));

if ($_REQUEST['guardar'] == "Guardar") {
	$fecha_hora = strftime("%x %X");
	$nombre_real_usuario = nombre_real_usuario($_SESSION['usuario'],$_SESSION['tipo']);

	$comentarios = "El $fecha_hora, $nombre_real_usuario anotó:\n"
	             . "<###>".$_REQUEST['comentarios']."</###>"
	             . "*****\n\n";
	$SQL_postulante_upd = "UPDATE pap SET comentarios=coalesce(comentarios,'')||'$comentarios' WHERE id=$id_postulante";
	if (consulta_dml($SQL_postulante_upd) > 0) {
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
<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"      value="<?php echo($modulo); ?>">
<input type="hidden" name="id_postulante" value="<?php echo($id_postulante); ?>">

<div style="margin-top: 5px">
  <input type="submit" name="guardar" value="Guardar">
</div>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style="margin-top: 5px">
  <tr>
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales del Postulante</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Comentarios</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan='4'>
      <div class="celdaNombreAttr" style="text-align: left; ">Actuales:</div>
      <div class='celdaValorAttr' style='height: 200px; overflow: auto;'><?php echo($comentarios); ?></div>
      <div class="celdaNombreAttr" style="text-align: left; ">Nueva:</div>
      <div class='celdaValorAttr'>
        <textarea name='comentarios' style='width: 450px; height: 80px'><?php echo($_REQUEST['comentarios']); ?></textarea>
      </div>
    </td>
  </tr>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
