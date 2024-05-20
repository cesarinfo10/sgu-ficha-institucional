<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_prog_asig = $_REQUEST['id_prog_asig'];
if (!is_numeric($id_prog_asig)) {
	echo(js("location.href='$enlbase=portada';"));
	exit;
}

$SQL_prog_asig = "SELECT pa.*,vpa.asignatura 
                  FROM prog_asig AS pa 
                  LEFT JOIN vista_prog_asig AS vpa USING (id) 
                  WHERE pa.id=$id_prog_asig";
$prog_asig     = consulta_sql($SQL_prog_asig);
if (count($prog_asig) == 0) {
	echo(js("location.href='$enlbase=portada';"));
	exit;
}	

$prog_asig[0] = array_map("nl2br",$prog_asig[0]);
extract($prog_asig[0]);
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2(<?php echo($validar_js); ?>);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	Ver Programa de asignatura: <?php echo($prog_asig[0]['asignatura']); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="history.back()">
    </td>
  </tr>
</table>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="650">
  <tr>
    <td class='celdaNombreAttr'>AÑO:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($ano); ?></td>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan='5'><?php echo($cod_asignatura." ".$asignatura); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nº Hrs Ped. semanales:</td>
    <td class='celdaValorAttr' ><?php echo($horas_semanal); ?></td>
    <td class='celdaNombreAttr'>Nº de Semanas semestrales:</td>
    <td class='celdaValorAttr' ><?php echo($nro_semanas_semestrales); ?></td>
    <td class='celdaNombreAttr'>Carga Académica semanal:</td>
    <td class='celdaValorAttr' ><?php echo($carga_acad_sem); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="6">Objetivo General:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($obj_generales); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="6">Objetivos Específicos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($obj_especificos); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="6" style='text-align: left;'>Contenidos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($contenidos); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="6" style='text-align: left;'>Método de instrucción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($met_instruccion); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="6" style='text-align: left;'>Método de Evaluación:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($evaluacion); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="6" style='text-align: left;'>Bibliografía Obligatoria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <b>Autor, Título, Editorial y Año</b><br>
      <?php echo($bib_obligatoria); ?>
    </td>
    <td class='celdaValorAttr' colspan="2" nowrap>
      <b>Otras asignaturas que requerirán este título*</b>
      <?php echo($bib_oblig_otras_asig); ?>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="6" style='text-align: left;'>Bibliografía Complementaria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="4">
      <b>Autor, Título, Editorial y Año</b><br>
      <?php echo($bib_complement); ?>
    </td>
    <td class='celdaValorAttr' colspan="2" nowrap>
      <b>Otras asignaturas que requerirán este título*</b>
      <?php echo($bib_compl_otras_asig); ?>
    </td>
  </tr>
<!--
<?php
	for($x=0;$x<pg_num_fields($resultado);$x++) {
		$nombre_campo = ucfirst(pg_field_name($resultado,$x));
		$valor_campo = $prog_asig[0][pg_field_name($resultado,$x)];
		echo("  <tr>\n");
		echo("    <td class='celdaNombreAttr' style='text-align: left;'>$nombre_campo:</td>\n");
		echo("  </tr>\n");
		echo("  <tr>\n");
		echo("    <td class='celdaValorAttr' >$valor_campo&nbsp;</td>\n");
		echo("  </tr>\n");
	};
?>
-->
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

