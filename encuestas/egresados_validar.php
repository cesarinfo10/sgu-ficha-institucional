<?php

$rut          = trim($_REQUEST['rut']);
$id_carrera   = $_REQUEST['id_carrera'];
$ANO_Encuesta = $_REQUEST['ano'];

if ($rut <> "" && $id_carrera > 0) {
	$SQL_estados_alumnos = "SELECT id FROM al_estados WHERE nombre IN ('Egresado','Graduado','Licenciado','Titulado','Post-Titulado')";

	$SQL_alumno = "SELECT id FROM alumnos WHERE rut='$rut' AND carrera_actual=$id_carrera AND estado IN ($SQL_estados_alumnos)";
	$alumno = consulta_sql($SQL_alumno);
	if (count($alumno) > 0) {
		$id_alumno = $alumno[0]['id'];
		echo(js("window.location='/sgu/encuestas/?modulo=egresados&id_alumno=$id_alumno&id_carrera=$id_carrera&ano=$ANO_Encuesta';"));
	} else {
		echo(msje_js("ERROR: Has ingresado tu RUT incorrectamente o bien no tienes estado terminal aún (Egresado, Graduado, Licenciado, Titulado o Post-Titulado).\\n"
		            ."Si tienes dudas, comunicate con la DAE o con la Unidad de Registro Académico"));
	}		
}
$rut_leido = false;
if (!empty($rut)) { $rut_leido = true; }

$cond_carrera = "";
if (!empty($rut)) { $cond_carrera = "WHERE id IN (SELECT carrera_actual FROM alumnos WHERE rut='$rut')"; }
$carreras = consulta_sql("SELECT id,nombre FROM carreras $cond_carrera ORDER BY nombre");
?>
<div align="center" class="tituloModulo">
  Encuesta para Alumnos Egresados <?php echo($ANO_Encuesta); ?>
</div>
<form name="formulario" action="index.php" method="post" onSubmit="return valida_rut(formulario.rut);">
<input type='hidden' name='modulo' value='egresados_validar'>
<input type='hidden' name='ano' value='<?php echo($ANO_Encuesta); ?>'>
<input type='hidden' name='rut_leido' value='<?php echo($rut_leido); ?>'>
<br>
<div class='texto'>
  Para proceder con la contestación de tu encuesta, por favor digita tu RUT y selecciona tu carrera de la que has egresado:<br>
  <br>
  <table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'>
      <input type="text" size="12" name="rut" value="<?php echo($rut); ?>"
             onChange="var valor=this.value;this.value=valor.toUpperCase();" 
             onBlur="if (formulario.rut_leido.value=='false') { submitform(); }"
             <?php echo($solo_leer); ?>>
      <script>formulario.rut.focus();</script>
      <br><sup>Ej: 73124400-6</sup>
    </td>
  </tr>
  <?php if ($rut <> "") { ?>
  <tr>
    <td class='celdaNombreAttr'>Carrera:</td>
    <td class='celdaValorAttr'>
      <select name='id_carrera'>
        <option>-- Seleccione --</option>
        <?php echo(select($carreras,$id_carrera)); ?>
      </select>
      <script>formulario.id_carrera.focus();</script>
    </td>
  </tr>
  <?php } ?>
  <tr><td colspan='2' class='celdaNombreAttr'><input type="submit" name="validar" value="Validar y continuar"></td></tr>
  </table>
</div>

</form>
