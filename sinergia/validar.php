<?php
$rut       = $_REQUEST['rut'];
$id_prueba = $_REQUEST['id_prueba'];

if (count($id_prueba) > 0) {
	foreach ($id_prueba AS $id => $activo) {
		consulta_dml("UPDATE sinergia.pruebas SET activo='$activo' WHERE id=$id");
	}
}

$HTML = "";
if ($rut <> "") {
	$aplicador = consulta_sql("SELECT rut FROM sinergia.aplicadores WHERE rut='$rut' AND activo");
	if (count($aplicador) > 0) {
		$pruebas = consulta_sql("SELECT id,nombre,activo,alias FROM sinergia.pruebas WHERE activo ORDER BY alias");		
		$HTML = "<tr class='filaTituloTabla'><td class='tituloTabla' colspan='2'>Pruebas Disponibles</td>"
		      . "<tr class='filaTituloTabla'>"
		      . "  <td class='tituloTabla'>Nombre</td>"
		      . "  <td class='tituloTabla'>Activo?</td>"
		      . "</tr>";
		for ($x=0;$x<count($pruebas);$x++) {
			$boton_ver = "<a href='?modulo={$pruebas[$x]['alias']}&rut_alumno=$rut&id_prueba={$pruebas[$x]['id']}' class='boton'>Ver prueba</a>";
			$HTML .= "<tr class='filaTabla'>"
				  .  "  <td class='textoTabla'>{$pruebas[$x]['nombre']}</td>"
				  .  "  <td class='textoTabla'>"
				  .  "    <select name='id_prueba[{$pruebas[$x]['id']}]' onChange='submitform();'>"
				  .  select($sino,$pruebas[$x]['activo'])
				  .  "    </select> $boton_ver"
				  .  "  </td>"
				  .  "</tr>";
		}		
	} else {
		$alumno = consulta_sql("SELECT rut FROM alumnos WHERE rut='$rut'");
		$pap    = consulta_sql("SELECT rut FROM pap WHERE rut='$rut'");
		if (count($alumno) == 0 && count($pap) == 0) {
			echo(msje_js("El RUT ingresado no corresponde a un aplicador ni a un alumno. No es posible continuar"));
			echo(js("location.href='http://www.umcervantes.cl';"));
			exit;
		}
		$SQL_pruebas = "SELECT p.id,nombre,alias,r.id AS folio_resp 
		                FROM sinergia.pruebas AS p 
		                LEFT JOIN sinergia.respuestas AS r ON (r.id_prueba=p.id AND r.rut_alumno='$rut' AND r.semestre=$SEMESTRE AND r.ano=$ANO) 
		                WHERE activo 
		                ORDER BY alias";
		$pruebas = consulta_sql($SQL_pruebas);
		$HTML = "<tr class='filaTituloTabla'><td class='tituloTabla' colspan='2'>Pruebas Disponibles para contestar</td>"
		      . "<tr class='filaTituloTabla'>"
		      . "  <td class='tituloTabla'>Nombre</td>"
		      . "  <td class='tituloTabla'>&nbsp;</td>"
		      . "</tr>";
		if (count($pruebas) == 0) {
			$HTML .= "<tr class='filaTabla'>"
				  .  "  <td class='textoTabla' colspan='2' align='center'>"
				  .  "    <br>*** No hay pruebas activas aún ***<br><br>"
				  .  "  </td>"
				  .  "</tr>";
		}
			
		for ($x=0;$x<count($pruebas);$x++) {
			$boton_contestar = "<a href='?modulo={$pruebas[$x]['alias']}&rut_alumno=$rut&id_prueba={$pruebas[$x]['id']}' class='boton'>Contestar</a>";
			if ($pruebas[$x]['folio_resp'] > 0) { $boton_contestar = "<span style='color: #009900;text-align: center'><b>Contestada Folio: {$pruebas[$x]['folio_resp']}</b></span>"; }
			$HTML .= "<tr class='filaTabla'>"
				  .  "  <td class='textoTabla'>{$pruebas[$x]['nombre']}</td>"
				  .  "  <td class='textoTabla'>$boton_contestar</td>"
				  .  "</tr>";
		}
		$HTML .= "<tr class='filaTabla'><td class='textoTabla' colspan='2' align='center'><br><a href='?modulo=validar&rut=$rut&validar=Validar' class='boton'>Actualizar</a><br><br></td></tr>";
	}
}

?>
<div class='titulomodulo'>
  Ingreso
</div>
<form name="formulario" action="index.php" method="post" onSubmit="return valida_rut(formulario.rut);">
<input type='hidden' name='modulo' value='validar'>
<br>
<?php if ($rut <> "") { ?>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($rut); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Periodo de trabajo:</td>
    <td class='celdaValorAttr'><?php echo($SEMESTRE.'-'.$ANO); ?></td>
  </tr>
</table>
<br>
<br>
<table cellpadding="2" cellspacing="1" class="tabla"  bgcolor="#FFFFFF">
  <?php echo($HTML); ?>
</table>

<?php } else { ?>

<div class='texto'>
  Por favor indique su RUT para continuar:
  <input type="text" size="12" name="rut" value="<?php echo($rut); ?>"
             onChange="var valor=this.value;this.value=valor.toUpperCase();" 
             <?php echo($solo_leer); ?>>
      <script>formulario.rut.focus();</script>
      <?php if (!$rut_valido) {?>
      <input type="submit" name="validar" value="Validar">
      <br>
      <sup>Ej: 73124400-6</sup>
      <?php } ?>

</div>
<?php } ?>

</form>
