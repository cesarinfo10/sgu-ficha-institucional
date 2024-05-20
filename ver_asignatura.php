<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$cod_asignatura = $_REQUEST['cod_asignatura'];
if ($cod_asignatura == "") {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_asignaturas';</script>");
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id_carrera,codigo AS \"código\",nombre,profesor AS \"profesor titular\",
                  carrera 
           FROM vista_asignaturas 
           WHERE codigo='$cod_asignatura';";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$asignatura = pg_fetch_all($resultado);
	$asig_profes = array();
	$id_carrera = $asignatura[0]['id_carrera'];
	$SQLtxt2 = "SELECT profesor,id_profesor FROM vista_asig_profes WHERE cod_asignatura='$cod_asignatura';";
	$resultado2 = pg_query($bdcon, $SQLtxt2);
	$filas2 = pg_numrows($resultado2);
	if ($filas2 > 0) {
		$asig_profes = pg_fetch_all($resultado2);
	};
	$SQLtxt3 = "SELECT id,ano FROM prog_asig WHERE cod_asignatura='$cod_asignatura' ORDER BY ano;";
	$resultado3 = pg_query($bdcon, $SQLtxt3);
	$filas3 = pg_numrows($resultado3);
	$prog_asig = array();
	if ($filas3 > 0) {
		$prog_asig = pg_fetch_all($resultado3);
	};
};
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($asignatura[0]['nombre']); ?>  
</div><br>
<table class="tabla">
  <tr>
<?php
	if ($_SESSION['tipo'] == 0) {
?>
    <td class="tituloTabla">
      <input type="button" name="editar" value="Editar" onClick="window.location='<?php echo("$enlbase=editar_asignatura&cod_asignatura=$cod_asignatura"); ?>'">
    </td>
    <td class="tituloTabla">
      <input type="button" name="asigna" value="Asignar profesores" onClick="window.location='<?php echo("$enlbase=asignar_profe_asignatura&cod_asignatura=$cod_asignatura"); ?>'">
    </td>      
    <td class="tituloTabla">
      <input type="button" name="crear_prog" value="Crear programa" onClick="window.location='<?php echo("$enlbase=crear_prog_asig&cod_asignatura=$cod_asignatura"); ?>'">
    </td>      
<?php
	};
?>
    <td class="tituloTabla">
<!--      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo("$enlbase=$enlace_volver"); ?>'"> -->
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo($_SESSION['enlace_volver']); ?>';">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=1;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $asignatura[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
		echo("  </tr>\n");
	};
	$profes = "&nbsp;";
	for($x=0;$x<$filas2;$x++) {
		$enlprofe = "$enlbase=ver_usuario&id_usuario=" . $asig_profes[$x]['id_profesor'];
		$profes .= "<a class='enlaces' href='$enlprofe'>" . $asig_profes[$x]['profesor'] . "</a><br>&nbsp;";
	};
	echo("  <tr>\n");
	echo("    <td class='celdaNombreAttr'>Profesores asociados:</td>\n");
	echo("    <td class='celdaValorAttr'>$profes</td>\n");
	echo("  </tr>\n");
	$programas = "&nbsp;";
	for($x=0;$x<$filas3;$x++) {
		$enlprog = "$enlbase=ver_prog_asig&id_prog_asig=" . $prog_asig[$x]['id'];
		$programas .= "A&ntilde;o <a class='enlaces' href='$enlprog'>" . $prog_asig[$x]['ano'] . "</a><br>&nbsp;";
	};
	echo("  <tr>\n");
	echo("    <td class='celdaNombreAttr'>Programas de Estudio:</td>\n");
	echo("    <td class='celdaValorAttr'>$programas</td>\n");
	echo("  </tr>\n");

?>
</table>
<!-- Fin: <?php echo($modulo); ?> -->

