<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_malla = $_REQUEST['id_malla'];
if (!is_numeric($id_malla)) {
	echo("<script language='JavaScript1.2'>location.href='principal.php?modulo=gestion_carreras';</script>");
	exit;
};

$bdcon = pg_connect("dbname=regacad" . $authbd);

$SQLtxt = "SELECT id,ano AS \"año\",carrera,niveles,cant_asig_oblig AS \"cantidad asignaturas Obligatorias\",
                  cant_asig_elect AS \"cantidad asignaturas Electivas\",
                  cant_asig_efp AS \"cantidad asignaturas EFP\",comentarios,id_escuela,id_carrera
           FROM vista_mallas WHERE id=$id_malla;";
$resultado = pg_query($bdcon, $SQLtxt);
$filas = pg_numrows($resultado);
if ($filas > 0) {
	$malla = pg_fetch_all($resultado);
	$niveles    = $malla[0]['niveles'];
	$id_escuela = $malla[0]['id_escuela'];
	$id_carrera = $malla[0]['id_carrera'];
	
	$SQLtxt2 = "SELECT * FROM vista_detalle_malla WHERE id_malla=$id_malla;";	
	$resultado2 = pg_query($bdcon, $SQLtxt2);
	$filas2 = pg_numrows($resultado2);
	if ($filas2 > 0) {
		$detalle_malla = pg_fetch_all($resultado2);
	};
		
	$SQLtxt3 = "SELECT id,nombre FROM lineas_tematicas WHERE id_escuela=$id_escuela;";
	$resultado3 = pg_query($bdcon, $SQLtxt3);
	$filas3 = pg_numrows($resultado3);
	if ($filas3 > 0) {
		$lineas_tematicas = pg_fetch_all($resultado3);
	};
	
	$SQLtxt4 = "SELECT caracter,count(id) AS cantidad
	            FROM vista_detalle_malla
	            WHERE id_malla = $id_malla 
	            GROUP BY caracter;";
	$resultado4 = pg_query($bdcon, $SQLtxt4);
	$filas4 = pg_numrows($resultado4);
	if ($filas4 > 0) {
		$estadisticas_malla = pg_fetch_all($resultado4);
	};
	
	$SQLtxt5 = "SELECT *
	            FROM vista_requisitos_malla
	            WHERE id_dm IN (SELECT id 
	                            FROM detalle_mallas
	                            WHERE id_malla=$id_malla);";
	$resultado5 = pg_query($bdcon, $SQLtxt5);
	$filas5 = pg_numrows($resultado5);
	if ($filas5 > 0) {
		$requisitos = pg_fetch_all($resultado5);
	};
};
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($malla[0]['carrera']); ?> - <?php echo($malla[0]['año']); ?>
</div>
<br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="window.href='<?php echo("?modulo=ver_malla&id_malla=$id_malla"); ?>';">
    </td>
  </tr>
</table>
<br>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
<?php
	for($x=0;$x<pg_num_fields($resultado)-2;$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $malla[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>\n");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>\n");
		echo("  </tr>\n");
	};
?>
</table>
<br>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla">
  <tr class='filaTituloTabla'>
    <td rowspan="2" class="tituloTabla" width="80">L&iacute;neas<br>Tem&aacute;ticas</td>
    <td colspan="<?php echo($malla[0]['niveles']); ?>" class='tituloTabla'>Semestres</td>
  </tr>
  <tr class='filaTituloTabla'>
<?php
	for($nivel=1;$nivel<=$niveles;$nivel++) {	
		echo("    <td class='tituloTabla'>$nivel</td>\n");
	};
?>
  </tr>
<?php
	$enlace = "$enlbase=editar_dm_editar_asig&id_dm";
	for($x=0;$x<$filas3;$x++) {
		$id_lt = $lineas_tematicas[$x]['id'];
		$nombre_lt = $lineas_tematicas[$x]['nombre'];
		$tieneAsig = false;
		$filaTabla = "";
		$filaTabla  = "  <tr>\n";
		$filaTabla .= "    <td class='tituloTabla' width='80'>$nombre_lt</td>\n";
		for($nivel=1;$nivel<=$niveles;$nivel++) {
			$title = "Insertar una Asignatura";
			$enlace1 = "$enlbase=editar_dm_insertar_asig&id_malla=$id_malla&nivel=$nivel&linea_tematica=$id_lt";
			$asignatura = "<a class='enlaces' href='$enlace1' title='$title'>Insertar Asignatura</a>";
//			$asignatura = "";
			for($y=0;$y<$filas2;$y++) {
				if ($nivel==intval($detalle_malla[$y]['nivel']) && $id_lt==intval($detalle_malla[$y]['id_linea_tematica'])) {
					$id_prog_asig      = $detalle_malla[$y]['id_prog_asig'];
					$cod_asignatura    = trim($detalle_malla[$y]['cod_asignatura']);
					$ano_asignatura    = $detalle_malla[$y]['ano'];
					$nombre_asignatura = $detalle_malla[$y]['asignatura'];
					$id_dm             = $detalle_malla[$y]['id'];
					$caracter          = $detalle_malla[$y]['caracter'];

					//$requisitos_asig = "Pre-req:";
					$requisitos_asig = "";
					for($i=0;$i<$filas5;$i++) {
						if ($requisitos[$i]['id_dm'] == $id_dm && $requisitos[$i]['tipo'] == 1) {
							//$requisitos_asig .= $requisitos[$i]['asignatura_req'] . ",";
							$requisitos_asig .= $requisitos[$i]['asignatura_req'] . "<br>";
						};
					};
					//$requisitos_asig = substr($requisitos_asig,0,strlen($requisitos_asig) - 1);
					//$requisitos_asig .= " Pre-req de:";
					//for($i=0;$i<$filas5;$i++) {
					//	if ($requisitos[$i]['id_dm'] == $id_dm && $requisitos[$i]['tipo'] == 2) {
					//		$requisitos_asig .= $requisitos[$i]['asignatura_req'] . ",";
					//	}
					//}
					//$requisitos_asig = substr($requisitos_asig,0,strlen($requisitos_asig) - 1);
					
					//$title = "$cod_asignatura $ano_asignatura $caracter $requisitos_asig";
					$title = "header=[Propiedades] fade=[on]"
					       . "body=[Año Programa: $ano_asignatura<br>"
					       . "      Carácter: $caracter<br>"
 					       . "      <b>Pre-requisitos:</b><br>$requisitos_asig]";

					$cont_asig = "<div class='ramoMalla' title='$title'>"
					           . "  <a class='enlaces' href='$enlace=$id_dm'>"
					           . "    <b>$cod_asignatura</b><br>$nombre_asignatura"
					           . "  </a>"
					           . "</div>";
					if ($asignatura <> "") {
						$cont_asig .= "<br>$asignatura";
					};
					$asignatura = $cont_asig;
					$tieneAsig = true;
				};
			};
			$filaTabla .= "    <td valign='top' class='celdaramoMalla'>$asignatura</td>\n";
		};
		$filaTabla .= "  </tr>\n";
		echo($filaTabla);
	};
?>
</table>
<div class="texto">
  Actualmente<br> 
	<?php
		for($z=0;$z<$filas4;$z++) {
			echo($estadisticas_malla[$z]['caracter'] . ":");
			echo($estadisticas_malla[$z]['cantidad'] . "<br>");
		};
  ?>
</div>
<!-- Fin: <?php echo($modulo); ?> -->

