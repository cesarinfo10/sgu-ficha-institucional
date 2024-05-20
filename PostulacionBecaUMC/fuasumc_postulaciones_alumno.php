<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

$id_alumno = $_REQUEST['id_alumno'];

$SQL_ing_gf = "SELECT sum(ing_liq_mensual_prom) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";
$SQL_cant_gf = "SELECT count(id) FROM dae.fuas_grupo_familiar WHERE id_fuas=fuas.id";

$SQL_fuas = "SELECT fuas.id,fuas.ano,a.id AS id_alumno,a.rut,a.nombres,apellidos,c.alias||'-'||a.jornada AS carrera,
	                semestre_cohorte||'-'||cohorte AS cohorte,a.mes_cohorte,fuas.estado,
	                fuas.email,fuas.telefono,fuas.tel_movil,ne.nombre AS nivel_educ,fuas.estado_civil,
	                CASE WHEN fuas.enfermo_cronico THEN 'Si' ELSE 'No' END AS enfermo_cronico,fuas.nombre_enfermedad,
                    fuas.pertenece_pueblo_orig,CASE WHEN fuas.acred_pert_pueblo_orig THEN 'Si' ELSE 'No' END AS acred_pert_pueblo_orig,
                    fuas.cat_ocupacional,CASE WHEN fuas.jefe_hogar THEN 'Si' ELSE 'No' END AS jefe_hogar,fuas.ing_liq_mensual_prom,
                    fuas.domicilio_grupo_fam,com.nombre AS comuna_grupo_fam,reg.nombre AS region_grupo_fam,tenencia_dom_grupo_fam,
                    round((coalesce(($SQL_ing_gf),0) + fuas.ing_liq_mensual_prom)/(($SQL_cant_gf) + 1),0) AS ingreso_percapita
             FROM dae.fuas
             LEFT JOIN alumnos            AS a   ON a.id=fuas.id_alumno
             LEFT JOIN carreras           AS c   ON c.id=a.carrera_actual
             LEFT JOIN comunas            AS com ON com.id=fuas.comuna_grupo_fam
             LEFT JOIN regiones           AS reg ON reg.id=fuas.region_grupo_fam
             LEFT JOIN dae.nivel_estudios AS ne  ON ne.id=nivel_educ
             WHERE id_alumno = $id_alumno
             ORDER BY fuas.fecha_creacion DESC ";
$fuas = consulta_sql($SQL_fuas);

$HTML_fuas = "";
if (count($fuas) > 0) {
	for ($x=0;$x<count($fuas);$x++) {
		extract($fuas[$x]);
		
		$enl = "$enlbase=fuasumc_ver&id_fuas=$id&id_alumno=$id_alumno";
		$enlace = "a class='enlitem' href='$enl'";
		
		if ($moroso_financiero == "t") { $estado .= " <sup>(M)</sup>"; }
		
		if ($mes_cohorte <> "") { $mes_cohorte = "(".substr($meses_palabra[$mes_cohorte-1]['nombre'],0,3).")"; }
		
		$ingreso_percapita = number_format($ingreso_percapita,0,',','.');
		$HTML_fuas .= "  <tr class='filaTabla' onClick=\"window.location='$enl';\">\n"
				   . "    <td class='textoTabla' align='center'>$id<br>[$ano]</td>\n"
				   . "    <td class='textoTabla'>$estado</td>\n"
				   . "    <td class='textoTabla'>$enfermo_cronico<br>$nombre_enfermedad</td>\n"
				   . "    <td class='textoTabla' align='right'>$$ingreso_percapita</td>\n"
//				   . "    <td class='textoTabla' align='center'>$prom_notas</td>\n"
				   . "    <td class='textoTabla' align='center'>$puntaje_notas</td>\n"
				   . "    <td class='textoTabla' align='center'>$puntaje_socioeconomico</td>\n"
				   . "    <td class='textoTabla' align='center'>$puntaje_sit_financiera</td>\n"
				   . "    <td class='textoTabla' align='center'>$puntaje_comp_cervantino</td>\n"
				   . "    <td class='textoTabla' align='center'>$puntaje_total</td>\n"
					. "  </tr>\n";
	}
} else {
	$HTML_fuas = "  <tr>"
				  . "    <td class='textoTabla' colspan='10'>"
				  . "      No hay postulaciones aún. Pincha en el botón «Nueva Postulación»"
				  . "    </td>\n"
				  . "  </tr>";
}             
?>
<!-- Inicio: <?php echo($modulo); ?> -->

<div class="tituloModulo">
  Postulación Beca UMC
</div>
    <table cellpadding="1" border="0" cellspacing="2" width="auto">
      <tr>
        <td class="celdaFiltro">
          Acciones:<br>
          <?php echo("<a href='$enlbase_sm=fuasumc_crear&id_alumno=$id_alumno' id='sgu_fancybox_medium' class='boton'>Nueva Postulación</a>"); ?>
        </td>
      </tr>
    </table>
<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' rowspan='2'>N°<br>[Año]</td>
    <td class='tituloTabla' rowspan='2'>Estado</td>
    <td class='tituloTabla' rowspan='2'><small>Enf. Crónico<br>Enfermedad</small></td>
    <td class='tituloTabla' rowspan='2'><small>Ingreso<br>Percápita</small></td>
<!--    <td class='tituloTabla' rowspan='2'><small>Promedio<br>de Notas</small></td> -->
    <td class='tituloTabla' colspan="5"><small>Puntaje Obtenido</small></td>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'><small><small>Notas</small></small></td>
    <td class='tituloTabla'><small><small>Socio<br>Económico</small></small></td>
    <td class='tituloTabla'><small><small>Situación<br>Financiera</small></small></td>
    <td class='tituloTabla'><small><small>Compromiso<br>Cervantino</small></small></td>
    <td class='tituloTabla'><small><small>TOTAL</small></small></td>
  </tr>
  <?php echo($HTML_fuas); ?>
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

<!-- Fin: <?php echo($modulo); ?> -->
