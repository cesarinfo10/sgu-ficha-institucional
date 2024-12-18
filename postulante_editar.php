<?php

include("validar_modulo.php");

$id_pap = $_REQUEST['id_pap'];
if (!is_numeric($id_pap) || !$_SESSION['autentificado']) {
	header("Location: index.php?modulo=gestion_postulantes");
	exit;
};

$aCampos = array("rut","nombres","apellidos","genero","est_civil",
                 "profesion","nacionalidad","pasaporte","direccion","comuna",
                 "region","telefono","tel_movil","email","referencia",
                 "admision","admision_subtipo","semestre_cohorte","cohorte","carrera1_post","jornada1_post","carrera2_post","jornada2_post",
                 "carrera3_post","jornada3_post","cert_nacimiento","fotografias","conc_notas_em",
                 "boletin_psu","copia_ced_iden","licencia_em","rbd_colegio","ano_egreso_col",
                 "promedio_col","ano_psu","puntaje_psu","id_inst_edsup_proced","carr_ies_pro",
                 "prom_nt_ies_pro","conc_nt_ies_pro","prog_as_ies_pro","fec_nac","referencia_comentarios",
                 "licencia_em_comp_solic","conc_notas_em_comp_solic",
                 "carrera4_post","jornada4_post","carrera5_post","jornada5_post",
                 "carrera6_post","jornada6_post","regimen","id_origen_bd");

$aRequeridos = array(0,1,2,3,4,5,6,8,9,10,11,12,14,15,16,17);
$requeridos  = requeridos($aRequeridos,$aCampos);
$requeridos .= ",'fec_nac_dia','fec_nac_mes','fec_nac_ano'";

if ($_REQUEST['editar'] == "Guardar") {
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

if ($_REQUEST['editar'] == "Guardar") {
	$SQLupdate = "UPDATE pap SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_pap;";
	if (consulta_dml($SQLupdate) > 0) {
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


?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>
<?php
	$forma = 'editar';
	$carreras       = consulta_sql("SELECT id,nombre||' ('||alias||')' AS nombre FROM carreras ORDER BY nombre;");

	include("postulante_formulario.php");
?>

<!-- Fin: <?php echo($modulo); ?> -->

