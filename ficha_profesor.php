<?php

if (!$_SESSION['autentificado']) {
	header("Location: index.php");
	exit;
};

include("validar_modulo.php");

if ($_SESSION['tipo'] <> 3) {
	echo(msje_js("Este módulo sólo lo pueden ejecutar usuarios de tipo 'Profesor'."));
	exit;
}

$id_profesor = $_SESSION['id_usuario'];
?>

<div class="tituloModulo">
  <?php echo($nombre_modulo); ?>
</div>
<table cellpadding="4" cellspacing="0" border="0" class="tabla" style='margin-top: 5px'>
  <tr>
    <td class="celdaFiltro" style="vertical-align: middle;">
      Acciones:<br>
      <?php
			//echo("<a href='$enlbase_sm=editar_profesor_datos_contacto&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Editar Antecedentes de Contacto</a> ");
			echo("<a href='$enlbase_sm=editar_profesor_datos_pago&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Actualizar Antecedentes de Pago</a> ");
			echo("<a href='$enlbase_sm=gestion_ev_docente_profes&id_profesor=$id_profesor' class='boton' id='sgu_fancybox'>Evaluación Docente</a> ");
      ?>
    </td>
  </tr>
</table>
<?php
include("ver_profesor.php");
//echo(js("location.href='$enlbase=ver_profesor&id_profesor=$id_profesor';"));
exit;
?>
<script type="text/javascript">

$(document).ready(function(){
	$("#sgu_fancybox").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 900,
		'height'			: 800,
		'maxHeight'			: 800,
		'afterClose'		: function () { location.reload(false); },
		'type'				: 'iframe'
	});
});

$(document).ready(function(){
	$("#sgu_fancybox_small").fancybox({
		'autoScale'			: false,
		'autoDimensions'	: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'width'				: 700,
		'height'			: 400,
		'maxHeight'			: 400,
		'afterClose'		: function () { },
		'type'				: 'iframe'
	});
});
</script>
