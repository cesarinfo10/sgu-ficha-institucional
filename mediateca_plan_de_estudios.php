<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_malla = $_REQUEST['id_malla'];
$vista    = $_REQUEST['vista'];
if (!is_numeric($id_malla)) {
	echo(js("location.href='principal.php?modulo=gestion_mallas';"));
	exit;
}

$SQL_malla = "SELECT id,ano,carrera,niveles,requisitos_titulacion,id_escuela,comentarios
              FROM vista_mallas
              WHERE id=$id_malla";
$malla     = consulta_sql($SQL_malla);
if (count($malla) > 0) {
	extract($malla[0]);
	
	$condicion = "NOT eliminado";
	if ($vista == "papelera") { $condicion = "eliminado"; }
	
	$SQL_arch_malla = "SELECT ma.id,ma.nombre,descripcion,to_char(arch_fecha,'DD-MM-YYYY HH24:MI') AS arch_fecha,
	                          to_char(arch_fec_mod,'DD-MM-YYYY HH24:MI') AS arch_fec_mod,vu.nombre_usuario
	                   FROM mallas_archivos AS ma
	                   LEFT JOIN vista_usuarios AS vu ON vu.id=id_usuario
	                   WHERE id_malla=$id_malla AND $condicion
	                   ORDER BY arch_fec_mod;";
	$arch_malla     = consulta_sql($SQL_arch_malla);	
} else {
	echo(msje_js("Se está intentando acceder a una malla inexistente. No es posible continuar"));
	echo(js("location.href='principal.php?modulo=gestion_mallas';"));
	exit;
}

if (is_numeric($_REQUEST['eliminar'])) {
	$id_arch = $_REQUEST['eliminar'];
	if ($vista == "papelera") {
		$SQL_arch_malla = "DELETE FROM mallas_archivos WHERE id=$id_arch;";
	} else {
		$SQL_arch_malla = "UPDATE mallas_archivos SET eliminado=true WHERE id=$id_arch;";
	}
	consulta_dml($SQL_arch_malla);
	echo(js("location.href='$enlbase=$modulo&id_malla=$id_malla';"));
}

if (is_numeric($_REQUEST['recuperar'])) {
	$id_arch = $_REQUEST['recuperar'];
	$SQL_arch_malla = "UPDATE mallas_archivos SET eliminado=false WHERE id=$id_arch;";
	consulta_dml($SQL_arch_malla);
	echo(js("location.href='$enlbase=$modulo&id_malla=$id_malla';"));
}

$HTML = "";
if (count($arch_malla) == 0) {
	
	$msje = "*** Aún no se han subido documentos a esta mediateca. "
	      . "Para subir el primer documento pinche <a href='$enlbase=mediateca_plan_de_estudios_subir_doc&id_malla=$id_malla' class='enlaces'>aquí</a>";
	if ($vista == "papelera") {
		$msje = "**** No hay documentos en la papelera";
	}
	
	$HTML = "<tr class='filaTabla'><td class='textoTabla' colspan='4'><br>"
	      . $msje
	      . "<br><br></td></tr>";
}
for ($x=0;$x<count($arch_malla);$x++) {
	extract($arch_malla[$x]);

	
	$atit      = "Papelera";
	$msje_elim = "¿Está seguro de enviar a la papelera este documento \«$nombre\»?";
	if ($vista == "papelera") {
		$atit      = "Eliminar";
		$msje_elim = "¿Está seguro de eliminar definitivamente este documento (luego no es recuperable) \«$nombre\»?";
	}
	
	$boton_eliminar = "";
	if ($_SESSION['tipo'] == 0) {
		$boton_eliminar = "<a href='#' onClick=\"if(confirm('$msje_elim')) { window.location='$enlbase=$modulo&eliminar=$id&id_malla=$id_malla&vista=$vista'; }\" class='boton' title='$atit'>X</a>";
	}
	
	$boton_recuperar = "";
	if ($vista == "papelera") {
		$boton_recuperar = "<a href='#' onClick=\"if(confirm('Desea recuperar este documento')) { window.location='$enlbase=$modulo&recuperar=$id&id_malla=$id_malla&vista=$vista'; }\" class='boton' title='Recuperar'>R</a>";
	}

	$enl         = "ver_arch_mediateca_plan_de_estudios.php?id=$id";
	$nombre      = "<a href='$enl' target='_blank' class='enlaces'>$nombre</a>";
	$descripcion = nl2br($descripcion);
	
	$HTML .= "<tr class='filaTabla'>\n"
		  .  "  <td class='textoTabla'>$boton_eliminar $boton_recuperar</td>\n"
		  .  "  <td class='textoTabla'><b>$nombre</b><br>$descripcion</td>\n"
		  .  "  <td class='textoTabla'>$arch_fecha</td>\n"
		  .  "  <td class='textoTabla'>$nombre_usuario</td>\n"
		  .  "</tr>\n";
}

$VISTAS = array(array('id' => "vigentes", 'nombre' => "Documentos Vigentes"),
                array('id' => "papelera", 'nombre' => "Papelera"));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>: <?php echo($carrera); ?> - <?php echo($ano); ?>
</div><br>
<table class="tabla">
  <tr>
    <td class="tituloTabla">
      <input type="button" name="subir_archivo_mediateca" value="Subir documento nuevo" onClick="window.location='<?php echo("$enlbase=mediateca_plan_de_estudios_subir_doc&id_malla=$id_malla"); ?>'">
    </td>
    <td class="tituloTabla">
      <input type="button" name="volver" value="Volver" onClick="window.location='<?php echo("$enlbase=ver_plan_de_estudios&id_malla=$id_malla"); ?>'">
    </td>
  </tr>
</table>
<br>
<form name="formulario" action="principal.php" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_malla" value="<?php echo($id_malla); ?>">
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($carrera); ?></td>
    <td class='celdaNombreAttr'>Año:</td>
    <td class='celdaValorAttr'><?php echo($ano); ?></td>
    <td class='celdaNombreAttr'>Id:</td>
    <td class='celdaValorAttr'><?php echo($id_malla); ?></td>
  </tr>
</table><br>
<div class='texto'>
  Seleccione vista:
  <select name='vista' onChange='submitform()'>
    <?php echo(select($VISTAS,$vista)); ?>
  </select>
</div>
<br>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" width="75%" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan='2'>Nombre y Descripción</td>
    <td class='tituloTabla'>Fecha de Subida</td>
    <td class='tituloTabla'>Creador</td>
  </tr>
  <?php echo($HTML); ?>  
</table>
</form>
<!-- Fin: <?php echo($modulo); ?> -->

