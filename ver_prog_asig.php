<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_malla     = $_REQUEST['id_malla'];
$id_prog_asig = $_REQUEST['id_prog_asig'];
if (!is_numeric($id_prog_asig)) {
	echo(js("location.href='$enlbase=gestion_asignaturas';"));
	exit;
}


$SQL_mallas_prog_asig = "SELECT char_comma_sum(c.alias||'-'||m.ano) AS mallas
                         FROM mallas AS m 
                         LEFT JOIN carreras AS c on c.id=m.id_carrera 
                         WHERE m.id IN (SELECT id_malla FROM detalle_mallas WHERE id_prog_asig=pa.id)";

$SQL_prog_asig = "SELECT pa.*,vpa.asignatura,($SQL_mallas_prog_asig) AS mallas 
                  FROM prog_asig AS pa 
                  LEFT JOIN vista_prog_asig AS vpa USING (id) 
                  WHERE pa.id=$id_prog_asig";
$prog_asig     = consulta_sql($SQL_prog_asig);
if (count($prog_asig) == 0) {
	echo(js("location.href='$enlbase=gestion_asignaturas';"));
	exit;
}	

$prog_asig[0] = array_map("nl2br",$prog_asig[0]);
extract($prog_asig[0]);

//if ($horas_autonomas_semanales > 0) { $horas_autonomas_semanales .= " semanales"; }
//if ($horas_semanal > 0) { $horas_semanal .= " semanales"; }

$av = consulta_sql("SELECT id,referencia,visitas FROM prog_asig_audiovisuales WHERE id_prog_asig=$id_prog_asig");
$HTML_av = "";
if (count($av) > 0) {
	$HTML_av = "<tr><td class='celdaNombreAttr' colspan='4' style='text-align: left;'>Audiovisuales:</td></tr>"
	         . "<tr><td class='celdaValorAttr' colspan='4'><ul style='margin-top: 5px'>";
	for($x=0;$x<count($av);$x++) {
		$referencia = preg_replace("/((http|https|www)[^\s]+)/", '<a href="$1" target="_blank">$0</a>', $av[$x]['referencia']);
		$referencia = preg_replace("/href=\"www/", 'href="http://www', $referencia);
		$HTML_av .= "<li style='margin-top: 5px'>$referencia</li>"; }
		//$HTML_av .= "<li style='margin-top: 5px'>".wordwrap($referencia, 120, "<br />\n")."</li>"; }
	$HTML_av .= "</ul></td></tr>";
}

//$bib_obligatoria = preg_replace("/((http|https|www)[^\s]+)/", "<a href='$1' target='_blank'>$0</a>", $bib_obligatoria);
//$bib_obligatoria = preg_replace("/href=\"www/", "href='http://www", $bib_obligatoria);

//$bib_complement = preg_replace("/((http|https|www)[^\s]+)/", "<a href='$1' target='_blank'>$0</a>", $bib_complement);
//$bib_complement = preg_replace("/href=\"www/", "href='http://www", $bib_complement);

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal.php" method="post" onSubmit="return enblanco2(<?php echo($validar_js); ?>);">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($prog_asig[0]['asignatura']); ?>
</div>

<div class="texto" style="margin-top: 5px">
<?php if ($_SESSION['tipo'] <= 2) { ?>
  <a href="<?php echo("$enlbase_sm=editar_prog_asig&id_prog_asig=$id_prog_asig&id_malla=$id_malla"); ?>" class='boton'>📝 Editar</a>
<?php	} ?>
<?php if (is_numeric($id_malla)) { ?>
  <a href="<?php echo("prog_asig.php?id_prog_asig=$id_prog_asig&id_malla=$id_malla"); ?>" class='boton'>📥 Descargar PDF</a>
<?php	} ?>
  <a href="#" class='boton' onClick="parent.jQuery.fancybox.close();">↺ Volver</a>
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" width="85%" style="margin-top: 5px">
<tr><td class='celdaNombreAttr' style='text-align: center;' colspan="4">Antecedentes del Programa de Estudios</td></tr>

<tr>
  <td class='celdaNombreAttr'>Año:</td>
  <td class='celdaValorAttr'><?php echo($ano); ?></td>
  <td class='celdaNombreAttr'>ID:</td>
  <td class='celdaValorAttr' ><?php echo($id); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Asignatura:</td>
  <td class='celdaValorAttr' colspan='3'><?php echo($cod_asignatura." ".$asignatura); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Horas Lectivas semanales:</td>
  <td class='celdaValorAttr' ><?php echo($horas_semanal); ?></td>
  <td class='celdaNombreAttr'>Horas autónomas semanales:</td>
  <td class='celdaValorAttr' ><?php echo($horas_autonomas_semanales); ?></td>
</tr>
<tr>
  <td class='celdaNombreAttr'>Semanas semestrales:</td>
  <td class='celdaValorAttr'><?php echo($nro_semanas_semestrales); ?></td>
  <td class='celdaNombreAttr'>Créditos:</td>
  <td class='celdaValorAttr'><?php echo($creditos); ?></td>
</tr>

<tr>
  <td class='celdaNombreAttr'>Malla(s):</td>
  <td class='celdaValorAttr' colspan='3'><?php echo($mallas); ?></td>
</tr>
  
<?php if (($_SESSION['tipo'] >= 0 ||$_SESSION['tipo'] <= 2) && $descripcion <> "") { ?>
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Descripción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4" align="justify"><?php echo($descripcion); ?></td></tr>
<?php } ?>  

<?php if (($_SESSION['tipo'] >= 0 ||$_SESSION['tipo'] <= 2) && $aporte_perfil_egreso <> "") { ?>
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Aporte al Perfil de Egreso:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($aporte_perfil_egreso); ?></td></tr>
<?php } ?>  
  
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Objetivo General:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($obj_generales); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="4">Objetivos Específicos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($obj_especificos); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Contenidos:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($contenidos); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: justify;'>Método de instrucción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($met_instruccion); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: justify;'>Método de Evaluación:</td></tr>
  <tr><td class='celdaValorAttr' colspan="4"><?php echo($evaluacion); ?></td></tr>
  
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: justify;'>Bibliografía Obligatoria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="3">
      <b>Autor, Título, Editorial y Año</b><br>
      <?php echo($bib_obligatoria); ?>
    </td>
    <td class='celdaValorAttr' colspan="2" nowrap>
      <b>Otras asignaturas que requerirán este título*</b>
      <?php echo($bib_oblig_otras_asig); ?>
    </td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="4" style='text-align: left;'>Bibliografía Complementaria:</td></tr>
  <tr>
    <td class='celdaValorAttr' colspan="3">
      <b>Autor, Título, Editorial y Año</b><br>
      <?php echo($bib_complement); ?>
    </td>
    <td class='celdaValorAttr' colspan="2" nowrap>
      <b>Otras asignaturas que requerirán este título*</b>
      <?php echo($bib_compl_otras_asig); ?>
    </td>
  </tr>
  <?php echo($HTML_av); ?>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

