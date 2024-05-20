<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT * FROM vista_escuelas;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
$escuelas = pg_fetch_all($resultado);
$cant_campos = pg_num_fields($resultado);

$SQLtxt2 = "SELECT id,nombre,coordinador,escuela FROM vista_carreras;";
$resultado2 = pg_query($bdcon, $SQLtxt2);
$filas2 = pg_numrows($resultado2);
$carreras = pg_fetch_all($resultado2);
?>

<!-- Inicio: Gestion de Escuelas -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div><br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
	<?php
		for ($y=1;$y<$cant_campos - 1;$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado,$y));
			echo("<td class='tituloTabla'>$nombre_campo</td>");
		};
		echo("<td class='tituloTabla'>Carreras</td>");
	?>  
  </tr>
<?php
	for ($x=0; $x<$filas; $x++) {
		$enl = "$enlbase=ver_escuela&id_escuela=" . $escuelas[$x]['id'];
		$enlace = "<a class='enlitem' href='$enl'>";
		echo("  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n");
		for ($z=1;$z<$cant_campos - 1;$z++) {
			echo("    <td class='textoTabla'>&nbsp;$enlace" . $escuelas[$x][pg_field_name($resultado,$z)] . "</a></td>\n");
		};
		echo("    <td class='textoTabla'>");
		for ($y=0;$y<$filas2;$y++) {
			if ($carreras[$y]['escuela'] == $escuelas[$x]['nombre']) {
				$enl = "$enlbase=ver_carrera&id_carrera=" . $carreras[$y]['id'];
				$enlace = "<a class='enlitem' href='$enl'>";
				echo($enlace . $carreras[$y]['nombre'] . "<br>");
				echo("&nbsp;(" . $carreras[$y]['coordinador'] . ")</a><br>");
			};
		};
		echo("  &nbsp;</td>\n");
		echo("</tr>\n");
		$total_carreras += $escuelas[$x]['cant_carreras'];
	};
?>
  <tr>
    <td class="celdaNombreAttr" colspan="2">Cantidad de carreras:</td>
    <td class='celdaNombreAttr'><?php echo($total_carreras); ?></td>
  </tr>
</table>
<!-- Fin: Gestion de Escuelas -->

