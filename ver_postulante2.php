<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$aSQLtxt[1][1]="SELECT id AS \"Código Interno\",nombre,rut, genero AS \"género\",
                       fec_nac AS \"fecha de nacimiento\",nacionalidad,
                       coalesce(pasaporte,'**No corresponde**') AS pasaporte,
                       direccion AS \"dirección\",comuna,region AS \"región\",
                       email,telefono AS \"teléfono fijo\",tel_movil AS \"teléfono móvil\",
                       admision AS \"Admisión\",carrera1 AS \"carrera 1\",
                       carrera2 AS \"carrera 2\",carrera3 AS \"carrera 3\"";
$aSQLtxt[1][2]="Datos<br>personales";
$aSQLtxt[2][1]="SELECT id AS \"Código Interno\",nombre,colegio,ano_egreso_col AS \"año egreso Enseñanza Media\",
                       ano_psu AS \"año PSU\",puntaje_psu AS \"puntaje PSU\",ies AS \"Estudios anteriores en\",
                       carr_ies_pro AS \"En la carrera de\",prom_nt_ies_pro AS \"promedio de notas\",
                       CASE conc_nt_ies_pro WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Concentración de notas?\",
                       CASE prog_as_ies_pro WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente programas de asignatura?\"";
$aSQLtxt[2][2]="Antecedentes<br>escolares/universitarios";
$aSQLtxt[3][1]="SELECT id AS \"Código Interno\",nombre,
                       CASE cert_nacimiento WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Certificado de Nacimiento?\",
                       CASE copia_ced_iden WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente fotocopia de C.I.?\",
                       CASE conc_notas_em  WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Concentración de Notas EM?\",
                       CASE licencia_em WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Licencia de Enseñanza Media?\",
                       CASE boletin_psu WHEN true THEN 'Sí' ELSE 'No' END AS \"está presente Boletín de resultados PSU?\"";

$aSQLtxt[3][2]="Datos de<br>control interno";

$id_pap = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

$ficha = $_REQUEST['ficha'];
if ($ficha == "") {
	$ficha = 1;
}
        
$SQL_postulante = $aSQLtxt[$ficha][1] . " FROM vista_pap WHERE id=$id_pap;";
$postulante     = consulta_sql($SQL_postulante);

if (count($postulante) > 0) {
	$SQL_postulante_pa_ext = "SELECT id,inst_edsup,asignatura,duracion,semestre,ano,nota_final
	                          FROM vista_convalidaciones
	                          WHERE id_pap=$id_pap ORDER BY ano,id;";
	$postulante_pa_ext     = consulta_sql($SQL_postulante_pa_ext);	                          
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($postulante[0]['nombre']); ?>
</div><br>
<table class="tabla">
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="editar_postulante">
<input type="hidden" name="id_escuela" value="<?php echo($id_postulante); ?>">
  <tr>
<?php
	if ($_SESSION['tipo'] == 4 || $_SESSION['tipo'] == 0) {
?>
    <td class="tituloTabla">
      <input type="button" name="editar" value="Editar" onClick="window.location='<?php echo("$enlbase=editar_postulante&id_pap=$id_pap"); ?>'">
    </td>
<?php
	};
?>
    <td class="tituloTabla">
      <input type="button" name="registrar_pa_externo" value="Registrar Programas Externos (Convalidables)" onClick="window.location='<?php echo("$enlbase=registrar_prog_asig_externo&id_pap=$id_pap"); ?>'">
    </td>    
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="history.back()">
    </td>
  </tr>
</form>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="2" cellpadding="4" class="tabla">
  <tr>
		<?php
			$enlace_ficha = "?modulo=$modulo&id_pap=$id_pap&ficha";			
			for($y=1;$y<=3;$y++) {
				$texto_ficha = $aSQLtxt[$y][2];
				if ($y <> $ficha) {
					echo("<td class='texto'><center>");
					echo("<a href='$enlace_ficha=$y'>$texto_ficha</a>");
				} else {
					echo("<td class='celdaNombreAttr'><center>");
					echo("<b>$texto_ficha</b>");
				};					
				echo("</center></td>\n");
			};
		?>
  </tr>
  <tr>
    <td colspan="3">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="100%">
<tr>
  <td class='celdaNombreAttr'>RUT:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
  <td class='celdaNombreAttr'>ID Postulante:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Nombre:</td>
  <td class='celdaValorAttr' colspan="3"><?php echo($postulante[0]['nombre']); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Género:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['genero']); ?></td>
  <td class='celdaNombreAttr'>Fecha de Nacimiento:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['fec_nac']); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Nacionalidad:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['nacionalidad']); ?></td>
  <td class='celdaNombreAttr'>Pasaporte:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['pasaporte']); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Dirección:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['rut']); ?></td>
  <td class='celdaNombreAttr'>ID Postulante:</td>
  <td class='celdaValorAttr'><?php echo($postulante[0]['id']); ?></td>
</tr>

<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $postulante[0][pg_field_name($resultado,$x)];
		echo("  <tr>");
		echo("    <td class='celdaNombreAttr'>$nombre_campo:</td>");
		echo("    <td class='celdaValorAttr'>&nbsp;$valor_campo</td>");
		echo("  </tr>");
	};
?>
</table>
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class="tituloTabla" colspan="9">Programas de Asignaturas externos registrados</td>
  </tr>
  <tr class='filaTituloTabla'>
	<?php
		for ($y=1;$y<pg_num_fields($resultado2);$y++) {
			$nombre_campo = ucfirst(pg_field_name($resultado2,$y));
			echo("<td class='tituloTabla'>$nombre_campo</td>\n");
		};
	?>
  </tr>
<?php
if ($filas2 > 0) {
	for ($x=0; $x<$filas2; $x++) {
		echo("  <tr class='filaTabla'\n");
		for ($z=1;$z<pg_num_fields($resultado2);$z++) {
			$nombre_campo = pg_field_name($resultado2,$z);
			$valor_campo = $convalidaciones[$x][$nombre_campo];
			echo("    <td class='textoTabla'>&nbsp;$valor_campo</a></td>\n");
		};
	};
} else {
	echo("  <tr>");
	echo("    <td colspan='6' class='textoTabla'>&nbsp;<center>No registra planes externos</center><br></td>");
	echo("  </tr>");
};
?>
</table>

<!-- Fin: <?php echo($modulo); ?> -->

