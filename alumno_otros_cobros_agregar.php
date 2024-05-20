<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$id_alumno = $_REQUEST['id_alumno'];
$id_glosa  = $_REQUEST['id_glosa'];
$cantidad  = $_REQUEST['cantidad'];
$descuento = $_REQUEST['descuento'];

if ($_REQUEST["fecha"] == "") { $_REQUEST["fecha"] = date("d-m-Y"); }


if ($cantidad == "") { $cantidad = 1; }

$alumno = consulta_sql("SELECT id,rut,nombre,estado FROM vista_alumnos WHERE id=$id_alumno");
extract($alumno[0]);
/*
if ($estado <> "Vigente") {
	echo(msje_js("Actualmente este alumno no tiene estado de Vigente.\\n\\n"
	            ."Esto impide añadir Otros Cobros a este alumno."));
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
*/

if ($id_glosa <> "") {
	$ANO_Aranceles = $ANO_MATRICULA;
	if ($_REQUEST['fecha'] <> "") { $ANO_Aranceles = "date_part('year','{$_REQUEST['fecha']}'::date)"; }
	
	$SQL_glosa = "SELECT CASE moneda 
	                          WHEN 'UF'    THEN uf.valor * aoi.monto 
	                          WHEN 'Pesos' THEN aoi.monto
	                     END AS monto
	              FROM finanzas.aranceles_otros_ingresos AS aoi
	              LEFT JOIN finanzas.valor_uf AS uf ON (fecha = '{$_REQUEST['fecha']}'::date)
	              WHERE aoi.ano=$ANO_Aranceles AND id_glosa=$id_glosa";	
	$glosa = consulta_sql($SQL_glosa);
	if (count($glosa) == 0) {
		echo(msje_js("ERROR: La glosa escogida no tiene arancel definido para el año $ANO_Aranceles.\\n\\n"
		            ."No es posible continuar. Avise de este error a la Vicerrectoría de Administración y Finanzas"));
		echo(js("window.location='$enlbase_sm=alumno_otros_cobros&id_alumno=$id_alumno';"));
		exit;
	}
	
	$monto_glosa = intval($glosa[0]['monto']);
	if ($monto_glosa == 0) {
		echo(msje_js("ERROR: La glosa escogida no tiene valor definido. Esto lo puede provocar el hecho que tenga "
		            ."su arancel definido en UF y el valor de ésta para hoy no se ha ingresado.\\n\\n"
		            ."No es posible continuar. Avise de este error a la Dirección de Informática"));
		echo(js("window.location='$enlbase_sm=alumno_otros_cobros&id_alumno=$id_alumno';"));
		exit;
	}
	$monto_total = $cantidad * ($monto_glosa - intval(str_replace(".","",$descuento)));
}

if ($_REQUEST['guardar'] == "Guardar") {
	$fecha = $_REQUEST["fecha"];
	$SQL_cobros_insert = "INSERT INTO finanzas.cobros (id_alumno,id_glosa,nro_cuota,monto,fecha_venc,id_usuario) VALUES ";
	$SQL_cobros_valores = array();
	$id_emisor = $_SESSION['id_usuario'];
	for ($x=0;$x<$cantidad;$x++) { 
		$nro_cuota = $x + 1;
		$monto = $monto_glosa - intval(str_replace(".","",$descuento));
		$SQL_cobros_valores[$x] = "($id_alumno,$id_glosa,$nro_cuota,$monto,'$fecha'::date,$id_emisor)";
	}
	$SQL_cobros_insert .= implode(",",$SQL_cobros_valores);
	if (consulta_dml($SQL_cobros_insert) == $cantidad) {
		echo(msje_js("Se han registrado los cobros de la glosa seleccionada y en la cantidad indicada.\\n\\n"
		            ."Ahora el alumno puede pasar a la caja a pagar"));
	} else {
		echo(msje_js("ERROR: No se han registrado los cobros.\\n\\n"
		            ."Por favor reintente o comunique este error a la Dirección de Informática si se ha producido nuevamente"));
	}
	echo(js("window.location='$enlbase_sm=alumno_otros_cobros&id_alumno=$id_alumno';"));
	exit;
}

$GLOSAS = consulta_sql("SELECT id,nombre FROM finanzas.glosas WHERE tipo ~ 'Otros Ingresos' AND boleta ORDER BY nombre;");

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="principal_sm.php" method="get">
<input type="hidden" name="modulo"    value="<?php echo($modulo); ?>">
<input type="hidden" name="id_alumno" value="<?php echo($id_alumno); ?>">
<input type="hidden" name="id_glosa"  value="<?php echo($id_glosa); ?>">
<input type="hidden" name="cantidad"  value="<?php echo($cantidad); ?>">

<?php if ($monto_glosa > 0 && $monto_total > 0) {?>
<br><div><input type="submit" name="guardar" value="Guardar"></div>
<?php } ?>

<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Antecedentes Personales del Alumno(a)</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>RUT:</u></td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
    <td class='celdaNombreAttr'>ID:</td>
    <td class='celdaValorAttr'><?php echo($id_alumno); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Nombre:</u></td>
    <td class='celdaValorAttr' colspan="3"><?php echo($nombre); ?></td>
  </tr>
  <tr><td class='celdaNombreAttr' colspan="6" style="text-align: center; ">Cobro</td></tr>
  <tr>
    <td class='celdaNombreAttr'><u>Cantidad:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' name='cantidad' size='2' value="<?php echo($cantidad); ?>" class='boton' onChange="submitform();">
      <input type='submit' name='act' value="Actualizar">
    </td>
    <td class='celdaNombreAttr'><u>Fecha:</u></td>
    <td class='celdaValorAttr'>
      <input type='text' name='fecha' size='10' value="<?php echo($_REQUEST["fecha"]); ?>" class='boton' onChange="submitform();"><br>
      <sup>DD-MM-AAAA</sup>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'><u>Glosa:</u></td>
    <td class='celdaValorAttr' colspan='3'>
      <select name='id_glosa' onChange="submitform();" class='filtro' style='max-width: none'>
		<option value=''>-- Seleccione --</option>
		<?php echo(select($GLOSAS,$id_glosa)); ?>
      </select>
    </td>
  </tr>
<?php if ($monto_glosa > 0 && $monto_total > 0) {?>
  <tr>
    <td class='celdaNombreAttr'>Monto Unitario:</td>
    <td class='celdaValorAttr'>$<?php echo(number_format($monto_glosa,0,",",".")); ?></td>
    <td class='celdaNombreAttr'>Descuento Unitario:</td>
    <td class='celdaValorAttr'>
      $<input type='text' name='descuento' size='10' value="<?php echo($descuento); ?>" class='montos' 
            onBlur="if (this.value.replace('.','').replace('.','') >= <?php echo($monto_glosa) ?>) { alert('ERROR: No puede ingresar un descuento que deje saldo en 0'); this.value=null; } submitform();"
            onKeyUp="puntitos(this,this.value.charAt(this.value.length-1),this.name); calc_total(this.value);">
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Monto Total:</td>
    <td class='celdaValorAttr' colspan='3'><b>$<?php echo(number_format($monto_total,0,",",".")); ?></b></td>
  </tr>
<?php } ?>
</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
