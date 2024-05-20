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
	if (time() > $FEC_FIN_POSTBECAUMC && $FEC_INI_POSTBECAUMC < time()) {
		echo(msje_js("Postulaciones para proceso de Matrículas $ANO_MATRICULA está cerrado."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	}
}

$id_alumno = $_REQUEST['id_alumno'];
$rut       = $_REQUEST['rut'];

if ($_REQUEST['crear'] == "Guardar") {
	$_REQUEST['ing_liq_mensual_prom'] = str_replace(".","",$_REQUEST['ing_liq_mensual_prom']);
	$aCampos = array('id_alumno','ano','email','telefono','tel_movil','nivel_educ','estado_civil',
	                 'enfermo_cronico','nombre_enfermedad','pertenece_pueblo_orig','acred_pert_pueblo_orig',
	                 'cat_ocupacional','jefe_hogar','ing_liq_mensual_prom',
	                 'domicilio_grupo_fam','comuna_grupo_fam','region_grupo_fam','tenencia_dom_grupo_fam');
	$SQLinsert = "INSERT INTO dae.fuas " . arr2sqlinsert($_REQUEST,$aCampos);
	//echo($SQLinsert);
	if (consulta_dml($SQLinsert) == 1) {
		echo(msje_js("Se ha guardado con éxito el formulario inicial.\\n\\n"
		            ."Se debe completar la información de Integrantes del "
		            ."Grupo Familiar, subir los respaldos de Ingresos "
		            ."(certificados de cotizaciones, certificado de censantía, "
		            ."carpeta tributaria, etc) para luego finalmente informar y "
		            ."presentar la postulación"));
		$fuas = consulta_sql("SELECT id FROM dae.fuas WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA");
		$id_fuas = $fuas[0]['id'];
		echo(js("window.location='$enlbase_sm=fuasumc_ver&id_fuas=$id_fuas&id_alumno=$id_alumno'"));
	} else {
		echo(msje_js("ERROR: El formulario no pudo guardarse.\\n\\n"
		            ."Es posible que ya tenga una postulación presentada.\\n\\n"
		            ."Comuníquese con la DAE"));
		echo(js("parent.jQuery.fancybox.close()"));
	}
	exit;
}

$SQL_alumno = "SELECT a.id AS id_alumno,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                      a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ae.nombre AS estado,a.id_pap,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN al_estados AS ae ON ae.id=a.estado
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) ";

$contratos = consulta_sql("SELECT id FROM finanzas.contratos WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA AND estado IS NOT NULL");

if (count($contratos) > 0) {
	echo(msje_js("ERROR: Ya tienes un contrato emitido para el proceso de Matrículas $ANO_MATRICULA.\\n\\n"
	            ."No puedes iniciar una postulación a Beca UMC.\\n\\n"
				."Comuníquese con la DAE"));

	echo(js("parent.jQuery.fancybox.close()"));
	exit;
}

if (!empty($id_alumno))                { $SQL_alumno .= "WHERE a.id=$id_alumno"; }
if (!empty($rut) && empty($id_alumno)) { $SQL_alumno .= "WHERE a.rut='$rut'"; }
//echo($SQL_alumno);
if (!empty($id_alumno) || !empty($rut)) { 
	$alumno = consulta_sql($SQL_alumno);
	if (count($alumno) == 0) {
		echo(msje_js("ERROR: El RUT ingresado no corresponde a un alumno."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	} elseif (count($alumno) > 1) {
		$HTML_alumnos = "";
		for ($x=0;$x<count($alumno);$x++) {
			extract($alumno[$x]);
			
			$enl = "$enlbase_sm=$modulo&id_alumno=$id_alumno&rut=$rut";
			$nombre = "<a class='enlitem' href='$enl'>$nombre</a>";
			
			if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
			if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
			
			$HTML_alumnos .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
						   . "    <td class='textoTabla'>$id_alumno</td>\n"
						   . "    <td class='textoTabla'>$rut</td>\n"
						   . "    <td class='textoTabla'>$nombre</td>\n"
						   . "    <td class='textoTabla'>$carrera</td>\n"
						   . "    <td class='textoTabla'>$regimen</td>\n"
						   . "    <td class='textoTabla'>$cohorte $mes_cohorte</td>\n"
						   . "    <td class='textoTabla'>$estado</td>\n"
						   . "    <td class='textoTabla'>$matriculado</td>\n"
						   . "  </tr>\n";
		}
		$id_alumno = null;
	} elseif (count($alumno) == 1) {
		$id_alumno = $alumno[0]['id_alumno'];
		$fuas = consulta_sql("SELECT id FROM dae.fuas WHERE id_alumno=$id_alumno AND ano=$ANO_MATRICULA");
		if (count($fuas) > 0) {
			echo(msje_js("ATENCIÖN: Ya existe una postulación registrada para este alumno.\\n\\n"
			            ."Pinche en «Aceptar» para revisarla."));
			$id_fuas = $fuas[0]['id'];
			echo(js("window.location='$enlbase_sm=fuasumc_ver&id_fuas=$id_fuas&id_alumno=$id_alumno'"));
			exit;
		}
	}			
}

?>
<div class="tituloModulo">
  Generar Postulación a Beca UMC
</div>
<div style="margin-top: 5px">
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="get">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<input type='hidden' name='ano' value='<?php echo($ANO_MATRICULA); ?>'>
<input type='hidden' name='rut' value='<?php echo($rut); ?>'>
<input type='hidden' name='forma' value='crear'>
<?php if (empty($rut)) { include_once("ingresar_rut.php"); } ?>
<?php if (!empty($id_alumno)) { include_once("fuasumc_formulario.php"); } ?>
</form>
</div>

<?php if (count($alumno) > 1) { ?>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'><td class='tituloTabla' colspan='8'>Seleccionar alumno</td></tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>ID</td>
    <td class='tituloTabla'>RUT</td>
    <td class='tituloTabla'>Nombre</td>
    <td class='tituloTabla'>Carrera</td>
    <td class='tituloTabla'>Regimen</td>
    <td class='tituloTabla'>Cohorte</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Mat?</td>
  </tr>
  <?php echo($HTML_alumnos); ?>
</table>
<?php } ?>
