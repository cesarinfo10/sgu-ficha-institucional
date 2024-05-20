<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
}

include("validar_modulo.php");

$id_pap = $_REQUEST['id_pap'];
if (!is_numeric($id_pap)) {
	echo(js("location.href='principal.php?modulo=gestion_postulantes';"));
	exit;
}

$mod_ant = $_SERVER['HTTP_REFERER'];
if ($mod_ant == "") { $mod_ant = "$enlbase=gestion_postulantes"; }

$ficha = $_REQUEST['ficha'];
if ($ficha == "") { $ficha = "postulante_ficha_datos_personales"; }

$fichas[0]['nombre'] = "Antecedentes Personales<br>y de la Postulación";
$fichas[0]['enlace'] = "postulante_ficha_datos_personales";
$fichas[1]['nombre'] = "Antecedentes<br>Escolares/Universitarios";
$fichas[1]['enlace'] = "postulante_ficha_datos_escolares_instedsup";
$fichas[2]['nombre'] = "Control<br>Interno";
$fichas[2]['enlace'] = "postulante_ficha_control_interno";

$HTML_botones_ficha = "";
for($x=0;$x<count($fichas);$x++) {
	$boton_ficha = $fichas[$x]['nombre'];
	$estilo_boton = "background: #DEF1FF";
	if ($fichas[$x]['enlace'] <> $ficha) {
		$enlace_ficha = "$enlbase=$modulo&id_pap=$id_pap&ficha=".$fichas[$x]['enlace'];
		$boton_ficha  = "<a class='enlaces' href='$enlace_ficha'>$boton_ficha</a>";
		$estilo_boton = "";
	}
	$HTML_botones_ficha .= "<td class='tituloTabla' style='$estilo_boton'>$boton_ficha</td>";
}       
$HTML_botones_ficha .= "<td></td>";

$SQL_pa_ext = "SELECT asignatura,inst_edsup,alias,semestre,ano,duracion,nota_final
			   FROM vista_convalidaciones
			   WHERE id_pap=$id_pap";
$pa_ext     = consulta_sql($SQL_pa_ext);

$comentarios = "";
$postulante = consulta_sql("SELECT rut,comentarios FROM pap WHERE id=$id_pap");
if ($postulante[0]['comentarios'] <> "") { $comentarios = "<span style='color: #FF0000'><b>(".substr_count($postulante[0]['comentarios'],"*****").")</b></span>"; }
$rut=trim($postulante[0]['rut']);

$id_foto = "";
$SQL_foto = "SELECT dd.id FROM doctos_digitalizados dd LEFT JOIN doctos_digital_tipos ddt ON dd.id_tipo=ddt.id WHERE rut='$rut' AND ddt.alias='fotos' AND NOT eliminado";
$foto = consulta_sql($SQL_foto);
if (count($foto) > 0) { $id_foto = $foto[0]['id']; }

verif_estado_carpeta_doctos($rut);
?>

<!-- Inicio: <?php echo($modulo); ?> -->
<div class="tituloModulo">
  	<?php echo($nombre_modulo); ?>
</div><br>

<table cellpadding="4" cellspacing="0" border="0" class="tabla">
  <tr class='filaTituloTabla'>
    <td class='tituloTabla'>Acciones:</td>
    <td class='tituloTabla'>Gestión de Recepción:</td>
    <td class='tituloTabla'>Contratos y otros doctos.:</td>
  </tr>
  <tr>
    <td class='textoTabla' align='center'>
<?php
	echo("<a href='$enlbase=postulante_editar&id_pap=$id_pap' class='boton'>Editar</a> "
	    ."<a href='postulante_imprimir_ficha_datos_personales.php?id_pap=$id_pap&rut=$rut' class='boton' target='_blank'>Imprimir</a> "
	    ."<a href='$enlbase=postulante_matricular&id_pap=$id_pap' class='boton'>Matricular</a> " 
	    ."<a id='sgu_fancybox' href='$enlbase_sm=postulante_arancel&id_pap=$id_pap' class='boton'>Arancel</a> " 
	    ."<a id='sgu_fancybox' href='$enlbase_sm=postulante_comentarios&id_postulante=$id_pap' class='boton'>Comentarios $comentarios</a> " 
	    ."<a href='$mod_ant' class='boton'>Volver</a> ");
?>
    </td>
    <td class='textoTabla' align='center'>
      <?php echo("<a id='sgu_fancybox' href='$enlbase_sm=doctos_digitalizados&rut=$rut' class='boton'>Documentación digitalizada</a>"); ?>

<?php
	//if ($_SESSION['tipo'] == 0) {    
		echo("<a id='sgu_fancybox' href='$enlbase_sm=registrar_prog_asig_externo&id_pap=$id_pap' class='boton'>Registrar Programa de Asignatura Externo</a>");
	//}
?>
    </td>
    <td class='textoTabla' align='center'>
      <?php echo("<a id='sgu_fancybox' href='$enlbase_sm=postulante_doctos_matricula&id_pap=$id_pap' class='boton'>Documentos de Matrícula</a>"); ?>
    </td>
  </tr>
</table>
<br>
<table cellpadding="0" cellspacing="0" border="0" class="tabla">
  <tr style="padding: 5px">
    <?php echo($HTML_botones_ficha); ?>
  </tr>
  <tr class='filaTituloTabla'>
    <td class='tituloTabla' colspan="<?php echo(count($fichas)); ?>">
      <?php include("$ficha.php"); ?>
		<?php if (count($pa_ext)>0) { ?>
		<br>
		<table bgcolor="#ffffff" cellspacing="1" cellpadding="2" class="tabla">
		  <tr class='filaTituloTabla'>
			<td class="tituloTabla" colspan="5">Programas de Asignaturas externos registrados (<?php echo(count($pa_ext)); ?>)</td>
		  </tr>
		  <tr class='filaTituloTabla'>
			<td class="tituloTabla">Asignatura</td>
			<td class="tituloTabla">Inst. Ed. Sup. Origen</td>
			<td class="tituloTabla">Año</td>
			<td class="tituloTabla">Duración</td>
			<td class="tituloTabla">NF</td>
		  </tr>
			<?php
				$HTML = "";
				for ($x=0;$x<count($pa_ext);$x++) {
					extract($pa_ext[$x]);
					$HTML .= "<tr>"
						  .  "  <td class='textoTabla'>$asignatura</td>"
						  .  "  <td class='textoTabla'><a title='$inst_edsup'>$alias</a></td>"
						  .  "  <td class='textoTabla'>$ano</td>"
						  .  "  <td class='textoTabla'>$duracion</td>"
						  .  "  <td class='textoTabla'>$nota_final</td>"
						  .  "</tr>";
				}
				echo($HTML);
			?>
		</table>
		<?php } ?>
    </td>
    <td valign='top'>
      <?php if ($id_foto <> "") { ?>
        <a href="doctos_digitalizados_ver.php?id=<?php echo($id_foto); ?>"><img align="right" src="doctos_digitalizados_ver.php?id=<?php echo($id_foto); ?>" width="200"></a>
      <?php } ?>
    </td>
  </tr>
</table>

<!-- Fin: <?php echo($modulo); ?> -->
<script type="text/javascript">
$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 700,
		'maxHeight'		: 550,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_big").fancybox({
		'autoScale'		: false,
		'autoDimensions': false,
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic',
		'width'			: 1000,
		'maxHeight'		: 700,
		'afterClose'	: function () { location.reload(true); },
		'type'			: 'iframe'
	});
});
</script>
