<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$aCampos = array("rut","nombres","apellidos","genero","nacionalidad",
                 "pasaporte","direccion","comuna","region","telefono",
                 "tel_movil","email","admision","admision_subtipo","carrera1_post","carrera2_post",
                 "carrera3_post","cert_nacimiento","conc_notas_em","boletin_psu","copia_ced_iden",
                 "licencia_em","rbd_colegio","ano_egreso_col","promedio_col","ano_psu",
                 "puntaje_psu","id_inst_edsup_proced","carr_ies_pro","prom_nt_ies_pro","conc_nt_ies_pro",
                 "prog_as_ies_pro","id_fte_finan","paga_matricula","id_convenio","id_beca",
                 "id_credito","fec_nac",'id_origen_bd');

$aRequeridos = array(0,1,2,3,4,6,7,8,9,11,12,13,16,17,19);
$requeridos  = requeridos($aRequeridos,$aCampos);
$requeridos .= ",'fec_nac_dia','fec_nac_mes','fec_nac_ano'";

$rut        = $_REQUEST['rut'];
$validar    = $_REQUEST['validar'];
$rut_valido = false;

if ($validar <> "" && $rut <> "") {
	$pap = consulta_sql("SELECT id FROM pap WHERE rut = '$rut';");
	if (!$pap) {
		$rut_valido = true;
	} else {
		$id_pap = $pap[0]['id'];
		$confirma_msje   = "Este rut ya está registrado.\\n Desea ver los datos?";
		$confirma_url_si = "$enlbase=ver_postulante&id_pap=$id_pap";
		$confirma_url_no = "$enlbase=$modulo";
		echo(confirma_js($confirma_msje,$confirma_url_si,$confirma_url_no));
	}
}

if ($_REQUEST['crear'] <> "") {
	if (!checkdate($_REQUEST['fec_nac_mes'],$_REQUEST['fec_nac_dia'],$_REQUEST['fec_nac_ano'])) {
		echo(msje_js(""
		            ."Al parecer hay un problema con la fecha de nacimiento.\\n"
		            ."Lo más seguro es que seleccionó una fecha imposible\\n"
		            ."(como un 29 de febrero con un año no biciesto o un 31 de mayo)\\n"
		            ."o bien no ha ingresado ninguna."
		            .""));
		$_REQUEST['crear'] = "";
	} else {
		$fec_nac = mktime(0,0,0,$_REQUEST['fec_nac_mes'],$_REQUEST['fec_nac_dia'],$_REQUEST['fec_nac_ano']);
		$_REQUEST['fec_nac'] = strftime("%Y-%m-%d",$fec_nac);		
	}
}

if ($_REQUEST['crear'] <> "") {
	$SQLinsert = "INSERT INTO pap " . arr2sqlinsert($_REQUEST,$aCampos);
	$resultado = pg_query($bdcon, $SQLinsert);
	$filas = pg_affected_rows($resultado);
	if ($filas > 0) {
		$pap    = consulta_sql("SELECT currval('serial_id_pap') AS id;");
		$id_pap = $pap[0]['id'];
		$confirma_msje   = "Se ha creado existosamente un nuevo postulante. Desea crear otro?";
		$confirma_url_si = "$enlbase=$modulo";
		$confirma_url_no = "$enlbase=ver_postulante&id_pap=$id_pap";
		echo(confirma_js($confirma_msje,$confirma_url_si,$confirma_url_no));		
	}
}

?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<?php
	if ($rut_valido) {
		$_REQUEST['fecha_post'] = strftime("%d/%m/%Y %X");
		include("formulario_postulante.php");
	} else {
?>
<form name="formulario" action="principal.php" method="get" onSubmit="return valida_rut(formulario.rut);">
  <input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
  <table bgcolor='#ffffff' cellspacing='1' cellpadding='2' class='tabla'>
    <tr>
      <td class='celdaNombreAttr'>RUT:</td>
      <td class='celdaValorAttr'>
        <input type='text' size="12" name='rut' onChange="var valor=this.value;this.value=valor.toUpperCase();" tabindex="1">
        <script>formulario.rut.focus();</script>
        <input type="submit" name="validar" value="Validar" tabindex="2">
      </td>
    </tr>
  </table>
</form>
<?php
	}
?>

<!-- Fin: <?php echo($modulo); ?> -->

