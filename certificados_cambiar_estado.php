<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo_uid_no_cero.php");

$codigo_barras = $_REQUEST["codigo_barras"];
$nuevo_estado  = $_REQUEST["nuevo_estado"];

if ($nuevo_estado == "") { $nuevo_estado = 'Entregado'; }

if (!empty($codigo_barras)) {
	$SQL_cod_barra = "SELECT folio FROM vista_alumnos_certificados_codbarras WHERE cod=lower('$codigo_barras')";
	$cod_barra = consulta_sql($SQL_cod_barra);	
	if (count($cod_barra) > 0){
		$folio = $cod_barra[0]['folio'];
		$id_operador = $_SESSION['id_usuario'];
		$SQL_certificado = "SELECT folio,trim(a.rut) AS rut,va.nombre AS alumno,cert.nombre AS docto,to_char(ac.fecha,'DD-tmMon-YYYY') AS fecha,ac.estado,
	                        CASE WHEN length(ac.archivo)>0 THEN 'Si' ELSE 'No' END AS docto_firmado
		                    FROM alumnos_certificados AS ac
		                    LEFT JOIN vista_alumnos_certificados_codbarras AS vac USING (folio)
		                    LEFT JOIN certificados    AS cert ON cert.id=ac.id_certificado
		                    LEFT JOIN alumnos         AS a    ON a.id=ac.id_alumno
		                    LEFT JOIN vista_alumnos   AS va   ON va.id=ac.id_alumno
		                    WHERE folio=$folio";
		$certificado = consulta_sql($SQL_certificado);
		if ($certificado[0]['estado'] == "Entregado") {
			echo(msje_js("ATENCIÓN: Este certificado ya ha sido ENTREGADO"));
			echo(js("window.location='$enlbase=$modulo';"));
		} 
		elseif ($nuevo_estado <> "Entregado" || ($nuevo_estado == "Entregado" && $certificado[0]['estado'] == "Firmado" && $certificado[0]['docto_firmado'] == "Si")) {
			consulta_dml("INSERT INTO alumnos_certificados_aud (folio,estado,id_usuario) VALUES ($folio,'$nuevo_estado',$id_operador)");
			consulta_dml("UPDATE alumnos_certificados SET estado='$nuevo_estado',estado_fecha=now(),estado_id_usuario='$id_operador' WHERE folio=$folio");
		} 
		elseif ($nuevo_estado == "Entregado" && ($certificado[0]['estado'] <> "Firmado" || $certificado[0]['docto_firmado'] == "No"))  {
			echo(msje_js("ERROR: No es posible ENTREGAR este certificado, ya que no tiene estado de Firmado "
			            ."y no podrá ser validado posteriormente (a través de la web).\\n\\n"
			            ."Por favor devuelva este certificado a Registro Académico."));
			echo(js("window.location='$enlbase=$modulo';"));
		}			
	}
}
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
	<?php echo($nombre_modulo); ?>
</div>
<form name="formulario" action="<?php echo($_SERVER['SCRIPT_NAME']); ?>" method="post">
<input type="hidden" name="modulo" value="<?php echo($modulo); ?>">
<input type="hidden" name="nuevo_estado" value="<?php echo($nuevo_estado); ?>">
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px;'>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>Código de Barras de Certificado:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><input type="text" name="codigo_barras" id="codigo_barras" size="20" value="" class='boton'></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' style='text-align: right'>Nuevo estado:</td>
    <td class='celdaValorAttr' bgcolor='#FFFFFF'><?php echo($nuevo_estado); ?></td>
  </tr>
<?php

if (count($cod_barra) > 0){
	echo("<tr class='filaTituloTabla'>"
	    ."  <td class='celdaValorAttr' bgcolor='#FFFFFF' colspan='2'>"
	    ."    Registrando cambio de estado al certificado <b>«{$certificado[0]['docto']}»</b>, <br>"
	    ."    Folio <b>{$certificado[0]['folio']}</b>, extendido al alumno <b>{$certificado[0]['alumno']}</b> "
	    ."    emitido el <b>{$certificado[0]['fecha']}</b>"
	    ."  </td>"
	    ."</tr>");
}

?>
</table> 
<script>document.getElementById("codigo_barras").focus();document.getElementById("codigo_barras").select();</script>
</form>

<!-- Fin: <?php echo($modulo); ?> -->
