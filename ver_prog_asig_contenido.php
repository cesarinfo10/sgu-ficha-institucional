<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

if (!is_numeric($id_prog_asig)) {
	echo(js("location.href='$enlbase=gestion_asignaturas';"));
	exit;
}

$SQL_prog_asig = "SELECT pa.*,vpa.asignatura 
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
?>

<div class="tituloModulo">
  Ver Programa de asignatura
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>AÑO:</td>
    <td class='celdaValorAttr'><?php echo($ano); ?></td>
    <td class='celdaNombreAttr' colspan="3">Id:</td>
    <td class='celdaValorAttr'><?php echo($id); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Asignatura:</td>
    <td class='celdaValorAttr' colspan='5'><?php echo($cod_asignatura." ".$asignatura); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr' nowrap>Nº Hrs Ped. semanales:</td>
    <td class='celdaValorAttr'><?php echo($horas_semanal); ?></td>
    <td class='celdaNombreAttr' colspan="3" nowrap>Nº de Semanas semestrales:</td>
    <td class='celdaValorAttr'><?php echo($nro_semanas_semestrales); ?></td>
<!--    <td class='celdaNombreAttr'>Carga Académica semanal:</td>
    <td class='celdaValorAttr' ><?php echo($carga_acad_sem); ?></td> -->
  </tr>
  
<?php if (($_SESSION['tipo'] >= 0 ||$_SESSION['tipo'] <= 2) && $descripcion <> "") { ?>
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="6">Descripción:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($descripcion); ?></td></tr>
<?php } ?>  

<?php if (($_SESSION['tipo'] >= 0 ||$_SESSION['tipo'] <= 2) && $aporte_perfil_egreso <> "") { ?>
  <tr><td class='celdaNombreAttr' style='text-align: left;' colspan="6">Aporte al Perfil de Egreso:</td></tr>
  <tr><td class='celdaValorAttr' colspan="6"><?php echo($aporte_perfil_egreso); ?></td></tr>
<?php } ?>  
  
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
    <td class='celdaValorAttr' colspan="2">
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
    <td class='celdaValorAttr' colspan="2">
      <b>Otras asignaturas que requerirán este título*</b>
      <?php echo($bib_compl_otras_asig); ?>
    </td>
  </tr>
</table>
