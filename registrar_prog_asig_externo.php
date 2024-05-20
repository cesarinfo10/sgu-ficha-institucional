<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_pap        = $_REQUEST['id_pap'];
$id_inst_edsup = $_REQUEST['id_inst_edsup'];
$nombre_asig   = $_REQUEST['nombre_asig'];
$duracion      = $_REQUEST['duracion'];
$semestre      = $_REQUEST['semestre'];
$ano           = $_REQUEST['ano'];
$nota_final    = $_REQUEST['nota_final'];

if ($_REQUEST['registrar'] <> "") {
	$filas = 0;
	$aCampos = array("id_pap","id_inst_edsup","nombre_asig","duracion","semestre","ano","nota_final");
	$SQLinsert = "INSERT INTO convalidaciones " . arr2sqlinsert($_REQUEST,$aCampos);

	if (consulta_dml($SQLinsert) > 0) {		
		echo(msje_js("Se ha registrado el Programa de Asignatura Externo éxitosamente."));		
		echo(js("parent.jQuery.fancybox.close();"));
	} else {
		echo(msje_js("ERROR: No se han guardado los antecedentes del Programa de Asignatura Externo."));
	}
}

$SQL_postulante = "SELECT id,nombre,rut,fecha_post,id_inst_edsup_proced FROM vista_pap WHERE id=$id_pap;";
$postulante     = consulta_sql($SQL_postulante);
if (count($postulante) > 0) {	
  	if ($postulante[0]['id_inst_edsup_proced'] == "") { 
    	echo(msje_js("ERROR: Debe registrar una IES de procedencia en la ficha del postulante (Antecedentes de Estudios Superiores)."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	}
	$inst_edsups = consulta_sql("SELECT id,coalesce(alias,' ')||'-'||substring(nombre for 50) AS nombre FROM inst_edsup ORDER BY id_tipo,nombre");
}

if ($id_inst_edsup == "") {
	$id_inst_edsup = $postulante[0]['id_inst_edsup_proced'];
}

$dl_asig = "";
$asig_ies = consulta_sql("SELECT nombre_asig FROM convalidaciones WHERE id_inst_edsup=$id_inst_edsup");
for ($x=0;$x<count($asig_ies);$x++) { $dl_asig .= "<option value='{$asig_ies[$x]['nombre_asig']}'>"; }

$duraciones = array(array("id" => 1,"nombre" => "Semestral"),
                    array("id" => 2,"nombre" => "Anual"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<form name="formulario" action="principal_sm.php" method="post" onSubmit="if (!enblanco2('id_inst_edsup','nombre_asig','ano') || !val_nota('nota_final')) { return false; }">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_pap" value="<?php echo($id_pap); ?>">
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<div style='margin-top: 5px'>
  <input type="submit" name="registrar" value="Registrar">
  <input type="button" name="cancelar" value="Cancelar" onClick="parent.jQuery.fancybox.close();">
</div>

<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr><td class="celdaNombreAttr" colspan='4' style='text-align: center'>Antecedentes del Estudiante</td></tr>

  <tr>
    <td class="celdaNombreAttr">RUT:</td>
    <td class="celdaValorAttr"><?php echo($postulante[0]['rut']); ?></td>
    <td class="celdaNombreAttr">ID:</td>
    <td class="celdaValorAttr"><?php echo($postulante[0]['id']); ?></td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre:</td>
    <td class="celdaValorAttr" colspan='3'><?php echo($postulante[0]['nombre']); ?></td>
  </tr>
  
  <tr><td class="celdaNombreAttr" colspan='4' style='text-align: center'>Antecedentes de la Asignatura Aprobada Externa</td></tr>
 
  <tr>
    <td class="celdaNombreAttr">IES origen:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="id_inst_edsup" class="filtro" style='width: auto' onChange="submitform();" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($inst_edsups,$id_inst_edsup)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nombre Asignatura:</td>
    <td class="celdaValorAttr" colspan='3'>
      <input type="text" size="30" list="nombres_asigs" name="nombre_asig" id="nombre_asig" value="<?php echo($nombre_asig); ?>" class="boton" required>
	  <datalist id='nombres_asigs'><?php echo($dl_asig); ?></datalist>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Nota de aprobación:</td>
    <td class="celdaValorAttr"><input type="text" size="3" maxlength="3" name="nota_final" value="<?php echo($nota_final); ?>" class="boton" required></td>
    <td class="celdaNombreAttr">Duracion:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="duracion" class="filtro" required>
        <?php echo(select($duraciones,$duracion)); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="celdaNombreAttr">Periodo de Aprobación:</td>
    <td class="celdaValorAttr" colspan='3'>
      <select name="semestre" class="filtro" required>
        <option value=''>-- Seleccione --</option>
        <?php echo(select($semestres,$semestre)); ?>
      </select> - 
      <input type="text" size="4" maxlength="4" name="ano" value="<?php echo($ano); ?>" class="boton" required>
    </td>
  </tr>
  <tr>
  </tr>
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->