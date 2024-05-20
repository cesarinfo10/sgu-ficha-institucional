<?php

include("validar_modulo.php");

$bdcon = pg_connect("dbname=regacad" . $authbd);

$id_pap = $_REQUEST['id_pap'];
if ($id_pap == "" || !$_SESSION['autentificado']) {
	header("Location: index.php?modulo=gestion_postulantes");
	exit;
};

$aCampos = array("rut","nombres","apellidos","genero","nacionalidad",
                 "pasaporte","direccion","comuna","region","telefono",
                 "tel_movil","email","admision","admision_subtipo","carrera1_post","carrera2_post",
                 "carrera3_post","cert_nacimiento","conc_notas_em","boletin_psu","copia_ced_iden",
                 "licencia_em","rbd_colegio","ano_egreso_col","promedio_col","ano_psu",
                 "puntaje_psu","id_inst_edsup_proced","carr_ies_pro","prom_nt_ies_pro","conc_nt_ies_pro",
                 "prog_as_ies_pro","id_fte_finan","paga_matricula","id_convenio","id_beca",
                 "id_credito","fec_nac");

$aRequeridos = array(0,1,2,3,4,6,7,8,9,11,12,13,16,17,19);
$requeridos  = requeridos($aRequeridos,$aCampos);
$requeridos .= ",'fec_nac_dia','fec_nac_mes','fec_nac_ano'";

if ($_REQUEST['editar'] <> "") {
	if (!checkdate($_REQUEST['fec_nac_mes'],$_REQUEST['fec_nac_dia'],$_REQUEST['fec_nac_ano'])) {
		echo(msje_js(""
		            ."Al parecer hay un problema con la fecha de nacimiento.\\n"
		            ."Lo más seguro es que seleccionó una fecha imposible\\n"
		            ."(como un 29 de febrero con un año no biciesto o un 31 de mayo)\\n"
		            ."o bien no ha ingresado ninguna."
		            .""));
		$_REQUEST['editar'] = "";
	} else {
		$fec_nac = mktime(0,0,0,$_REQUEST['fec_nac_mes'],$_REQUEST['fec_nac_dia'],$_REQUEST['fec_nac_ano']);
		$_REQUEST['fec_nac'] = strftime("%Y-%m-%d",$fec_nac);		
	}
}

if ($_REQUEST['editar'] <> "") {
	$SQLinsert = "UPDATE pap SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_pap;";
	$resultado = pg_query($bdcon, $SQLinsert);
	$filas = pg_affected_rows($resultado);
	if ($filas > 0) {
		$msje   = "Se han guardado existosamente los cambios";
		echo(msje_js($msje));
		echo(js("window.location='principal.php?modulo=ver_postulante&id_pap=$id_pap';"));		
	}
}

$SQL_pap = "SELECT *,to_char(fec_nac, 'DD') AS fec_nac_dia,
                   to_char(fec_nac, 'MM') AS fec_nac_mes,
                   to_char(fec_nac, 'YYYY') AS fec_nac_ano,
                   trim(rut) AS rut
            FROM pap
            WHERE id='$id_pap';";

$pap = consulta_sql($SQL_pap);
$_REQUEST = array_merge($_REQUEST,$pap[0]);

$SEMESTRES_COHORTES = array(array("id"=>1,"nombre"=>1),
                            array("id"=>2,"nombre"=>2));
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<?php
	$forma = 'editar';
	include("formulario_postulante.php");
?>

<!-- Fin: <?php echo($modulo); ?> -->

