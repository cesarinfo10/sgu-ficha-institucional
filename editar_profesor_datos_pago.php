<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if (!is_numeric($_REQUEST['id_profesor'])) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
$id_profesor = $_REQUEST['id_profesor'];
$mod_ant     = $_REQUEST['mod_ant'];

$SQL_profesor = "SELECT u.id,u.rut,u.nombre,u.apellido,u.sexo,u.fec_nac,
                        u.direccion,u.comuna,u.region,u.telefono,u.tel_movil,u.email,u.email_personal,u.nacionalidad,
                        u.nombre_usuario,u.grado_academico,to_char(u.grado_acad_fecha,'DD-MM-YYYY') AS grado_acad_fecha,u.grado_acad_universidad,
                        u.doc_fotocopia_ci,u.doc_curriculum_vitae,u.doc_certif_grado_acad,u.id_escuela,u.categorizacion,u.grado_acad_nombre,u.grado_acad_pais,
                        u.horas_planta,u.horas_plazo_fijo,u.horas_planta_docencia,u.horas_plazo_fijo_docencia,u.horas_honorarios,u.horas_honorarios_docencia,u.funcion
               FROM usuarios AS u
               WHERE u.id=$id_profesor AND tipo=3;";
$profesor = consulta_sql($SQL_profesor);
if (count($profesor) == 0) {
	echo(js("parent.jQuery.fancybox.close();"));
	exit;
}
$SQL_profesor_pago = "SELECT tipo_deposito,cod_banco_deposito,tipo_cuenta_deposito,nro_cuenta_deposito,if.nombre AS banco_deposito,fpp.email,
							 to_char(fecha_reg,'DD-tmMon-YYYY HH24:MI') AS fecha_reg,u_reg.nombre_usuario AS usuario_reg,id_usuario_reg,
							 to_char(fecha_mod,'DD-tmMon-YYYY HH24:MI') AS fecha_mod,u_mod.nombre_usuario AS usuario_mod,id_usuario_mod
					  FROM finanzas.profesores_pago AS fpp
					  LEFT JOIN finanzas.inst_financieras AS if ON if.codigo=fpp.cod_banco_deposito
					  LEFT JOIN usuarios AS u_reg ON u_reg.id=fpp.id_usuario_reg
					  LEFT JOIN usuarios AS u_mod ON u_mod.id=fpp.id_usuario_mod
					  WHERE id_profesor=$id_profesor";
$profesor_pago = consulta_sql($SQL_profesor_pago);

if ($_REQUEST['guardar'] == "Guardar") {
	if (count($profesor_pago) > 0) {
		$aCampos = array('tipo_deposito','cod_banco_deposito','tipo_cuenta_deposito','nro_cuenta_deposito','email','id_usuario_mod');
		$SQL_ins_upd = "UPDATE finanzas.profesores_pago SET ".arr2sqlupdate($_REQUEST,$aCampos).",fecha_mod=now() WHERE id_profesor=$id_profesor";
	} else {
		$aCampos = array('id_profesor','tipo_deposito','cod_banco_deposito','tipo_cuenta_deposito','nro_cuenta_deposito','email','id_usuario_reg');
		$SQL_ins_upd = "INSERT INTO finanzas.profesores_pago "  . arr2sqlinsert($_REQUEST,$aCampos);
	}
	//echo($SQL_ins_upd);
	if (consulta_dml($SQL_ins_upd) > 0) {
		echo(msje_js("Se han guardado exitosamente los datos."));
		echo(js("parent.jQuery.fancybox.close();"));
		exit;
	} else {
		echo(msje_js("ERROR: No se han guardado los datos."));
	}
}

$usuario_reg = ($profesor_pago[0]['id_usuario_reg'] == $_SESSION['id_usuario']) ? "Mi" : $profesor_pago[0]['usuario_reg'];
$usuario_mod = ($profesor_pago[0]['id_usuario_mod'] == $_SESSION['id_usuario']) ? "Mi" : $profesor_pago[0]['usuario_mod'];
	
$registrado_por = ($profesor_pago[0]['fecha_reg'] <> "") ? $usuario_reg." el ".$profesor_pago[0]['fecha_reg'] : "*** Sin registro ***";
$modificado_por = ($profesor_pago[0]['fecha_mod'] <> "") ? $usuario_mod." el ".$profesor_pago[0]['fecha_mod'] : "*** Sin modificaciones ***";

$_REQUEST = array_merge($_REQUEST,$profesor_pago[0]);

$readonly = "disabled";
if ($_SESSION['tipo'] == 0 || $mod_ant == "crear_profesor") { $readonly = ""; }

$TIPOS_DEPOSITO   = consulta_sql("SELECT id,nombre FROM vista_tipos_deposito");
$TIPOS_CUENTA     = consulta_sql("SELECT id,nombre FROM vista_tipos_cuenta");
$INST_FINANCIERAS = consulta_sql("SELECT codigo AS id,nombre FROM finanzas.inst_financieras");
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>

<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="id_profesor" value="<?php echo($id_profesor); ?>">
<input type="hidden" name="id_usuario_reg" value="<?php echo($_SESSION['id_usuario']); ?>">
<input type="hidden" name="id_usuario_mod" value="<?php echo($_SESSION['id_usuario']); ?>">
<div style='margin-top: 5px'>
  <input type="submit" name='guardar' onClick="return confirm('¿Está seguro de informar estos antecedentes para el pago de sus servicios docentes?');" value="Guardar">
  <input type="button" name='cancelar' onClick="parent.jQuery.fancybox.close();" value="Cancelar">
</div>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla" style='margin-top: 5px'>
  <tr>  
    <td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes Personales</td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Código Interno:</td>
    <td class='celdaValorAttr'><?php echo($profesor[0]['id']); ?></td>
    <td class='celdaNombreAttr'>RUT:</td>
    <td class='celdaValorAttr'><?php echo($profesor[0]['rut']); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Nombre:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($profesor[0]['nombre']." ".$profesor[0]['apellido']); ?></td>
  </tr>

  <tr><td class='celdaNombreAttr' colspan="4" style="text-align: center; ">Antecedentes de Pago</td>
  <tr>
    <td class='celdaNombreAttr'>Forma de pago:</td>
    <td class='celdaValorAttr' colspan="3">
      <select name="tipo_deposito" class="filtro" onChange="validar_tipo_deposito(this.value)" required>
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_DEPOSITO,$_REQUEST['tipo_deposito'])); ?>
      </select><br>
      <small>
	    La opción Servipag úsela sólo si no dispone de Cuenta Corriente, Cuenta Vista o Cuenta RUT (sólo Banco Estado).<br>
	    Considere que deberá asistir personal y presencialmente a una oficina de Servipag para realizar el retiro de su pago.
	  </small>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Banco:</td>
    <td class='celdaValorAttr'>
      <select name="cod_banco_deposito" class="filtro">
        <option value="">-- Seleccione --</option>
        <?php echo(select($INST_FINANCIERAS,$_REQUEST['cod_banco_deposito'])); ?>
      </select>
    </td>
    <td class='celdaNombreAttr'>Tipo de Cuenta:</td>
    <td class='celdaValorAttr'>
      <select name="tipo_cuenta_deposito" class="filtro">
        <option value="">-- Seleccione --</option>
        <?php echo(select($TIPOS_CUENTA,$_REQUEST['tipo_cuenta_deposito'])); ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>N° Cuenta:</td>
    <td class='celdaValorAttr'><input type="number" size="10" name="nro_cuenta_deposito" value="<?php echo($_REQUEST['nro_cuenta_deposito']); ?>" class="boton"></td>
    <td class='celdaNombreAttr'>e-Mail:</td>
    <td class='celdaValorAttr'><input type="email" size="15" name="email" value="<?php echo($_REQUEST['email']); ?>" class="boton"></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Registrado por:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($registrado_por); ?></td>
  </tr>
  <tr>
    <td class='celdaNombreAttr'>Modificado por:</td>
    <td class='celdaValorAttr' colspan="3"><?php echo($modificado_por); ?></td>
  </tr>

</table>
</form>

<!-- Fin: <?php echo($modulo); ?> -->

<script>

validar_tipo_deposito("<?php echo($profesor_pago[0]['tipo_deposito']); ?>");

function validar_tipo_deposito(valor) {
	if (valor == "Transferencia Bancaria") {
		formulario.cod_banco_deposito.required = true;
		formulario.cod_banco_deposito.disabled = false;
		formulario.tipo_cuenta_deposito.required = true;
		formulario.tipo_cuenta_deposito.disabled = false;
		formulario.nro_cuenta_deposito.required = true;
		formulario.nro_cuenta_deposito.disabled = false;
		formulario.email.required = true;
		formulario.email.disabled = false;
	} else {
		formulario.cod_banco_deposito.required = false;
		formulario.cod_banco_deposito.disabled = true;
		formulario.tipo_cuenta_deposito.required = false;
		formulario.tipo_cuenta_deposito.disabled = true;
		formulario.nro_cuenta_deposito.required = false;
		formulario.nro_cuenta_deposito.disabled = true;
		formulario.email.required = false;
		formulario.email.disabled = true;
	}
}
		
		
		
</script>
