<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

setlocale(LC_MONETARY,"es_CL.UTF8");
if (isset($_SESSION['tipo'])) { 
	include("validar_modulo.php"); 
} else { 
	$datos_modulo = modulos($modulo); 
	$nombre_modulo = $datos_modulo['nombre'];
}

$id_fuas = $_REQUEST['id_fuas'];
$forma = 'editar';

if ($_REQUEST['editar'] == "Guardar") {
	$_REQUEST['ing_liq_mensual_prom'] = str_replace(".","",$_REQUEST['ing_liq_mensual_prom']);
	$aCampos = array('email','telefono','tel_movil','nivel_educ','estado_civil',
	                 'enfermo_cronico','nombre_enfermedad','pertenece_pueblo_orig','acred_pert_pueblo_orig',
	                 'cat_ocupacional','jefe_hogar','ing_liq_mensual_prom',
	                 'domicilio_grupo_fam','comuna_grupo_fam','region_grupo_fam','tenencia_dom_grupo_fam');
	$SQLupd = "UPDATE dae.fuas SET " . arr2sqlupdate($_REQUEST,$aCampos) . " WHERE id=$id_fuas;";
	//echo($SQLinsert);
	if (consulta_dml($SQLupd) == 1) {
		echo(msje_js("Se ha guardado con éxito el formulario inicial.\\n\\n"
		            ."No olvide que debe completar la información de Integrantes del "
		            ."Grupo Familiar, subir los respaldos de Ingresos "
		            ."(certificados de cotizaciones, certificado de censantía, "
		            ."carpeta tributaria, etc) para luego finalmente informar y "
		            ."presentar la postulación"));
		$fuas = consulta_sql("SELECT id FROM dae.fuas WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA");
	} else {
		echo(msje_js("ERROR: El formulario no pudo guardarse.\\n\\n"
		            ."Intente nuevamnete o Comuníquese con la DAE"));
	}
	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

$SQL_fuas = "SELECT fuas.*,
                    a.id AS id_alumno,rut,nombres,apellidos,c.nombre AS carrera,
	                CASE a.jornada WHEN 'D' THEN 'Diurna' WHEN 'V' THEN 'Vespertina' END AS jornada,
	                semestre_cohorte||'-'||cohorte AS cohorte 
	         FROM dae.fuas 
	         LEFT JOIN alumnos  AS a ON a.id=fuas.id_alumno
             LEFT JOIN carreras AS c ON c.id=a.carrera_actual
	         WHERE fuas.id=$id_fuas";
$fuas = consulta_sql($SQL_fuas);

if (count($fuas) == 1) {
	$_REQUEST = array_merge($_REQUEST,$fuas[0]);
}
?>
<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<div style="margin-top: 5px">
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="get">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_fuas' value='<?php echo($id_fuas); ?>'>
<input type='hidden' name='forma' value='<?php echo($forma); ?>'>
<?php if (!empty($id_fuas)) { include_once("fuasumc_formulario.php"); } ?>
</form>
</div>
