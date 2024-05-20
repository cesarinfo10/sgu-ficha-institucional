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
	if (time() > $FEC_FIN_SOLICITUDES && $FEC_INI_SOLICITUDES < time()) {
		echo(msje_js("En este momento no podemos atender tu solicitud."));
		echo(js("parent.jQuery.fancybox.close()"));
		exit;
	}
}

$id_alumno  = $_REQUEST['id_alumno'];
$tipo_solic = $_REQUEST['tipo_solic'];
$rut        = $_REQUEST['rut'];

$SQL_alumno = "SELECT a.id AS id_alumno,trim(a.rut) AS rut,upper(a.apellidos)||' '||initcap(a.nombres) AS nombre,c.alias||'-'||a.jornada AS carrera,
                      a.semestre_cohorte||'-'||a.cohorte AS cohorte,a.mes_cohorte,ae.nombre AS estado,a.id_pap,
                      CASE WHEN m.id_alumno IS NOT NULL THEN 'Si' ELSE 'No' END AS matriculado,moroso_financiero
               FROM alumnos AS a
               LEFT JOIN carreras AS c ON c.id=a.carrera_actual
               LEFT JOIN al_estados AS ae ON ae.id=a.estado
               LEFT JOIN matriculas AS m ON (m.id_alumno=a.id AND semestre=$SEMESTRE AND ano=$ANO) ";

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
		$tipos_solic       = consulta_sql("SELECT id,nombre FROM gestion.solic_tipos WHERE alias='$tipo_solic'");
		$id_tipo_solic     = $tipos_solic[0]['id'];
		$nombre_tipo_solic = $tipos_solic[0]['nombre'];
		$solic = consulta_sql("SELECT id,estado FROM gestion.solicitudes WHERE id_alumno=$id_alumno AND id_tipo=$id_tipo_solic AND estado IN ('En preparación','Presentada','Pendiente')");
		if (count($solic) > 0) {
			switch ($solic[0]['estado']) {
				case "En preparación":
					$msje = "ATENCIÓN: Tiene actualmente una solicitud en Preparación, la que debe presentar "
					      . "y esperar la respuesta.\\n\\n"
					      . "Pinche en «Aceptar» para revisarla y presentarla."; 
					break;
				case "Pendiente":	
			  	case "Presentada":
					$msje = "ATENCIÓN: Ya existe una solicitud registrada que se encuentra en estado de {$solic[0]['estado']}.\\n\\n"
					      . "Debe esperar respuesta de esta, para comenzar con otra solicitud del mismo tipo.\\n\\n"
					      . "Pinche en «Aceptar» para revisarla.";
			}
			echo(msje_js($msje));
			$id_solic = $solic[0]['id'];
			echo(js("window.location='$enlbase_sm=solicitudes_ver&id_solic=$id_solic&id_alumno=$id_alumno&tipo=$tipo_solic';"));
			exit;
		}
	}
}

$SOLIC_TIPOS = consulta_sql("SELECT alias AS id,nombre FROM gestion.solic_tipos WHERE activo AND now()::date>=fecha");

?>
<div class="tituloModulo">
  Generar Nueva Solicitud
</div>
<div style="margin-top: 5px">
<form name='formulario' action='<?php echo($_SERVER['SCRIPT_NAME']); ?>' method="get" id="form1">
<input type='hidden' name='modulo' value='<?php echo($modulo); ?>'>
<input type='hidden' name='id_alumno' value='<?php echo($id_alumno); ?>'>
<input type='hidden' name='tipo_solic' value='<?php echo($tipo_solic); ?>'>
<input type='hidden' name='forma' value='crear'>
<?php if (empty($tipo_solic)) { ?>
<table cellpadding="2" height='auto' cellspacing="1" border="0" bgcolor="#ffffff" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class='celdaNombreAttr'>Tipo:</td>
    <td class='celdaValorAttr'>
	  <select name='tipo_solic' class='filtro' onChange="submitform();">
	    <option>-- Seleccione --</option>
		<?php echo(select($SOLIC_TIPOS,$tipo_solic)); ?>
	  </select>
    </td>
  </tr>
</table>
<?php } else { include_once("$tipo_solic.php"); } ?>
</form>
</div>