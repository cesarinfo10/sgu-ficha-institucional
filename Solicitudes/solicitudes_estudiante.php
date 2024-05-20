<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_alumno = $_REQUEST['id_alumno'];
if ($id_alumno == "") { $id_alumno = $_SESSION['id']; }

$SQL_solic = "SELECT s.id,ts.nombre AS tipo,to_char(s.fecha,'DD-tmMon-YYYY HH24:MI') AS fecha,s.estado,to_char(s.estado_fecha,'DD-tmMon-YYYY HH24:MI') AS estado_fecha,resp_obs,ts.alias AS alias_solic
              FROM gestion.solicitudes AS s
			  LEFT JOIN gestion.solic_tipos AS ts ON ts.id = s.id_tipo
			  WHERE id_alumno=$id_alumno
			  ORDER BY fecha DESC";
$solicitudes = consulta_sql($SQL_solic);

$tipos_solic = consulta_sql("SELECT alias FROM gestion.solic_tipos");
$SQL_ds = array();
for($x=0;$x<count($tipos_solic);$x++) {
	$SQL_detalle_solic = "(SELECT * FROM gestion.vista_{$tipos_solic[$x]['alias']} AS vds LEFT JOIN gestion.solicitudes AS s ON s.id=vds.id_solicitud WHERE s.id_alumno=$id_alumno)";
	$SQL_ds = array_merge($SQL_ds,array($SQL_detalle_solic));
}
$SQL_detalles_solic = implode(" UNION ",$SQL_ds);

$HTML_solic = "";
if (count($solicitudes) > 0) {
	
	for ($x=0;$x<count($solicitudes);$x++) {
		extract($solicitudes[$x]);
		
		$detalle_solic = consulta_sql(sql_datos_solic($alias_solic,$id));
		$detalle = nl2br($detalle_solic[0]['detalle']);

		$enl = "$enlbase_sm=solicitudes_ver&id_solic=$id&id_alumno=$id_alumno&tipo=$alias_solic";
		$enlace = "a class='enlitem' href='$enl'";
		
		$tipo = "<a href='$enl' class='enlaces' id='sgu_fancybox_medium'>$tipo</a>";

		$fecha = str_replace(" ","<br>",$fecha);
		$estado_fecha = str_replace(" ","<br>",$estado_fecha);

		$estado = "<div class='".str_replace(" ","",$estado)."'>$estado</div>";

		$HTML_solic .= "  <tr class='filaTabla'>\n"
				    . "    <td class='textoTabla' align='center'>$id</td>\n"
				    . "    <td class='textoTabla'>$tipo</td>\n"
				    . "    <td class='textoTabla' align='center'>$fecha</td>\n"
				    . "    <td class='textoTabla'><small>$detalle</small></td>\n"
				    . "    <td class='textoTabla' align='center'><div>$estado</div>$estado_fecha</td>\n"
				    . "    <td class='textoTabla'>$observaciones</td>\n"
					. "  </tr>\n";
	}
} else {
	$HTML_solic = "  <tr>"
				  . "    <td class='textoTabla' colspan='10'>"
				  . "      <br><br>** No hay solicitudes aún. Pincha en el botón «Nueva Solicitud» para iniciar una. **<br><br><br>"
				  . "    </td>\n"
				  . "  </tr>";
}             
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Solicitudes
</div>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Acciones:<br>
          <?php echo("<a href='$enlbase_sm=solicitudes_crear&id_alumno=$id_alumno' id='sgu_fancybox_medium' class='boton'>Nueva Solicitud</a>"); ?>
        </td>
      </tr>
    </table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>N°</td>
    <td class='tituloTabla'>Tipo</td>
    <td class='tituloTabla'>Fecha</td>
    <td class='tituloTabla'>Detalle</td>
    <td class='tituloTabla'>Estado</td>
    <td class='tituloTabla'>Observaciones</td>
  </tr>
  <?php echo($HTML_solic);?>
</table>
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'width'				: 1200,
		'height'			: 600,
		'afterClose'		: function () {  },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_medium").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 750,
		'maxHeight'			: 750,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 850,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { location.reload(true); },
		'type'				: 'iframe'
	});
});
</script>

<!-- Fin: <?php echo($modulo);  ?> -->

<?php

function sql_datos_solic($alias_solic,$id_solicitud) {
	return "SELECT * FROM gestion.vista_$alias_solic WHERE id_solicitud=$id_solicitud";
}

?>